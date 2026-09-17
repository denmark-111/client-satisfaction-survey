<?php

namespace Tests\Feature;

use App\Models\FormOption;
use App\Models\Service;
use App\Models\SurveyResponse;
use App\Models\SurveySession;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetSurveyResponsesApiTest extends TestCase
{
    use RefreshDatabase;

    private string $apiKey = 'css_test_dairy_loan_key_1234567890abcdef';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function getSurveyResponsesApi(array $queryParams = [])
    {
        return $this->withHeaders(['X-API-Key' => $this->apiKey])
            ->getJson(route('api.survey-responses.index', $queryParams));
    }

    private function validSurveyPayload(array $overrides = []): array
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

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson(route('api.survey-responses.index'));

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated client service. Missing API key in X-API-Key or Authorization header.',
            ]);
    }

    public function test_get_survey_responses_returns_same_shape_as_post_request_body(): void
    {
        $center = FormOption::where('category', 'center')->first();
        $region = FormOption::where('category', 'region')->first();
        $service = Service::first();

        $postPayload = $this->validSurveyPayload([
            'client_system' => 'dairy-loan-portal',
            'external_transaction_id' => 'TX-GET-001',
            'webhook_url' => 'https://external-system.example.com/api/css-webhook',
        ]);

        // Submit via POST endpoint
        $postResponse = $this->withHeaders(['X-API-Key' => $this->apiKey])
            ->postJson(route('api.survey-responses.store'), $postPayload);

        $postResponse->assertStatus(201);
        $sessionToken = $postResponse->json('data.session_token');

        // Retrieve via GET endpoint
        $getResponse = $this->getSurveyResponsesApi();

        $getResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'session_token',
                        'client_system',
                        'external_transaction_id',
                        'webhook_url',
                        'agreed_to_participate',
                        'respondent_name',
                        'respondent_contact_number',
                        'center_id',
                        'center_code',
                        'division_office',
                        'client_type',
                        'date_service_availed',
                        'sex',
                        'age',
                        'region_id',
                        'region_code',
                        'service_id',
                        'service_code',
                        'overall_satisfaction',
                        'remarks',
                        'cc1_awareness',
                        'cc2_visibility',
                        'cc3_helpfulness',
                        'sqd0_overall',
                        'sqd1_responsiveness',
                        'sqd2_reliability',
                        'sqd3_access_facilities',
                        'sqd4_communication',
                        'sqd5_costs',
                        'sqd6_integrity',
                        'sqd7_assurance',
                        'sqd8_outcome',
                    ],
                ],
            ]);

        $item = $getResponse->json('data.0');

        $this->assertEquals($sessionToken, $item['session_token']);
        $this->assertEquals('dairy-loan-portal', $item['client_system']);
        $this->assertEquals('TX-GET-001', $item['external_transaction_id']);
        $this->assertEquals('https://external-system.example.com/api/css-webhook', $item['webhook_url']);
        $this->assertTrue($item['agreed_to_participate']);
        $this->assertEquals('Maria Santos', $item['respondent_name']);
        $this->assertEquals('09181234567', $item['respondent_contact_number']);
        $this->assertEquals($center->id, $item['center_id']);
        $this->assertEquals($center->code, $item['center_code']);
        $this->assertEquals('Technical Operations', $item['division_office']);
        $this->assertEquals('Citizen', $item['client_type']);
        $this->assertEquals(Carbon::today()->format('Y-m-d'), $item['date_service_availed']);
        $this->assertEquals('Female', $item['sex']);
        $this->assertEquals(28, $item['age']);
        $this->assertEquals($region->id, $item['region_id']);
        $this->assertEquals($region->code, $item['region_code']);
        $this->assertEquals($service->id, $item['service_id']);
        $this->assertEquals($service->code, $item['service_code']);
        $this->assertEquals(5, $item['overall_satisfaction']);
        $this->assertNull($item['remarks']);
        $this->assertEquals(1, $item['cc1_awareness']);
        $this->assertEquals(1, $item['cc2_visibility']);
        $this->assertEquals(1, $item['cc3_helpfulness']);
        $this->assertEquals(5, $item['sqd0_overall']);
        $this->assertEquals(5, $item['sqd1_responsiveness']);
        $this->assertEquals(5, $item['sqd2_reliability']);
        $this->assertEquals(5, $item['sqd3_access_facilities']);
        $this->assertEquals(5, $item['sqd4_communication']);
        $this->assertEquals(5, $item['sqd5_costs']);
        $this->assertEquals(5, $item['sqd6_integrity']);
        $this->assertEquals(5, $item['sqd7_assurance']);
        $this->assertEquals(5, $item['sqd8_outcome']);
    }

    public function test_get_survey_responses_supports_filtering(): void
    {
        $center = FormOption::where('category', 'center')->first();
        $region = FormOption::where('category', 'region')->first();
        $service = Service::first();

        // Submit response 1
        $this->withHeaders(['X-API-Key' => $this->apiKey])
            ->postJson(route('api.survey-responses.store'), $this->validSurveyPayload([
                'respondent_name' => 'First Respondent',
                'external_transaction_id' => 'TX-FILTER-1',
                'client_type' => 'Citizen',
            ]));

        // Submit response 2
        $this->withHeaders(['X-API-Key' => $this->apiKey])
            ->postJson(route('api.survey-responses.store'), $this->validSurveyPayload([
                'respondent_name' => 'Second Respondent',
                'external_transaction_id' => 'TX-FILTER-2',
                'client_type' => 'Business',
            ]));

        // Filter by external_transaction_id
        $resTx = $this->getSurveyResponsesApi(['external_transaction_id' => 'TX-FILTER-1']);
        $resTx->assertStatus(200);
        $this->assertCount(1, $resTx->json('data'));
        $this->assertEquals('First Respondent', $resTx->json('data.0.respondent_name'));

        // Filter by client_type
        $resType = $this->getSurveyResponsesApi(['client_type' => 'Business']);
        $resType->assertStatus(200);
        $this->assertCount(1, $resType->json('data'));
        $this->assertEquals('Second Respondent', $resType->json('data.0.respondent_name'));

        // Filter by center_code
        $resCenter = $this->getSurveyResponsesApi(['center_code' => $center->code]);
        $resCenter->assertStatus(200);
        $this->assertCount(2, $resCenter->json('data'));
    }

    public function test_get_survey_responses_supports_pagination(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->withHeaders(['X-API-Key' => $this->apiKey])
                ->postJson(route('api.survey-responses.store'), $this->validSurveyPayload([
                    'respondent_name' => "Respondent $i",
                    'external_transaction_id' => "TX-PAG-$i",
                ]));
        }

        $resPage = $this->getSurveyResponsesApi(['per_page' => 2, 'page' => 1]);
        $resPage->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'pagination' => [
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                ],
            ]);

        $this->assertCount(2, $resPage->json('data'));
        $this->assertEquals(5, $resPage->json('pagination.total'));
        $this->assertEquals(2, $resPage->json('pagination.per_page'));
        $this->assertEquals(1, $resPage->json('pagination.current_page'));
        $this->assertEquals(3, $resPage->json('pagination.last_page'));
    }

    public function test_get_single_survey_response_by_id(): void
    {
        $postResponse = $this->withHeaders(['X-API-Key' => $this->apiKey])
            ->postJson(route('api.survey-responses.store'), $this->validSurveyPayload([
                'respondent_name' => 'Single Response Test',
            ]));

        $responseId = $postResponse->json('data.response_id');

        $getResponse = $this->withHeaders(['X-API-Key' => $this->apiKey])
            ->getJson(route('api.survey-responses.show', ['id' => $responseId]));

        $getResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $responseId,
                    'respondent_name' => 'Single Response Test',
                    'client_type' => 'Citizen',
                    'overall_satisfaction' => 5,
                ],
            ]);
    }

    public function test_get_single_survey_response_returns_404_when_not_found(): void
    {
        $response = $this->withHeaders(['X-API-Key' => $this->apiKey])
            ->getJson(route('api.survey-responses.show', ['id' => 99999]));

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Survey response not found.',
            ]);
    }
}
