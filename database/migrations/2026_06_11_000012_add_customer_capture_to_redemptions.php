<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('redemptions', function (Blueprint $table) {
            // First-party customer capture — the retailer value prop.
            $table->string('customer_email')->nullable()->after('customer_name');
            $table->boolean('marketing_opt_in')->default(false)->after('customer_email');
        });
    }

    public function down(): void
    {
        Schema::table('redemptions', function (Blueprint $table) {
            $table->dropColumn(['customer_email', 'marketing_opt_in']);
        });
    }
};
