<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Derives the location facets used by the programmatic SEO pages straight from
 * the live business data - cities (Newcastle, Leeds, ...) and postcode districts
 * (NE1, NE2, ...). No hard-coded geography: areas appear automatically as soon
 * as a business in them is onboarded.
 */
class LocationService
{
    /** Outward postcode token for a business, e.g. "NE1 4AA" -> "NE1". */
    public static function outward(?string $postcode): ?string
    {
        $postcode = trim((string) $postcode);

        return $postcode === '' ? null : Str::upper(explode(' ', $postcode)[0]);
    }

    /**
     * Every location facet with a live-business count, biggest first.
     * Each entry: ['type'=>city|district, 'slug'=>, 'label'=>, 'count'=>].
     */
    public static function all(): Collection
    {
        $live = Business::live()->get(['city', 'postcode']);

        $cities = $live->pluck('city')->filter()->countBy()
            ->map(fn ($count, $name) => [
                'type' => 'city',
                'slug' => Str::slug($name),
                'label' => $name,
                'count' => $count,
            ]);

        $districts = $live->pluck('postcode')->map(fn ($p) => self::outward($p))->filter()->countBy()
            ->map(fn ($count, $code) => [
                'type' => 'district',
                'slug' => Str::lower($code),
                'label' => $code,
                'count' => $count,
            ]);

        return $cities->values()->merge($districts->values())
            ->sortByDesc('count')->values();
    }

    /** Resolve a location slug (city or district) to its facet, or null. */
    public static function resolve(string $slug): ?array
    {
        return self::all()->firstWhere('slug', Str::lower($slug));
    }

    /** Live businesses in a location facet, ranked. Filtered in PHP (small set). */
    public static function businesses(array $location): Collection
    {
        $q = Business::live()->ranked()->with(['category.parent', 'activeOffers']);

        if ($location['type'] === 'city') {
            return $q->where('city', $location['label'])->get();
        }

        // District: exact outward-code match (so NE1 never swallows NE15).
        return $q->get()->filter(fn ($b) => self::outward($b->postcode) === Str::upper($location['label']))->values();
    }

    /** Districts that sit within a city (for internal linking between areas). */
    public static function nearby(array $location, int $limit = 6): Collection
    {
        return self::all()
            ->reject(fn ($l) => $l['slug'] === $location['slug'])
            ->take($limit)
            ->values();
    }
}
