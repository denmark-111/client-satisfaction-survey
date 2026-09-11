<?php

namespace Tests\Feature;

use App\Models\ApiClient;
use App\Models\FormOption;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiClientAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_unauthenticated_request_is_rejected_with_401(): void
    {
        $response = $this->postJson(route('api.survey-sessions.store'), [
            'respondent_name' => 'John Doe',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated client service. Missing API key in X-API-Key or Authorization header.',
            ]);
    }

    public function test_invalid_api_key_is_rejected_with_401(): void
    {
        $response = $this->withHeaders([
            'X-API-Key' => 'css_invalid_key_999999999999999999999999',
        ])->postJson(route('api.survey-sessions.store'), [
            'respondent_name' => 'John Doe',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated client service. Invalid API key.',
            ]);
    }

    public function test_inactive_client_service_is_rejected_with_403(): void
    {
        $created = ApiClient::createWithKey('Inactive Service', 'inactive-service', 'css_test_inactive_key_123');
        $created['client']->update(['is_active' => false]);

        $response = $this->withHeaders([
            'X-API-Key' => 'css_test_inactive_key_123',
        ])->postJson(route('api.survey-sessions.store'), [
            'respondent_name' => 'John Doe',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Client service is inactive or unauthorized.',
            ]);
    }

    public function test_permitted_client_can_create_survey_session_using_x_api_key_header(): void
    {
        $center = FormOption::where('category', 'center')->first();
        $region = FormOption::where('category', 'region')->first();
        $service = Service::first();

        $response = $this->withHeaders([
            'X-API-Key' => 'css_test_dairy_loan_key_1234567890abcdef',
        ])->postJson(route('api.survey-sessions.store'), [
            'external_transaction_id' => 'TX-DL-001',
            'respondent_name' => 'Juan Dela Cruz',
            'center_id' => $center->id,
            'region_id' => $region->id,
            'service_id' => $service->id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Survey session created successfully.',
            ]);

        $token = $response->json('data.token');
        $this->assertNotNull($token);

        $this->assertDatabaseHas('survey_sessions', [
            'token' => $token,
            'client_system' => 'dairy-loan-system',
            'external_transaction_id' => 'TX-DL-001',
            'respondent_name' => 'Juan Dela Cruz',
        ]);
    }

    public function test_permitted_client_can_create_survey_session_using_bearer_token(): void
    {
        $center = FormOption::where('category', 'center')->first();
        $region = FormOption::where('category', 'region')->first();
        $service = Service::first();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer css_test_herd_management_key_1234567890abcdef',
        ])->postJson(route('api.survey-sessions.store'), [
            'external_transaction_id' => 'TX-HERD-999',
            'respondent_name' => 'Maria Reyes',
            'center_id' => $center->id,
            'region_id' => $region->id,
            'service_id' => $service->id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $token = $response->json('data.token');
        $this->assertDatabaseHas('survey_sessions', [
            'token' => $token,
            'client_system' => 'herd-management-service',
            'external_transaction_id' => 'TX-HERD-999',
        ]);
    }

    public function test_permitted_client_can_submit_survey_response_directly(): void
    {
        $center = FormOption::where('category', 'center')->first();
        $region = FormOption::where('category', 'region')->first();
        $service = Service::first();

        $payload = [
            'agreed_to_participate' => true,
            'respondent_name' => 'Direct Respondent',
            'respondent_contact_number' => '09123456789',
            'center_id' => $center->id,
            'division_office' => 'Field Office',
            'client_type' => 'Citizen',
            'date_service_availed' => Carbon::today()->format('Y-m-d'),
            'sex' => 'Female',
            'age' => 30,
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
        ];

        $response = $this->withHeaders([
            'X-API-Key' => 'css_test_client_key_1234567890abcdef',
        ])->postJson(route('api.survey-responses.store'), $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'client_system' => 'test-client',
                ],
            ]);
    }

    public function test_client_last_used_at_is_updated_on_successful_request(): void
    {
        $client = ApiClient::where('slug', 'dairy-loan-system')->first();
        $this->assertNull($client->last_used_at);

        $this->withHeaders([
            'X-API-Key' => 'css_test_dairy_loan_key_1234567890abcdef',
        ])->postJson(route('api.survey-sessions.store'), [
            'respondent_name' => 'Usage Test',
        ]);

        $client->refresh();
        $this->assertNotNull($client->last_used_at);
    }

    public function test_artisan_client_create_command_registers_new_client(): void
    {
        $this->artisan('client:create', [
            'name' => 'Livestock Tracking System',
            '--slug' => 'livestock-tracking',
            '--key' => 'css_live_custom_livestock_key_12345',
        ])->assertSuccessful();

        $this->assertDatabaseHas('api_clients', [
            'name' => 'Livestock Tracking System',
            'slug' => 'livestock-tracking',
            'is_active' => true,
        ]);

        $client = ApiClient::where('slug', 'livestock-tracking')->first();
        $this->assertNotNull($client);
        $this->assertEquals(hash('sha256', 'css_live_custom_livestock_key_12345'), $client->api_key_hash);
    }
}
