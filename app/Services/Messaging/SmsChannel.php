<?php

namespace App\Services\Messaging;

use App\Models\Business;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * SMS delivery channel for the Messaging Studio.
 *
 * Follows the house "demo-able now, live when keys added" rule: with no real
 * provider credentials in env (and no connected provider row with real config)
 * sends are logged + counted and reported as 'demo'. The moment a provider's
 * env keys exist, delivery goes live with no change to any calling code.
 *
 * ClickSend (our recommended cheap default) and Twilio are wired for real HTTP
 * delivery; the other four providers degrade to a logged optimistic send with a
 * clear "live-send not yet wired" note. Any missing credential routes back to
 * demo - this class never throws.
 */
class SmsChannel extends BaseChannel
{
    /** Per-segment character budget for a plain GSM-7 SMS. */
    public const SEGMENT_LEN = 160;

    public function key(): string
    {
        return 'sms';
    }

    /**
     * Env credential requirements per provider slug. A provider is "ready" only
     * when every listed config value is present.
     *
     * @return array<string, array<int, string>>
     */
    public function providerDrivers(): array
    {
        // ClickSend first: it is the recommended cheap default, so when more than
        // one provider has real keys readyProvider() picks it. A sender id (from)
        // is optional for ClickSend, so it is not required to be "ready".
        return [
            'clicksend' => ['services.clicksend.username', 'services.clicksend.key'],
            'twilio' => ['services.twilio.sid', 'services.twilio.token', 'services.twilio.from'],
            'vonage' => ['services.vonage.key', 'services.vonage.secret', 'services.vonage.from'],
            'messagebird' => ['services.messagebird.key', 'services.messagebird.originator'],
            'plivo' => ['services.plivo.auth_id', 'services.plivo.auth_token', 'services.plivo.from'],
            'aws_sns' => ['services.sns.key', 'services.sns.secret', 'services.sns.region'],
        ];
    }

    /** True when every env credential for the given provider is configured. */
    public function providerReady(string $provider): bool
    {
        $needs = $this->providerDrivers()[$provider] ?? null;
        if ($needs === null) {
            return false;
        }

        foreach ($needs as $configKey) {
            if (blank(config($configKey))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Map of provider slug => ready (env creds present) for the UI to badge.
     *
     * @return array<string, bool>
     */
    public function readiness(): array
    {
        $out = [];
        foreach (array_keys($this->providerDrivers()) as $slug) {
            $out[$slug] = $this->providerReady($slug);
        }

        return $out;
    }

    /** The first provider with real env credentials, if any. */
    public function readyProvider(): ?string
    {
        foreach (array_keys($this->providerDrivers()) as $slug) {
            if ($this->providerReady($slug)) {
                return $slug;
            }
        }

        return null;
    }

    public function connected(): bool
    {
        return $this->readyProvider() !== null;
    }

    /**
     * Resolve the provider to deliver through: a ready env provider wins; else a
     * connected MessagingChannel row whose provider has real env creds.
     */
    public function activeProvider(): ?string
    {
        if ($ready = $this->readyProvider()) {
            return $ready;
        }

        $row = $this->connection();
        if ($row && $this->providerReady($row->provider)) {
            return $row->provider;
        }

        return null;
    }

    public function previewData(array $message, ?Business $brand = null): array
    {
        $body = trim((string) ($message['body'] ?? ''));
        $url = trim((string) ($message['url'] ?? ''));

        // Compose the message exactly as a phone would show it: body + link.
        $full = $body;
        if ($url !== '') {
            $full = $body === '' ? $url : $body."\n".$url;
        }

        $chars = mb_strlen($full);
        $segments = max(1, (int) ceil($chars / self::SEGMENT_LEN));

        return [
            'sender' => $brand?->smsSenderId() ?: 'locolie',
            'brand_name' => $brand?->name ?: 'locolie',
            'brand_color' => $brand?->brandColor() ?: '#7c3aed',
            'body' => $body,
            'url' => $url ?: null,
            'segments' => $segments,
            'char_count' => $chars,
            'stop_line' => 'Reply STOP to opt out',
        ];
    }

    public function send(array $message, iterable $recipients, ?Business $brand = null): SendResult
    {
        // Normalise recipients to a count + phone list without consuming a
        // generator twice.
        $phones = [];
        foreach ($recipients as $r) {
            $phone = is_array($r) ? ($r['phone'] ?? null) : (is_string($r) ? $r : null);
            if (! blank($phone)) {
                $phones[] = (string) $phone;
            }
        }
        $count = count($phones);

        $preview = $this->previewData($message, $brand);
        $body = $preview['body'];
        if ($preview['url']) {
            $body = $body === '' ? $preview['url'] : $body."\n".$preview['url'];
        }

        $provider = $this->activeProvider();

        // Demo path: no provider with real credentials.
        if ($provider === null) {
            Log::info('[sms] would send', [
                'sender' => $preview['sender'],
                'brand' => $preview['brand_name'],
                'recipients' => $count,
                'segments' => $preview['segments'],
                'body' => $body,
            ]);

            return SendResult::demo(
                $count,
                "Logged {$count} SMS in demo mode - connect a provider with real keys to deliver."
            );
        }

        // Live path: route to a driver. Never throw - degrade to a failed result.
        try {
            return match ($provider) {
                'twilio' => $this->sendViaTwilio($phones, $body, $preview['sender']),
                'vonage' => $this->sendViaVonage($phones, $body, $preview['sender']),
                'messagebird' => $this->sendViaMessagebird($phones, $body, $preview['sender']),
                'plivo' => $this->sendViaPlivo($phones, $body, $preview['sender']),
                'aws_sns' => $this->sendViaAwsSns($phones, $body, $preview['sender']),
                'clicksend' => $this->sendViaClicksend($phones, $body, $preview['sender']),
                default => SendResult::demo($count, "Unknown provider [{$provider}] - logged in demo mode."),
            };
        } catch (Throwable $e) {
            Log::error('[sms] send failed', ['provider' => $provider, 'error' => $e->getMessage()]);

            return SendResult::failed("SMS send via {$provider} failed: {$e->getMessage()}", $provider);
        }
    }

    /** Real Twilio delivery via the Messages REST API (one request per phone). */
    protected function sendViaTwilio(array $phones, string $body, string $sender): SendResult
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');

        if (blank($sid) || blank($token) || blank($from)) {
            return SendResult::demo(count($phones), 'Twilio credentials incomplete - logged in demo mode.');
        }

        $sent = 0;
        $errors = [];
        foreach ($phones as $to) {
            $resp = Http::asForm()
                ->withBasicAuth($sid, $token)
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'To' => $to,
                    'From' => $from,
                    'Body' => $body,
                ]);

            if ($resp->successful()) {
                $sent++;
            } else {
                $errors[] = $resp->json('message') ?? "HTTP {$resp->status()}";
            }
        }

        if ($sent === 0 && $errors) {
            return SendResult::failed('Twilio rejected every message: '.implode('; ', array_slice($errors, 0, 3)), 'twilio');
        }

        $note = "Sent {$sent} SMS via Twilio.";
        if ($errors) {
            $note .= ' '.count($errors).' failed.';
        }

        return SendResult::sent($sent, 'twilio', $note);
    }

