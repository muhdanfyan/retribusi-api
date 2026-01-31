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
            // We keep name but we can nullable the base_amount or remove it since it's in rates now
            $table->decimal('base_amount', 15, 2)->nullable()->change();
            $table->string('unit')->nullable()->change();
        });

        Schema::table('zones', function (Blueprint $table) {
            $table->foreignId('retribution_classification_id')->nullable()->after('retribution_type_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retribution_types', function (Blueprint $table) {
            $table->decimal('base_amount', 15, 2)->nullable(false)->change();
            $table->string('unit')->nullable(false)->change();
        });

        Schema::table('zones', function (Blueprint $table) {
            $table->dropForeign(['retribution_classification_id']);
            $table->dropColumn('retribution_classification_id');
        });
    }
};
