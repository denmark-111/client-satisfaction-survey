<?php

namespace Tests\Feature;

use App\Models\FormOption;
use App\Models\Service;
use App\Models\SurveyResponse;
use App\Models\SurveySession;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveySessionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function validSurveySubmissionData(array $overrides = []): array
    {
        $center = FormOption::where('category', 'center')->first();
        $region = FormOption::where('category', 'region')->first();
        $service = Service::first();

        return array_merge([
            'agreed_to_participate' => '1',
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

    public function test_client_system_can_create_survey_session_without_auth_using_ids(): void
    {
        $center = FormOption::where('category', 'center')->first();
        $region = FormOption::where('category', 'region')->first();
        $service = Service::first();

        $payload = [
            'client_system' => 'dairy-loan-system',
            'external_transaction_id' => 'TX-100234',
            'respondent_name' => 'Juan Dela Cruz',
            'respondent_contact_number' => '09171234567',
            'center_id' => $center->id,
            'division_office' => 'Dairy Processing Unit',
            'client_type' => 'Citizen',
            'date_service_availed' => Carbon::today()->format('Y-m-d'),
            'sex' => 'Male',
            'age' => 35,
            'region_id' => $region->id,
            'service_id' => $service->id,
        ];

        $response = $this->postJson(route('api.survey-sessions.store'), $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'survey_url',
                    'expires_at',
                ],
            ])
            ->assertJson([
                'success' => true,
            ]);

        $token = $response->json('data.token');
        $this->assertNotNull($token);
        $this->assertStringContainsString('token=' . $token, $response->json('data.survey_url'));

        $this->assertDatabaseHas('survey_sessions', [
            'token' => $token,
            'client_system' => 'dairy-loan-system',
            'external_transaction_id' => 'TX-100234',
            'respondent_name' => 'Juan Dela Cruz',
            'center_id' => $center->id,
            'region_id' => $region->id,
            'service_id' => $service->id,
            'status' => 'pending',
        ]);
    }

    public function test_client_system_can_create_survey_session_using_semantic_codes(): void
    {
        $payload = [
            'client_system' => 'herd-management-service',
            'external_transaction_id' => 'HERD-8821',
            'respondent_name' => 'Ana Reyes',
            'center_code' => 'CO',
            'region_code' => 'NCR',
            'service_code' => 'SRV-AB-NATURAL-HEAT',
            'client_type' => 'Business',
            'sex' => 'Female',
            'age' => 40,
        ];

        $response = $this->postJson(route('api.survey-sessions.store'), $payload);

        $response->assertStatus(201);
        $token = $response->json('data.token');

        $center = FormOption::where('category', 'center')->where('code', 'CO')->first();
        $region = FormOption::where('category', 'region')->where('code', 'NCR')->first();
        $service = Service::where('code', 'SRV-AB-NATURAL-HEAT')->first();

        $this->assertDatabaseHas('survey_sessions', [
            'token' => $token,
            'center_id' => $center->id,
            'region_id' => $region->id,
            'service_id' => $service->id,
            'respondent_name' => 'Ana Reyes',
        ]);
    }

    public function test_manual_mode_loads_empty_and_unlocked_form_without_token(): void
    {
        $response = $this->get(route('survey.create'));

        $response->assertStatus(200);
        $response->assertDontSee('type="hidden" name="center_id"', false);
        $response->assertDontSee('type="hidden" name="service_id"', false);
        $response->assertDontSee('type="hidden" name="respondent_name"', false);
        $response->assertSee('name="center_id" required', false);
        $response->assertSee('name="service_id" required', false);
        $response->assertDontSee('Pre-filled');
    }

    public function test_survey_page_loads_prefilled_and_disables_provided_fields_without_indicators(): void
    {
        $service = Service::where('code', 'SRV-AB-NATURAL-HEAT')->first();
        $center = FormOption::where('category', 'center')->where('code', 'CO')->first();

        $session = SurveySession::create([
            'token' => 'test-token-1234567890abcdef',
            'client_system' => 'testing',
            'respondent_name' => 'Session Respondent',
            'service_id' => $service->id,
            'center_id' => $center->id,
            'status' => 'pending',
            'expires_at' => now()->addDays(3),
        ]);

        $response = $this->get(route('survey.create', ['token' => $session->token]));

        $response->assertStatus(200);
        // Ensure no pre-filled indicators or badges
        $response->assertDontSee('Pre-filled');
        $response->assertDontSee('🔒');
        // Ensure session token is present
        $response->assertSee('name="session_token" value="test-token-1234567890abcdef"', false);
        // Respondent name is disabled and has hidden fallback input
        $response->assertSee('<input type="hidden" name="respondent_name" value="Session Respondent">', false);
        $response->assertSee('disabled', false);
        // Center and Service selects have hidden inputs
        $response->assertSee('<input type="hidden" name="center_id" value="' . $center->id . '">', false);
        $response->assertSee('<input type="hidden" name="service_id" value="' . $service->id . '">', false);
    }

    public function test_submitting_prefilled_survey_completes_session_and_associates_survey(): void
    {
        $service = Service::where('code', 'SRV-AB-NATURAL-HEAT')->first();
        $center = FormOption::where('category', 'center')->where('code', 'CO')->first();
        $region = FormOption::where('category', 'region')->where('code', 'NCR')->first();

        $session = SurveySession::create([
            'token' => 'session-to-complete-123',
            'respondent_name' => 'Original Name',
            'center_id' => $center->id,
            'region_id' => $region->id,
            'service_id' => $service->id,
            'status' => 'pending',
            'expires_at' => now()->addDays(2),
        ]);

        $submissionData = $this->validSurveySubmissionData([
            'session_token' => $session->token,
            'respondent_name' => 'Tampered Name Attempt', // Session has 'Original Name' which should be enforced
            'center_id' => $center->id,
            'region_id' => $region->id,
            'service_id' => $service->id,
        ]);

        $response = $this->post(route('survey.store'), $submissionData);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('survey.confirmation'));

        $surveyResponse = SurveyResponse::latest('id')->first();
        $this->assertNotNull($surveyResponse);
        $this->assertNotNull($surveyResponse->id);
        // Enforced server-side value from locked session field
        $this->assertEquals('Original Name', $surveyResponse->respondent_name);
        $this->assertEquals($session->id, $surveyResponse->survey_session_id);

        $session->refresh();
        $this->assertEquals('completed', $session->status);
        $this->assertNotNull($session->completed_at);
        $this->assertEquals($surveyResponse->id, $session->response->id);
    }

    public function test_completed_session_shows_completion_notice_and_prevents_duplicate_submission(): void
    {
        $session = SurveySession::create([
            'token' => 'already-used-token',
            'status' => 'completed',
            'completed_at' => now()->subHour(),
        ]);

        SurveyResponse::create(array_merge($this->validSurveySubmissionData(), [
            'survey_session_id' => $session->id,
        ]));

        $response = $this->get(route('survey.create', ['token' => $session->token]));

        $response->assertStatus(200);
        $response->assertSee('This survey has already been completed.');
        $response->assertSee('confirmation-island');
        $response->assertSee('Return to Home');
        $response->assertDontSee('Kiosk');
        $response->assertDontSee('⚠️');
    }

    public function test_expired_session_shows_expired_notice(): void
    {
        $session = SurveySession::create([
            'token' => 'expired-token-xyz',
            'status' => 'pending',
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->get(route('survey.create', ['token' => $session->token]));

        $response->assertStatus(200);
        $response->assertSee('This survey link has expired.');
        $response->assertSee('confirmation-island');
        $response->assertSee('Return to Home');
        $response->assertDontSee('Kiosk');
        $response->assertDontSee('⚠️');
    }

    public function test_invalid_token_shows_invalid_notice(): void
    {
        $response = $this->get(route('survey.create', ['token' => 'completely-fake-token']));

        $response->assertStatus(200);
        $response->assertSee('The survey link is invalid or does not exist.');
        $response->assertSee('confirmation-island');
        $response->assertSee('Return to Home');
        $response->assertDontSee('Kiosk');
        $response->assertDontSee('⚠️');
    }
}
