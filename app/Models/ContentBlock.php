<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * One editable piece of site content addressed by a dotted key
 * (e.g. "home.hero.title"). Looked up in views via the global cms() helper,
 * which caches per key. Saving busts that cache so edits show live.
 */
#[Fillable(['key', 'group', 'type', 'value', 'label', 'help', 'sort', 'updated_by'])]
class ContentBlock extends Model
{
    protected function casts(): array
    {
        return [
            'sort' => 'integer',
        ];
    }

    /** Cache key used by the cms() helper for this block's key. */
    public static function cacheKey(string $key): string
    {
        return 'cms.block.'.$key;
    }

    /** Bust the cached value for a single key. */
    public static function forget(string $key): void
    {
        Cache::forget(self::cacheKey($key));
    }

    /** Keep the per-key cache in sync on every save/delete. */
    protected static function booted(): void
    {
        static::saved(fn (self $b) => self::forget($b->key));
        static::deleted(fn (self $b) => self::forget($b->key));
    }
}
