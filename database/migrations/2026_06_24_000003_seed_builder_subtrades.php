<?php

use Database\Seeders\CategorySeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Re-run the (idempotent) category seeder so the Builders sub-trades — and
     * any later tweaks to the tree — land on environments where the earlier
     * seed migration already ran. updateOrCreate by slug makes this a no-op
     * where nothing changed.
     */
    public function up(): void
    {
        (new CategorySeeder)->run();
    }

    public function down(): void
    {
        // No-op: re-seeding is not reversible in a meaningful way.
    }
};
