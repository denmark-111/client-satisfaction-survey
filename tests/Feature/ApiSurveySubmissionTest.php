<?php

namespace Tests\Feature;

use App\Models\FormOption;
use App\Models\Service;
use App\Models\Survey;
use App\Models\SurveySession;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiSurveySubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function validSurveyData(array $overrides = []): array
    {
        $center = FormOption::where('category', 'center')->first();
        $region = FormOption::where('category', 'region')->first();
        $service = Service::first();

        return array_merge([
            'agreed_to_participate' => true,
            'respondent_name' => 'Maria Santos',
            'respondent_contact_number' => '09181234567',
            'center_id' => $center->id,
            'division_office' => 'Technical Operations',
            'client_type' => 'Citizen',
            'date_service_availed' => Carbon::today()->format('Y-m-d'),
            'sex' => 'Female',
            'age' => 28,
            'region_id' => $region->id,
            'service_id' => $service->id,
            'overall_satisfaction' => 5,
            'remarks' => null,
            'cc1_awareness' => 1,
            'cc2_visibility' => 1,
            'cc3_helpfulness' => 1,
            'sqd0_overall' => 5,
            'sqd1_responsiveness' => 5,
            'sqd2_reliability' => 5,
            'sqd3_access_facilities' => 5,
            'sqd4_communication' => 5,
            'sqd5_costs' => 5,
            'sqd6_integrity' => 5,
            'sqd7_assurance' => 5,
            'sqd8_outcome' => 5,
        ], $overrides);
    }

    public function test_client_can_submit_survey_directly_without_session(): void
    {
        $payload = $this->validSurveyData([
            'client_system' => 'dairy-loan-portal',
            'external_transaction_id' => 'TX-DIRECT-99',
        ]);

        $response = $this->postJson(route('api.surveys.store'), $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Survey submitted successfully.',
                'data' => [
                    'client_system' => 'dairy-loan-portal',
                    'external_transaction_id' => 'TX-DIRECT-99',
                    'webhook_status' => 'none',
                ],
            ]);

        $surveyId = $response->json('data.survey_id');
        $sessionToken = $response->json('data.session_token');

        $this->assertDatabaseHas('surveys', [
            'id' => $surveyId,
            'respondent_name' => 'Maria Santos',
            'overall_satisfaction' => 5,
        ]);

        $this->assertDatabaseHas('survey_sessions', [
            'token' => $sessionToken,
            'survey_id' => $surveyId,
            'client_system' => 'dairy-loan-portal',
            'external_transaction_id' => 'TX-DIRECT-99',
            'status' => 'completed',
        ]);
    }

    public function test_client_can_submit_survey_using_semantic_codes(): void
    {
        $center = FormOption::where('category', 'center')->first();
        $region = FormOption::where('category', 'region')->first();
        $service = Service::first();

        $payload = $this->validSurveyData();
        unset($payload['center_id'], $payload['region_id'], $payload['service_id']);

        $payload['center_code'] = $center->code;
        $payload['region_code'] = $region->code;
        $payload['service_code'] = $service->code;

        $response = $this->postJson(route('api.surveys.store'), $payload);

        $response->assertStatus(201)->assertJson(['success' => true]);

        $surveyId = $response->json('data.survey_id');
        $this->assertDatabaseHas('surveys', [
            'id' => $surveyId,
            'center_id' => $center->id,
            'region_id' => $region->id,
            'service_id' => $service->id,
        ]);
    }

    public function test_client_can_submit_survey_against_precreated_session_token(): void
    {
        $center = FormOption::where('category', 'center')->first();
        $region = FormOption::where('category', 'region')->first();
        $service = Service::first();

        $session = SurveySession::create([
            'token' => 'precreated-token-123456',
            'client_system' => 'kiosk-station-1',
            'external_transaction_id' => 'TX-SESSION-001',
            'respondent_name' => 'Original Prefill Name',
            'center_id' => $center->id,
            'division_office' => 'Prefilled Office',
            'client_type' => 'Business',
            'date_service_availed' => Carbon::yesterday()->format('Y-m-d'),
            'sex' => 'Male',
            'age' => 45,
            'region_id' => $region->id,
            'service_id' => $service->id,
            'status' => 'pending',
            'expires_at' => now()->addDays(2),
        ]);

        $payload = $this->validSurveyData([
            'session_token' => $session->token,
            // Attempt to spoof prefilled/locked fields in request payload
            'respondent_name' => 'Attempted Overwrite Name',
            'division_office' => 'Attempted Office',
            'client_type' => 'Citizen',
            'age' => 19,
        ]);

        $response = $this->postJson(route('api.surveys.store'), $payload);

        $response->assertStatus(201)->assertJson(['success' => true]);

        $surveyId = $response->json('data.survey_id');
        $survey = Survey::find($surveyId);

        // Assert locked fields from session were enforced
        $this->assertEquals('Original Prefill Name', $survey->respondent_name);
        $this->assertEquals('Prefilled Office', $survey->division_office);
        $this->assertEquals('Business', $survey->client_type);
        $this->assertEquals(45, $survey->age);

        $session->refresh();
        $this->assertEquals('completed', $session->status);
        $this->assertEquals($surveyId, $session->survey_id);
    }

    public function test_api_submission_fails_with_invalid_session_token(): void
    {
        $payload = $this->validSurveyData([
            'session_token' => 'non-existent-token-xyz',
        ]);

        $response = $this->postJson(route('api.surveys.store'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['session_token']);
    }

    public function test_api_submission_returns_conflict_if_session_already_completed(): void
    {
        $existingSurvey = Survey::create($this->validSurveyData());

        $session = SurveySession::create([
            'token' => 'completed-token-already',
            'status' => 'completed',
            'survey_id' => $existingSurvey->id,
            'completed_at' => now(),
        ]);

        $payload = $this->validSurveyData([
            'session_token' => $session->token,
        ]);

        $response = $this->postJson(route('api.surveys.store'), $payload);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'This survey session has already been completed.',
            ]);
    }

    public function test_api_submission_returns_error_if_session_expired(): void
    {
        $session = SurveySession::create([
            'token' => 'expired-token-session',
            'status' => 'pending',
            'expires_at' => now()->subDay(),
        ]);

        $payload = $this->validSurveyData([
            'session_token' => $session->token,
        ]);

        $response = $this->postJson(route('api.surveys.store'), $payload);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'This survey session has expired.',
            ]);
    }

    public function test_api_submission_validates_remarks_required_when_satisfaction_3_or_below(): void
    {
        $payload = $this->validSurveyData([
            'overall_satisfaction' => 2,
            'remarks' => null,
        ]);

        $response = $this->postJson(route('api.surveys.store'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['remarks']);
    }

    public function test_api_submission_validates_citizens_charter_rules(): void
    {
        // When CC1 is 4, CC2 and CC3 must not be provided
        $payload = $this->validSurveyData([
            'cc1_awareness' => 4,
            'cc2_visibility' => 2,
            'cc3_helpfulness' => 2,
        ]);

        $response = $this->postJson(route('api.surveys.store'), $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cc2_visibility', 'cc3_helpfulness']);

        // When CC1 is 1-3, CC2 and CC3 are required
        $payload2 = $this->validSurveyData([
            'cc1_awareness' => 2,
            'cc2_visibility' => null,
            'cc3_helpfulness' => null,
        ]);

        $response2 = $this->postJson(route('api.surveys.store'), $payload2);
        $response2->assertStatus(422)
            ->assertJsonValidationErrors(['cc2_visibility', 'cc3_helpfulness']);
    }

    public function test_api_submission_rejects_future_service_date(): void
    {
        $payload = $this->validSurveyData([
            'date_service_availed' => Carbon::tomorrow()->format('Y-m-d'),
        ]);

        $response = $this->postJson(route('api.surveys.store'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date_service_availed']);
    }
}
