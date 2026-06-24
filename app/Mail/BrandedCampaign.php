<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * A single branded marketing/transactional email rendered from the normalised
 * preview array produced by EmailChannel::previewData(). The same array drives
 * the on-screen mockup and the real inbox email, so what you preview is exactly
 * what is delivered.
 */
class BrandedCampaign extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array  $preview     normalised email data (subject, body_html, brand_color, ...)
     * @param  array  $recipient   ['email' => ..., 'name' => ...]
     */
    public function __construct(
        public array $preview,
        public array $recipient = [],
    ) {}

    public function envelope(): Envelope
    {
        $fromEmail = $this->preview['from_email'] ?? 'hello@locolie.com';
        $fromName = $this->preview['from_name'] ?? 'locolie';
        $replyTo = $this->preview['reply_to'] ?? $fromEmail;

        return new Envelope(
            from: new Address($fromEmail, $fromName),
            replyTo: filled($replyTo) ? [new Address($replyTo, $fromName)] : [],
            subject: $this->preview['subject'] ?? 'A message from locolie',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.branded',
            with: [
                'preview' => $this->preview,
                'recipient' => $this->recipient,
            ],
        );
    }
}
