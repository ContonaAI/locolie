<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

/**
 * Scouts ~3 real independents per category across major UK cities (no photos)
 * so the "reach" map shows national coverage. These are EXPANSION prospects
 * (onboarded = false), keeping the Newcastle MVP listings clean.
 */
class MultiCitySeeder extends Seeder
{
    public function run(): void
    {
        $key = config('services.google.maps_key');
        if (! $key) {
            $this->command->error('No Google key - skipping.');

            return;
        }

        $cities = ['London', 'Manchester', 'Birmingham', 'Leeds', 'Liverpool', 'Edinburgh', 'Glasgow', 'Bristol'];

        $nouns = [
            'food-drink' => 'independent cafe or restaurant',
            'pubs-bars' => 'independent pub or bar',
            'retail' => 'independent shop or boutique',
            'hairdressers' => 'hairdresser or barber',
            'beauty' => 'beauty or nail salon',
            'fitness' => 'independent gym or fitness studio',
            'builders' => 'builder or construction company',
            'mechanics' => 'car garage or mechanic',
            'trades' => 'plumber or electrician',
            'pet-care' => 'pet groomer or pet shop',
        ];

        $catIds = Category::pluck('id', 'slug');
        $total = 0;

        foreach ($cities as $city) {
            $cityCount = 0;
            foreach ($nouns as $slug => $noun) {
                $resp = Http::withHeaders([
                    'X-Goog-Api-Key' => $key,
                    'X-Goog-FieldMask' => 'places.id,places.displayName,places.location,places.formattedAddress,places.rating,places.userRatingCount',
                ])->post('https://places.googleapis.com/v1/places:searchText', [
                    'textQuery' => "{$noun} in {$city} UK",
                    'maxResultCount' => 6,
                    'regionCode' => 'GB',
                ]);

                $made = 0;
                foreach ($resp->json('places') ?? [] as $p) {
                    if ($made >= 3) {
                        break;
                    }
                    $name = data_get($p, 'displayName.text');
                    $lat = data_get($p, 'location.latitude');
                    $lng = data_get($p, 'location.longitude');
                    if (! $name || ! $lat) {
                        continue;
                    }
                    $addr = (string) data_get($p, 'formattedAddress');
                    $pc = preg_match('/\b([A-Z]{1,2}\d[A-Z\d]?)\s*\d[A-Z]{2}\b/', strtoupper($addr), $m) ? $m[1] : null;

                    Business::updateOrCreate(
                        ['google_place_id' => data_get($p, 'id')],
                        [
                            'name' => $name,
                            'slug' => Business::uniqueSlug($name),
                            'category_id' => $catIds[$slug] ?? null,
                            'address' => $addr,
                            'postcode' => $pc,
                            'city' => $city,
                            'lat' => $lat,
                            'lng' => $lng,
                            'rating' => data_get($p, 'rating'),
                            'reviews_count' => (int) data_get($p, 'userRatingCount', 0),
                            'plan' => 'free',
                            'onboarded' => false, // expansion prospect, not live yet
                            'status' => 'active',
                        ]
                    );
                    $made++;
                    $cityCount++;
                    $total++;
                }
            }
            $this->command->info("  {$city}: {$cityCount}");
        }

        $this->command->info("Done - scouted {$total} businesses across ".count($cities).' cities.');
    }
}
