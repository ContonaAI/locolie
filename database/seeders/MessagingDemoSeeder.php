<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\DeviceToken;
use App\Models\MessageTemplate;
use App\Models\PushSubscription;
use App\Models\Redemption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds tangible demo audiences for the Messaging Studio: SMS phone opt-ins on
 * existing redemptions, web + native push devices, brand accent colours, and a
 * set of platform-default templates. Idempotent - safe to run repeatedly.
 */
class MessagingDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSmsOptIns();
        $this->seedPushDevices();
        $this->seedBrandColours();
        $this->seedTemplates();

        $this->command?->info('Messaging demo data seeded.');
    }

    /** Give ~60% of email-capturing redemptions a phone + SMS opt-in. */
    protected function seedSmsOptIns(): void
    {
        $rows = Redemption::whereNotNull('customer_email')->whereNull('customer_phone')->get();
        foreach ($rows as $i => $r) {
            if ($i % 10 < 6) { // ~60%
                $r->update([
                    'customer_phone' => '+447'.str_pad((string) (700000000 + ($r->id * 137 % 99999999)), 9, '0', STR_PAD_LEFT),
                    'sms_opt_in' => true,
                ]);
            }
        }
    }

    /** Seed web + native push devices so the push audience is non-zero. */
    protected function seedPushDevices(): void
    {
        // Web push subscriptions (counted, not really deliverable).
        for ($i = 0; $i < 48; $i++) {
            PushSubscription::firstOrCreate(
                ['endpoint' => "https://fcm.googleapis.com/fcm/send/demo-{$i}-".Str::random(12)],
                ['public_key' => Str::random(80), 'auth_token' => Str::random(22), 'category_prefs' => null]
            );
        }

        // Native device tokens for the future iOS / Android apps.
        $platforms = ['ios' => 34, 'android' => 27, 'web' => 18];
        foreach ($platforms as $platform => $count) {
            for ($i = 0; $i < $count; $i++) {
                DeviceToken::firstOrCreate(
                    ['token' => "{$platform}-demo-".Str::random(40)],
                    [
                        'platform' => $platform,
                        'app_version' => '1.0.0',
                        'locale' => 'en-GB',
                        'last_seen_at' => now()->subDays($i % 14),
                    ]
                );
            }
        }
    }

    /** Give onboarded businesses a deterministic brand accent colour. */
    protected function seedBrandColours(): void
    {
        $palette = ['#059669', '#2563eb', '#db2777', '#d97706', '#7c3aed', '#dc2626', '#0891b2', '#ca8a04'];
        foreach (Business::where('onboarded', true)->whereNull('brand_color')->get() as $b) {
            $b->update(['brand_color' => $palette[$b->id % count($palette)]]);
        }
    }

    /** Platform-default starter templates for each channel. */
    protected function seedTemplates(): void
    {
        $templates = [
            ['channel' => 'email', 'name' => 'Weekly offer drop', 'subject' => 'This week at {{business}}', 'body' => "Hi {{name}},\n\nHere is what is on this week - show this email in store to claim.\n\nSee you soon."],
            ['channel' => 'email', 'name' => 'We miss you', 'subject' => 'It has been a while', 'body' => "Hi {{name}},\n\nWe have not seen you in a bit. Here is 20% off to tempt you back."],
            ['channel' => 'sms', 'name' => 'Flash offer', 'subject' => null, 'body' => '{{business}}: 2-for-1 today only. Show this text at the till. Reply STOP to opt out.'],
            ['channel' => 'sms', 'name' => 'Booking reminder', 'subject' => null, 'body' => 'Reminder: your booking at {{business}} is tomorrow. Reply STOP to opt out.'],
            ['channel' => 'push', 'name' => 'Nearby deal', 'subject' => 'A deal near you', 'body' => '{{business}} has a new offer 200m away - tap to see it.'],
            ['channel' => 'push', 'name' => 'New arrival', 'subject' => 'Just in', 'body' => 'Something new just landed at {{business}}. Take a look.'],
        ];

        foreach ($templates as $t) {
            MessageTemplate::firstOrCreate(
                ['business_id' => null, 'channel' => $t['channel'], 'name' => $t['name']],
                ['subject' => $t['subject'], 'body' => $t['body'], 'is_default' => true]
            );
        }
    }
}
