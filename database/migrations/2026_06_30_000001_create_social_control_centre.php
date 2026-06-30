<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Social media control centre: the marketing calendar + per-platform post
 * drafts + handle / account management + the OAuth plumbing for direct API
 * publishing (Facebook, Instagram, TikTok, LinkedIn).
 *
 * social_accounts holds the connected handle + (encrypted) access token per
 * platform; social_posts holds the calendar of ideas / drafts / scheduled /
 * posted updates. Publishing goes live once the developer apps are approved -
 * until then a post can be drafted and scheduled, and the publisher returns a
 * clear "not connected" result.
 *
 * Each block is guarded so this is safe to run on a database where an earlier
 * partial run already created a table.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Connected social accounts (one row per platform) ─────────────────
        if (! Schema::hasTable('social_accounts')) {
            Schema::create('social_accounts', function (Blueprint $table) {
                $table->id();
                $table->string('platform');                 // facebook | instagram | tiktok | linkedin
                $table->string('handle')->nullable();        // @ourshop
                $table->string('display_name')->nullable();   // "locolie Newcastle"
                $table->text('access_token')->nullable();     // encrypted cast on the model
                $table->timestamp('token_expires_at')->nullable();
                $table->boolean('connected')->default(false);
                $table->json('meta')->nullable();             // page_id, ig_user_id, scopes, org_urn ...
                $table->timestamps();
                $table->unique('platform');
            });
        }

        // ── The marketing calendar: post drafts per platform ─────────────────
        if (! Schema::hasTable('social_posts')) {
            Schema::create('social_posts', function (Blueprint $table) {
                $table->id();
                $table->json('platforms');                    // ["facebook","instagram"]
                $table->text('body');
                $table->json('media')->nullable();            // ["social/abc.jpg", ...] asset paths
                $table->string('status')->default('draft');   // idea | draft | scheduled | posted | failed
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('posted_at')->nullable();
                $table->string('external_id')->nullable();    // platform post id once published
                $table->text('error')->nullable();            // last publish error, if any
                $table->string('created_by')->nullable();      // portal operator label
                $table->json('meta')->nullable();             // per-platform results, link, etc.
                $table->timestamps();
                $table->index(['status', 'scheduled_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('social_posts');
        Schema::dropIfExists('social_accounts');
    }
};
