<?php

namespace App\Services\Messaging;

use App\Models\Business;
use App\Models\Campaign;
use App\Models\MessagingChannel;

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

    /**
     * Send through a channel and log a Campaign row.
     *
     * @param  array  $message  channel-specific payload (subject/body/title/...)
     */
    public function dispatch(string $channelKey, array $message, iterable $recipients, ?Business $brand = null): SendResult
    {
        $result = $this->channel($channelKey)->send($message, $recipients, $brand);

        Campaign::create([
            'business_id' => $brand?->id,
            'channel' => $channelKey,
            'status' => $result->status,
            'provider' => $result->provider,
            'template_id' => $message['template_id'] ?? null,
            'subject' => $message['subject'] ?? $message['title'] ?? null,
            'body' => $message['body'] ?? '',
            'sent_count' => $result->sent,
            'meta' => $result->meta ?: null,
        ]);

        return $result;
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
