<?php

namespace App\Services\Messaging;

use App\Http\Controllers\TrackingController;
use App\Mail\BrandedCampaign;
use App\Models\Business;
use App\Models\Subscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

/**
 * Email delivery channel for the Messaging Studio.
 *
 * Follows the house "demo-able now, live when keys added" rule: with no real
 * provider configured, sends are logged + counted and reported as 'demo'; the
 * moment Gmail OAuth, Resend, Mailjet, or a non-log mailer is configured, the
 * very same call delivers a branded, responsive HTML email for real - no caller
 * change.
 *
 * Mailjet is delivered over its Send API v3.1 directly via the Laravel Http
 * client (no SDK dependency); every other provider goes out through a Laravel
 * mailer transport. The same BrandedCampaign HTML is used either way, so the
 * on-screen preview matches the delivered email exactly.
 */
class EmailChannel extends BaseChannel
{
    public function key(): string
    {
        return 'email';
    }

    /**
     * True only when email is genuinely deliverable. We treat the channel as
     * live if real env credentials exist (Gmail refresh token, a Resend key, or
     * a configured non-log default mailer). A stored "demo connect" row alone
     * does NOT make us live - that just flips the UI; real delivery still needs
     * keys, and until then we degrade to logging.
     */
    public function connected(): bool
    {
        return $this->deliverable();
    }

    /** Whether a real mail transport is actually wired up. */
    protected function deliverable(): bool
    {
        if (filled(config('services.google.gmail_refresh_token'))) {
            return true;
        }

        if (filled(config('services.resend.key'))) {
            return true;
        }

        if ($this->mailjetConfigured()) {
            return true;
        }

        // A default mailer other than the no-op 'log'/'array' transports.
        $default = config('mail.default');

        return filled($default) && ! in_array($default, ['log', 'array'], true);
    }

    /** Whether a Mailjet API key + secret pair is present. */
    protected function mailjetConfigured(): bool
    {
        return filled(config('services.mailjet.key'))
            && filled(config('services.mailjet.secret'));
    }

    /**
     * Normalise a compose payload into the flat shape the email mockup partial
     * and the BrandedCampaign mailable both consume. Sensible defaults come from
     * the sending brand's identity helpers, falling back to platform defaults.
     */
    public function previewData(array $message, ?Business $brand = null): array
    {
        $defaults = config('messaging.defaults', []);

        $fromName = $brand?->emailFromName()
            ?: ($message['from_name'] ?? ($defaults['from_name'] ?? 'locolie'));

        $fromEmail = $message['from_email']
            ?? config('services.google.gmail_from')
            ?? ($defaults['from_address'] ?? 'hello@locolie.com');

        $replyTo = $brand?->reply_to_email
            ?: ($message['reply_to'] ?? $fromEmail);

        $brandColor = $brand?->brandColor()
            ?: ($message['brand_color'] ?? ($defaults['brand_color'] ?? '#059669'));

        $subject = trim((string) ($message['subject'] ?? '')) ?: 'A little something from '.($brand?->name ?? 'locolie');

        return [
            'from_name' => $fromName,
            'from_email' => $fromEmail,
            'reply_to' => $replyTo,
            'subject' => $subject,
            'preheader' => trim((string) ($message['preheader'] ?? '')),
            'logo_url' => $brand?->logoUrl() ?? ($message['logo_url'] ?? null),
            'brand_color' => $this->normaliseColor($brandColor),
            'brand_initials' => $brand?->brandInitials() ?? ($message['brand_initials'] ?? 'GL'),
            'brand_name' => $brand?->name ?? ($message['brand_name'] ?? 'locolie'),
            'body_html' => $this->bodyHtml($message['body'] ?? ''),
            'cta_label' => trim((string) ($message['cta_label'] ?? '')),
            'cta_url' => trim((string) ($message['cta_url'] ?? '')),
            'footer' => trim((string) ($message['footer'] ?? ''))
                ?: ('You are receiving this because you shop local with '.($brand?->name ?? 'locolie').'.'),
        ];
    }

