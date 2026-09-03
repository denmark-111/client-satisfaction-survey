<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSurveyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'agreed_to_participate' => ['accepted'],
            'respondent_name' => ['nullable', 'string', 'max:255'],
            'respondent_contact_number' => ['nullable', 'string', 'max:50'],
            'center_id' => ['required', Rule::exists('form_options', 'id')->where('category', 'center')->where('is_active', true)],
            'division_office' => ['required', 'string', 'max:255'],
            'client_type' => ['required', Rule::in(['Citizen', 'Business', 'Government(Employee or Another Agency)'])],
            'date_service_availed' => ['required', 'date'],
            'sex' => ['required', Rule::in(['Male', 'Female', 'Intersex', 'Prefer not to say'])],
            'age' => ['required', 'integer', 'min:1', 'max:120'],
            'region_id' => ['required', Rule::exists('form_options', 'id')->where('category', 'region')->where('is_active', true)],
            'service_id' => ['required', Rule::exists('form_options', 'id')->where('category', 'service')->where('is_active', true)],
            'overall_satisfaction' => ['required', 'integer', 'min:1', 'max:10'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'cc1_awareness' => ['required', 'integer', 'between:1,4'],
            'cc2_visibility' => ['nullable', 'integer', 'between:1,5'],
            'cc3_helpfulness' => ['nullable', 'integer', 'between:1,4'],
            'sqd0_overall' => ['required', 'integer', 'between:0,5'],
            'sqd1_responsiveness' => ['required', 'integer', 'between:0,5'],
            'sqd2_reliability' => ['required', 'integer', 'between:0,5'],
            'sqd3_access_facilities' => ['required', 'integer', 'between:0,5'],
            'sqd4_communication' => ['required', 'integer', 'between:0,5'],
            'sqd5_costs' => ['required', 'integer', 'between:0,5'],
            'sqd6_integrity' => ['required', 'integer', 'between:0,5'],
            'sqd7_assurance' => ['required', 'integer', 'between:0,5'],
            'sqd8_outcome' => ['required', 'integer', 'between:0,5'],
        ];
    }
}
