<?php

namespace App\Services\Messaging;

use App\Models\Business;
use App\Models\DeviceToken;
use App\Models\PushSubscription;
use App\Services\PushService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
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

    /**
     * Whether native Android/web (FCM) push is wired up. Either the HTTP v1 path
     * (project id + a service-account JSON) or the legacy server key satisfies it.
     */
    protected function fcmConfigured(): bool
    {
        $v1 = filled(config('services.fcm.project_id'))
            && filled(config('services.fcm.credentials'));

        return $v1 || filled(config('services.fcm.server_key'));
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
     * Send to Apple Push Notification service over HTTP/2, authorised by a
     * provider JWT signed (ES256) with the .p8 auth key. Logs + counts when creds
     * are absent so the iOS flow is demoable before the app ships. Requires the
     * curl HTTP/2 stack; if unavailable we log and report 0 reached rather than
     * throwing. Returns the number of devices accepted.
     */
    protected function sendApns(array $tokens, string $title, string $body, array $data = []): int
    {
        $count = count($tokens);

        if (! $this->apnsConfigured()) {
            Log::info('[push:apns] would send', ['title' => $title, 'devices' => $count]);

            return $count;
        }

        $jwt = $this->apnsJwt();
        $bundleId = config('services.apns.bundle_id');
        if (blank($jwt) || blank($bundleId) || ! function_exists('curl_init')) {
            Log::warning('[push:apns] cannot build live request (jwt/bundle/curl missing) - skipping');

            return 0;
        }

        // api.push.apple.com for production; switch host for the sandbox if needed.
        $host = config('services.apns.host', 'https://api.push.apple.com');
        $payload = json_encode(array_filter([
            'aps' => ['alert' => ['title' => $title, 'body' => $body], 'sound' => 'default'],
            'data' => $data ?: null,
        ]));

        $reached = 0;
        foreach ($tokens as $token) {
            $ch = curl_init("{$host}/3/device/{$token}");
            curl_setopt_array($ch, [
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'authorization: bearer '.$jwt,
                    'apns-topic: '.$bundleId,
                    'apns-push-type: alert',
                    'content-type: application/json',
                ],
            ]);
            curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($status === 200) {
                $reached++;
            } else {
                Log::warning('[push:apns] device rejected', ['status' => $status]);
            }
        }

        return $reached;
    }

    /**
     * Mint (and cache) the APNs provider JWT (ES256). The auth key may be the raw
     * .p8 contents or a path to the file. Apple lets a token live up to an hour;
     * we refresh well inside that window.
     */
    protected function apnsJwt(): ?string
    {
        return Cache::remember('messaging.apns.jwt', 3000, function () {
            $keyId = config('services.apns.key_id');
            $teamId = config('services.apns.team_id');
            $authKey = config('services.apns.auth_key');

            if (blank($keyId) || blank($teamId) || blank($authKey)) {
                return null;
            }

            // Allow either inline .p8 contents or a filesystem path to the key.
            $pem = Str::contains($authKey, 'BEGIN PRIVATE KEY')
                ? $authKey
                : (is_file($authKey) ? file_get_contents($authKey) : null);

            if (blank($pem)) {
                return null;
            }

            $header = $this->base64Url(json_encode(['alg' => 'ES256', 'kid' => $keyId]));
            $claims = $this->base64Url(json_encode(['iss' => $teamId, 'iat' => time()]));
            $signingInput = $header.'.'.$claims;

            $signature = '';
            if (! openssl_sign($signingInput, $signature, $pem, 'sha256')) {
                Log::error('[push:apns] failed to sign provider JWT');

                return null;
            }

            // openssl returns a DER ECDSA signature; APNs expects raw R||S (64 bytes).
            $raw = $this->derToRawEcdsa($signature);

            return $signingInput.'.'.$this->base64Url($raw);
        });
    }

    /** Convert a DER-encoded ECDSA signature to the raw R||S form APNs requires. */
    protected function derToRawEcdsa(string $der): string
    {
        $pos = 3; // skip SEQUENCE tag (0x30), total length, first INTEGER tag (0x02)
        $rLen = ord($der[$pos]);
        $pos++;
        $r = substr($der, $pos, $rLen);
        $pos += $rLen + 1; // skip R then the next 0x02 tag
        $sLen = ord($der[$pos]);
        $pos++;
        $s = substr($der, $pos, $sLen);

        // Left-pad / trim each component to 32 bytes.
        $r = str_pad(ltrim($r, "\x00"), 32, "\x00", STR_PAD_LEFT);
        $s = str_pad(ltrim($s, "\x00"), 32, "\x00", STR_PAD_LEFT);

        return $r.$s;
    }

    /**
     * Send to Firebase Cloud Messaging - our chosen native client (Android + web).
     * Prefers the FCM HTTP v1 API (OAuth token minted from a service account);
     * falls back to the legacy server-key HTTP API. Logs + counts when no creds
     * are present so the flow is demoable. Returns the number of devices reached.
     */
    protected function sendFcm(array $tokens, string $title, string $body, array $data = []): int
    {
        $count = count($tokens);

        if (! $this->fcmConfigured()) {
            Log::info('[push:fcm] would send', ['title' => $title, 'devices' => $count]);

            return $count;
        }

        // Stringify data values - FCM data payloads must be string => string.
        $stringData = array_map(fn ($v) => (string) $v, $data);

        try {
            if (filled(config('services.fcm.project_id')) && filled(config('services.fcm.credentials'))) {
                return $this->sendFcmV1($tokens, $title, $body, $stringData);
            }

            return $this->sendFcmLegacy($tokens, $title, $body, $stringData);
        } catch (\Throwable $e) {
            Log::error('[push:fcm] send failed', ['error' => $e->getMessage()]);

            return 0;
        }
    }

    /** FCM HTTP v1: one POST per token, authorised by a short-lived OAuth token. */
    protected function sendFcmV1(array $tokens, string $title, string $body, array $data): int
    {
        $projectId = config('services.fcm.project_id');
        $accessToken = $this->fcmAccessToken();
        if (blank($accessToken)) {
            Log::warning('[push:fcm] no OAuth token minted - skipping live send');

            return 0;
        }

        $endpoint = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
        $reached = 0;

        foreach ($tokens as $token) {
            $resp = Http::withToken($accessToken)
                ->acceptJson()
                ->post($endpoint, [
                    'message' => [
                        'token' => $token,
                        'notification' => ['title' => $title, 'body' => $body],
                        'data' => $data ?: (object) [],
                    ],
                ]);

            if ($resp->successful()) {
                $reached++;
            } else {
                Log::warning('[push:fcm] v1 rejected', ['status' => $resp->status(), 'error' => $resp->json('error.message')]);
            }
        }

        return $reached;
    }

    /** Legacy FCM HTTP API: a single multicast POST keyed by the server key. */
    protected function sendFcmLegacy(array $tokens, string $title, string $body, array $data): int
    {
        $serverKey = config('services.fcm.server_key');

        $resp = Http::withHeaders(['Authorization' => 'key='.$serverKey])
            ->acceptJson()
            ->post('https://fcm.googleapis.com/fcm/send', [
                'registration_ids' => array_values($tokens),
                'notification' => ['title' => $title, 'body' => $body],
                'data' => $data,
            ]);

        if (! $resp->successful()) {
            Log::warning('[push:fcm] legacy rejected', ['status' => $resp->status()]);

            return 0;
        }

        return (int) ($resp->json('success') ?? 0);
    }

    /**
     * Mint (and cache) a Google OAuth2 access token for FCM from the configured
     * service account. The service account may be a path to a JSON file or the
     * raw JSON itself. Uses a self-signed JWT grant so there is no SDK dependency.
     */
    protected function fcmAccessToken(): ?string
    {
        return Cache::remember('messaging.fcm.access_token', 3300, function () {
            $sa = $this->fcmServiceAccount();
            if (! $sa || blank($sa['client_email'] ?? null) || blank($sa['private_key'] ?? null)) {
                return null;
            }

            $now = time();
            $claims = [
                'iss' => $sa['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => $sa['token_uri'] ?? 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ];

            $segments = [
                $this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])),
                $this->base64Url(json_encode($claims)),
            ];
            $signingInput = implode('.', $segments);

            $signature = '';
            if (! openssl_sign($signingInput, $signature, $sa['private_key'], 'sha256WithRSAEncryption')) {
                Log::error('[push:fcm] failed to sign JWT for OAuth grant');

                return null;
            }
            $segments[] = $this->base64Url($signature);
            $jwt = implode('.', $segments);

            $resp = Http::asForm()->post($sa['token_uri'] ?? 'https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            return $resp->successful() ? $resp->json('access_token') : null;
        });
    }

    /** Load the FCM service account as an array from either inline JSON or a file path. */
    protected function fcmServiceAccount(): ?array
    {
        $value = config('services.fcm.credentials');
        if (blank($value)) {
            return null;
        }

        // Inline JSON (starts with "{") takes precedence over a filesystem path.
        $json = Str::startsWith(trim($value), '{')
            ? $value
            : (is_file($value) ? file_get_contents($value) : null);

        if (blank($json)) {
            return null;
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : null;
    }

    protected function base64Url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
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
