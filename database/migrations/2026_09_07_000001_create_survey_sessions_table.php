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
        Schema::create('survey_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->string('client_system')->nullable();
            $table->string('external_transaction_id')->nullable();

            // Pre-fill fields
            $table->string('respondent_name')->nullable();
            $table->string('respondent_contact_number')->nullable();
            $table->foreignId('center_id')->nullable()->constrained('form_options')->noActionOnDelete();
            $table->string('division_office')->nullable();
            $table->string('client_type')->nullable();
            $table->date('date_service_availed')->nullable();
            $table->string('sex')->nullable();
            $table->unsignedSmallInteger('age')->nullable();
            $table->foreignId('region_id')->nullable()->constrained('form_options')->noActionOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->noActionOnDelete();

            // Lifecycle & Status
            $table->string('status', 20)->default('pending'); // 'pending', 'completed', 'expired'
            $table->foreignId('survey_id')->nullable()->constrained('surveys')->noActionOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['token', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_sessions');
    }
};
