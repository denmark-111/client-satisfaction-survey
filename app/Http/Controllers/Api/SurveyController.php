<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreSurveyRequest;
use App\Jobs\SendSurveyCompletedWebhook;
use App\Models\Survey;
use App\Models\SurveySession;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class SurveyController extends Controller
{
    /**
     * Submit a survey directly via API.
     */
    public function store(StoreSurveyRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if ((int) ($validated['cc1_awareness'] ?? 0) === 4) {
            $validated['cc2_visibility'] = null;
            $validated['cc3_helpfulness'] = null;
        }

        $validated['agreed_to_participate'] = true;

        $sessionToken = $validated['session_token'] ?? null;
        $clientSystem = $validated['client_system'] ?? null;
        $externalTransactionId = $validated['external_transaction_id'] ?? null;
        $webhookUrl = $validated['webhook_url'] ?? null;

        unset(
            $validated['session_token'],
            $validated['client_system'],
            $validated['external_transaction_id'],
            $validated['webhook_url'],
            $validated['center_code'],
            $validated['region_code'],
            $validated['service_code']
        );

        $session = null;
        if ($sessionToken) {
            $session = SurveySession::where('token', $sessionToken)->first();

            if (! $session) {
                return response()->json([
                    'success' => false,
                    'message' => 'The provided survey session token does not exist.',
                ], 404);
            }

            if ($session->isCompleted()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This survey session has already been completed.',
                ], 409);
            }

            if ($session->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This survey session has expired.',
                ], 422);
            }

            if (! empty($webhookUrl)) {
                $session->update(['webhook_url' => $webhookUrl]);
            }
        }

        $survey = Survey::create($validated);

        if ($session) {
            $session->markCompleted($survey);
        } else {
            $session = SurveySession::create([
                'token' => Str::random(64),
                'client_system' => $clientSystem,
                'external_transaction_id' => $externalTransactionId,
                'webhook_url' => $webhookUrl,
                'respondent_name' => $survey->respondent_name,
                'respondent_contact_number' => $survey->respondent_contact_number,
                'center_id' => $survey->center_id,
                'division_office' => $survey->division_office,
                'client_type' => $survey->client_type,
                'date_service_availed' => $survey->date_service_availed,
                'sex' => $survey->sex,
                'age' => $survey->age,
                'region_id' => $survey->region_id,
                'service_id' => $survey->service_id,
                'status' => 'completed',
                'completed_at' => now(),
                'survey_id' => $survey->id,
            ]);
        }

        $webhookStatus = 'none';
        if (! empty($session->webhook_url)) {
            try {
                SendSurveyCompletedWebhook::dispatch($survey->id, $session->id);
                $webhookStatus = 'queued';
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Synchronous webhook delivery failed: ' . $e->getMessage());
                $webhookStatus = 'failed';
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Survey submitted successfully.',
            'data' => [
                'survey_id' => $survey->id,
                'session_token' => $session->token,
                'client_system' => $session->client_system,
                'external_transaction_id' => $session->external_transaction_id,
                'webhook_status' => $webhookStatus,
            ],
        ], 201);
    }
}
