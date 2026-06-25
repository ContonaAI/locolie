<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\LocationService;
use App\Services\SeoContentService;

/**
 * Programmatic local-SEO pages: a self-generating set of "{category} in
 * {location}" landing pages plus area hubs and a directory index. Pages are
 * built from live data + the SeoContentService, so they appear automatically as
 * businesses are onboarded and each carries unique copy, FAQs and rich snippets.
 */
class SeoController extends Controller
{
    public function __construct(protected SeoContentService $seo) {}

    /** /local - directory index linking every area and category combo. */
    public function index()
    {
        $locations = LocationService::all();
        $parents = Category::query()->when(
            Category::supportsHierarchy(),
            fn ($q) => $q->parents(),
        )->with('children')->orderBy('sort')->get();

        return view('site.seo.index', [
            'locations' => $locations,
            'parents' => $parents,
        ]);
    }

    /** /local/{area} - every independent business in an area. */
    public function area(string $area)
    {
        $location = LocationService::resolve($area) ?? abort(404);
        $businesses = LocationService::businesses($location);

        // Group the area's businesses by their top-level category for the page.
        $byCategory = $businesses->groupBy(fn ($b) => $b->category?->parent?->name ?? $b->category?->name ?? 'Other')
            ->sortKeys();

        return view('site.seo.area', [
            'location' => $location,
            'businesses' => $businesses,
            'byCategory' => $byCategory,
            'content' => $this->seo->locationHub($location, $businesses),
            'nearby' => LocationService::nearby($location),
            'categories' => $this->areaCategories($businesses),
        ]);
    }

    /** /local/{area}/{category} - the money page: a category in an area. */
    public function categoryInArea(string $area, string $categorySlug)
    {
        $location = LocationService::resolve($area) ?? abort(404);
        $category = Category::where('slug', $categorySlug)->firstOr(fn () => abort(404));

        $ids = $this->categoryAndDescendantIds($category);
        $businesses = LocationService::businesses($location)
            ->filter(fn ($b) => in_array($b->category_id, $ids, true))
            ->values();

        return view('site.seo.landing', [
            'location' => $location,
            'category' => $category,
            'businesses' => $businesses,
            'content' => $this->seo->categoryInLocation($category, $location, $businesses),
            'children' => Category::supportsHierarchy()
                ? $category->children()->get()
                : collect(),
            'nearby' => LocationService::nearby($location),
            'siblingCategories' => $this->siblingCategories($category),
        ]);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /** Distinct categories present in an area, with counts, for chip links. */
    protected function areaCategories($businesses)
    {
        return $businesses
            ->groupBy(fn ($b) => $b->category?->parent?->slug ?? $b->category?->slug)
            ->map(fn ($g, $slug) => [
                'slug' => $slug,
                'name' => $g->first()->category?->parent?->name ?? $g->first()->category?->name,
                'count' => $g->count(),
            ])
            ->filter(fn ($c) => $c['slug'])
            ->sortByDesc('count')->values();
    }

    /** Sibling/related categories for cross-linking (same parent). */
    protected function siblingCategories(Category $category)
    {
        if (! Category::supportsHierarchy() || ! $category->parent_id) {
            return collect();
        }

        return Category::where('parent_id', $category->parent_id)
            ->where('id', '!=', $category->id)
            ->orderBy('sort')->get();
    }

    protected function categoryAndDescendantIds(Category $category): array
    {
        $ids = [$category->id];
        if (Category::supportsHierarchy()) {
            foreach (Category::where('parent_id', $category->id)->get() as $child) {
                $ids = array_merge($ids, $this->categoryAndDescendantIds($child));
            }
        }

        return $ids;
    }
}
