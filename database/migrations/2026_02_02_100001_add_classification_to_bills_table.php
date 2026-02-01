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
        Schema::table('bills', function (Blueprint $table) {
            $table->unsignedBigInteger('retribution_classification_id')->nullable()->after('retribution_type_id');
            $table->foreign('retribution_classification_id', 'bills_class_id_foreign')->references('id')->on('retribution_classifications')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropForeign('bills_class_id_foreign');
            $table->dropColumn('retribution_classification_id');
        });
    }
};
