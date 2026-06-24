<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->string('sale_type')->default('ongoing')->after('discount_type'); // ongoing | limited | seasonal
            $table->unsignedInteger('quantity')->nullable()->after('per_user_limit'); // null = unlimited
            $table->unsignedInteger('redeemed_count')->default(0)->after('quantity');
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->boolean('featured')->default(false)->after('status');
            $table->json('reviews')->nullable()->after('photos'); // real Google reviews when available
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn(['sale_type', 'quantity', 'redeemed_count']);
        });
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['featured', 'reviews']);
        });
    }
};
