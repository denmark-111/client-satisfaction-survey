<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSurveyRequest;
use App\Models\FormOption;
use App\Models\Survey;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SurveyController extends Controller
{
    public function create(): View
    {
        $centers = FormOption::category('center')->get();
        $regions = FormOption::category('region')->get();
        $services = FormOption::category('service')->get();

        return view('survey.create', compact('centers', 'regions', 'services'));
    }

    public function store(StoreSurveyRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['agreed_to_participate'] = true;

        Survey::create($validated);

        return redirect()->route('survey.confirmation');
    }

    public function confirmation(): View
    {
        return view('survey.confirmation');
    }
}
