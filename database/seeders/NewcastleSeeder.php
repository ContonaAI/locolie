<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Category;
use App\Models\Offer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewcastleSeeder extends Seeder
{
    public function run(): void
    {
        $cat = fn (string $slug) => Category::where('slug', $slug)->value('id');

        $hours = [
            'mon' => '9:00–17:30', 'tue' => '9:00–17:30', 'wed' => '9:00–17:30',
            'thu' => '9:00–18:00', 'fri' => '9:00–18:00', 'sat' => '10:00–16:00', 'sun' => 'Closed',
        ];

        // One independent NE1 (Newcastle city centre) business per category.
        $businesses = [
            [
                'name' => 'The Quayside Tap', 'category' => 'pubs-bars', 'postcode' => 'NE1 3RW',
                'lat' => 54.9696, 'lng' => -1.6005, 'rating' => 4.7, 'reviews_count' => 318,
                'description' => 'Independent craft-beer pub on the Quayside with 12 rotating taps.',
                'offer' => ['title' => '2 pints for £8, Mon–Thu', 'badge' => '2 FOR £8', 'discount_type' => 'amount', 'terms' => 'Selected cask & keg lines · Mon–Thu before 7pm'],
            ],
            [
                'name' => 'Tyne Build & Restore', 'category' => 'builders', 'postcode' => 'NE1 2EX',
                'lat' => 54.9738, 'lng' => -1.6131, 'rating' => 4.9, 'reviews_count' => 64,
                'description' => 'Family-run builders specialising in period property restoration.',
                'offer' => ['title' => 'Free quote + 10% off first project', 'badge' => '10% OFF', 'discount_type' => 'percent', 'terms' => 'New customers · projects over £500'],
            ],
            [
                'name' => 'Grainger Street Garage', 'category' => 'mechanics', 'postcode' => 'NE1 5JG',
                'lat' => 54.9701, 'lng' => -1.6151, 'rating' => 4.8, 'reviews_count' => 211,
                'description' => 'Independent MOT centre and servicing garage in the city centre.',
                'offer' => ['title' => '£20 off a full service', 'badge' => '£20 OFF', 'discount_type' => 'amount', 'terms' => 'One per vehicle · cannot combine with MOT offer'],
            ],
            [
                'name' => 'Bigg Market Barbers', 'category' => 'hairdressers', 'postcode' => 'NE1 1EW',
                'lat' => 54.9725, 'lng' => -1.6138, 'rating' => 4.6, 'reviews_count' => 489,
                'description' => 'Traditional barbershop — cuts, hot towel shaves and beard trims.',
                'offer' => ['title' => '15% off cut & beard combo', 'badge' => '15% OFF', 'discount_type' => 'percent', 'terms' => 'Tue–Thu · walk-ins welcome'],
            ],
            [
                'name' => 'Pilgrim Street Kitchen', 'category' => 'food-drink', 'postcode' => 'NE1 6QF',
                'lat' => 54.9719, 'lng' => -1.6097, 'rating' => 4.8, 'reviews_count' => 276,
                'description' => 'All-day independent kitchen and brunch spot.',
                'offer' => ['title' => 'Free coffee with any brunch', 'badge' => 'FREE COFFEE', 'discount_type' => 'free', 'terms' => 'Before noon · one per customer'],
            ],
            [
                'name' => 'Tyneside Plumbing & Electrical', 'category' => 'trades', 'postcode' => 'NE1 4SD',
                'lat' => 54.9684, 'lng' => -1.6112, 'rating' => 4.9, 'reviews_count' => 152,
                'description' => 'NICEIC-registered electricians and Gas Safe plumbers.',
                'offer' => ['title' => 'No call-out fee for app users', 'badge' => 'NO FEE', 'discount_type' => 'other', 'terms' => 'Standard hours · NE1 & surrounding only'],
            ],
            [
                'name' => 'Grey Street Records', 'category' => 'retail', 'postcode' => 'NE1 6EE',
                'lat' => 54.9706, 'lng' => -1.6118, 'rating' => 4.7, 'reviews_count' => 133,
                'description' => 'Independent vinyl record shop on historic Grey Street.',
                'offer' => ['title' => '20% off all second-hand vinyl', 'badge' => '20% OFF', 'discount_type' => 'percent', 'terms' => 'In-store only · excludes new releases'],
            ],
            [
                'name' => 'Quayside Strength Co.', 'category' => 'fitness', 'postcode' => 'NE1 3DX',
                'lat' => 54.9691, 'lng' => -1.6019, 'rating' => 4.8, 'reviews_count' => 188,
                'description' => 'Independent strength & conditioning gym by the river.',
                'offer' => ['title' => 'First week free, no contract', 'badge' => 'FREE WEEK', 'discount_type' => 'free', 'terms' => 'New members · 18+'],
            ],
            [
                'name' => 'Toon Paws Grooming', 'category' => 'pet-care', 'postcode' => 'NE1 7RU',
                'lat' => 54.9752, 'lng' => -1.6147, 'rating' => 4.9, 'reviews_count' => 97,
                'description' => 'Independent dog groomers in the heart of Newcastle.',
                'offer' => ['title' => '£5 off first full groom', 'badge' => '£5 OFF', 'discount_type' => 'amount', 'terms' => 'New customers · booking required'],
            ],
            [
                'name' => 'Newcastle Sports Therapy', 'category' => 'health', 'postcode' => 'NE1 8QB',
                'lat' => 54.9760, 'lng' => -1.6089, 'rating' => 4.9, 'reviews_count' => 124,
                'description' => 'Independent sports massage and physiotherapy clinic.',
                'offer' => ['title' => '£10 off a 60-min sports massage', 'badge' => '£10 OFF', 'discount_type' => 'amount', 'terms' => 'First appointment · Mon–Fri'],
            ],
        ];

        foreach ($businesses as $b) {
            $business = Business::updateOrCreate(
                ['slug' => Str::slug($b['name'])],
                [
                    'name' => $b['name'],
                    'category_id' => $cat($b['category']),
                    'description' => $b['description'],
                    'address' => 'Newcastle upon Tyne',
                    'postcode' => $b['postcode'],
                    'lat' => $b['lat'],
                    'lng' => $b['lng'],
                    'hours' => $hours,
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
