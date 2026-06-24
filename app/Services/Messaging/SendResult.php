<?php

namespace App\Services\Messaging;

/** Outcome of a channel send - what the CRM logs and shows back to the user. */
class SendResult
{
    public function __construct(
        public int $sent,
        public string $status,          // 'sent' (live) | 'demo' | 'failed'
        public ?string $provider = null,
        public string $note = '',
        public array $meta = [],
    ) {}

    public static function demo(int $sent, string $note = ''): self
    {
        return new self($sent, 'demo', null, $note ?: 'Logged in demo mode (no provider connected).');
    }

    public static function sent(int $sent, string $provider, string $note = ''): self
    {
        return new self($sent, 'sent', $provider, $note);
    }

    public static function failed(string $note, ?string $provider = null): self
    {
        return new self(0, 'failed', $provider, $note);
    }

    public function isLive(): bool
    {
        return $this->status === 'sent';
    }
}
