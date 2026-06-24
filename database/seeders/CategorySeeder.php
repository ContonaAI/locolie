<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Two-level taxonomy: parent groups with leaf sub-categories.
     * Businesses always link to a LEAF slug. All 12 original leaf slugs are
     * preserved (food-drink, pubs-bars, retail, hairdressers, beauty, fitness,
     * builders, mechanics, trades, pet-care, health, services) so existing
     * business seeders and data keep working.
     */
    protected array $tree = [
        'eat-drink' => ['Food & Drink', [
            'food-drink' => 'Restaurants & Cafés',
            'pubs-bars'  => 'Pubs & Bars',
            'takeaways'  => 'Takeaways',
            'bakeries'   => 'Bakeries & Desserts',
        ]],
        'health-beauty' => ['Health & Beauty', [
            'hairdressers' => 'Hairdressers',
            'barbers'      => 'Barbers',
            'beauty'       => 'Beauty & Nails',
            'spa'          => 'Spa & Massage',
            'health'       => 'Health & Wellbeing',
        ]],
        'fitness-leisure' => ['Fitness & Leisure', [
            'fitness'    => 'Gyms & Fitness',
            'yoga'       => 'Yoga & Pilates',
            'activities' => 'Activities & Days Out',
        ]],
        'home-maintenance' => ['Home & Maintenance', [
            'builders'   => 'Builders',
            'trades'     => 'Plumbers & Electricians',
            'decorators' => 'Painters & Decorators',
            'cleaning'   => 'Cleaning Services',
            'gardening'  => 'Gardening & Landscaping',
        ]],
        'motoring' => ['Motoring', [
            'mechanics' => 'Car Servicing & MOT',
            'tyres'     => 'Tyres & Exhausts',
            'valeting'  => 'Car Wash & Valeting',
        ]],
        'shopping' => ['Shopping', [
            'retail'  => 'Retail & Gifts',
            'fashion' => 'Fashion & Clothing',
            'florists' => 'Florists',
        ]],
        'pets' => ['Pets', [
            'pet-care' => 'Pet Care & Grooming',
            'vets'     => 'Vets',
        ]],
        'professional' => ['Professional Services', [
            'services'      => 'Local Services',
            'estate-agents' => 'Estate Agents',
            'photography'   => 'Photography',
        ]],
    ];

    public function run(): void
    {
        $parentSort = 0;
        $leafSort = 0;

        foreach ($this->tree as $parentSlug => [$parentName, $children]) {
            $parent = Category::updateOrCreate(
                ['slug' => $parentSlug],
                ['name' => $parentName, 'parent_id' => null, 'sort' => ++$parentSort],
            );

            foreach ($children as $slug => $name) {
                Category::updateOrCreate(
                    ['slug' => $slug],
                    ['name' => $name, 'parent_id' => $parent->id, 'sort' => ++$leafSort],
                );
            }
        }
    }
}
