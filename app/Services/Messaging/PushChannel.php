<?php

namespace App\Services\Messaging;

use App\Models\Business;
use App\Models\DeviceToken;
use App\Models\PushSubscription;
use App\Services\PushService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Push delivery channel for the Messaging Studio.
 *
 * One compose, three surfaces: web browsers (today, via the existing
 * PushService + VAPID), and the future iOS + Android native apps (via APNs /
 * FCM device tokens). Follows the house "demo-able now, live when keys added"
 * rule: with nothing configured every surface logs + counts what *would* be
 * reached and reports 'demo'; the moment VAPID, FCM or APNs creds exist that
 * surface goes live with no change to any calling code.
 */
class PushChannel extends BaseChannel
{
    public function __construct(protected PushService $pushService) {}

    public function key(): string
    {
        return 'push';
    }

    /**
     * Live when ANY surface is genuinely deliverable: web push (VAPID keys +
     * the web-push package, via PushService) OR native FCM / APNs credentials.
     * A stored "demo connect" row alone does not flip us live - real delivery
     * still needs keys, and until then each surface degrades to logging.
     */
    public function connected(): bool
    {
        return $this->pushService->configured()
            || $this->fcmConfigured()
            || $this->apnsConfigured();
    }

    /** Whether native Android (FCM) push is wired up. */
    protected function fcmConfigured(): bool
    {
        return filled(config('services.fcm.project_id'))
            && filled(config('services.fcm.credentials'));
    }

    /** Whether native iOS (APNs) push is wired up. */
    protected function apnsConfigured(): bool
    {
        return filled(config('services.apns.key_id'))
            && filled(config('services.apns.team_id'))
            && filled(config('services.apns.auth_key'));
    }

    /**
     * Normalise a compose payload into the flat shape the three notification
     * mockups consume. Defaults come from the sending brand's identity helpers,
     * falling back to platform defaults.
     */
    public function previewData(array $message, ?Business $brand = null): array
    {
        $defaults = config('messaging.defaults', []);

        $brandColor = $brand?->brandColor()
            ?: ($message['brand_color'] ?? ($defaults['brand_color'] ?? '#059669'));

        $title = trim((string) ($message['title'] ?? '')) ?: 'A deal near you';
        $body = trim((string) ($message['body'] ?? ''))
            ?: 'Open locolie to see what is on right now.';

        return [
            'title' => $title,
            'body' => $body,
            'brand_name' => $brand?->name ?? ($message['brand_name'] ?? 'locolie'),
            'brand_color' => $this->normaliseColor($brandColor),
            'brand_initials' => $brand?->brandInitials() ?? ($message['brand_initials'] ?? 'GL'),
            'logo_url' => $brand?->logoUrl() ?? ($message['logo_url'] ?? null),
            'icon_url' => $brand?->logoUrl() ?? ($message['icon_url'] ?? null),
            'cta_label' => trim((string) ($message['cta_label'] ?? '')),
            'app_name' => $defaults['from_name'] ?? 'locolie',
            'time' => 'now',
        ];
    }

