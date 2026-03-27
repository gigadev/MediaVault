<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('stripe_price_id')->nullable();
            $table->integer('max_media_items')->nullable();
            $table->integer('max_family_members')->nullable();
            $table->boolean('can_cross_family_share')->default(false);
            $table->boolean('can_use_api_lookup')->default(false);
            $table->boolean('can_create_custom_media_types')->default(false);
            $table->integer('price_monthly')->default(0);
            $table->integer('price_yearly')->nullable();
            $table->json('features')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
