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
    'logo_path', 'brand_color', 'email_from_name', 'reply_to_email', 'sms_sender_id',
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

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function templates(): HasMany
    {
        return $this->hasMany(MessageTemplate::class);
    }

    // ── Brand identity used to make every message bespoke ────────────────────

    /** Public URL for the brand logo, or null if none uploaded. */
    public function logoUrl(): ?string
    {
        if (blank($this->logo_path)) {
            return null;
        }

        return \Illuminate\Support\Str::startsWith($this->logo_path, ['http://', 'https://', '/'])
            ? $this->logo_path
            : \Illuminate\Support\Facades\Storage::disk('public')->url($this->logo_path);
    }

    /** Brand accent colour, falling back to the locolie emerald. */
    public function brandColor(): string
    {
        return $this->brand_color ?: '#059669';
    }

    /** Initials shown when a brand has no logo. */
    public function brandInitials(): string
    {
        return \Illuminate\Support\Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($w) => \Illuminate\Support\Str::substr($w, 0, 1))
            ->implode('') ?: 'GL';
    }

    /** Friendly "from" name for email, defaulting to the business name. */
    public function emailFromName(): string
    {
        return $this->email_from_name ?: $this->name;
    }

    /** SMS sender id (alphanumeric, <=11 chars), defaulting to a slug of the name. */
    public function smsSenderId(): string
    {
        $id = $this->sms_sender_id
            ?: \Illuminate\Support\Str::of($this->name)->replaceMatches('/[^A-Za-z0-9]/', '')->substr(0, 11);

        return (string) ($id ?: 'locolie');
    }
}
