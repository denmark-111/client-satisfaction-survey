<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSurveySessionRequest;
use App\Models\FormOption;
use App\Models\Service;
use App\Models\SurveySession;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class SurveySessionController extends Controller
{
    /**
     * Create a new survey session with pre-filled details.
     */
    public function store(CreateSurveySessionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $centerId = $validated['center_id'] ?? null;
        if (! $centerId && ! empty($validated['center_code'])) {
            $centerId = FormOption::where('category', 'center')
                ->where('code', $validated['center_code'])
                ->value('id');
        }

        $regionId = $validated['region_id'] ?? null;
        if (! $regionId && ! empty($validated['region_code'])) {
            $regionId = FormOption::where('category', 'region')
                ->where('code', $validated['region_code'])
                ->value('id');
        }

        $serviceId = $validated['service_id'] ?? null;
        if (! $serviceId && ! empty($validated['service_code'])) {
            $serviceId = Service::where('code', $validated['service_code'])->value('id');
        }

        $expiresInHours = (int) ($validated['expires_in_hours'] ?? 168); // 7 days default
        $expiresAt = now()->addHours($expiresInHours);

        $session = SurveySession::create([
            'token' => Str::random(64),
            'client_system' => $validated['client_system'] ?? null,
            'external_transaction_id' => $validated['external_transaction_id'] ?? null,
            'respondent_name' => $validated['respondent_name'] ?? null,
            'respondent_contact_number' => $validated['respondent_contact_number'] ?? null,
            'center_id' => $centerId,
            'division_office' => $validated['division_office'] ?? null,
            'client_type' => $validated['client_type'] ?? null,
            'date_service_availed' => $validated['date_service_availed'] ?? null,
            'sex' => $validated['sex'] ?? null,
            'age' => $validated['age'] ?? null,
            'region_id' => $regionId,
            'service_id' => $serviceId,
            'webhook_url' => $validated['webhook_url'] ?? null,
            'status' => 'pending',
            'expires_at' => $expiresAt,
        ]);

        $surveyUrl = route('survey.create', ['token' => $session->token]);

        return response()->json([
            'success' => true,
            'message' => 'Survey session created successfully.',
            'data' => [
                'token' => $session->token,
                'survey_url' => $surveyUrl,
                'expires_at' => $session->expires_at?->toIso8601String(),
            ],
        ], 201);
    }
}
