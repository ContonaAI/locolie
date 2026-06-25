<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'business_id', 'customer_email', 'rule_id', 'label',
    'code', 'status', 'earned_at', 'redeemed_at',
])]
class LoyaltyReward extends Model
{
    protected function casts(): array
    {
        return [
            'earned_at' => 'datetime',
            'redeemed_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(LoyaltyRule::class, 'rule_id');
    }
}
