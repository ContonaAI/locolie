<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A reusable, brand-aware message body for one channel (email | sms | push).
 * A null business_id is a platform default available to every brand.
 */
#[Fillable(['business_id', 'channel', 'name', 'subject', 'body', 'meta', 'is_default'])]
class MessageTemplate extends Model
{
    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'is_default' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
