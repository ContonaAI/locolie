<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A push target for a single device. Web subscriptions still live in
 * PushSubscription; this table carries native FCM (Android) and APNs (iOS)
 * tokens so the same broadcast code reaches the future mobile apps.
 */
#[Fillable(['user_id', 'platform', 'token', 'app_version', 'locale', 'topics', 'last_seen_at'])]
class DeviceToken extends Model
{
    protected function casts(): array
    {
        return [
            'topics' => 'array',
            'last_seen_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
