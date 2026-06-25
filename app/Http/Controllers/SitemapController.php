<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Category;
use App\Services\LocationService;
use App\Support\Locales;
use Illuminate\Support\Carbon;

/**
 * XML sitemap, optimised for international + image discovery:
 *   - hreflang alternates per URL (en-GB + x-default + the community languages
 *     as ?hl= variants) via the xhtml namespace,
 *   - <lastmod> so Google knows what changed,
 *   - tuned <priority> / <changefreq> per page type,
 *   - <image:image> entries for business photos (Google Images).
 *
 * Self-building from live data, so new shops / areas / categories appear
 * automatically. Stays well under the 50,000-URL / 50MB sitemap limits at our
 * scale; if it ever grows past that, split into a sitemap index.
 */
class SitemapController extends Controller
{
    public function index()
    {
        $now = Carbon::now()->toAtomString();
        $entries = [];

        // ── Static marketing + hub pages ─────────────────────────────────────
        foreach ([
            ['/', '1.0', 'daily'],
            ['/for-business', '0.8', 'weekly'],
            ['/app', '0.7', 'weekly'],
            ['/local', '0.7', 'weekly'],
            ['/terms', '0.2', 'yearly'],
            ['/privacy', '0.2', 'yearly'],
            ['/cookies', '0.2', 'yearly'],
        ] as [$path, $priority, $freq]) {
            $entries[$path] = ['loc' => url($path), 'lastmod' => $now, 'priority' => $priority, 'changefreq' => $freq, 'alts' => true];
        }

        // ── Categories ────────────────────────────────────────────────────────
        foreach (Category::all() as $cat) {
            $entries['/category/'.$cat->slug] = [
                'loc' => url('/category/'.$cat->slug),
                'lastmod' => optional($cat->updated_at)->toAtomString() ?? $now,
                'priority' => '0.6', 'changefreq' => 'weekly', 'alts' => true,
            ];
        }

        // ── Shops (with their photo as an image entry) ───────────────────────
        foreach (Business::live()->get() as $b) {
            $entries['/shop/'.$b->slug] = [
                'loc' => url('/shop/'.$b->slug),
                'lastmod' => optional($b->updated_at)->toAtomString() ?? $now,
                'priority' => $b->isPaid() ? '0.7' : '0.5',
                'changefreq' => 'weekly',
                'alts' => true,
                'image' => ! empty($b->photos[0]) ? url($b->photos[0]) : null,
                'image_caption' => $b->name,
            ];
        }

        // ── Programmatic "{category} in {area}" pages + area hubs ────────────
        foreach (LocationService::all() as $loc) {
            $entries['/local/'.$loc['slug']] = [
                'loc' => url('/local/'.$loc['slug']),
                'lastmod' => $now, 'priority' => '0.6', 'changefreq' => 'weekly', 'alts' => true,
            ];
            foreach (LocationService::businesses($loc) as $b) {
                foreach (array_filter([$b->category?->slug, $b->category?->parent?->slug]) as $cslug) {
                    $path = '/local/'.$loc['slug'].'/'.$cslug;
                    $entries[$path] = [
                        'loc' => url($path), 'lastmod' => $now,
                        'priority' => '0.7', 'changefreq' => 'weekly', 'alts' => true,
                    ];
                }
            }
        }

        return response($this->render($entries), 200, ['Content-Type' => 'application/xml']);
    }

    /** Build the XML document from the entry map. */
    protected function render(array $entries): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'
            .' xmlns:xhtml="http://www.w3.org/1999/xhtml"'
            .' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n";

        foreach ($entries as $e) {
            $xml .= '  <url>'."\n";
            $xml .= '    <loc>'.e($e['loc']).'</loc>'."\n";

            // hreflang alternates (en-GB + x-default + community languages).
            if (! empty($e['alts'])) {
                foreach (Locales::alternatesFor($e['loc']) as $alt) {
                    $xml .= '    <xhtml:link rel="alternate" hreflang="'.e($alt['hreflang']).'" href="'.e($alt['href']).'"/>'."\n";
                }
            }

            if (! empty($e['image'])) {
                $xml .= '    <image:image><image:loc>'.e($e['image']).'</image:loc>'
                    .'<image:caption>'.e($e['image_caption'] ?? '').'</image:caption></image:image>'."\n";
            }

            $xml .= '    <lastmod>'.e($e['lastmod']).'</lastmod>'."\n";
            $xml .= '    <changefreq>'.e($e['changefreq']).'</changefreq>'."\n";
            $xml .= '    <priority>'.e($e['priority']).'</priority>'."\n";
            $xml .= '  </url>'."\n";
        }

        return $xml.'</urlset>'."\n";
    }
}
