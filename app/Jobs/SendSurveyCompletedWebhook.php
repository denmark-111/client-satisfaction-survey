<?php

namespace App\Jobs;

use App\Models\SurveyResponse;
use App\Models\SurveySession;
use App\Models\WebhookDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class SendSurveyCompletedWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $surveyResponseId;
    public ?int $surveySessionId;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array<int, int>
     */
    public array $backoff = [10, 60, 300];

    /**
     * Create a new job instance.
     */
    public function __construct(int $surveyResponseId, ?int $surveySessionId = null)
    {
        $this->surveyResponseId = $surveyResponseId;
        $this->surveySessionId = $surveySessionId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $responseModel = SurveyResponse::with(['center', 'region', 'service'])->find($this->surveyResponseId);
        if (! $responseModel) {
            return;
        }

        $session = $this->surveySessionId
            ? SurveySession::find($this->surveySessionId)
            : $responseModel->session;

        $webhookUrl = $session?->webhook_url;
        if (empty($webhookUrl)) {
            return;
        }

        $payload = [
            'event' => 'survey.completed',
            'timestamp' => now()->toIso8601String(),
            'data' => [
                'session_token' => $session?->token,
                'client_system' => $session?->client_system,
                'external_transaction_id' => $session?->external_transaction_id,
                'response_id' => $responseModel->id,
                'survey_id' => $responseModel->id,
                'respondent' => [
                    'name' => $responseModel->respondent_name,
                    'contact_number' => $responseModel->respondent_contact_number,
                    'client_type' => $responseModel->client_type,
                    'sex' => $responseModel->sex,
                    'age' => $responseModel->age,
                ],
                'service' => [
                    'id' => $responseModel->service_id,
                    'code' => $responseModel->service?->code,
                    'name' => $responseModel->service?->name,
                ],
                'center' => [
                    'id' => $responseModel->center_id,
                    'code' => $responseModel->center?->code,
                    'name' => $responseModel->center?->label,
                ],
                'region' => [
                    'id' => $responseModel->region_id,
                    'code' => $responseModel->region?->code,
                    'name' => $responseModel->region?->label,
                ],
                'division_office' => $responseModel->division_office,
                'date_service_availed' => $responseModel->date_service_availed?->format('Y-m-d'),
                'ratings' => [
                    'overall_satisfaction' => $responseModel->overall_satisfaction,
                    'remarks' => $responseModel->remarks,
                    'citizen_charter' => [
                        'cc1_awareness' => $responseModel->cc1_awareness,
                        'cc2_visibility' => $responseModel->cc2_visibility,
                        'cc3_helpfulness' => $responseModel->cc3_helpfulness,
                    ],
                    'sqd' => [
                        'sqd0_overall' => $responseModel->sqd0_overall,
                        'sqd1_responsiveness' => $responseModel->sqd1_responsiveness,
                        'sqd2_reliability' => $responseModel->sqd2_reliability,
                        'sqd3_access_facilities' => $responseModel->sqd3_access_facilities,
                        'sqd4_communication' => $responseModel->sqd4_communication,
                        'sqd5_costs' => $responseModel->sqd5_costs,
                        'sqd6_integrity' => $responseModel->sqd6_integrity,
                        'sqd7_assurance' => $responseModel->sqd7_assurance,
                        'sqd8_outcome' => $responseModel->sqd8_outcome,
                    ],
                ],
                'completed_at' => ($session?->completed_at ?? $responseModel->created_at)?->toIso8601String(),
            ],
        ];

        $delivery = WebhookDelivery::create([
            'survey_session_id' => $session?->id,
            'survey_response_id' => $responseModel->id,
            'url' => $webhookUrl,
            'event' => 'survey.completed',
            'payload' => $payload,
            'status' => 'pending',
            'attempts' => $this->attempts(),
        ]);

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'ClientSatisfactionSurvey-Webhook/1.0',
                    'X-Webhook-Event' => 'survey.completed',
                    'X-Webhook-Delivery' => (string) $delivery->id,
                    'X-Webhook-Timestamp' => now()->toIso8601String(),
                ])
                ->post($webhookUrl, $payload);

            if ($response->successful()) {
                $delivery->update([
                    'response_status' => $response->status(),
                    'response_body' => Str::limit($response->body(), 4000),
                    'status' => 'success',
                    'attempts' => $this->attempts(),
                ]);
            } else {
                $delivery->update([
                    'response_status' => $response->status(),
                    'response_body' => Str::limit($response->body(), 4000),
                    'status' => 'failed',
                    'error_message' => 'HTTP request failed with status: ' . $response->status(),
                    'attempts' => $this->attempts(),
                ]);

                throw new \RuntimeException('Webhook delivery failed with HTTP status: ' . $response->status());
            }
        } catch (Throwable $e) {
            $delivery->update([
                'status' => 'failed',
                'error_message' => Str::limit($e->getMessage(), 1000),
                'attempts' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        if ($this->surveyResponseId) {
            WebhookDelivery::where('survey_response_id', $this->surveyResponseId)
                ->where('status', 'pending')
                ->latest()
                ->first()
                ?->update([
                    'status' => 'failed',
                    'error_message' => $exception ? Str::limit($exception->getMessage(), 1000) : 'Job failed.',
                ]);
        }
    }
}
