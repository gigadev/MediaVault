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
        Schema::create('collection_media_item', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('collection_id')->constrained('collections')->cascadeOnDelete();
            $table->foreignUlid('media_item_id')->constrained('media_items')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_media_item');
    }
};
