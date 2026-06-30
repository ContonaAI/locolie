<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Foundation of the DB-backed CMS: a flat key/value store of editable content
 * blocks. Each row is one editable piece of copy/media addressed by a dotted
 * key (e.g. "home.hero.title"), so views can do {{ cms('home.hero.title', '...') }}
 * and fall back to the hardcoded default until an admin overrides it.
 *
 * Kept deliberately simple + extensible: a richer page-builder (the pages table)
 * sits alongside this for managed pages later. Guarded so it is safe to re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('content_blocks')) {
            Schema::create('content_blocks', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();          // dotted slug, e.g. "home.hero.title"
                $table->string('group')->default('general')->index(); // editor grouping, e.g. "home"
                $table->string('type')->default('text');  // text | richtext | html | image | url
                $table->longText('value')->nullable();    // the stored content (string or JSON for richer types)
                $table->string('label')->nullable();      // human label shown in the editor
                $table->text('help')->nullable();         // optional hint for the editor
                $table->integer('sort')->default(0);      // ordering within a group
                $table->string('updated_by')->nullable(); // who last saved it
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('content_blocks');
    }
};
