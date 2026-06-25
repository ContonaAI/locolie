<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Generates the on-page content for the programmatic local-SEO pages
 * ("{category} in {location}"). Everything is derived deterministically from the
 * category, the location and the real businesses present, so:
 *   - every page is unique (no duplicate-content penalty),
 *   - copy, FAQs and keywords vary per page via a seeded template pick,
 *   - pages rebuild themselves as businesses are onboarded.
 *
 * Returns plain arrays the Blade views + the JSON-LD partial consume.
 */
class SeoContentService
{
    /** Content for a "{category} in {location}" landing page. */
    public function categoryInLocation(Category $category, array $location, Collection $businesses): array
    {
        $cat = $category->name;
        $catLower = Str::lower($cat);
        $loc = $location['label'];
        $count = $businesses->count();
        $names = $businesses->take(3)->pluck('name')->all();
        $seed = $category->slug.':'.$location['slug'];

        $title = Str::limit("$cat in $loc - Local & Independent | locolie", 60, '');
        $h1 = "$cat in $loc";

        $meta = $this->pick([
            "Find {$count} independent {$catLower} in {$loc}. Compare local options, see real offers and book direct - support {$loc}'s high street with locolie.",
            "Looking for {$catLower} in {$loc}? Discover {$count} independent, locally-rated businesses with exclusive offers on locolie.",
            "The best independent {$catLower} in {$loc}, all in one place. {$count} local businesses, real reviews and offers you won't find on the chains.",
        ], $seed);

        return [
            'title' => $title,
            'h1' => $h1,
            'meta_description' => Str::limit($meta, 158, ''),
            'intro' => $this->intro($catLower, $loc, $count, $names, $seed),
            'keywords' => $this->keywords($cat, $loc),
            'faqs' => $this->faqs($cat, $catLower, $loc, $count, $seed),
            'count' => $count,
        ];
    }

    /** Content for an area hub page ("Local businesses in {location}"). */
    public function locationHub(array $location, Collection $businesses): array
    {
        $loc = $location['label'];
        $count = $businesses->count();

        return [
            'title' => Str::limit("Local & independent businesses in $loc | locolie", 60, ''),
            'h1' => "Independent businesses in $loc",
            'meta_description' => Str::limit("Discover {$count} independent local businesses in {$loc} - food, trades, health, shopping and more, with offers you can only get on locolie.", 158, ''),
            'intro' => [
                "From a quick bite to a trusted tradesperson, {$loc} is full of independents worth backing. We have {$count} local businesses listed, each with offers you won't find at the chains.",
            ],
            'keywords' => ["things to do in {$loc}", "local businesses {$loc}", "independent shops {$loc}", "{$loc} offers"],
            'count' => $count,
        ];
    }

    // ── Builders ──────────────────────────────────────────────────────────────

    /** @return array<int,string> 1-2 unique intro paragraphs. */
    protected function intro(string $catLower, string $loc, int $count, array $names, string $seed): array
    {
        $lead = $this->pick([
            "Searching for {$catLower} in {$loc}? You're in the right place. locolie lists independent, locally-owned {$catLower} across {$loc} - the kind of businesses that actually live and work in your community.",
            "{$loc} has some brilliant independent {$catLower}, and locolie brings them together in one place. No faceless chains, just real local businesses you can trust and support.",
            "Looking for {$catLower} near you in {$loc}? Every business below is an independent local trader on locolie, often with an exclusive offer to say thanks for shopping local.",
        ], $seed);

        $support = $count > 0
            ? $this->pick([
                "Right now there are {$count} to choose from".($names ? ', including '.$this->humanList($names).'.' : '.'),
                "We currently feature {$count} ".($count === 1 ? 'business' : 'businesses').' here'.($names ? ' - '.$this->humanList($names).' among them.' : '.'),
            ], $seed.'b')
            : "We're adding more {$catLower} in {$loc} all the time - check back soon, or browse nearby areas below.";

        $close = "Every listing shows real ratings, opening hours and any live offers, so you can choose with confidence and keep your money in {$loc}.";

        return [$lead.' '.$support, $close];
    }

    /** @return array<int,string> */
    protected function keywords(string $cat, string $loc): array
    {
        $c = Str::lower($cat);

        return [
            "{$c} in {$loc}",
            "{$c} near me",
            "best {$c} {$loc}",
            "independent {$c} {$loc}",
            "local {$c} {$loc}",
            "{$loc} {$c} offers",
        ];
    }

    /** @return array<int,array{q:string,a:string}> */
    protected function faqs(string $cat, string $catLower, string $loc, int $count, string $seed): array
    {
        $faqs = [
            [
                'q' => "How do I find the best {$catLower} in {$loc}?",
                'a' => "Browse the {$catLower} listed on this page - each is an independent business in {$loc} with real ratings and reviews. Use the offers and ratings to compare, then contact or visit them direct.",
            ],
            [
                'q' => "Are these {$catLower} independent and local to {$loc}?",
                'a' => "Yes. locolie only lists genuine independent businesses, so every option here is locally owned and based in or around {$loc} - not a national chain.",
            ],
            [
                'q' => "Do {$catLower} in {$loc} offer any discounts?",
                'a' => "Many do. Businesses on locolie publish exclusive offers you won't find elsewhere. Look for the offer badge on a listing, then show the code or QR in store to redeem it.",
            ],
            [
                'q' => "How much do {$catLower} cost in {$loc}?",
                'a' => "Prices vary by business and the work involved. Because these are independents, it's worth contacting two or three for a quote - and claiming any locolie offer to save on your first visit.",
            ],
            [
                'q' => "Is it free to use locolie to find {$catLower}?",
                'a' => "Completely free for shoppers. locolie helps you discover and support independent {$catLower} in {$loc}, with offers that put money back in your pocket and keep it in the local economy.",
            ],
        ];

        // Rotate which 4 show so pages differ, but always lead with the first.
        $offset = crc32($seed) % 2;

        return array_merge([$faqs[0]], array_slice($faqs, 1 + $offset, 3));
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /** Deterministically pick one option from a pool, seeded by the page. */
    protected function pick(array $pool, string $seed): string
    {
        return $pool[crc32($seed) % count($pool)];
    }

    protected function humanList(array $items): string
    {
        if (count($items) <= 1) {
            return (string) ($items[0] ?? '');
        }
        $last = array_pop($items);

        return implode(', ', $items).' and '.$last;
    }
}
