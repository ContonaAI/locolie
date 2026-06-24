<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Redemption;
use Illuminate\Database\Seeder;

/**
 * Seeds captured customers (redemptions with email) onto paid businesses so the
 * "Your customers" CRM feature is demoable. Idempotent-ish: only adds if a
 * business has none yet.
 */
class DemoCustomersSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            ['Sarah J.', 'sarah.j@example.com'], ['Mark T.', 'markt@example.com'],
            ['Priya K.', 'priya@example.com'], ['Dan W.', 'danw@example.com'],
            ['Chloe M.', 'chloe.m@example.com'], ['Aisha R.', 'aisha@example.com'],
            ['Tom B.', 'tomb@example.com'], ['Leah S.', 'leahs@example.com'],
            ['Jordan P.', 'jordanp@example.com'], ['Nina F.', 'ninaf@example.com'],
        ];

        $businesses = Business::whereIn('plan', ['premium', 'featured'])->with('offers')->get();
        $made = 0;

        foreach ($businesses as $b) {
            $offer = $b->offers->first();
            if (! $offer) {
                continue;
            }
            // Skip if this business already has captured customers.
            $has = Redemption::where('offer_id', $offer->id)->whereNotNull('customer_email')->exists();
            if ($has) {
                continue;
            }

            $count = rand(5, 10);
            foreach (array_slice($names, 0, $count) as $i => $n) {
                Redemption::create([
                    'offer_id' => $offer->id,
                    'customer_name' => $n[0],
                    'customer_email' => $n[1],
                    'marketing_opt_in' => ($i % 4 !== 0),
                    'code' => str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT),
                    'status' => 'redeemed',
                    'redeemed_at' => now()->subDays(rand(1, 30)),
                    'expires_at' => now(),
                ]);
                $made++;
            }
        }

        $this->command->info("Seeded {$made} captured customers across {$businesses->count()} paid businesses.");
    }
}
