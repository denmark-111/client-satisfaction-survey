<?php

namespace App\Jobs;

use App\Models\Survey;
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

    public int $surveyId;
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
    public function __construct(int $surveyId, ?int $surveySessionId = null)
    {
        $this->surveyId = $surveyId;
        $this->surveySessionId = $surveySessionId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $survey = Survey::with(['center', 'region', 'service'])->find($this->surveyId);
        if (! $survey) {
            return;
        }

        $session = $this->surveySessionId
            ? SurveySession::find($this->surveySessionId)
            : $survey->session;

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
                'survey_id' => $survey->id,
                'respondent' => [
                    'name' => $survey->respondent_name,
                    'contact_number' => $survey->respondent_contact_number,
                    'client_type' => $survey->client_type,
                    'sex' => $survey->sex,
                    'age' => $survey->age,
                ],
                'service' => [
                    'id' => $survey->service_id,
                    'code' => $survey->service?->code,
                    'name' => $survey->service?->name,
                ],
                'center' => [
                    'id' => $survey->center_id,
                    'code' => $survey->center?->code,
                    'name' => $survey->center?->label,
                ],
                'region' => [
                    'id' => $survey->region_id,
                    'code' => $survey->region?->code,
                    'name' => $survey->region?->label,
                ],
                'division_office' => $survey->division_office,
                'date_service_availed' => $survey->date_service_availed?->format('Y-m-d'),
                'ratings' => [
                    'overall_satisfaction' => $survey->overall_satisfaction,
                    'remarks' => $survey->remarks,
                    'citizen_charter' => [
                        'cc1_awareness' => $survey->cc1_awareness,
                        'cc2_visibility' => $survey->cc2_visibility,
                        'cc3_helpfulness' => $survey->cc3_helpfulness,
                    ],
                    'sqd' => [
                        'sqd0_overall' => $survey->sqd0_overall,
                        'sqd1_responsiveness' => $survey->sqd1_responsiveness,
                        'sqd2_reliability' => $survey->sqd2_reliability,
                        'sqd3_access_facilities' => $survey->sqd3_access_facilities,
                        'sqd4_communication' => $survey->sqd4_communication,
                        'sqd5_costs' => $survey->sqd5_costs,
                        'sqd6_integrity' => $survey->sqd6_integrity,
                        'sqd7_assurance' => $survey->sqd7_assurance,
                        'sqd8_outcome' => $survey->sqd8_outcome,
                    ],
                ],
                'completed_at' => ($session?->completed_at ?? $survey->created_at)?->toIso8601String(),
            ],
        ];

        $delivery = WebhookDelivery::create([
            'survey_session_id' => $session?->id,
            'survey_id' => $survey->id,
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
        if ($this->surveyId) {
            WebhookDelivery::where('survey_id', $this->surveyId)
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
