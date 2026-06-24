<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Category;
use App\Models\Offer;
use Illuminate\Database\Seeder;

class BusinessSeeder extends Seeder
{
    public function run(): void
    {
        $cat = fn (string $slug) => Category::where('slug', $slug)->value('id');

        $defaultHours = [
            'mon' => '8:00–17:00', 'tue' => '8:00–17:00', 'wed' => '8:00–17:00',
            'thu' => '8:00–17:00', 'fri' => '8:00–18:00', 'sat' => '9:00–17:00', 'sun' => 'Closed',
        ];

        // Each business carries one seed offer (mirrors the demo data).
        $businesses = [
            [
                'name' => 'The Workshop Café', 'category' => 'food-drink', 'postcode' => 'RG40 1AT',
                'lat' => 51.4112, 'lng' => -0.8341, 'rating' => 4.8, 'reviews_count' => 247,
                'description' => 'Speciality coffee, all-day brunch and pastries on Peach Street.',
                'offer' => ['title' => '20% off everything, Mon–Fri', 'badge' => '20% OFF', 'discount_type' => 'percent', 'terms' => 'Eat-in only · Mon–Fri · one per customer'],
            ],
            [
                'name' => 'The Bramble Bistro', 'category' => 'food-drink', 'postcode' => 'RG40 2BP',
                'lat' => 51.4098, 'lng' => -0.8359, 'rating' => 4.9, 'reviews_count' => 412,
                'description' => 'Neighbourhood French bistro with a seasonal menu.',
                'offer' => ['title' => '2-for-1 on mains', 'badge' => '2-FOR-1', 'discount_type' => 'bogo', 'terms' => 'Dinner only · booking advised'],
            ],
            [
                'name' => 'Salon Loft', 'category' => 'beauty', 'postcode' => 'RG40 1XS',
                'lat' => 51.4125, 'lng' => -0.8318, 'rating' => 4.7, 'reviews_count' => 138,
                'description' => 'Independent hair studio in the heart of Wokingham.',
                'offer' => ['title' => '15% off any cut & finish', 'badge' => '15% OFF', 'discount_type' => 'percent', 'terms' => 'New clients · Tue–Thu'],
            ],
            [
                'name' => 'Penrose & Wright', 'category' => 'retail', 'postcode' => 'RG40 1AR',
                'lat' => 51.4107, 'lng' => -0.8330, 'rating' => 4.7, 'reviews_count' => 96,
                'description' => 'Wine merchant and small-plates bar.',
                'offer' => ['title' => '25% off small plates', 'badge' => '25% OFF', 'discount_type' => 'percent', 'terms' => 'Before 6pm'],
            ],
            [
                'name' => 'Riverside Gym', 'category' => 'fitness', 'postcode' => 'RG41 2RX',
                'lat' => 51.4156, 'lng' => -0.8402, 'rating' => 4.6, 'reviews_count' => 203,
                'description' => 'Independent gym and studio classes.',
                'offer' => ['title' => 'First month free', 'badge' => 'FREE', 'discount_type' => 'free', 'terms' => 'No joining fee · 3-month minimum'],
            ],
        ];

        foreach ($businesses as $b) {
            $business = Business::updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($b['name'])],
                [
                    'name' => $b['name'],
                    'category_id' => $cat($b['category']),
                    'description' => $b['description'],
                    'address' => 'Wokingham',
                    'postcode' => $b['postcode'],
                    'lat' => $b['lat'],
                    'lng' => $b['lng'],
                    'hours' => $defaultHours,
                    'rating' => $b['rating'],
                    'reviews_count' => $b['reviews_count'],
                    'status' => 'active',
                ]
            );

            Offer::updateOrCreate(
                ['business_id' => $business->id, 'title' => $b['offer']['title']],
                array_merge($b['offer'], ['status' => 'active']),
            );
        }
    }
}
