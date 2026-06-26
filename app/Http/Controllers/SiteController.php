<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Category;

class SiteController extends Controller
{
    /** Public marketing homepage. */
    public function home()
    {
        return view('site.home', [
            'mapsKey' => config('services.google.maps_key'),
            'mapsId' => config('services.google.maps_id'),
            'stats' => [
                'businesses' => Business::live()->count(),
                'categories' => Category::supportsHierarchy() ? Category::leaves()->count() : Category::count(),
                'offers' => \App\Models\Offer::where('status', 'active')->count(),
            ],
            'categories' => $this->categoriesWithCounts(),
            'featured' => Business::live()->ranked()
                ->with(['category', 'activeOffers'])
                ->whereNotNull('photos')
                ->take(6)->get(),
            // Per-city counts (for the dynamic, location-aware hero).
            'cityData' => Business::whereNotNull('city')
                ->selectRaw('city, count(*) as c, max(onboarded) as live')
                ->groupBy('city')->get()
                ->mapWithKeys(fn ($r) => [$r->city => ['count' => (int) $r->c, 'live' => (bool) $r->live]]),
            // Lightweight points for the reach map.
            'mapPoints' => Business::whereNotNull('lat')->get(['lat', 'lng', 'onboarded', 'city'])
                ->map(fn ($b) => ['lat' => (float) $b->lat, 'lng' => (float) $b->lng, 'live' => (bool) $b->onboarded]),
        ]);
    }

    /** Dedicated For-Business marketing page. */
    public function forBusiness()
    {
        return view('site.for-business', [
            'plans' => Business::PLANS,
        ]);
    }

    /** SEO category landing page — real independents in that category. */
    public function category(string $slug)
    {
        $category = Category::where('slug', $slug)->firstOr(fn () => abort(404));

        // A parent category lists every business across its sub-categories.
        $ids = $this->categoryAndDescendantIds($category);

        $businesses = Business::live()->ranked()
            ->whereIn('category_id', $ids)
            ->with(['category', 'activeOffers'])
            ->get();

        return view('site.category', [
            'category' => $category,
            'businesses' => $businesses,
            'categories' => $this->categoriesWithCounts(),
        ]);
    }

    /** This category id plus every descendant id (handles parent → sub → sub-trade). */
    protected function categoryAndDescendantIds(Category $category): array
    {
        $ids = [$category->id];
        foreach (Category::where('parent_id', $category->id)->get() as $child) {
            $ids = array_merge($ids, $this->categoryAndDescendantIds($child));
        }

        return $ids;
    }

    /** SEO landing page for a single business — great for local search. */
    public function business(string $slug)
    {
        $business = Business::live()->where('slug', $slug)
            ->with(['category', 'activeOffers'])
            ->firstOr(fn () => abort(404));

        $related = Business::live()->ranked()
            ->where('category_id', $business->category_id)
            ->where('id', '!=', $business->id)
            ->whereNotNull('photos')
            ->take(3)->get();

        return view('site.business', compact('business', 'related'));
    }

    /** Categories that actually have live businesses, with counts. */
    protected function categoriesWithCounts()
    {
        return Category::query()
            ->withCount(['businesses as live_count' => fn ($q) => $q->live()])
            ->orderBy('sort')->get()
            ->filter(fn ($c) => $c->live_count > 0)
            ->values();
    }
}
