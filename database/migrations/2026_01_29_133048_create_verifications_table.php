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
        Schema::create('verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opd_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained(); // Submitter
            $table->string('document_number')->unique();
            $table->string('taxpayer_name');
            $table->string('type');
            $table->decimal('amount', 15, 2);
            $table->string('status')->default('pending'); // pending, in_review, approved, rejected
            $table->text('notes')->nullable();
            $table->foreignId('verifier_id')->nullable()->references('id')->on('users');
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verifications');
    }
};
