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
        // 1. Add columns to classifications
        Schema::table('retribution_classifications', function (Blueprint $table) {
            $table->json('form_schema')->nullable()->after('code');
            $table->json('requirements')->nullable()->after('form_schema');
        });

        // 2. Copy data from parent types to child classifications
        $types = DB::table('retribution_types')->select('id', 'form_schema', 'requirements')->get();
        foreach ($types as $type) {
            DB::table('retribution_classifications')
                ->where('retribution_type_id', $type->id)
                ->update([
                    'form_schema' => $type->form_schema,
                    'requirements' => $type->requirements,
                ]);
        }

        // 3. Drop columns from types
        Schema::table('retribution_types', function (Blueprint $table) {
            $table->dropColumn(['form_schema', 'requirements']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retribution_types', function (Blueprint $table) {
            $table->json('form_schema')->nullable()->after('unit');
            $table->json('requirements')->nullable()->after('form_schema');
        });

        Schema::table('retribution_classifications', function (Blueprint $table) {
            $table->dropColumn(['form_schema', 'requirements']);
        });
    }
};
