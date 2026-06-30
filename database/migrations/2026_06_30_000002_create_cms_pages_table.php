<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional managed-pages table to support a page-builder later: a row per
 * admin-managed page (slug, title, status) whose body is composed of an
 * ordered list of blocks stored as JSON. Not wired into routing in this
 * foundation pass - it exists so the builder can grow on top of it without
 * another migration. Guarded so it is safe to re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pages')) {
            Schema::create('pages', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();           // URL slug, e.g. "about"
                $table->string('title');
                $table->string('status')->default('draft'); // draft | published
                $table->json('blocks')->nullable();         // ordered page-builder blocks
                $table->json('meta')->nullable();           // seo title/description, layout choices
                $table->integer('sort')->default(0);
                $table->string('updated_by')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
