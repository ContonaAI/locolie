<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

#[Fillable([
    'user_id', 'category_id', 'name', 'slug', 'description', 'address', 'postcode',
    'lat', 'lng', 'phone', 'website', 'hours', 'photos', 'reviews', 'google_place_id',
    'rating', 'reviews_count', 'status', 'featured', 'qr_token', 'owner_secret',
    'plan', 'priority', 'onboarded', 'claimed_at', 'lead_notes', 'owner_email', 'password', 'city',
])]
#[\Illuminate\Database\Eloquent\Attributes\Hidden(['owner_secret', 'password'])]
class Business extends Authenticatable
{
    /** Plan catalogue — label, monthly price, and the perks each unlocks. */
    public const PLANS = [
        'free' => ['label' => 'Free', 'price' => 0, 'priority' => 0, 'featured' => false,
            'perks' => ['Listed in search & map', 'Publish offers', 'QR redemptions']],
        'featured' => ['label' => 'Featured', 'price' => 19, 'priority' => 50, 'featured' => true,
            'perks' => ['Everything in Free', 'Featured-rail placement', '“Sponsored” badge', 'Priority in search & map', 'Monthly email feature']],
        'premium' => ['label' => 'Premium', 'price' => 49, 'priority' => 100, 'featured' => true,
            'perks' => ['Everything in Featured', 'Top placement', 'Push to nearby shoppers', 'Unlimited email campaigns', 'Analytics dashboard']],
    ];

    protected function casts(): array
    {
        return [
            'hours' => 'array',
            'photos' => 'array',
            'reviews' => 'array',
            'lat' => 'float',
            'lng' => 'float',
            'rating' => 'float',
            'featured' => 'boolean',
            'onboarded' => 'boolean',
            'claimed_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** Config for this business's current plan. */
    public function planConfig(): array
    {
        return self::PLANS[$this->plan] ?? self::PLANS['free'];
    }

    public function isPaid(): bool
    {
        return in_array($this->plan, ['featured', 'premium'], true);
    }

    public function canPush(): bool
    {
        return $this->plan === 'premium';
    }

    /** Onboarded, live businesses only — what shoppers should ever see. */
    public function scopeLive(Builder $q): Builder
    {
        return $q->where('status', 'active')->where('onboarded', true);
    }

    /** Paid placement first, then featured, then rating. */
    public function scopeRanked(Builder $q): Builder
    {
        return $q->orderByDesc('priority')->orderByDesc('featured')->orderByDesc('rating');
    }

    protected static function booted(): void
    {
        static::creating(function (Business $business) {
            if (blank($business->slug)) {
                $business->slug = static::uniqueSlug($business->name);
            }
            if (blank($business->qr_token)) {
                $business->qr_token = Str::random(32);
            }
            if (blank($business->owner_secret)) {
                $business->owner_secret = Str::random(40);
            }
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'business';
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function activeOffers(): HasMany
    {
        return $this->offers()->where('status', 'active');
    }

    public function favouritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favourites');
    }
}
