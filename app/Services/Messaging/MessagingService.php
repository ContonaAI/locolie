<?php

namespace App\Services\Messaging;

use App\Jobs\SendCampaignJob;
use App\Models\Business;
use App\Models\Campaign;
use App\Models\MessagingChannel;
use App\Models\Subscription;
use Illuminate\Support\Carbon;

/**
 * Registry + facade over the three delivery channels. Resolves a channel by
 * key, exposes connection state for the studio, and records every send to the
 * campaigns log so the CRM has a single history across email, SMS and push.
 */
class MessagingService
{
    /** @var array<string,Channel> */
    protected array $channels = [];

    public function __construct(EmailChannel $email, SmsChannel $sms, PushChannel $push)
    {
        foreach ([$email, $sms, $push] as $channel) {
            $this->channels[$channel->key()] = $channel;
        }
    }

    public function channel(string $key): Channel
    {
        return $this->channels[$key] ?? throw new \InvalidArgumentException("Unknown channel [$key]");
    }

    /** @return array<string,Channel> */
    public function all(): array
    {
        return $this->channels;
    }

    /** Connection state for every channel, for the studio dashboard. */
    public function overview(): array
    {
        return collect($this->channels)->map(fn (Channel $c) => [
            'key' => $c->key(),
            'label' => $c->label(),
            'connected' => $c->connected(),
            'status' => $c->status(),
            'provider' => $c->activeProvider(),
            'providers' => config("messaging.channels.{$c->key()}.providers", []),
        ])->all();
    }

    /** Default consent topic gated per channel (null = no email/phone consent gate). */
    public function defaultTopic(string $channelKey): ?string
    {
        return ['email' => 'offers', 'sms' => 'sms_alerts', 'push' => null][$channelKey] ?? null;
    }

    /**
     * Send through a channel and log a Campaign row.
     *
     * Always applies consent (anyone who unsubscribed from the relevant topic is
     * dropped), records the campaign first (so emails can carry tracking + a
     * per-recipient unsubscribe link), and sends now - unless the send is large
     * or scheduled, in which case it is queued so the request never blocks.
     *
     * @param  array  $message  channel-specific payload (subject/body/title/...)
     * @param  array  $options  topic, queue (bool), scheduled_at (Carbon|string|null)
     */
    public function dispatch(string $channelKey, array $message, iterable $recipients, ?Business $brand = null, array $options = []): SendResult
    {
        $topic = $options['topic'] ?? $this->defaultTopic($channelKey);
        $message['_topic'] = $topic;
        $list = $this->applyConsent($topic, $recipients);
        $count = count($list);

        $scheduledAt = ! empty($options['scheduled_at']) ? Carbon::parse($options['scheduled_at']) : null;
        if ($scheduledAt && $scheduledAt->isPast()) {
            $scheduledAt = null; // a past time means "send now"
        }
        $queue = ($options['queue'] ?? false) || $scheduledAt !== null || $count > 50;

        $campaign = Campaign::create([
            'business_id' => $brand?->id,
            'channel' => $channelKey,
            'status' => $scheduledAt ? 'scheduled' : ($queue ? 'queued' : 'sending'),
            'template_id' => $message['template_id'] ?? null,
            'subject' => $message['subject'] ?? $message['title'] ?? null,
            'body' => $message['body'] ?? '',
            'sent_count' => 0,
            'scheduled_at' => $scheduledAt,
            'meta' => ['topic' => $topic, 'intended' => $count],
        ]);

        if ($queue) {
            $job = new SendCampaignJob($campaign->id, $channelKey, $message, $list, $brand?->id);
            dispatch($scheduledAt ? $job->delay($scheduledAt) : $job);

            $note = $scheduledAt
                ? "Scheduled for {$scheduledAt->format('j M, H:i')} - {$count} recipient(s)."
                : "Queued - sending to {$count} recipient(s) in the background.";

            return new SendResult($count, $scheduledAt ? 'scheduled' : 'queued', null, $note, ['campaign_id' => $campaign->id]);
        }

        return $this->deliver($campaign, $channelKey, $message, $list, $brand);
    }

    /**
     * Perform the actual send for a logged campaign and update its row. Shared by
     * the synchronous path and SendCampaignJob.
     */
    public function deliver(Campaign $campaign, string $channelKey, array $message, array $recipients, ?Business $brand = null): SendResult
    {
        $message['_campaign_id'] = $campaign->id;
        $result = $this->channel($channelKey)->send($message, $recipients, $brand);

        $campaign->update([
            'status' => $result->status,
            'provider' => $result->provider,
            'sent_count' => $result->sent,
            'meta' => array_merge($campaign->meta ?? [], $result->meta ?: []),
        ]);

        return $result;
    }

    /**
     * Drop anyone who has unsubscribed from this topic. Recipients identified
     * only by phone (no email) pass through - their opt-in was applied upstream.
     */
    protected function applyConsent(?string $topic, iterable $recipients): array
    {
        $list = collect($recipients)->map(fn ($r) => is_array($r) ? $r : ['email' => $r])->all();
        if (! $topic) {
            return array_values($list);
        }

        return array_values(array_filter($list, function ($r) use ($topic) {
            $email = $r['email'] ?? null;

            return $email ? Subscription::isSubscribed($email, $topic) : true;
        }));
    }

    /**
     * Record/refresh a connected provider. A "demo connect" (no real keys) is
     * stored with status 'connected' so the UI flips to live-looking, matching
     * the studio's demo-first philosophy.
     */
    public function connect(string $channel, string $provider, array $config = [], ?string $label = null): MessagingChannel
    {
        return MessagingChannel::updateOrCreate(
            ['channel' => $channel, 'provider' => $provider],
            [
                'label' => $label,
                'status' => 'connected',
                'config' => $config,
                'connected_at' => now(),
            ]
        );
    }

    public function disconnect(string $channel, string $provider): void
    {
        MessagingChannel::where('channel', $channel)->where('provider', $provider)->delete();
    }
}
