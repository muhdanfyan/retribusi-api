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
        Schema::table('verifications', function (Blueprint $table) {
            $table->foreignId('taxpayer_id')->nullable()->after('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tax_object_id')->nullable()->after('taxpayer_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('verifications', function (Blueprint $table) {
            $table->dropForeign(['verifications_taxpayer_id_foreign']);
            $table->dropForeign(['verifications_tax_object_id_foreign']);
            $table->dropColumn(['taxpayer_id', 'tax_object_id']);
        });
    }
};
