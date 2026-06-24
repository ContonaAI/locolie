<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;

/**
 * A single (contact, topic) consent record — the source of truth for who may be
 * messaged on which topic. Subscribe / unsubscribe is per topic, or to all,
 * and is managed with no login via signed links (see SubscriptionController).
 */
class Subscription extends Model
{
    protected $fillable = [
        'user_id', 'email', 'phone', 'topic', 'channel', 'status',
        'source', 'consented_at', 'unsubscribed_at', 'ip_address', 'meta',
    ];

    protected $casts = [
        'consented_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'meta' => 'array',
    ];

    /**
     * The topics a person can subscribe to. `channel` decides which contact
     * detail it needs (email vs phone); `default` is the state for a brand-new
     * contact who hasn't expressed a preference yet.
     */
    public const TOPICS = [
        'offers' => [
            'label' => 'Offers & discounts near you',
            'description' => 'New deals from independent shops, pubs and makers on your high street.',
            'channel' => 'email',
            'default' => true,
        ],
        'product_updates' => [
            'label' => 'Product & feature updates',
            'description' => 'locolie news, new areas going live and improvements to the app.',
            'channel' => 'email',
            'default' => true,
        ],
        'sms_alerts' => [
            'label' => 'SMS text alerts',
            'description' => 'Time-sensitive offers by text. Standard message rates may apply.',
            'channel' => 'sms',
            'default' => true,
        ],
        'business_updates' => [
            'label' => 'Business owner emails',
            'description' => 'Tips, plan and billing notices for businesses listed on locolie.',
            'channel' => 'email',
            'default' => true,
        ],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function topicLabel(string $topic): string
    {
        return self::TOPICS[$topic]['label'] ?? ucfirst(str_replace('_', ' ', $topic));
    }

    public static function channelFor(string $topic): string
    {
        return self::TOPICS[$topic]['channel'] ?? 'email';
    }

    /**
     * Set a contact's state for one topic, recording the consent in the audit
     * log. Idempotent — keyed on (email, topic).
     */
    public static function setTopic(string $email, string $topic, bool $subscribed, array $attrs = []): self
    {
        $email = strtolower(trim($email));
        $channel = self::channelFor($topic);

        $sub = self::updateOrCreate(
            ['email' => $email, 'topic' => $topic],
            array_filter([
                'channel' => $channel,
                'status' => $subscribed ? 'subscribed' : 'unsubscribed',
                'phone' => $attrs['phone'] ?? null,
                'user_id' => $attrs['user_id'] ?? null,
                'source' => $attrs['source'] ?? 'preference_centre',
                'ip_address' => $attrs['ip_address'] ?? null,
                'consented_at' => $subscribed ? now() : null,
                'unsubscribed_at' => $subscribed ? null : now(),
            ], fn ($v) => ! is_null($v)),
        );

        ConsentLog::record(
            email: $email,
            action: $subscribed ? 'subscribed' : 'unsubscribed',
            attrs: array_merge($attrs, ['topic' => $topic, 'channel' => $channel]),
        );

        return $sub;
    }

    /** Current state for every topic for this email, defaulting unknowns. */
    public static function statusMap(?string $email): array
    {
        $rows = $email
            ? self::where('email', strtolower(trim($email)))->pluck('status', 'topic')
            : collect();

        $map = [];
        foreach (self::TOPICS as $topic => $meta) {
            $map[$topic] = $rows->has($topic)
                ? $rows[$topic] === 'subscribed'
                : $meta['default'];
        }

        return $map;
    }

    /**
     * May we message this email on this topic? Honours the topic's default for
     * contacts we've never recorded a preference for. Use this to gate every
     * marketing send so unsubscribes are always respected.
     */
    public static function isSubscribed(?string $email, string $topic): bool
    {
        if (! $email) {
            return false;
        }

        return self::statusMap($email)[$topic] ?? false;
    }

    /** Unsubscribe this email from every topic. */
    public static function unsubscribeAll(string $email, array $attrs = []): void
    {
        foreach (array_keys(self::TOPICS) as $topic) {
            self::setTopic($email, $topic, false, array_merge($attrs, ['source' => $attrs['source'] ?? 'unsubscribe_all']));
        }
    }

    /** Signed, login-free link to a contact's preference centre. */
    public static function preferencesUrl(string $email): string
    {
        return URL::signedRoute('subscriptions.preferences', ['email' => strtolower(trim($email))]);
    }

    /** Signed, login-free one-click unsubscribe link (optionally a single topic). */
    public static function unsubscribeUrl(string $email, ?string $topic = null): string
    {
        return URL::signedRoute('subscriptions.unsubscribe', array_filter([
            'email' => strtolower(trim($email)),
            'topic' => $topic,
        ]));
    }
}
