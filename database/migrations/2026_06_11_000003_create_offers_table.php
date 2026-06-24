<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('badge')->nullable();           // e.g. "20% OFF", "2-FOR-1"
            $table->text('description')->nullable();
            $table->text('terms')->nullable();
            $table->string('discount_type')->default('percent'); // percent | amount | bogo | free | other
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('redemption_limit_total')->nullable();
            $table->unsignedInteger('per_user_limit')->nullable();
            $table->string('status')->default('active');   // active | paused | expired
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
