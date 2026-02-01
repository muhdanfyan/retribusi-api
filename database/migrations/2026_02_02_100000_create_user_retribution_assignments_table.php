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
        Schema::create('user_retribution_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('retribution_type_id');
            $table->unsignedBigInteger('retribution_classification_id')->nullable();
            
            $table->foreign('retribution_type_id', 'ura_type_id_foreign')->references('id')->on('retribution_types')->onDelete('cascade');
            $table->foreign('retribution_classification_id', 'ura_class_id_foreign')->references('id')->on('retribution_classifications')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_retribution_assignments');
    }
};
