<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Category;
use Illuminate\Http\Request;

class BrowseController extends Controller
{
    public function categories()
    {
        // Parent groups, each with their leaf sub-categories nested under `children`.
        return Category::parents()
            ->with(['children' => fn ($q) => $q->orderBy('sort')])
            ->orderBy('sort')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'children' => $p->children->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                ])->values(),
            ]);
    }

    public function businesses(Request $request)
    {
        $query = Business::query()
            ->with(['category.parent', 'activeOffers'])
            ->live();

        if ($postcode = $request->query('postcode')) {
            // Match on the outward code, e.g. "NE1" matches "NE1 6QF".
            $query->where('postcode', 'like', trim($postcode).'%');
        }

        if ($slug = $request->query('category')) {
            // Match a leaf slug directly, or a parent slug (= all its sub-categories).
            $query->whereHas('category', fn ($q) => $q
                ->where('slug', $slug)
                ->orWhereHas('parent', fn ($p) => $p->where('slug', $slug)));
        }

        if ($q = $request->query('q')) {
            $query->where(fn ($w) => $w
                ->where('name', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%"));
        }

        // Paid placement first, then live-offer count, then rating.
        $businesses = $query->ranked()->get()
            ->sortByDesc(fn ($b) => [$b->priority, $b->activeOffers->count(), (float) $b->rating])
            ->values();

        return $businesses->map(fn ($b) => $this->present($b));
    }

    public function business(Business $business)
    {
        $business->load(['category.parent', 'activeOffers']);

        return $this->present($business, full: true);
    }

    /** Resolve a window-sticker QR token to its business (used by the in-app scanner). */
    public function byToken(string $token)
    {
        $business = Business::with(['category.parent', 'activeOffers'])
            ->where('qr_token', $token)
            ->firstOr(fn () => abort(404));

        return $this->present($business, full: true);
    }

    protected function present(Business $b, bool $full = false): array
    {
        $data = [
            'id' => $b->id,
            'name' => $b->name,
            'slug' => $b->slug,
            'category' => $b->category?->name,
            'category_slug' => $b->category?->slug,
            'category_parent' => $b->category?->parent?->name,
            'category_parent_slug' => $b->category?->parent?->slug,
            'postcode' => $b->postcode,
            'lat' => $b->lat,
            'lng' => $b->lng,
            'hours' => $b->hours,
            'rating' => $b->rating,
            'reviews_count' => $b->reviews_count,
            'image' => is_array($b->photos) ? ($b->photos[0] ?? null) : null,
            'featured' => (bool) $b->featured,
            'plan' => $b->plan,
            'distance' => $this->fakeDistance($b),
            'offers' => $b->activeOffers->map(fn ($o) => [
                'id' => $o->id,
                'title' => $o->title,
                'badge' => $o->badge,
                'terms' => $o->terms,
                'discount_type' => $o->discount_type,
                'sale_type' => $o->sale_type,
                'remaining' => $o->remaining(),
            ])->values(),
        ];

        if ($full) {
            $data += [
                'description' => $b->description,
                'address' => $b->address,
                'phone' => $b->phone,
                'website' => $b->website,
                'reviews' => (is_array($b->reviews) && count($b->reviews)) ? $b->reviews : $this->reviews($b),
            ];
        }

        return $data;
    }

    /** Sample Google-style reviews for the prototype (deterministic per business). */
    protected function reviews(Business $b): array
    {
        $pool = [
            ['author' => 'Sarah J.',  'text' => 'Genuinely lovely independent spot — the app discount made it even better.'],
            ['author' => 'Mark T.',   'text' => 'Staff were great and redeeming the offer took two seconds at the till.'],
            ['author' => 'Priya K.',  'text' => 'Found this through locolie and now go every week. Highly recommend.'],
            ['author' => 'Dan W.',    'text' => 'Friendly, local and good value. Exactly what the high street needs.'],
            ['author' => 'Chloe M.',  'text' => 'Quick, easy and the offer was honoured with no fuss at all.'],
        ];

        $start = $b->id % count($pool);

        return collect([0, 1, 2])->map(function ($i) use ($pool, $start, $b) {
            $r = $pool[($start + $i) % count($pool)];

            return [
                'author' => $r['author'],
                'text' => $r['text'],
                'rating' => min(5, (int) round(($b->rating ?? 4.7))),
            ];
        })->all();
    }

    /** Stable pseudo-distance for the prototype (no live geolocation yet). */
    protected function fakeDistance(Business $b): string
    {
        return number_format(0.2 + (($b->id * 37) % 13) / 10, 1);
    }
}
