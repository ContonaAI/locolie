<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database. Idempotent — safe to re-run.
     */
    public function run(): void
    {
        // Admin (us)
        User::updateOrCreate(
            ['email' => 'admin@golocal.test'],
            ['name' => 'GoLocal Admin', 'role' => 'admin', 'password' => Hash::make('password')],
        );

        // A demo business owner
        User::updateOrCreate(
            ['email' => 'owner@golocal.test'],
            ['name' => 'Demo Owner', 'role' => 'owner', 'password' => Hash::make('password')],
        );

        // Businesses are seeded live from Google Places on demand:
        //   php artisan db:seed --class="Database\Seeders\GooglePlacesSeeder"
        $this->call([
            CategorySeeder::class,
        ]);
    }
}
