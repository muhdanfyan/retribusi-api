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
        Schema::create('taxpayers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opd_id')->constrained()->onDelete('cascade');
            $table->string('nik', 16);
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('npwpd')->nullable();         // Nomor Pokok WP Daerah
            $table->string('object_name')->nullable();   // Nama objek (kios, kendaraan, dll)
            $table->string('object_address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('nik');
            $table->index(['opd_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxpayers');
    }
};
