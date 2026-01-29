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
        Schema::table('taxpayers', function (Blueprint $table) {
            $table->string('password')->nullable()->after('object_address');
        });

        // Set default password as NIK (hashed) for existing taxpayers
        \App\Models\Taxpayer::all()->each(function ($taxpayer) {
            $taxpayer->update([
                'password' => \Illuminate\Support\Facades\Hash::make($taxpayer->nik)
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taxpayers', function (Blueprint $table) {
            $table->dropColumn('password');
        });
    }
};
