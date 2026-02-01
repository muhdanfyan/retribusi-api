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
        Schema::table('tax_objects', function (Blueprint $table) {
            $table->string('nop')->nullable()->unique()->after('id');
            $table->foreignId('retribution_classification_id')->nullable()->after('retribution_type_id')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_objects', function (Blueprint $table) {
            $table->dropForeign(['retribution_classification_id']);
            $table->dropColumn(['nop', 'retribution_classification_id']);
        });
    }
};
