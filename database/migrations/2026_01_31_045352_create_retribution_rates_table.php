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
        Schema::create('retribution_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opd_id')->constrained()->onDelete('cascade');
            $table->foreignId('retribution_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('retribution_classification_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('zone_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('unit')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retribution_rates');
    }
};
