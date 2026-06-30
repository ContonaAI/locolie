<?php

namespace Database\Seeders;

use App\Models\ContentBlock;
use Illuminate\Database\Seeder;

/**
 * Seeds a handful of sensible default content blocks so the /portal/content
 * editor is not empty on first open. Idempotent - matches by key, so it is
 * safe to run repeatedly and will not clobber values an admin has already
 * edited (only fills in label/group/type/sort + a starter value once).
 *
 * Copy here is generic on purpose; live launch-market values (city / price)
 * stay in config/locolie.php and views interpolate the shared $ll vars - the
 * CMS does not re-hardcode them.
 */
class ContentBlockSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->blocks() as $i => $block) {
            ContentBlock::firstOrCreate(
                ['key' => $block['key']],
                array_merge($block, ['sort' => $block['sort'] ?? $i]),
            );
        }

        $this->command?->info('Content blocks seeded ('.count($this->blocks()).' defaults).');
    }

    /** The starter set of editable blocks, grouped by page. */
    protected function blocks(): array
    {
        return [
            // ── Home page ────────────────────────────────────────────────────
            [
                'key' => 'home.hero.eyebrow',
                'group' => 'home',
                'type' => 'text',
                'label' => 'Hero eyebrow',
                'help' => 'Small label above the main headline.',
                'value' => 'Your high street, rewarded',
            ],
            [
                'key' => 'home.hero.title',
                'group' => 'home',
                'type' => 'text',
                'label' => 'Hero title',
                'help' => 'The big headline on the homepage.',
                'value' => 'Discover and support local businesses',
            ],
            [
                'key' => 'home.hero.subtitle',
                'group' => 'home',
                'type' => 'richtext',
                'label' => 'Hero subtitle',
                'help' => 'Supporting line under the headline.',
                'value' => 'Find exclusive offers, collect loyalty rewards and back the independent shops near you.',
            ],
            [
                'key' => 'home.hero.cta_label',
                'group' => 'home',
                'type' => 'text',
                'label' => 'Hero button label',
                'value' => 'Explore offers near me',
            ],
            [
                'key' => 'home.hero.cta_url',
                'group' => 'home',
                'type' => 'url',
                'label' => 'Hero button link',
                'help' => 'Where the hero button points.',
                'value' => '/local',
            ],
            [
                'key' => 'home.hero.image',
                'group' => 'home',
                'type' => 'image',
                'label' => 'Hero image URL',
                'help' => 'Paste an image URL (full path or a /storage/... path).',
                'value' => '/images/seo/local-shopping.jpg',
            ],

            // ── For-business page ────────────────────────────────────────────
            [
                'key' => 'business.hero.title',
                'group' => 'for-business',
                'type' => 'text',
                'label' => 'Business hero title',
                'value' => 'Grow your local business',
            ],
            [
                'key' => 'business.hero.subtitle',
                'group' => 'for-business',
                'type' => 'richtext',
                'label' => 'Business hero subtitle',
                'value' => 'Reach nearby shoppers, run offers and build a loyal customer base - all from one simple dashboard.',
            ],
            [
                'key' => 'business.cta.label',
                'group' => 'for-business',
                'type' => 'text',
                'label' => 'Business CTA label',
                'value' => 'List your business',
            ],

            // ── Footer (shared) ──────────────────────────────────────────────
            [
                'key' => 'footer.tagline',
                'group' => 'footer',
                'type' => 'text',
                'label' => 'Footer tagline',
                'value' => 'Local first. Always.',
            ],
            [
                'key' => 'footer.contact_email',
                'group' => 'footer',
                'type' => 'text',
                'label' => 'Contact email',
                'value' => 'hello@locolie.com',
            ],
        ];
    }
}
