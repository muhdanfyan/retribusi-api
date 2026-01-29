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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The citizen
            $table->foreignId('retribution_type_id')->constrained();
            $table->string('bill_number')->unique();
            $table->decimal('amount', 15, 2);
            $table->string('status')->default('pending'); // pending, paid, expired
            $table->string('period'); // e.g., "Januari 2026"
            $table->json('metadata')->nullable(); // For plate numbers, kiosk IDs, etc.
            $table->timestamp('due_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
