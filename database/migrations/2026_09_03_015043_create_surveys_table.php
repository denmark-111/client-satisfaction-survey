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
        Schema::create('surveys', function (Blueprint $table) {// Consent & Personal Info
            $table->boolean('agreed_to_participate')->default(true);
            $table->string('respondent_name')->nullable();
            $table->string('respondent_contact_number')->nullable();

            // Respondent Details
            $table->foreignId('center_id')->constrained('form_options');
            $table->string('division_office');
            $table->string('client_type'); // Citizen, Business, Government
            $table->date('date_service_availed');
            $table->string('sex'); // Male, Female, Intersex, Prefer not to say
            $table->unsignedSmallInteger('age');
            $table->foreignId('region_id')->constrained('form_options');

            // Service Availed
            $table->foreignId('service_id')->constrained('form_options');

            // Overall Satisfaction & Remarks
            $table->unsignedTinyInteger('overall_satisfaction'); // 1-10
            $table->text('remarks')->nullable();

            // Citizen's Charter (CC)
            $table->unsignedTinyInteger('cc1_awareness');
            $table->unsignedTinyInteger('cc2_visibility')->nullable();
            $table->unsignedTinyInteger('cc3_helpfulness')->nullable();

            // Service Quality Dimensions (SQD0 to SQD8)
            // Stored as 1 (Strongly Agree) to 5 (Strongly Disagree), or 0 for Not Applicable
            $table->unsignedTinyInteger('sqd0_overall');
            $table->unsignedTinyInteger('sqd1_responsiveness');
            $table->unsignedTinyInteger('sqd2_reliability');
            $table->unsignedTinyInteger('sqd3_access_facilities');
            $table->unsignedTinyInteger('sqd4_communication');
            $table->unsignedTinyInteger('sqd5_costs');
            $table->unsignedTinyInteger('sqd6_integrity');
            $table->unsignedTinyInteger('sqd7_assurance');
            $table->unsignedTinyInteger('sqd8_outcome');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
