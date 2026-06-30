<?php

namespace App\Services\Messaging;

use App\Jobs\SendCampaignJob;
use App\Models\Business;
use App\Models\Campaign;
use App\Models\MessagingChannel;
use App\Models\Redemption;
use App\Models\Subscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Registry + facade over the three delivery channels. Resolves a channel by
 * key, exposes connection state for the studio, and records every send to the
 * campaigns log so the CRM has a single history across email, SMS and push.
 *
 * ---------------------------------------------------------------------------
 * CONTACT-ANONYMISATION CONTRACT (enforced on the sending side)
 * ---------------------------------------------------------------------------
 * MessagingService is the ONLY sanctioned route from a retailer to a customer.
 * Retailers never hold, export, or type raw customer email/mobile - they pick a
 * customer by id or pick a segment, and the actual address is resolved here,
 * server-side, from our own stored Redemption / Subscription data.
 *
 * Practically this means retailer-facing send flows MUST pass either:
 *   - an audience SPEC (channel + brand + segment) to resolveAudience(), which
 *     reads addresses out of stored records the retailer never sees; or
 *   - a list of customer IDs to recipientsFromCustomerIds(); never raw
 *     addresses lifted from a retailer-supplied field.
 *
 * The two trusted exceptions are the platform's own "send a test to MY address"
 * action and platform-operator broadcasts - both originate server-side from a
 * verified owner, not from an exported contact list. dispatch() still applies
 * consent filtering to whatever it is handed, so a leaked address cannot be
 * messaged once that customer has opted out.
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
     * Resolve an audience SPEC into deliverable recipients, entirely server-side,
     * from our own stored Redemption records. This is the anonymisation-safe way
     * for a retailer flow to reach customers: the caller names a channel, a brand
     * and a segment - never an address - and the addresses are read out here.
     *
     * Recipients carry an opaque 'customer_id' (the redemption id) plus the name,
     * so downstream UI can show "who" without ever surfacing the raw address.
     * consent is still applied later by dispatch().
     *
     * @param  string  $channelKey  'email' | 'sms'
     * @param  'all'|'opted_in'  $segment  audience segment within the brand
     * @return Collection<int,array{customer_id:int,email?:string,phone?:string,name:string}>
     */
    public function resolveAudience(string $channelKey, ?Business $brand = null, string $segment = 'opted_in'): Collection
    {
        $query = Redemption::query();

        if ($brand) {
            $query->whereHas('offer', fn ($o) => $o->where('business_id', $brand->id));
        }

        if ($channelKey === 'sms') {
            $query->whereNotNull('customer_phone');
            if ($segment === 'opted_in') {
                $query->where('sms_opt_in', true);
            }
        } else { // email
            $query->whereNotNull('customer_email');
            if ($segment === 'opted_in') {
                $query->where('marketing_opt_in', true);
            }
        }

        return $query->get(['id', 'customer_name', 'customer_email', 'customer_phone'])
            ->map(fn (Redemption $r) => array_filter([
                'customer_id' => $r->id,
                'name' => $r->customer_name ?: 'Customer',
                // Email is kept even on SMS sends so the consent gate can drop
                // anyone who unsubscribed from SMS alerts (keyed on email).
                'email' => $r->customer_email,
                'phone' => $channelKey === 'sms' ? $r->customer_phone : null,
            ], fn ($v) => $v !== null && $v !== ''))
            ->unique(fn ($r) => $r['email'] ?? $r['phone'] ?? $r['customer_id'])
            ->values();
    }

    /**
     * Resolve a list of opaque customer (redemption) IDs into deliverable
     * recipients server-side. Use this when a retailer flow lets a user tick
     * specific customers: the UI passes IDs, NOT addresses, and we look the
     * address up here so it never leaves our system in retailer-facing payloads.
     *
     * @param  array<int,int>  $customerIds  redemption ids
     * @return Collection<int,array{customer_id:int,email?:string,phone?:string,name:string}>
     */
    public function recipientsFromCustomerIds(string $channelKey, array $customerIds): Collection
    {
        if (empty($customerIds)) {
            return collect();
        }

        return Redemption::whereIn('id', $customerIds)
            ->get(['id', 'customer_name', 'customer_email', 'customer_phone'])
            ->map(fn (Redemption $r) => array_filter([
                'customer_id' => $r->id,
                'name' => $r->customer_name ?: 'Customer',
                'email' => $r->customer_email,
                'phone' => $channelKey === 'sms' ? $r->customer_phone : null,
            ], fn ($v) => $v !== null && $v !== ''))
            ->values();
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
