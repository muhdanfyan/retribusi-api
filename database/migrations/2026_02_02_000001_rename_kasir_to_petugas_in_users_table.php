<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing users with role 'kasir' to 'petugas'
        DB::table('users')
            ->where('role', 'kasir')
            ->update(['role' => 'petugas']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert users with role 'petugas' back to 'kasir'
        DB::table('users')
            ->where('role', 'petugas')
            ->update(['role' => 'kasir']);
    }
};
