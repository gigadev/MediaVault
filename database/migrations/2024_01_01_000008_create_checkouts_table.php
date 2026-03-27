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
        Schema::create('checkouts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('family_id')->constrained('families')->cascadeOnDelete();
            $table->foreignUlid('media_item_id')->constrained('media_items')->cascadeOnDelete();
            $table->foreignId('checked_out_to_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('checked_out_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_out_at');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->string('condition_on_checkout')->nullable();
            $table->string('condition_on_return')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkouts');
    }
};
