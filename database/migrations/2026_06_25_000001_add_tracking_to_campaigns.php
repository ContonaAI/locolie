<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Email open/click tracking counters on the campaign log, so reporting can show
 * measured engagement instead of modelled benchmarks once a provider is live.
 * Guarded so a partial earlier run is safe to re-apply.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            if (! Schema::hasColumn('campaigns', 'opens')) {
                $table->unsignedInteger('opens')->default(0)->after('sent_count');
            }
            if (! Schema::hasColumn('campaigns', 'clicks')) {
                $table->unsignedInteger('clicks')->default(0)->after('opens');
            }
            // Distinct opener emails, so we can report unique opens, not just raw.
            if (! Schema::hasColumn('campaigns', 'opened_by')) {
                $table->json('opened_by')->nullable()->after('clicks');
            }
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn(['opens', 'clicks', 'opened_by']);
        });
    }
};
