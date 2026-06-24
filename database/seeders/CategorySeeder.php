<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Food & Drink',           'slug' => 'food-drink',   'sort' => 1],
            ['name' => 'Pubs & Bars',            'slug' => 'pubs-bars',    'sort' => 2],
            ['name' => 'Retail',                 'slug' => 'retail',       'sort' => 3],
            ['name' => 'Hairdressers',           'slug' => 'hairdressers', 'sort' => 4],
            ['name' => 'Beauty',                 'slug' => 'beauty',       'sort' => 5],
            ['name' => 'Fitness',                'slug' => 'fitness',      'sort' => 6],
            ['name' => 'Builders',               'slug' => 'builders',     'sort' => 7],
            ['name' => 'Mechanics',              'slug' => 'mechanics',    'sort' => 8],
            ['name' => 'Plumbers & Electricians','slug' => 'trades',       'sort' => 9],
            ['name' => 'Pet Care',               'slug' => 'pet-care',     'sort' => 10],
            ['name' => 'Health & Wellbeing',     'slug' => 'health',       'sort' => 11],
            ['name' => 'Services',               'slug' => 'services',     'sort' => 12],
        ];

        foreach ($categories as $c) {
            Category::updateOrCreate(['slug' => $c['slug']], $c);
        }
    }
}
