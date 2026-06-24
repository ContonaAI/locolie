<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            // Paid tier: free | featured | premium
            $table->string('plan')->default('free')->after('status');
            // Higher = surfaces higher in lists/map (driven by plan).
            $table->unsignedInteger('priority')->default(0)->after('plan');
            // CRM onboarding: a Google-Maps-sourced lead vs a live, onboarded business.
            $table->boolean('onboarded')->default(false)->after('priority');
            $table->timestamp('claimed_at')->nullable()->after('onboarded');
            $table->text('lead_notes')->nullable()->after('claimed_at');
            // Business self-serve login (CRM).
            $table->string('owner_email')->nullable()->after('lead_notes');
            $table->string('password')->nullable()->after('owner_email');
        });

        // Web-push subscriptions (shoppers opt in; paid businesses can broadcast).
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint', 600)->unique();
            $table->string('public_key')->nullable();
            $table->string('auth_token')->nullable();
            $table->json('category_prefs')->nullable();
            $table->timestamps();
        });

        // Outbound messages log (email + push campaigns) so the CRM has a history.
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel'); // email | push
            $table->string('subject')->nullable();
            $table->text('body');
            $table->unsignedInteger('sent_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('push_subscriptions');
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['plan', 'priority', 'onboarded', 'claimed_at', 'lead_notes', 'owner_email', 'password']);
        });
    }
};
