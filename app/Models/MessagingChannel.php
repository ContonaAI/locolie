<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * A connected delivery provider for a channel - e.g. Google for email,
 * Twilio for SMS, FCM for native push. Secrets live in env; this row stores
 * the connection state and non-secret display config the UI needs.
 */
#[Fillable(['channel', 'provider', 'label', 'status', 'config', 'connected_at'])]
class MessagingChannel extends Model
{
    protected function casts(): array
    {
        return [
            'config' => 'array',
            'connected_at' => 'datetime',
        ];
    }

    public function isLive(): bool
    {
        return $this->status === 'connected';
    }
}
