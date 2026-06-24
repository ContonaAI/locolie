<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            // Prototype-grade owner key returned at signup; scopes owner-only API actions.
            $table->string('owner_secret', 40)->nullable()->unique()->after('qr_token');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('owner_secret');
        });
    }
};
