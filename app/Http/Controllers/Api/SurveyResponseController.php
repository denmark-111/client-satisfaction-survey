<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreSurveyResponseRequest;
use App\Jobs\SendSurveyCompletedWebhook;
use App\Models\SurveyResponse;
use App\Models\SurveySession;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class SurveyResponseController extends Controller
{
    /**
     * Submit a survey response directly via API.
     */
    public function store(StoreSurveyResponseRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if ((int) ($validated['cc1_awareness'] ?? 0) === 4) {
            $validated['cc2_visibility'] = null;
            $validated['cc3_helpfulness'] = null;
        }

        $validated['agreed_to_participate'] = true;

        $sessionToken = $validated['session_token'] ?? null;
        $client = $request->attributes->get('client_service');
        $clientSystem = $validated['client_system'] ?? ($client?->slug ?? null);
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

            $lockableFields = [
                'respondent_name',
                'respondent_contact_number',
                'center_id',
                'division_office',
                'client_type',
                'date_service_availed',
                'sex',
                'age',
                'region_id',
                'service_id',
            ];

            $sessionUpdates = [];
            if (! empty($webhookUrl)) {
                $sessionUpdates['webhook_url'] = $webhookUrl;
            }
            foreach ($lockableFields as $field) {
                if ($session->isFieldLocked($field)) {
                    $validated[$field] = $session->$field;
                } elseif (isset($validated[$field])) {
                    $sessionUpdates[$field] = $validated[$field];
                }
            }

            if (! empty($sessionUpdates)) {
                $session->update($sessionUpdates);
            }
        } else {
            $session = SurveySession::create([
                'token' => Str::random(64),
                'client_system' => $clientSystem,
                'external_transaction_id' => $externalTransactionId,
                'webhook_url' => $webhookUrl,
                'respondent_name' => $validated['respondent_name'] ?? null,
                'respondent_contact_number' => $validated['respondent_contact_number'] ?? null,
                'center_id' => $validated['center_id'] ?? null,
                'division_office' => $validated['division_office'] ?? null,
                'client_type' => $validated['client_type'] ?? null,
                'date_service_availed' => $validated['date_service_availed'] ?? null,
                'sex' => $validated['sex'] ?? null,
                'age' => $validated['age'] ?? null,
                'region_id' => $validated['region_id'] ?? null,
                'service_id' => $validated['service_id'] ?? null,
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        $validated['survey_session_id'] = $session->id;
        $response = SurveyResponse::create($validated);

        if ($sessionToken && $session) {
            $session->markCompleted();
        }

        $webhookStatus = 'none';
        if (! empty($session->webhook_url)) {
            try {
                SendSurveyCompletedWebhook::dispatch($response->id, $session->id);
                $webhookStatus = 'queued';
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Synchronous webhook delivery failed: ' . $e->getMessage());
                $webhookStatus = 'failed';
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Survey response submitted successfully.',
            'data' => [
                'response_id' => $response->id,
                'survey_id' => $response->id,
                'session_token' => $session->token,
                'client_system' => $session->client_system,
                'external_transaction_id' => $session->external_transaction_id,
                'webhook_status' => $webhookStatus,
            ],
        ], 201);
    }
}
