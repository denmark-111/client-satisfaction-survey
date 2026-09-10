<?php

namespace Tests\Feature;

use App\Jobs\SendSurveyCompletedWebhook;
use App\Models\FormOption;
use App\Models\Service;
use App\Models\SurveyResponse;
use App\Models\SurveySession;
use App\Models\WebhookDelivery;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebhookDispatchTest extends TestCase
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
            'respondent_name' => 'Pedro Penduko',
            'respondent_contact_number' => '09191234567',
            'center_id' => $center->id,
            'division_office' => 'Livestock Extension',
            'client_type' => 'Citizen',
            'date_service_availed' => Carbon::today()->format('Y-m-d'),
            'sex' => 'Male',
            'age' => 32,
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

    public function test_direct_api_submission_with_webhook_url_sends_webhook_and_logs_delivery(): void
    {
        Http::fake([
            'https://client-portal.test/api/webhooks/survey' => Http::response(['received' => true], 200),
        ]);

        $payload = $this->validSurveyData([
            'client_system' => 'client-portal-app',
            'external_transaction_id' => 'TX-PORTAL-88',
            'webhook_url' => 'https://client-portal.test/api/webhooks/survey',
        ]);

        $response = $this->postJson(route('api.survey-responses.store'), $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'webhook_status' => 'queued',
                ],
            ]);

        $responseId = $response->json('data.response_id');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://client-portal.test/api/webhooks/survey'
                && $request['event'] === 'survey.completed'
                && $request['data']['client_system'] === 'client-portal-app'
                && $request['data']['external_transaction_id'] === 'TX-PORTAL-88'
                && $request['data']['respondent']['name'] === 'Pedro Penduko'
                && $request['data']['ratings']['overall_satisfaction'] === 5
                && $request->header('X-Webhook-Event')[0] === 'survey.completed';
        });

        $this->assertDatabaseHas('webhook_deliveries', [
            'survey_response_id' => $responseId,
            'url' => 'https://client-portal.test/api/webhooks/survey',
            'event' => 'survey.completed',
            'status' => 'success',
            'response_status' => 200,
        ]);
    }

    public function test_survey_session_created_with_webhook_url_dispatches_when_completed_via_api(): void
    {
        Http::fake([
            'https://external-client.test/webhooks/callback' => Http::response(['ack' => true], 200),
        ]);

        $sessionResponse = $this->postJson(route('api.survey-sessions.store'), [
            'client_system' => 'dairy-loan-crm',
            'external_transaction_id' => 'TX-CRM-501',
            'webhook_url' => 'https://external-client.test/webhooks/callback',
        ]);

        $sessionResponse->assertStatus(201);
        $token = $sessionResponse->json('data.token');

        $payload = $this->validSurveyData([
            'session_token' => $token,
        ]);

        $response = $this->postJson(route('api.survey-responses.store'), $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'webhook_status' => 'queued',
                ],
            ]);

        $responseId = $response->json('data.response_id');

        Http::assertSent(function ($request) use ($token) {
            return $request->url() === 'https://external-client.test/webhooks/callback'
                && $request['data']['session_token'] === $token
                && $request['data']['client_system'] === 'dairy-loan-crm';
        });

        $this->assertDatabaseHas('webhook_deliveries', [
            'survey_response_id' => $responseId,
            'url' => 'https://external-client.test/webhooks/callback',
            'status' => 'success',
        ]);
    }

    public function test_web_survey_submission_dispatches_webhook_when_session_has_webhook_url(): void
    {
        Http::fake([
            'https://external-service.test/webhook' => Http::response(['status' => 'ok'], 200),
        ]);

        $sessionResponse = $this->postJson(route('api.survey-sessions.store'), [
            'client_system' => 'partner-agency',
            'external_transaction_id' => 'TX-WEB-777',
            'webhook_url' => 'https://external-service.test/webhook',
        ]);

        $sessionResponse->assertStatus(201);
        $token = $sessionResponse->json('data.token');

        $webPayload = $this->validSurveyData([
            'session_token' => $token,
            'agreed_to_participate' => '1',
        ]);

        $response = $this->post(route('survey.store'), $webPayload);

        $response->assertRedirect(route('survey.confirmation'));

        Http::assertSent(function ($request) use ($token) {
            return $request->url() === 'https://external-service.test/webhook'
                && $request['data']['session_token'] === $token;
        });

        $this->assertDatabaseHas('webhook_deliveries', [
            'url' => 'https://external-service.test/webhook',
            'status' => 'success',
            'response_status' => 200,
        ]);
    }

    public function test_submission_without_webhook_url_does_not_send_webhook(): void
    {
        Http::fake();

        $payload = $this->validSurveyData();

        $response = $this->postJson(route('api.survey-responses.store'), $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'webhook_status' => 'none',
                ],
            ]);

        Http::assertNothingSent();
        $this->assertDatabaseCount('webhook_deliveries', 0);
    }

    public function test_webhook_failure_is_logged_in_deliveries_table(): void
    {
        Http::fake([
            'https://failing-client.test/webhook' => Http::response('Internal Server Error', 500),
        ]);

        $session = SurveySession::create([
            'token' => 'failing-test-session',
            'webhook_url' => 'https://failing-client.test/webhook',
            'status' => 'completed',
        ]);
        $responseModel = SurveyResponse::create(array_merge($this->validSurveyData(), [
            'survey_session_id' => $session->id,
        ]));

        $job = new SendSurveyCompletedWebhook($responseModel->id, $session->id);

        try {
            $job->handle();
            $this->fail('Job should have thrown an exception on HTTP failure');
        } catch (\Throwable $e) {
            $this->assertStringContainsString('Webhook delivery failed with HTTP status: 500', $e->getMessage());
        }

        $this->assertDatabaseHas('webhook_deliveries', [
            'survey_session_id' => $session->id,
            'survey_response_id' => $responseModel->id,
            'url' => 'https://failing-client.test/webhook',
            'status' => 'failed',
            'response_status' => 500,
        ]);
    }
}
