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
        Schema::create('media_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('family_id')->constrained('families')->cascadeOnDelete();
            $table->foreignUlid('media_type_id')->constrained('media_types');
            $table->foreignId('added_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title')->index();
            $table->string('barcode')->nullable()->index();
            $table->smallInteger('year')->nullable();
            $table->text('description')->nullable();
            $table->string('cover_image_path')->nullable();
            $table->json('metadata')->nullable();
            $table->string('condition')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('is_shareable')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_items');
    }
};
