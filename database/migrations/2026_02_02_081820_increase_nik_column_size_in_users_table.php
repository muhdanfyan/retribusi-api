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
        Schema::table('users', function (Blueprint $table) {
            // Increase NIK column to 50 chars for flexibility (NIK: 16, NIP: 18, or other formats)
            // Note: unique constraint already exists, only changing size
            $table->string('nik', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert back to 16 characters
            $table->string('nik', 16)->nullable()->change();
        });
    }
};
