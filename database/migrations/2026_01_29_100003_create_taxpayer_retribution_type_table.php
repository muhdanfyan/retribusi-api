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
        Schema::create('taxpayer_retribution_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taxpayer_id')->constrained()->onDelete('cascade');
            $table->foreignId('retribution_type_id')->constrained()->onDelete('cascade');
            $table->decimal('custom_amount', 15, 2)->nullable(); // Override tarif khusus
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['taxpayer_id', 'retribution_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxpayer_retribution_type');
    }
};
