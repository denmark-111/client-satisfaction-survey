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
        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_session_id')->nullable()->constrained('survey_sessions')->noActionOnDelete();
            $table->foreignId('survey_response_id')->nullable()->constrained('survey_responses')->noActionOnDelete();
            $table->string('url', 2048);
            $table->string('event', 64)->default('survey.completed');
            $table->json('payload');
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->longText('response_body')->nullable();
            $table->text('error_message')->nullable();
            $table->string('status', 20)->default('pending'); // 'pending', 'success', 'failed'
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamps();

            $table->index(['event', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
    }
};
