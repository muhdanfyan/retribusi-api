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
        Schema::create('object_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_object_id')->constrained()->onDelete('cascade');
            
            // Petugas Pendata
            $table->date('pendata_tanggal')->nullable();
            $table->string('pendata_nama')->nullable();
            $table->string('pendata_nip')->nullable();
            $table->string('pendata_tanda_tangan_url')->nullable();
            
            // Pejabat Berwenang
            $table->date('pejabat_tanggal')->nullable();
            $table->string('pejabat_nama')->nullable();
            $table->string('pejabat_nip')->nullable();
            $table->string('pejabat_tanda_tangan_url')->nullable();
            
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('object_verifications');
    }
};