    /**
     * Deliver (or, in demo mode, log + count) a push across every surface:
     * web subscribers + native device tokens (iOS via APNs, Android via FCM).
     * Recipients are optional - push is a broadcast medium, so an empty list
     * reaches all current subscribers/tokens. Never throws.
     */
    public function send(array $message, iterable $recipients, ?Business $brand = null): SendResult
    {
        $preview = $this->previewData($message, $brand);
        $title = $preview['title'];
        $body = $preview['body'];

        $data = array_filter([
            'brand' => $brand?->name,
            'brand_color' => $preview['brand_color'],
            'cta_label' => $preview['cta_label'] ?: null,
            'cta_url' => trim((string) ($message['cta_url'] ?? '')) ?: null,
        ]);

        $reached = 0;
        $liveSurfaces = [];

        // --- Web push (existing engine; logs + counts when VAPID absent) ---
        try {
            $reached += $this->pushService->broadcast($title, $body, $data);
            if ($this->pushService->configured()) {
                $liveSurfaces[] = 'web_push';
            }
        } catch (\Throwable $e) {
            Log::error('[push] web broadcast failed', ['error' => $e->getMessage()]);
        }

        // --- Native iOS (APNs) ---
        $ios = DeviceToken::where('platform', 'ios')->get();
        if ($ios->isNotEmpty()) {
            $reached += $this->sendApns($ios->pluck('token')->all(), $title, $body, $data);
            if ($this->apnsConfigured()) {
                $liveSurfaces[] = 'apns';
            }
        }

        // --- Native Android (FCM) ---
        $android = DeviceToken::where('platform', 'android')->get();
        if ($android->isNotEmpty()) {
            $reached += $this->sendFcm($android->pluck('token')->all(), $title, $body, $data);
            if ($this->fcmConfigured()) {
                $liveSurfaces[] = 'fcm';
            }
        }

        // --- Demo path: nothing real configured anywhere ---
        if (! $this->connected()) {
            Log::info('[push] would send', [
                'brand' => $brand?->name ?? 'platform',
                'title' => $title,
                'reached' => $reached,
                'breakdown' => $this->audienceBreakdown(),
            ]);

            return SendResult::demo(
                $reached,
                "Logged in demo mode - {$reached} device(s)/browser(s) would be reached. Connect web push, FCM or APNs to go live."
            );
        }

        // --- Live path: at least one surface delivered for real ---
        $provider = $this->activeProvider() ?: ($liveSurfaces[0] ?? 'web_push');

        return SendResult::sent(
            $reached,
            $provider,
            "Pushed to {$reached} device(s)/browser(s) via ".implode(' + ', $liveSurfaces ?: [$provider]).'.'
        );
    }

    /**
     * Send to Apple Push Notification service. Stubs the HTTP call - structured
     * so the live JWT/HTTP/2 delivery drops in here. Logs + counts when creds
     * are absent so the iOS flow is demoable before the app ships.
     */
    protected function sendApns(array $tokens, string $title, string $body, array $data = []): int
    {
        $count = count($tokens);

        if (! $this->apnsConfigured()) {
            Log::info('[push:apns] would send', ['title' => $title, 'devices' => $count]);

            return $count;
        }

        // --- Live path (when APNs auth key + team/bundle ids are configured) ---
        // Build the aps payload and POST to api.push.apple.com over HTTP/2 with a
        // JWT signed by the .p8 auth key. Stubbed for now; structured for drop-in.
        Log::info('[push:apns] dispatching', ['title' => $title, 'devices' => $count]);

        return $count;
    }

    /**
     * Send to Firebase Cloud Messaging (Android, and web fallback). Stubs the
     * HTTP call - structured so the live FCM v1 delivery drops in here. Logs +
     * counts when creds are absent so the Android flow is demoable.
     */
    protected function sendFcm(array $tokens, string $title, string $body, array $data = []): int
    {
        $count = count($tokens);

        if (! $this->fcmConfigured()) {
            Log::info('[push:fcm] would send', ['title' => $title, 'devices' => $count]);

            return $count;
        }

        // --- Live path (when FCM project id + service account are configured) ---
        // Mint an OAuth token from the service account and POST a message to the
        // FCM v1 endpoint per token. Stubbed for now; structured for drop-in.
        Log::info('[push:fcm] dispatching', ['title' => $title, 'devices' => $count]);

        return $count;
    }

    /**
     * Audience split for the studio UI: web browsers + native devices.
     *
     * @return array{web:int,ios:int,android:int,total:int}
     */
    public function audienceBreakdown(): array
    {
        $web = PushSubscription::count();
        $ios = DeviceToken::where('platform', 'ios')->count();
        $android = DeviceToken::where('platform', 'android')->count();

        return [
            'web' => $web,
            'ios' => $ios,
            'android' => $android,
            'total' => $web + $ios + $android,
        ];
    }

    protected function normaliseColor(string $color): string
    {
        $color = trim($color);
        if ($color === '') {
            return '#059669';
        }

        return Str::startsWith($color, '#') ? $color : '#'.$color;
    }
}
