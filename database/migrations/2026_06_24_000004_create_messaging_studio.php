<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Messaging Studio: branded email + SMS + push across web and (future) native apps.
 *
 * Adds per-brand identity (logo, colour, sender names) so every message can be
 * bespoke to the business; a template store; a connected-provider registry
 * ("connect to Google", Twilio, FCM, ...); native device tokens for iOS/Android
 * push; and SMS opt-in capture alongside the existing email capture.
 *
 * Each block is guarded so this is safe to run on a database where an earlier
 * partial run already added some columns/tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Per-brand identity used to make every message bespoke ────────────
        Schema::table('businesses', function (Blueprint $table) {
            if (! Schema::hasColumn('businesses', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('photos');
            }
            if (! Schema::hasColumn('businesses', 'brand_color')) {
                $table->string('brand_color', 9)->nullable()->after('logo_path');
            }
            if (! Schema::hasColumn('businesses', 'email_from_name')) {
                $table->string('email_from_name')->nullable()->after('brand_color');
            }
            if (! Schema::hasColumn('businesses', 'reply_to_email')) {
                $table->string('reply_to_email')->nullable()->after('email_from_name');
            }
            if (! Schema::hasColumn('businesses', 'sms_sender_id')) {
                $table->string('sms_sender_id', 11)->nullable()->after('reply_to_email');
            }
        });

        // ── SMS opt-in capture (mirrors the existing email capture) ──────────
        Schema::table('redemptions', function (Blueprint $table) {
            if (! Schema::hasColumn('redemptions', 'customer_phone')) {
                $table->string('customer_phone', 32)->nullable()->after('customer_email');
            }
            if (! Schema::hasColumn('redemptions', 'sms_opt_in')) {
                $table->boolean('sms_opt_in')->default(false)->after('marketing_opt_in');
            }
        });

        // ── Richer campaign log (sms joins email|push; status + provider) ────
        Schema::table('campaigns', function (Blueprint $table) {
            if (! Schema::hasColumn('campaigns', 'status')) {
                $table->string('status')->default('sent')->after('channel'); // draft|scheduled|sent|failed
            }
            if (! Schema::hasColumn('campaigns', 'provider')) {
                $table->string('provider')->nullable()->after('status');
            }
            if (! Schema::hasColumn('campaigns', 'template_id')) {
                $table->unsignedBigInteger('template_id')->nullable()->after('provider');
            }
            if (! Schema::hasColumn('campaigns', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('sent_count');
            }
            if (! Schema::hasColumn('campaigns', 'meta')) {
                $table->json('meta')->nullable()->after('scheduled_at');
            }
        });

        // ── Reusable, brand-aware message templates ──────────────────────────
        if (! Schema::hasTable('message_templates')) {
            Schema::create('message_templates', function (Blueprint $table) {
                $table->id();
                // null business_id = a platform-default template available to everyone.
                $table->foreignId('business_id')->nullable()->constrained()->cascadeOnDelete();
                $table->string('channel');               // email | sms | push
                $table->string('name');
                $table->string('subject')->nullable();   // email subject / push title
                $table->text('body');
                $table->json('meta')->nullable();         // layout, brand overrides, cta, image
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });
        }

        // ── Connected delivery providers ("connect to Google", Twilio, ...) ──
        if (! Schema::hasTable('messaging_channels')) {
            Schema::create('messaging_channels', function (Blueprint $table) {
                $table->id();
                $table->string('channel');                  // email | sms | push
                $table->string('provider');                 // google | smtp | twilio | vonage | fcm | apns | web_push ...
                $table->string('label')->nullable();        // e.g. the connected account name
                $table->string('status')->default('disconnected'); // connected | demo | disconnected
                $table->json('config')->nullable();         // non-secret display config (secrets stay in env)
                $table->timestamp('connected_at')->nullable();
                $table->timestamps();
                $table->unique(['channel', 'provider']);
            });
        }

        // ── Native + web device tokens for push (future iOS / Android apps) ──
        if (! Schema::hasTable('device_tokens')) {
            Schema::create('device_tokens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('platform');                 // web | ios | android
                $table->string('token', 512)->unique();     // FCM token / APNs token / web endpoint id
                $table->string('app_version')->nullable();
                $table->string('locale', 8)->nullable();
                $table->json('topics')->nullable();          // category subscriptions
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
        Schema::dropIfExists('messaging_channels');
        Schema::dropIfExists('message_templates');

        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn(['status', 'provider', 'template_id', 'scheduled_at', 'meta']);
        });
        Schema::table('redemptions', function (Blueprint $table) {
            $table->dropColumn(['customer_phone', 'sms_opt_in']);
        });
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'brand_color', 'email_from_name', 'reply_to_email', 'sms_sender_id']);
        });
    }
};
