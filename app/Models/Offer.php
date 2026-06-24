<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'business_id', 'title', 'badge', 'description', 'terms', 'discount_type', 'sale_type',
    'starts_at', 'ends_at', 'redemption_limit_total', 'per_user_limit', 'quantity', 'redeemed_count', 'status',
])]
class Offer extends Model
{
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /** Remaining redemptions for a limited offer, or null if unlimited. */
    public function remaining(): ?int
    {
        return $this->quantity === null ? null : max(0, $this->quantity - $this->redeemed_count);
    }

    public function isSoldOut(): bool
    {
        return $this->quantity !== null && $this->redeemed_count >= $this->quantity;
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(Redemption::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->where(fn ($q) => $q->whereNull('quantity')->orWhereColumn('redeemed_count', '<', 'quantity'));
    }
}
