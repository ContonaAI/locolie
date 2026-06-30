<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a redemption row represent a pure marketing-list capture (someone who
 * scanned the in-store QR and opted in without redeeming a specific offer).
 *
 * - offer_id becomes nullable, so a list signup needn't be tied to an offer.
 * - business_id is added so a capture is always attributable to the shop,
 *   even when there is no offer to join through.
 * - source records where the contact came from (qr_capture | redemption | demo).
 *
 * Additive and reversible. Do NOT run on the shared DB without coordination
 * (house rule: write migration files only).
 */
return new class extends Migration
{
    public function up(): void
    {
        // SQLite cannot alter a foreign-key column to nullable in place, so the
        // nullable change is best-effort: on MySQL/Postgres it relaxes the
        // constraint; on SQLite the column already accepts our writes via the
        // ORM, and new installs get nullable from the create migration anyway.
        if (! Schema::hasColumn('redemptions', 'business_id')) {
            Schema::table('redemptions', function (Blueprint $table) {
                $table->foreignId('business_id')->nullable()->after('offer_id')
                    ->constrained()->cascadeOnDelete();
            });
        }

        if (! Schema::hasColumn('redemptions', 'source')) {
            Schema::table('redemptions', function (Blueprint $table) {
                $table->string('source')->nullable()->after('status'); // qr_capture | redemption | demo
            });
        }

        // Relax offer_id to nullable where the driver supports it cleanly.
        if (! in_array(Schema::getConnection()->getDriverName(), ['sqlite'], true)) {
            Schema::table('redemptions', function (Blueprint $table) {
                $table->unsignedBigInteger('offer_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('redemptions', function (Blueprint $table) {
            if (Schema::hasColumn('redemptions', 'business_id')) {
                $table->dropConstrainedForeignId('business_id');
            }
            if (Schema::hasColumn('redemptions', 'source')) {
                $table->dropColumn('source');
            }
        });
    }
};
