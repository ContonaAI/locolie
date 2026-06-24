<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Nested taxonomy. A node value is either a string (leaf name) or
     * [name, [children…]]. Depth is arbitrary — e.g. Builders expands into
     * Google-style building trades. Businesses link to the deepest node that
     * fits; the original 12 leaf slugs are preserved so existing data/seeders
     * keep working.
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
            'builders' => ['Builders', [
                'general-builders' => 'General Builders',
                'extensions'       => 'Extensions & New Builds',
                'loft-conversions' => 'Loft Conversions',
                'roofing'          => 'Roofing',
                'bricklaying'      => 'Bricklaying & Masonry',
                'plastering'       => 'Plastering & Rendering',
                'carpentry'        => 'Carpentry & Joinery',
                'groundworks'      => 'Groundworks & Foundations',
                'scaffolding'      => 'Scaffolding',
                'damp-proofing'    => 'Damp Proofing',
            ]],
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
            'retail'   => 'Retail & Gifts',
            'fashion'  => 'Fashion & Clothing',
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

    protected int $sort = 0;

    public function run(): void
    {
        $this->sort = 0;
        $this->seedLevel($this->tree, null);
    }

    protected function seedLevel(array $nodes, ?int $parentId): void
    {
        foreach ($nodes as $slug => $value) {
            [$name, $children] = is_array($value) ? $value : [$value, null];

            $cat = Category::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'parent_id' => $parentId, 'sort' => ++$this->sort],
            );

            if (is_array($children)) {
                $this->seedLevel($children, $cat->id);
            }
        }
    }
}
