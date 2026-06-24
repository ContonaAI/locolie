<?php

namespace App\Services\Messaging;

use App\Models\Business;

/**
 * Contract every delivery channel (email, SMS, push) implements.
 *
 * Channels follow the house "demo-able now, live when keys added" rule: with no
 * provider configured they log + count what *would* be sent and report status
 * 'demo'; once env credentials exist they deliver for real and report 'connected'
 * - with no change required in any calling code.
 */
interface Channel
{
    /** Channel key: 'email' | 'sms' | 'push'. */
    public function key(): string;

    /** Human label, e.g. "Email". */
    public function label(): string;

    /** True when a real provider is configured and delivery is live. */
    public function connected(): bool;

    /** 'connected' (live) or 'demo' (logged + counted only). */
    public function status(): string;

    /** Active provider slug (e.g. 'google', 'twilio', 'web_push') or null. */
    public function activeProvider(): ?string;

    /**
     * Normalised data a Blade mockup needs to render "as the customer sees it".
     * $message keys vary by channel (subject/body/title/cta/image...). $brand is
     * the sending business, or null for a platform-wide send.
     */
    public function previewData(array $message, ?Business $brand = null): array;

    /**
     * Deliver (or, in demo mode, log + count) a message to an audience.
     *
     * @param  array  $message  subject/body/title/etc. (channel specific)
     * @param  iterable  $recipients  rows with email/phone/token as relevant
     */
    public function send(array $message, iterable $recipients, ?Business $brand = null): SendResult;
}
