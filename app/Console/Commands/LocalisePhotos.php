<?php

namespace App\Console\Commands;

use App\Models\Business;
use Illuminate\Console\Command;

/**
 * Move business photos out of the ephemeral storage/app/public/biz dir into the
 * git-tracked public/img/biz dir, and rewrite their URLs from /storage/biz/...
 * to /img/biz/.... Run once after the import-location change; idempotent.
 *
 * Why: on the release-based production host, storage/app/public + its symlink are
 * recreated each deploy, so /storage images kept disappearing. Images under
 * public/ ship with the repo (like the compiled assets) and always exist.
 */
class LocalisePhotos extends Command
{
    protected $signature = 'photos:localise';

    protected $description = 'Move business photos into the git-tracked public/img/biz dir and fix their URLs';

    public function handle(): int
    {
        $from = storage_path('app/public/biz');
        $to = public_path('img/biz');
        if (! is_dir($to)) {
            mkdir($to, 0755, true);
        }

        // 1. Copy any existing files across (keep originals; harmless if absent).
        $moved = 0;
        foreach (is_dir($from) ? (glob($from.'/*') ?: []) : [] as $file) {
            $dest = $to.'/'.basename($file);
            if (! file_exists($dest) && copy($file, $dest)) {
                $moved++;
            }
        }
        $this->info("Copied {$moved} photo file(s) into public/img/biz.");

        // 2. Rewrite photo URLs in the database.
        $fixed = 0;
        foreach (Business::whereNotNull('photos')->get() as $b) {
            $photos = collect($b->photos)
                ->map(fn ($p) => is_string($p) ? str_replace('/storage/biz/', '/img/biz/', $p) : $p)
                ->all();
            if ($photos !== $b->photos) {
                $b->update(['photos' => $photos]);
                $fixed++;
            }
        }
        $this->info("Rewrote photo URLs on {$fixed} business record(s).");

        return self::SUCCESS;
    }
}
