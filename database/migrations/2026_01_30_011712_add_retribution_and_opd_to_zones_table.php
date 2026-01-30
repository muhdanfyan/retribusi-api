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
        Schema::table('zones', function (Blueprint $table) {
            $table->foreignId('opd_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->foreignId('retribution_type_id')->nullable()->after('opd_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2)->default(0)->after('multiplier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->dropForeign(['opd_id']);
            $table->dropForeign(['retribution_type_id']);
            $table->dropColumn(['opd_id', 'retribution_type_id', 'amount']);
        });
    }
};
