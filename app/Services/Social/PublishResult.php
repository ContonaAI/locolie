<?php

namespace App\Services\Social;

/** Outcome of publishing a post to one platform - logged onto the SocialPost. */
class PublishResult
{
    public function __construct(
        public string $platform,
        public string $status,            // 'posted' (live) | 'not_connected' | 'failed'
        public ?string $externalId = null,
        public string $note = '',
        public array $meta = [],
    ) {}

    public static function posted(string $platform, string $externalId, string $note = ''): self
    {
        return new self($platform, 'posted', $externalId, $note ?: 'Published.');
    }

    /** App / token not configured yet - a clear, non-throwing signal for the UI. */
    public static function notConnected(string $platform, string $note = ''): self
    {
        $label = \App\Models\SocialAccount::label($platform);

        return new self($platform, 'not_connected', null, $note
            ?: "Not connected - register the {$label} app and connect the account to publish.");
    }

    public static function failed(string $platform, string $note): self
    {
        return new self($platform, 'failed', null, $note);
    }

    public function isPosted(): bool
    {
        return $this->status === 'posted';
    }
}
