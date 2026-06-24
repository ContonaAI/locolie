<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consent & subscription framework (GDPR / UK GDPR + PECR).
 *
 * - `subscriptions`  : one row per (contact, topic). The single source of truth
 *                      for who may be messaged on which channel/topic. Lets a
 *                      person subscribe/unsubscribe per topic, or to everything,
 *                      with no login (managed via signed links).
 * - `consent_log`    : append-only audit trail — what was consented to, when,
 *                      from where, and the IP. Required for GDPR accountability.
 * - consent columns  : T&C acceptance + privacy-policy version stamped on the
 *                      `users` and `businesses` records at signup.
 *
 * Every block is guarded so a partial earlier run is safe to re-apply.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Who may be messaged, per topic ──────────────────────────────────
        if (! Schema::hasTable('subscriptions')) {
            Schema::create('subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('email', 160)->nullable()->index();
                $table->string('phone', 32)->nullable()->index();
                $table->string('topic');                              // offers | product_updates | sms_alerts | business_updates
                $table->string('channel')->default('email');          // email | sms | push
                $table->string('status')->default('subscribed');      // subscribed | unsubscribed
                $table->string('source')->nullable();                 // signup | redemption | preference_centre | import | admin
                $table->timestamp('consented_at')->nullable();
                $table->timestamp('unsubscribed_at')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();

                // A contact can hold exactly one state per topic.
                $table->unique(['email', 'topic']);
            });
        }

        // ── Append-only consent audit trail ─────────────────────────────────
        if (! Schema::hasTable('consent_log')) {
            Schema::create('consent_log', function (Blueprint $table) {
                $table->id();
                $table->string('email', 160)->nullable()->index();
                $table->string('phone', 32)->nullable();
                $table->string('action');                  // subscribed | unsubscribed | terms_accepted | privacy_accepted
                $table->string('topic')->nullable();
                $table->string('channel')->nullable();
                $table->string('source')->nullable();
                $table->string('document_version')->nullable();   // e.g. terms/privacy version accepted
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 512)->nullable();
                $table->json('meta')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }

        // ── Stamp legal acceptance on the account records ───────────────────
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'terms_accepted_at')) {
                $table->timestamp('terms_accepted_at')->nullable()->after('role');
            }
            if (! Schema::hasColumn('users', 'privacy_version')) {
                $table->string('privacy_version')->nullable()->after('terms_accepted_at');
            }
            if (! Schema::hasColumn('users', 'marketing_consent_at')) {
                $table->timestamp('marketing_consent_at')->nullable()->after('privacy_version');
            }
        });

        Schema::table('businesses', function (Blueprint $table) {
            if (! Schema::hasColumn('businesses', 'terms_accepted_at')) {
                $table->timestamp('terms_accepted_at')->nullable();
            }
            if (! Schema::hasColumn('businesses', 'privacy_version')) {
                $table->string('privacy_version')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_log');
        Schema::dropIfExists('subscriptions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['terms_accepted_at', 'privacy_version', 'marketing_consent_at']);
        });
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['terms_accepted_at', 'privacy_version']);
        });
    }
};
