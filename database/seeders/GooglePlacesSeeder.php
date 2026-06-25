<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Category;
use App\Models\Offer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Pulls real Newcastle (NE1) businesses from the Google Places API (New) —
 * 3 per category across 10 categories — and replaces the existing companies.
 * Run on demand: php artisan db:seed --class="Database\Seeders\GooglePlacesSeeder"
 */
class GooglePlacesSeeder extends Seeder
{
    public function run(): void
    {
        $key = config('services.google.maps_key');
        if (! $key) {
            $this->command->error('GOOGLE_MAPS_API_KEY not set — skipping.');

            return;
        }

        $hours = [
            'mon' => '9:00–17:30', 'tue' => '9:00–17:30', 'wed' => '9:00–17:30',
            'thu' => '9:00–18:00', 'fri' => '9:00–18:00', 'sat' => '10:00–16:00', 'sun' => 'Closed',
        ];

        $cats = [
            'food-drink'   => ['q' => 'independent restaurant or cafe in Newcastle city centre NE1', 'offers' => [['20% OFF', '20% off food, Mon–Fri', 'Eat-in only · one per customer'], ['FREE COFFEE', 'Free coffee with any brunch', 'Before noon · one per customer'], ['2-FOR-1', '2-for-1 on mains', 'Dinner only · booking advised']]],
            'pubs-bars'    => ['q' => 'pub or bar in Newcastle city centre NE1', 'offers' => [['2 FOR £8', '2 selected pints for £8', 'Mon–Thu before 7pm'], ['15% OFF', '15% off your tab', 'Sun–Thu'], ['FREE', 'Free bar snack with any drink', 'One per customer']]],
            'retail'       => ['q' => 'independent shop or boutique in Newcastle city centre NE1', 'offers' => [['20% OFF', '20% off one item', 'In-store only'], ['£5 OFF', '£5 off when you spend £25', 'One per customer'], ['10% OFF', '10% off for app users', 'Excludes sale items']]],
            'hairdressers' => ['q' => 'hairdresser or barber in Newcastle city centre NE1', 'offers' => [['15% OFF', '15% off any cut', 'Tue–Thu · new clients'], ['£10 OFF', '£10 off colour & cut', 'New clients · booking required'], ['FREE', 'Free fringe trim with any cut', 'One per customer']]],
            'beauty'       => ['q' => 'beauty or nail salon in Newcastle city centre NE1', 'offers' => [['20% OFF', '20% off first treatment', 'New clients'], ['£5 OFF', '£5 off a full set', 'Booking required'], ['15% OFF', '15% off facials', 'Mon–Wed']]],
            'fitness'      => ['q' => 'gym or fitness studio in Newcastle city centre NE1', 'offers' => [['FREE WEEK', 'First week free', 'New members · 18+'], ['50% OFF', '50% off first month', 'No joining fee'], ['FREE', 'Free class taster', 'One per person']]],
            'builders'     => ['q' => 'builder or construction company in Newcastle NE1', 'offers' => [['10% OFF', '10% off your first project', 'Projects over £500'], ['FREE QUOTE', 'Free no-obligation quote', 'New customers'], ['£50 OFF', '£50 off jobs over £1000', 'One per customer']]],
            'mechanics'    => ['q' => 'car garage or mechanic in Newcastle NE1', 'offers' => [['£20 OFF', '£20 off a full service', 'One per vehicle'], ['FREE', 'Free MOT with any service', 'Booking required'], ['10% OFF', '10% off all repairs', 'App users only']]],
            'trades'       => ['q' => 'plumber or electrician in Newcastle NE1', 'offers' => [['NO FEE', 'No call-out fee for app users', 'Standard hours'], ['10% OFF', '10% off any job', 'New customers'], ['FREE QUOTE', 'Free quote within 24h', 'NE area only']]],
            'pet-care'     => ['q' => 'pet groomer or pet shop in Newcastle NE1', 'offers' => [['£5 OFF', '£5 off first full groom', 'New customers · booking required'], ['15% OFF', '15% off pet supplies', 'In-store only'], ['FREE', 'Free nail trim with any groom', 'One per pet']]],
        ];

        // Wipe existing companies (cascades offers + redemptions).
        Business::query()->delete();
        $this->command->info('Cleared existing businesses.');

        $total = 0;

        foreach ($cats as $slug => $cfg) {
            $catId = Category::where('slug', $slug)->value('id');

            $resp = Http::withHeaders([
                'X-Goog-Api-Key' => $key,
                'X-Goog-FieldMask' => 'places.id,places.displayName,places.location,places.formattedAddress,places.rating,places.userRatingCount,places.photos,places.reviews',
            ])->post('https://places.googleapis.com/v1/places:searchText', [
                'textQuery' => $cfg['q'],
                'maxResultCount' => 20,
                'regionCode' => 'GB',
            ]);

            $places = $resp->json('places') ?? [];
            $made = 0;

            foreach ($places as $p) {
                if ($made >= 12) { // ~12 per category × 10 categories ≈ 120 businesses
                    break;
                }
                $name = data_get($p, 'displayName.text');
                $lat = data_get($p, 'location.latitude');
                $lng = data_get($p, 'location.longitude');
                if (! $name || ! $lat) {
                    continue;
                }

                $addr = (string) data_get($p, 'formattedAddress');
                $pc = 'NE1';
                if (preg_match('/\b([A-Z]{1,2}\d[A-Z\d]?\s*\d[A-Z]{2})\b/', strtoupper($addr), $m)) {
                    $pc = str_starts_with($m[1], 'NE') ? $m[1] : 'NE1';
                }

                $offer = $cfg['offers'][$made % 3];

                // Plan distribution for the demo: 1 premium + 2 featured per category, rest free.
                $plan = $made === 0 ? 'premium' : ($made <= 2 ? 'featured' : 'free');
                $planCfg = \App\Models\Business::PLANS[$plan];

                // Real Google reviews (up to 3) when returned.
                $reviews = collect(data_get($p, 'reviews') ?? [])
                    ->map(fn ($r) => [
                        'author' => data_get($r, 'authorAttribution.displayName', 'Google user'),
                        'text' => (string) data_get($r, 'text.text', data_get($r, 'originalText.text', '')),
                        'rating' => (int) round((float) data_get($r, 'rating', 5)),
                    ])
                    ->filter(fn ($r) => $r['text'] !== '')
                    ->take(3)->values()->all();

                $business = Business::updateOrCreate(
                    ['google_place_id' => data_get($p, 'id')],
                    [
                        'name' => $name,
                        'slug' => Business::uniqueSlug($name),
                        'category_id' => $catId,
                        'address' => $addr,
                        'postcode' => $pc,
                        'lat' => $lat,
                        'lng' => $lng,
                        'hours' => $hours,
                        'rating' => data_get($p, 'rating'),
                        'reviews_count' => (int) data_get($p, 'userRatingCount', 0),
                        'reviews' => $reviews ?: null,
                        'featured' => $planCfg['featured'],
                        'plan' => $plan,
                        'priority' => $planCfg['priority'],
                        'onboarded' => true, // MVP: treat all seeded businesses as onboarded
                        'claimed_at' => now(),
                        'owner_email' => 'owner+'.\Illuminate\Support\Str::slug($name).'@locolie.test',
                        'status' => 'active',
                    ]
                );

                // Vary sale types so the lifecycle + filters are demonstrable:
                // 0 = ongoing, 1 = limited (stock), 2 = seasonal (ends soon).
                $saleType = ['ongoing', 'limited', 'seasonal'][$made % 3];
                Offer::updateOrCreate(
                    ['business_id' => $business->id, 'title' => $offer[1]],
                    [
                        'badge' => $offer[0], 'title' => $offer[1], 'terms' => $offer[2], 'discount_type' => 'other',
                        'sale_type' => $saleType,
                        'quantity' => $saleType === 'limited' ? (15 + $made * 5) : null,
                        'ends_at' => $saleType === 'seasonal' ? now()->addWeeks(2) : null,
                        'status' => 'active',
                    ],
                );

                // Download + cache the first Places photo so cards/profiles are visual.
                $photoName = data_get($p, 'photos.0.name');
                if ($photoName) {
                    try {
                        $img = Http::get("https://places.googleapis.com/v1/{$photoName}/media", [
                            'maxHeightPx' => 480,
                            'key' => $key,
                        ]);
                        $ct = (string) $img->header('Content-Type');
                        if ($img->successful() && str_starts_with($ct, 'image/')) {
                            // Save into the git-tracked public/img/biz dir so the
                            // image ships with the repo and survives prod redeploys
                            // (the old /storage symlink kept dropping images).
                            $url = \App\Services\PlacesService::storePhoto($business->id, $img->body());
                            $business->update(['photos' => [$url]]);
                        }
                    } catch (\Throwable $e) {
                        // ignore — fall back to gradient placeholder
                    }
                }

                $made++;
                $total++;
            }

            $this->command->info("  {$slug}: {$made} businesses");
        }

        $this->command->info("Done — {$total} real businesses seeded from Google Places.");
    }
}