    /**
     * Deliver (or, in demo mode, log + count) the email to an audience.
     *
     * @param  iterable<array{email:string,name?:string}>  $recipients
     */
    public function send(array $message, iterable $recipients, ?Business $brand = null): SendResult
    {
        $list = collect($recipients)
            ->map(fn ($r) => is_array($r) ? $r : ['email' => $r])
            ->filter(fn ($r) => filter_var($r['email'] ?? null, FILTER_VALIDATE_EMAIL))
            ->values();

        $preview = $this->previewData($message, $brand);
        $count = $list->count();

        // --- Demo path: no real transport, just log + count what would send ---
        if (! $this->deliverable()) {
            Log::info('[email] would send', [
                'brand' => $brand?->name ?? 'platform',
                'subject' => $preview['subject'],
                'recipients' => $count,
                'sample' => $list->take(3)->pluck('email')->all(),
            ]);

            return SendResult::demo(
                $count,
                "Logged in demo mode - {$count} branded email(s) would send. Connect a provider to go live."
            );
        }

        // --- Live path: actually deliver via the BrandedCampaign mailable ---
        $provider = $this->activeProvider() ?? $this->inferredProvider();
        $campaignId = $message['_campaign_id'] ?? null;
        $topic = $message['_topic'] ?? 'offers';

        // Mailjet has its own API delivery path (Send API v3.1). Only take it when
        // the key + secret are actually present; otherwise fall through to a
        // Laravel mailer so we never throw on a half-configured Mailjet row.
        if ($provider === 'mailjet' && $this->mailjetConfigured()) {
            return $this->sendViaMailjet($list, $preview, $campaignId, $topic);
        }

        try {
            $sent = 0;
            foreach ($list as $recipient) {
                // Per-recipient copy carrying tracking + a signed unsubscribe link.
                $recipientPreview = $this->withTracking($preview, $recipient['email'], $campaignId, $topic);
                $mailable = new BrandedCampaign($recipientPreview, $recipient);

                if ($mailer = $this->mailerFor($provider)) {
                    Mail::mailer($mailer)->to($recipient['email'], $recipient['name'] ?? null)->send($mailable);
                } else {
                    Mail::to($recipient['email'], $recipient['name'] ?? null)->send($mailable);
                }
                $sent++;
            }

            return SendResult::sent($sent, $provider, "Sent {$sent} branded email(s) via {$provider}.");
        } catch (\Throwable $e) {
            Log::error('[email] send failed', ['error' => $e->getMessage()]);

            return SendResult::failed('Email send failed: '.$e->getMessage(), $provider);
        }
    }

