<?php

use App\Models\Business;
use Illuminate\Database\Migrations\Migration;

/**
 * Self-heal photo URLs in production. Business photos now ship in the repo under
 * public/img/biz (durable across redeploys); this rewrites any legacy
 * /storage/biz/... URL to /img/biz/... so the live DB points at the committed
 * files. Idempotent and safe to re-run - only touches rows that need it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Business::whereNotNull('photos')->get()->each(function (Business $b) {
            $photos = collect($b->photos)
                ->map(fn ($p) => is_string($p) ? str_replace('/storage/biz/', '/img/biz/', $p) : $p)
                ->all();

            if ($photos !== $b->photos) {
                $b->updateQuietly(['photos' => $photos]);
            }
        });
    }

    public function down(): void
    {
        // One-way data normalisation; nothing to reverse.
    }
};
