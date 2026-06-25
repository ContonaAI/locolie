<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Category;
use App\Models\Offer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Push curated local data (categories, businesses, offers + photos) up to the
 * live site via the token-guarded sync API. Run from your machine:
 *
 *   php artisan sync:push
 *
 * One-way (local -> production) and idempotent — safe to run repeatedly.
 */
class SyncPush extends Command
{
    protected $signature = 'sync:push {--target= : Override the destination base URL}
                                       {--skip-images : Push data only, no photo files}';

    protected $description = 'Push local businesses, offers, categories and photos to the live site';

    public function handle(): int
    {
        $target = rtrim($this->option('target') ?: config('sync.target'), '/');
        $token = config('sync.token');

        if (blank($token)) {
            $this->error('No SYNC_TOKEN set in this environment. Add it to your local .env first.');

            return self::FAILURE;
        }

        $this->info("Syncing to: {$target}");

        // ── Snapshot the remote before ───────────────────────────────────────
        $before = $this->status($target, $token);
        if ($before === null) {
            return self::FAILURE;
        }
        $this->line('  Remote before: '.$this->fmt($before));

        // ── Build payload from local DB ──────────────────────────────────────
        $categories = Category::all()->map(fn ($c) => $c->only(['name', 'slug', 'icon', 'sort']))->all();

        $businesses = Business::with('category')->get()->map(function (Business $b) {
            $row = $b->only($b->getFillable());
            unset($row['user_id'], $row['category_id']);
            $row['category_slug'] = $b->category?->slug;

            return $row;
        })->all();

        $offers = Offer::with('business')->get()->map(function (Offer $o) {
            $row = $o->only($o->getFillable());
            unset($row['business_id']);
            $row['business_slug'] = $o->business?->slug;
            $row['business_google_place_id'] = $o->business?->google_place_id;

            return $row;
        })->all();

        $this->line(sprintf('  Local data: %d categories, %d businesses, %d offers',
            count($categories), count($businesses), count($offers)));

        // ── Push data ────────────────────────────────────────────────────────
        $resp = Http::withToken($token)->acceptJson()->timeout(120)
            ->post("{$target}/api/sync/data", compact('categories', 'businesses', 'offers'));

        if ($resp->failed()) {
            $this->error('Data push failed ('.$resp->status().'): '.$resp->body());

            return self::FAILURE;
        }
        $up = $resp->json('upserted');
        $this->info(sprintf('  Pushed: %d categories, %d businesses, %d offers',
            $up['categories'], $up['businesses'], $up['offers']));

        // ── Push images ──────────────────────────────────────────────────────
        // Photos now live in the git-tracked public/img/biz dir, so they already
        // ship to prod with the code. This upload is a belt-and-braces fallback
        // for hosts where the repo images are not present.
        if (! $this->option('skip-images')) {
            $dir = public_path('img/biz');
            $files = is_dir($dir) ? glob($dir.'/*') : [];
            if ($files) {
                $this->line('  Uploading '.count($files).' photos...');
                $bar = $this->output->createProgressBar(count($files));
                $failed = 0;
                foreach ($files as $file) {
                    $r = Http::withToken($token)
                        ->attach('file', file_get_contents($file), basename($file))
                        ->post("{$target}/api/sync/image", ['path' => 'biz/'.basename($file)]);
                    $failed += $r->failed() ? 1 : 0;
                    $bar->advance();
                }
                $bar->finish();
                $this->newLine();
                if ($failed) {
                    $this->warn("  {$failed} image(s) failed to upload.");
                }
            }
        }

        // ── Snapshot the remote after ────────────────────────────────────────
        $after = $this->status($target, $token);
        $this->newLine();
        $this->info('Done. Remote now: '.$this->fmt($after));

        return self::SUCCESS;
    }

    private function status(string $target, string $token): ?array
    {
        $r = Http::withToken($token)->acceptJson()->get("{$target}/api/sync/status");
        if ($r->failed()) {
            $this->error('Could not reach sync endpoint ('.$r->status().'): '.$r->body());
            $this->line('Check the server has the same SYNC_TOKEN set and is deployed.');

            return null;
        }

        return $r->json();
    }

    private function fmt(?array $s): string
    {
        return $s ? "{$s['businesses']} businesses, {$s['offers']} offers, {$s['categories']} categories, {$s['images']} images" : 'n/a';
    }
}