    /**
     * Deliver via the Mailjet Send API v3.1 (https://api.mailjet.com/v3.1/send).
     *
     * Each recipient gets its own Message so the per-recipient tracking pixel,
     * click-wrapped CTA and one-click unsubscribe header are preserved - exactly
     * as the Laravel-mailer path does. We render the same emails.branded view to
     * HTML, so the inbox email matches the on-screen preview. Mailjet caps a
     * single request at 50 Messages, so we batch. Never throws - any transport
     * failure degrades to a SendResult::failed.
     *
     * @param  \Illuminate\Support\Collection<int,array{email:string,name?:string}>  $list
     */
    protected function sendViaMailjet($list, array $preview, ?int $campaignId, string $topic): SendResult
    {
        $key = config('services.mailjet.key');
        $secret = config('services.mailjet.secret');
        $fromEmail = config('services.mailjet.from') ?: $preview['from_email'];
        $fromName = $preview['from_name'] ?? 'locolie';

        $messages = $list->map(function (array $recipient) use ($preview, $campaignId, $topic, $fromEmail, $fromName) {
            $rp = $this->withTracking($preview, $recipient['email'], $campaignId, $topic);

            $message = [
                'From' => ['Email' => $fromEmail, 'Name' => $fromName],
                'To' => [array_filter([
                    'Email' => $recipient['email'],
                    'Name' => $recipient['name'] ?? null,
                ])],
                'Subject' => $rp['subject'] ?? 'A message from locolie',
                'HTMLPart' => $this->renderHtml($rp, $recipient),
            ];

            if (filled($rp['reply_to'] ?? null)) {
                $message['ReplyTo'] = ['Email' => $rp['reply_to'], 'Name' => $fromName];
            }

            // RFC 8058 one-click unsubscribe, mirroring the mailer path's headers.
            if (filled($rp['unsubscribe_url'] ?? null)) {
                $message['Headers'] = [
                    'List-Unsubscribe' => '<'.$rp['unsubscribe_url'].'>',
                    'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
                ];
            }

            return $message;
        })->values();

        $sent = 0;
        $errors = [];

        try {
            foreach ($messages->chunk(50) as $batch) {
                $resp = Http::withBasicAuth($key, $secret)
                    ->acceptJson()
                    ->post('https://api.mailjet.com/v3.1/send', [
                        'Messages' => $batch->values()->all(),
                    ]);

                if (! $resp->successful()) {
                    $errors[] = $resp->json('ErrorMessage') ?? "HTTP {$resp->status()}";

                    continue;
                }

                foreach ((array) $resp->json('Messages', []) as $m) {
                    if (($m['Status'] ?? null) === 'success') {
                        $sent += count($m['To'] ?? []);
                    } else {
                        $errors[] = $m['Errors'][0]['ErrorMessage'] ?? 'Mailjet rejected a message.';
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('[email] mailjet send failed', ['error' => $e->getMessage()]);

            return SendResult::failed('Mailjet send failed: '.$e->getMessage(), 'mailjet');
        }

        if ($sent === 0 && $errors) {
            return SendResult::failed('Mailjet rejected every message: '.implode('; ', array_slice($errors, 0, 3)), 'mailjet');
        }

        $note = "Sent {$sent} branded email(s) via Mailjet.";
        if ($errors) {
            $note .= ' '.count($errors).' failed.';
        }

        return SendResult::sent($sent, 'mailjet', $note);
    }

    /** Render the branded email view to an HTML string for API-based providers. */
    protected function renderHtml(array $preview, array $recipient): string
    {
        return View::make('emails.branded', [
            'preview' => $preview,
            'recipient' => $recipient,
        ])->render();
    }

    /**
     * Add per-recipient tracking + a signed unsubscribe/preferences link to a
     * copy of the brand preview. The open pixel and click-wrapped CTA only apply
     * when we have a campaign id to attribute them to.
     */
    protected function withTracking(array $preview, string $email, ?int $campaignId, string $topic): array
    {
        $preview['topic'] = $topic; // drives the legal footer partial's unsubscribe link
        $preview['unsubscribe_url'] = Subscription::unsubscribeUrl($email, $topic);
        $preview['preferences_url'] = Subscription::preferencesUrl($email);

        if ($campaignId) {
            $preview['open_pixel_url'] = route('track.open', [
                't' => TrackingController::token($campaignId, $email),
            ]);

            if (filled($preview['cta_url'] ?? '')) {
                $preview['cta_url'] = route('track.click', [
                    't' => TrackingController::token($campaignId, $email, $preview['cta_url']),
                ]);
            }
        }

        return $preview;
    }

    /** Best-guess provider slug when none is explicitly connected. */
    protected function inferredProvider(): string
    {
        if (filled(config('services.google.gmail_refresh_token'))) {
            return 'google';
        }
        if (filled(config('services.resend.key'))) {
            return 'resend';
        }
        if ($this->mailjetConfigured()) {
            return 'mailjet';
        }

        return 'smtp';
    }

    /** Map a provider slug to a configured Laravel mailer, if one applies. */
    protected function mailerFor(?string $provider): ?string
    {
        if ($provider === 'resend' && array_key_exists('resend', (array) config('mail.mailers', []))) {
            return 'resend';
        }
        if ($provider === 'smtp' && array_key_exists('smtp', (array) config('mail.mailers', []))) {
            return 'smtp';
        }

        return null; // fall back to the default mailer
    }

    protected function normaliseColor(string $color): string
    {
        $color = trim($color);
        if ($color === '') {
            return '#059669';
        }

        return Str::startsWith($color, '#') ? $color : '#'.$color;
    }

    /**
     * Convert a plain-text body into safe HTML paragraphs. If the body already
     * looks like HTML (contains a tag), pass it through untouched.
     */
    protected function bodyHtml(string $body): string
    {
        $body = trim($body);
        if ($body === '') {
            return '';
        }

        if (Str::contains($body, ['<p', '<div', '<br', '<a ', '<strong', '<ul', '<ol', '<h1', '<h2', '<h3'])) {
            return $body;
        }

        return collect(preg_split('/\n{2,}/', $body))
            ->map(fn ($para) => '<p style="margin:0 0 16px;">'.nl2br(e(trim($para))).'</p>')
            ->implode('');
    }
}
