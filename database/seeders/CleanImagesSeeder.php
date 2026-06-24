<?php
namespace Database\Seeders;
use App\Models\Business;
use Illuminate\Database\Seeder;
class CleanImagesSeeder extends Seeder {
    public function run(): void {
        $base = storage_path('app/public/biz/');
        $keep = [];
        foreach (Business::whereNotNull('photos')->get() as $b) {
            if (!empty($b->photos[0])) $keep[basename($b->photos[0])] = true;
        }
        $deleted = 0; $freed = 0;
        foreach (glob($base.'*') as $f) {
            if (!isset($keep[basename($f)])) { $freed += filesize($f); @unlink($f); $deleted++; }
        }
        $this->command->info("Deleted {$deleted} orphans, freed ".number_format($freed/1048576,1)."MB");
    }
}
