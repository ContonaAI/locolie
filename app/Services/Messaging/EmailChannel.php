<?php

namespace App\Services\Messaging;

use App\Http\Controllers\TrackingController;
use App\Mail\BrandedCampaign;
use App\Models\Business;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Email delivery channel for the Messaging Studio.
 *
 * Follows the house "demo-able now, live when keys added" rule: with no real
 * provider configured, sends are logged + counted and reported as 'demo'; the
 * moment Gmail OAuth, Resend, or a non-log mailer is configured, the very same
 * call delivers a branded, responsive HTML email for real - no caller change.
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

        // A default mailer other than the no-op 'log'/'array' transports.
        $default = config('mail.default');

        return filled($default) && ! in_array($default, ['log', 'array'], true);
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
