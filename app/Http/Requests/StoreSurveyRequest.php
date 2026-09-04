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
            'date_service_availed' => ['required', 'date', 'before_or_equal:today'],
            'sex' => ['required', Rule::in(['Male', 'Female', 'Intersex', 'Prefer not to say'])],
            'age' => ['required', 'integer', 'min:1', 'max:120'],
            'region_id' => ['required', Rule::exists('form_options', 'id')->where('category', 'region')->where('is_active', true)],
            'service_id' => ['required', Rule::exists('form_options', 'id')->where('category', 'service')->where('is_active', true)],
            'overall_satisfaction' => ['required', 'integer', 'min:1', 'max:10'],
            'remarks' => [
                'required_if:overall_satisfaction,1,2,3',
                'nullable',
                'string',
                'max:2000',
            ],
            'cc1_awareness' => ['required', 'integer', 'between:1,4'],
            'cc2_visibility' => [
                'required_if:cc1_awareness,1,2,3',
                Rule::prohibitedIf(fn () => (int) $this->input('cc1_awareness') === 4),
                'nullable',
                'integer',
                'between:1,5',
            ],
            'cc3_helpfulness' => [
                'required_if:cc1_awareness,1,2,3',
                Rule::prohibitedIf(fn () => (int) $this->input('cc1_awareness') === 4),
                'nullable',
                'integer',
                'between:1,4',
            ],
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

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'remarks.required' => 'Remarks is required when the overall rating is 3 and below.',
            'remarks.required_if' => 'Remarks is required when the overall rating is 3 and below.',
            'date_service_availed.before_or_equal' => 'Date Service Availed cannot be a future date.',
            'cc2_visibility.required_if' => 'CC2 is required when aware of Citizen\'s Charter.',
            'cc2_visibility.prohibited' => 'CC2 should not be answered when CC1 option 4 is selected.',
            'cc3_helpfulness.required_if' => 'CC3 is required when aware of Citizen\'s Charter.',
            'cc3_helpfulness.prohibited' => 'CC3 should not be answered when CC1 option 4 is selected.',
        ];
    }
}
