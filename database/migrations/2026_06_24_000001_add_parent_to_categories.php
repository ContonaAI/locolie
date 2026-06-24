<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Self-referencing parent. Top-level (parent) categories have parent_id = null;
        // businesses link to the deepest available sub-category.
        // Plain indexed column (no FK) — avoids self-referencing-FK failures on MySQL
        // that would abort the whole migrate run, and the app doesn't rely on the
        // constraint. Guarded so it's safe to re-run.
        if (! Schema::hasColumn('categories', 'parent_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('categories', 'parent_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('parent_id');
            });
        }
    }
};
