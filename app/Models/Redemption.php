<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'offer_id', 'business_id', 'user_id', 'customer_name', 'customer_email', 'customer_phone',
    'marketing_opt_in', 'sms_opt_in', 'code', 'status', 'source', 'expires_at', 'redeemed_at',
])]
class Redemption extends Model
{
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'redeemed_at' => 'datetime',
            'marketing_opt_in' => 'boolean',
            'sms_opt_in' => 'boolean',
        ];
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->status !== 'redeemed'
            && $this->expires_at !== null
            && $this->expires_at->isPast();
    }
}
