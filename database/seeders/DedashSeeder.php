<?php
namespace Database\Seeders;
use App\Models\Business; use App\Models\Offer; use Illuminate\Database\Seeder;
class DedashSeeder extends Seeder {
    private function fix(?string $s): ?string {
        return $s === null ? null : str_replace(["\u{2014}","\u{2013}"], ['-','-'], $s);
    }
    public function run(): void {
        $n = 0;
        foreach (Offer::all() as $o) {
            $o->title = $this->fix($o->title); $o->terms = $this->fix($o->terms); $o->badge = $this->fix($o->badge);
            if ($o->isDirty()) { $o->save(); $n++; }
        }
        foreach (Business::all() as $b) {
            $b->name = $this->fix($b->name); $b->description = $this->fix($b->description);
            if (is_array($b->reviews)) {
                $b->reviews = array_map(fn($r) => array_merge($r, ['text' => $this->fix($r['text'] ?? ''), 'author' => $this->fix($r['author'] ?? '')]), $b->reviews);
            }
            if (is_array($b->hours)) {
                $b->hours = array_map(fn($h) => $this->fix($h), $b->hours);
            }
            if ($b->isDirty()) { $b->save(); $n++; }
        }
        $this->command->info("De-dashed {$n} records.");
    }
}
