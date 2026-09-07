<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSurveyRequest;
use App\Models\FormOption;
use App\Models\Service;
use App\Models\Survey;
use App\Models\SurveySession;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function create(Request $request): View
    {
        $centers = FormOption::category('center')->get();
        $regions = FormOption::category('region')->get();
        $services = Service::active()->orderBy('sort_order')->orderBy('name')->get();

        $session = null;
        $sessionError = null;

        if ($request->filled('token')) {
            $foundSession = SurveySession::where('token', $request->query('token'))->first();

            if (! $foundSession) {
                $sessionError = 'The survey link is invalid or does not exist.';
            } elseif ($foundSession->isCompleted()) {
                $sessionError = 'This survey has already been completed. Thank you for your feedback!';
            } elseif ($foundSession->isExpired()) {
                $sessionError = 'This survey link has expired.';
            } else {
                $session = $foundSession;
            }
        }

        return view('survey.create', compact('centers', 'regions', 'services', 'session', 'sessionError'));
    }

    public function store(StoreSurveyRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['agreed_to_participate'] = true;

        if ((int) ($validated['cc1_awareness'] ?? 0) === 4) {
            $validated['cc2_visibility'] = null;
            $validated['cc3_helpfulness'] = null;
        }

        $session = null;
        if (! empty($validated['session_token'])) {
            $session = SurveySession::where('token', $validated['session_token'])->first();

            if ($session && $session->isValid()) {
                // Enforce verified session values for locked fields
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

                foreach ($lockableFields as $field) {
                    if ($session->isFieldLocked($field)) {
                        $validated[$field] = $session->$field;
                    }
                }
            }
        }

        unset($validated['session_token']);

        $survey = Survey::create($validated);

        if ($session && $session->isValid()) {
            $session->markCompleted($survey);
        }

        return redirect()->route('survey.confirmation');
    }

    public function confirmation(): View
    {
        return view('survey.confirmation');
    }
}
