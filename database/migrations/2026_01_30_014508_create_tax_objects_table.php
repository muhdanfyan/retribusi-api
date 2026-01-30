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
        Schema::create('tax_objects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taxpayer_id')->constrained()->onDelete('cascade');
            $table->foreignId('retribution_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('opd_id')->constrained()->onDelete('cascade');
            $table->foreignId('zone_id')->nullable()->constrained()->onDelete('set null');
            
            $table->string('name');
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            $table->json('metadata')->nullable(); // For flexible fields (size, power, etc)
            $table->string('status')->default('pending'); // pending, active, inactive, rejected
            
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_objects');
    }
};
