<?php

namespace Database\Seeders;

use App\Models\Business;
use Illuminate\Database\Seeder;

/**
 * Bulk-recompress all cached business photos: max 360px wide, JPEG quality 60,
 * PNG→JPEG. Cards only ever show a small thumbnail, so this is plenty — and it
 * drops total payload by ~80% (the source of the ngrok bandwidth blow-out).
 */
class OptimiseImagesSeeder extends Seeder
{
    public function run(): void
    {
        $base = storage_path('app/public/');
        $before = 0;
        $after = 0;
        $done = 0;

        foreach (Business::whereNotNull('photos')->get() as $b) {
            $photos = $b->photos;
            if (! $photos || empty($photos[0])) {
                continue;
            }
            $rel = ltrim(str_replace('/storage/', '', $photos[0]), '/');
            $path = $base.$rel;
            if (! is_file($path)) {
                continue;
            }

            $before += filesize($path);
            $img = @imagecreatefromstring(file_get_contents($path));
            if (! $img) {
                continue;
            }

            $w = imagesx($img);
            $h = imagesy($img);
            $max = 360;
            if ($w > $max) {
                $nh = (int) round($h * $max / $w);
                $tmp = imagecreatetruecolor($max, $nh);
                imagecopyresampled($tmp, $img, 0, 0, 0, 0, $max, $nh, $w, $h);
                imagedestroy($img);
                $img = $tmp;
            }

            $newRel = 'biz/'.$b->id.'.jpg';
            $newPath = $base.$newRel;
            imagejpeg($img, $newPath, 60);
            imagedestroy($img);

            // Remove the old file if the extension changed (e.g. .png → .jpg).
            if ($rel !== $newRel && is_file($base.$rel)) {
                @unlink($base.$rel);
            }

            $after += filesize($newPath);
            $b->update(['photos' => ['/storage/'.$newRel]]);
            $done++;
        }

        $mb = fn ($b) => number_format($b / 1048576, 1);
        $this->command->info("Optimised {$done} images: {$mb($before)}MB → {$mb($after)}MB");
    }
}
