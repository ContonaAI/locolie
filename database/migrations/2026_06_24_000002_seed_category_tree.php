<?php

use Database\Seeders\CategorySeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Populate the parent/sub-category taxonomy on deploy. The Deploy.dev hook
     * only runs `migrate --force` (not seeders), so the restructure has to ride
     * along here. CategorySeeder is idempotent (updateOrCreate by slug), so this
     * is safe to run alongside an existing local seed.
     */
    public function up(): void
    {
        (new CategorySeeder)->run();
    }

    public function down(): void
    {
        // No-op: dropping the parent_id column (previous migration) already
        // unwinds the hierarchy; the category rows themselves are kept.
    }
};
