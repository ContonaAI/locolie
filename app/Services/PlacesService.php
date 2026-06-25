<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Thin wrapper over the Google Places API (New) — shared by the live business
 * signup flow and the seeder, so retailer-created listings carry the exact same
 * data (name, address, coords, rating, photo) that customers see.
 */
class PlacesService
{
    public function enabled(): bool
    {
        return (bool) config('services.google.maps_key');
    }

    protected function key(): ?string
    {
        return config('services.google.maps_key');
    }

    /** Text search → list of candidate businesses. */
    public function search(string $query): array
    {
        if (! $this->enabled()) {
            return [];
        }

        $resp = Http::withHeaders([
            'X-Goog-Api-Key' => $this->key(),
            'X-Goog-FieldMask' => 'places.id,places.displayName,places.formattedAddress,places.location,places.rating,places.userRatingCount',
        ])->post('https://places.googleapis.com/v1/places:searchText', [
            'textQuery' => $query,
            'regionCode' => 'GB',
            'maxResultCount' => 6,
        ]);

        return collect($resp->json('places') ?? [])
            ->map(fn ($p) => [
                'place_id' => data_get($p, 'id'),
                'name' => data_get($p, 'displayName.text'),
                'address' => data_get($p, 'formattedAddress'),
                'lat' => data_get($p, 'location.latitude'),
                'lng' => data_get($p, 'location.longitude'),
                'rating' => data_get($p, 'rating'),
                'reviews' => (int) data_get($p, 'userRatingCount', 0),
                'postcode' => $this->postcode(data_get($p, 'formattedAddress')),
            ])
            ->filter(fn ($x) => $x['name'] && $x['lat'])
            ->values()
            ->all();
    }

    /** Full details for a place id (incl. photo reference). */
    public function details(string $placeId): ?array
    {
        if (! $this->enabled()) {
            return null;
        }

        $resp = Http::withHeaders([
            'X-Goog-Api-Key' => $this->key(),
            'X-Goog-FieldMask' => 'id,displayName,formattedAddress,location,rating,userRatingCount,photos',
        ])->get("https://places.googleapis.com/v1/places/{$placeId}");

        return $resp->successful() ? $resp->json() : null;
    }

    /**
     * Download a Places photo into the git-tracked public/img/biz directory and
     * return a host-relative URL, or null.
     *
     * Imported photos are written under public/ (NOT storage/app/public) so they
     * ship with the repo like the compiled assets do, and therefore survive every
     * production redeploy. The old /storage symlink approach lost images whenever
     * a release recreated the storage dir / dropped the symlink.
     */
    public function downloadPhoto(?string $photoName, int $businessId): ?string
    {
        if (! $photoName || ! $this->enabled()) {
            return null;
        }

        try {
            $img = Http::get("https://places.googleapis.com/v1/{$photoName}/media", [
                'maxHeightPx' => 360,
                'key' => $this->key(),
            ]);
            $ct = (string) $img->header('Content-Type');
            if ($img->successful() && str_starts_with($ct, 'image/')) {
                return static::storePhoto($businessId, $this->recompress($img->body()));
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return null;
    }

    /**
     * Persist a business photo's bytes to public/img/biz/{id}.jpg and return its
     * host-relative URL. Shared by the importer, the seeder and the data sync so
     * every path stores images the same, durable way.
     */
    public static function storePhoto(int $businessId, string $bytes): string
    {
        $dir = public_path('img/biz');
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        file_put_contents("{$dir}/{$businessId}.jpg", $bytes);

        return "/img/biz/{$businessId}.jpg";
    }

    /** Resize to max 360px wide + JPEG q60 so cards stay tiny (~20KB). */
    protected function recompress(string $body): string
    {
        $src = @imagecreatefromstring($body);
        if (! $src) {
            return $body;
        }
        $w = imagesx($src);
        $h = imagesy($src);
        $max = 360;
        if ($w > $max) {
            $nh = (int) round($h * $max / $w);
            $dst = imagecreatetruecolor($max, $nh);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $max, $nh, $w, $h);
            imagedestroy($src);
            $src = $dst;
        }
        ob_start();
        imagejpeg($src, null, 60);
        imagedestroy($src);

        return ob_get_clean();
    }

    public function postcode(?string $addr): ?string
    {
        if ($addr && preg_match('/\b([A-Z]{1,2}\d[A-Z\d]?\s*\d[A-Z]{2})\b/', strtoupper($addr), $m)) {
            return $m[1];
        }

        return null;
    }
}
