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
        Schema::table('retribution_types', function (Blueprint $table) {
            $table->json('form_schema')->nullable()->after('unit'); // Defines required form fields
            $table->json('requirements')->nullable()->after('form_schema'); // Defines required documents
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retribution_types', function (Blueprint $table) {
            $table->dropColumn(['form_schema', 'requirements']);
        });
    }
};
