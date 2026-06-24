<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Category;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Local -> production data sync. Pushes curated categories, businesses, offers
 * (and their photo files) from the local SQLite build up to the live MySQL site.
 *
 * One-way and idempotent: records are matched on natural keys and upserted, so
 * re-running never duplicates and never deletes production-only data. Guarded by
 * the `sync.token` middleware (see routes/api.php).
 */
class SyncController extends Controller
{
    /** Current data footprint of THIS environment — used by the sync command + admin page. */
    public function status()
    {
        return response()->json($this->counts());
    }

    /** Upsert categories, businesses and offers from the pushed payload. */
    public function data(Request $request)
    {
        $data = $request->validate([
            'categories' => ['array'],
            'businesses' => ['array'],
            'offers' => ['array'],
        ]);

        $result = ['categories' => 0, 'businesses' => 0, 'offers' => 0];

        // 1. Categories — matched on slug.
        foreach ($data['categories'] ?? [] as $row) {
            if (blank($row['slug'] ?? null)) {
                continue;
            }
            Category::updateOrCreate(
                ['slug' => $row['slug']],
                collect($row)->only(['name', 'icon', 'sort'])->toArray(),
            );
            $result['categories']++;
        }

        // Build a slug -> id map once for fast category resolution.
        $catIds = Category::pluck('id', 'slug');

        // 2. Businesses — matched on google_place_id when present, else slug.
        foreach ($data['businesses'] ?? [] as $row) {
            $match = filled($row['google_place_id'] ?? null)
                ? ['google_place_id' => $row['google_place_id']]
                : ['slug' => $row['slug'] ?? null];

            if (blank(reset($match))) {
                continue;
            }

            $attrs = collect($row)->except(['category_slug', 'category_id', 'user_id'])->toArray();
            $attrs['category_id'] = $catIds[$row['category_slug'] ?? null] ?? null;

            Business::withoutEvents(fn () => Business::updateOrCreate($match, $attrs));
            $result['businesses']++;
        }

        // Build a business natural-key -> id map for offer resolution.
        $bizBySlug = Business::pluck('id', 'slug');
        $bizByPlace = Business::whereNotNull('google_place_id')->pluck('id', 'google_place_id');

        // 3. Offers — matched on (business + title).
        foreach ($data['offers'] ?? [] as $row) {
            $bizId = $bizByPlace[$row['business_google_place_id'] ?? null]
                ?? $bizBySlug[$row['business_slug'] ?? null]
                ?? null;

            if (! $bizId || blank($row['title'] ?? null)) {
                continue;
            }

            $attrs = collect($row)->except(['business_slug', 'business_google_place_id', 'business_id'])->toArray();
            $attrs['business_id'] = $bizId;

            Offer::updateOrCreate(['business_id' => $bizId, 'title' => $row['title']], $attrs);
            $result['offers']++;
        }

        Cache::forever('sync.last_at', now()->toIso8601String());

        return response()->json([
            'ok' => true,
            'upserted' => $result,
            'totals' => $this->counts(),
        ]);
    }

    /** Receive a single photo file and store it under storage/app/public/<path>. */
    public function image(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'image', 'max:10240'],
            'path' => ['required', 'string'],
        ]);

        // Sanitise: only allow biz/<filename>, no traversal.
        $path = ltrim($request->string('path'), '/');
        if (! preg_match('#^biz/[A-Za-z0-9._-]+$#', $path)) {
            return response()->json(['message' => 'Illegal image path.'], 422);
        }

        Storage::disk('public')->putFileAs(
            dirname($path),
            $request->file('file'),
            basename($path),
        );

        return response()->json(['ok' => true, 'path' => $path]);
    }

    private function counts(): array
    {
        $bizDir = Storage::disk('public')->exists('biz') ? Storage::disk('public')->files('biz') : [];

        return [
            'categories' => Category::count(),
            'businesses' => Business::count(),
            'offers' => Offer::count(),
            'images' => count($bizDir),
            'last_sync' => Cache::get('sync.last_at'),
        ];
    }
}
