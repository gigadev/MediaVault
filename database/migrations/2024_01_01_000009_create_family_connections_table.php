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
        Schema::create('family_connections', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('requester_family_id')->constrained('families')->cascadeOnDelete();
            $table->foreignUlid('receiver_family_id')->constrained('families')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->timestamp('requested_at');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['requester_family_id', 'receiver_family_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_connections');
    }
};