    protected function sendViaVonage(array $phones, string $body, string $sender): SendResult
    {
        return $this->stubProvider('vonage', $phones);
    }

    protected function sendViaMessagebird(array $phones, string $body, string $sender): SendResult
    {
        return $this->stubProvider('messagebird', $phones);
    }

    protected function sendViaPlivo(array $phones, string $body, string $sender): SendResult
    {
        return $this->stubProvider('plivo', $phones);
    }

    protected function sendViaAwsSns(array $phones, string $body, string $sender): SendResult
    {
        return $this->stubProvider('aws_sns', $phones);
    }

    /**
     * Real ClickSend delivery via the REST API (one batched request, up to the
     * provider's per-call message cap). Basic-auth is username + API key. The
     * sender id ("from") is optional - ClickSend uses a shared/dedicated number
     * when omitted, and alphanumeric ids are honoured where the destination
     * country allows them.
     */
    protected function sendViaClicksend(array $phones, string $body, string $sender): SendResult
    {
        $username = config('services.clicksend.username');
        $apiKey = config('services.clicksend.key');
        $from = config('services.clicksend.from') ?: $sender;

        if (blank($username) || blank($apiKey)) {
            return SendResult::demo(count($phones), 'ClickSend credentials incomplete - logged in demo mode.');
        }

        $messages = array_map(fn ($to) => array_filter([
            'source' => 'php',
            'from' => $from ?: null,
            'to' => $to,
            'body' => $body,
        ]), $phones);

        $sent = 0;
        $errors = [];

        // ClickSend caps messages per request; batch to stay well under it.
        foreach (array_chunk($messages, 100) as $batch) {
            $resp = Http::withBasicAuth($username, $apiKey)
                ->acceptJson()
                ->post('https://rest.clicksend.com/v3/sms/send', [
                    'messages' => array_values($batch),
                ]);

            if (! $resp->successful()) {
                $errors[] = $resp->json('response_msg') ?? "HTTP {$resp->status()}";

                continue;
            }

            foreach ((array) $resp->json('data.messages', []) as $m) {
                if (($m['status'] ?? null) === 'SUCCESS') {
                    $sent++;
                } else {
                    $errors[] = $m['status'] ?? 'ClickSend rejected a message.';
                }
            }
        }

        if ($sent === 0 && $errors) {
            return SendResult::failed('ClickSend rejected every message: '.implode('; ', array_slice($errors, 0, 3)), 'clicksend');
        }

        $note = "Sent {$sent} SMS via ClickSend.";
        if ($errors) {
            $note .= ' '.count($errors).' failed.';
        }

        return SendResult::sent($sent, 'clicksend', $note);
    }

    /**
     * Providers whose live HTTP delivery is not yet wired: log + count and
     * report optimistically as sent, with a clear note. Credentials are already
     * confirmed present by activeProvider() before we reach here.
     */
    protected function stubProvider(string $provider, array $phones): SendResult
    {
        $count = count($phones);
        Log::info('[sms] live-send via stub driver', ['provider' => $provider, 'recipients' => $count]);

        $label = config("messaging.channels.sms.providers.{$provider}.label", $provider);

        return SendResult::sent($count, $provider, "Counted {$count} SMS - {$label} live-send not yet wired.");
    }
}
