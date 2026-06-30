<?php

use App\Models\ContentBlock;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

if (! function_exists('cms')) {
    /**
     * Fetch an editable content block by its dotted key, falling back to the
     * provided default until an admin overrides it in /portal/content.
     *
     *   {{ cms('home.hero.title', 'Discover local offers') }}
     *
     * Each key is cached forever and busted on save (see ContentBlock::booted),
     * so reads are cheap and edits show live with no redeploy. Returns $default
     * for unknown keys and stays safe before the table exists (early boot,
     * fresh installs) so views never fatal.
     */
    function cms(string $key, $default = null)
    {
        $value = Cache::rememberForever(ContentBlock::cacheKey($key), function () use ($key) {
            // Guard: during early boot / before migrations the table may be absent.
            try {
                if (! Schema::hasTable('content_blocks')) {
                    return null;
                }
            } catch (\Throwable $e) {
                return null;
            }

            $block = ContentBlock::query()->where('key', $key)->first();

            // Sentinel '' distinguishes "looked up, none found" from a real value,
            // so a genuine empty stored value can still override the default.
            return $block ? ['v' => $block->value] : '';
        });

        if ($value === '' || $value === null) {
            return $default;
        }

        return $value['v'] ?? $default;
    }
}
