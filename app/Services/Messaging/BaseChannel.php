<?php

namespace App\Services\Messaging;

use App\Models\MessagingChannel;

/** Shared helpers for concrete channels (config lookup, connection row). */
abstract class BaseChannel implements Channel
{
    /** The catalogue entry for this channel from config/messaging.php. */
    public function catalogue(): array
    {
        return config("messaging.channels.{$this->key()}", []);
    }

    /** Provider list (slug => meta) from the catalogue. */
    public function providers(): array
    {
        return $this->catalogue()['providers'] ?? [];
    }

    /** The stored connection row for the active provider, if any. */
    public function connection(): ?MessagingChannel
    {
        return MessagingChannel::where('channel', $this->key())
            ->where('status', 'connected')
            ->latest('connected_at')
            ->first();
    }

    public function label(): string
    {
        return $this->catalogue()['label'] ?? ucfirst($this->key());
    }

    public function status(): string
    {
        return $this->connected() ? 'connected' : 'demo';
    }

    public function activeProvider(): ?string
    {
        return $this->connection()?->provider;
    }
}
