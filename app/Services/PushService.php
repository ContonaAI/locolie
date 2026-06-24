<?php

namespace App\Services;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;

/**
 * Web-push engine. Stores shopper subscriptions and broadcasts notifications.
 *
 * Real delivery needs VAPID keys + the minishlink/web-push package. Until those
 * are configured this degrades gracefully: it logs the payload and reports how
 * many shoppers *would* be reached — so the CRM flow is fully demoable now and
 * becomes live the moment keys are added (no code change to the callers).
 */
class PushService
{
    public function configured(): bool
    {
        return (bool) config('services.vapid.public') && class_exists(\Minishlink\WebPush\WebPush::class);
    }

    public function subscribe(array $data): PushSubscription
    {
        return PushSubscription::updateOrCreate(
            ['endpoint' => $data['endpoint']],
            [
                'public_key' => $data['keys']['p256dh'] ?? null,
                'auth_token' => $data['keys']['auth'] ?? null,
                'category_prefs' => $data['category_prefs'] ?? null,
            ]
        );
    }

    /** Broadcast to all subscribers. Returns the number reached (or queued). */
    public function broadcast(string $title, string $body, array $data = []): int
    {
        $subs = PushSubscription::all();
        $payload = json_encode(['title' => $title, 'body' => $body, 'data' => $data]);

        if (! $this->configured()) {
            Log::info('[push] would broadcast', ['title' => $title, 'recipients' => $subs->count()]);

            return $subs->count();
        }

        // --- Live path (when VAPID + minishlink/web-push are installed) ---
        $webPush = new \Minishlink\WebPush\WebPush([
            'VAPID' => [
                'subject' => config('services.vapid.subject') ?: config('app.url'),
                'publicKey' => config('services.vapid.public'),
                'privateKey' => config('services.vapid.private'),
            ],
        ]);

        foreach ($subs as $sub) {
            $webPush->queueNotification(
                \Minishlink\WebPush\Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'publicKey' => $sub->public_key,
                    'authToken' => $sub->auth_token,
                ]),
                $payload
            );
        }

        $reached = 0;
        foreach ($webPush->flush() as $report) {
            if ($report->isSuccess()) {
                $reached++;
            }
        }

        return $reached;
    }
}
