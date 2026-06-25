<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Loyalty rules engine. Retailer-configurable schemes that reward customers
 * for repeat usage - "scan X times, get Y free" (visit stamp cards) and
 * "spend £X, unlock a discount" (spend thresholds). Progress is keyed by the
 * customer email captured at redemption; accrual happens when the retailer
 * verifies a code at the till. Plain indexed columns (no FK constraints) to
 * stay MySQL-safe and idempotent.
 */
return new class extends Migration
{
    public function up(): void
    {
        // One loyalty scheme per business: on/off + customer-facing copy.
        if (! Schema::hasTable('loyalty_programs')) {
            Schema::create('loyalty_programs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id')->unique();
                $table->boolean('active')->default(false);
                $table->string('headline')->nullable();   // e.g. "Coffee Club"
                $table->string('blurb')->nullable();       // short line shown to shoppers
                $table->text('terms')->nullable();         // business-specific small print
                $table->timestamps();
            });
        }

        // The rules. A business can run a few at once (a stamp card + a spend perk).
        if (! Schema::hasTable('loyalty_rules')) {
            Schema::create('loyalty_rules', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id')->index();
                $table->boolean('active')->default(true);
                $table->unsignedInteger('sort')->default(0);
                $table->string('name');                    // "Buy 5, get 1 free"
                $table->string('metric')->default('visits'); // visits | spend
                $table->unsignedInteger('threshold');      // visit count, or pence for spend
                $table->boolean('repeat')->default(true);  // stamp card cycles vs one-time
                $table->string('reward_type')->default('free'); // free | percent | amount | gift
                $table->unsignedInteger('reward_value')->nullable(); // % or pence
                $table->string('reward_label');            // "Free coffee", "10% off"
                $table->timestamps();
            });
        }

        // Per-customer accrual against a business.
        if (! Schema::hasTable('loyalty_progress')) {
            Schema::create('loyalty_progress', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id');
                $table->string('customer_email');
                $table->unsignedInteger('visits')->default(0);
                $table->unsignedInteger('spend')->default(0);   // lifetime pence
                $table->json('counters')->nullable();           // per-rule progress since last reward
                $table->timestamp('last_visit_at')->nullable();
                $table->timestamps();
                $table->unique(['business_id', 'customer_email']);
                $table->index('customer_email');
            });
        }

        // Rewards a customer has earned and can redeem in store.
        if (! Schema::hasTable('loyalty_rewards')) {
            Schema::create('loyalty_rewards', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id')->index();
                $table->string('customer_email')->index();
                $table->unsignedBigInteger('rule_id')->nullable();
                $table->string('label');
                $table->string('code', 12)->unique();
                $table->string('status')->default('earned');    // earned | redeemed | void
                $table->timestamp('earned_at')->nullable();
                $table->timestamp('redeemed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_rewards');
        Schema::dropIfExists('loyalty_progress');
        Schema::dropIfExists('loyalty_rules');
        Schema::dropIfExists('loyalty_programs');
    }
};
