<?php

namespace App\Http\Requests\Api;

use App\Models\FormOption;
use App\Models\Service;
use App\Models\SurveySession;
use Carbon\Carbon;
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
     * Prepare data for validation by resolving codes to IDs and enforcing locked fields from sessions.
     */
    protected function prepareForValidation(): void
    {
        $toMerge = [];

        if ($this->filled('session_token')) {
            $session = SurveySession::where('token', $this->input('session_token'))->first();

            if ($session && $session->isValid()) {
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
                        $val = $session->$field;
                        $toMerge[$field] = $val instanceof Carbon
                            ? $val->format('Y-m-d')
                            : $val;
                    }
                }
            }
        }

        // Semantic code resolutions
        if (! isset($toMerge['center_id']) && ! $this->filled('center_id') && $this->filled('center_code')) {
            $centerId = FormOption::where('category', 'center')
                ->where('code', $this->input('center_code'))
                ->where('is_active', true)
                ->value('id');
            if ($centerId) {
                $toMerge['center_id'] = $centerId;
            }
        }

        if (! isset($toMerge['region_id']) && ! $this->filled('region_id') && $this->filled('region_code')) {
            $regionId = FormOption::where('category', 'region')
                ->where('code', $this->input('region_code'))
                ->where('is_active', true)
                ->value('id');
            if ($regionId) {
                $toMerge['region_id'] = $regionId;
            }
        }

        if (! isset($toMerge['service_id']) && ! $this->filled('service_id') && $this->filled('service_code')) {
            $serviceId = Service::where('code', $this->input('service_code'))
                ->where('is_active', true)
                ->value('id');
            if ($serviceId) {
                $toMerge['service_id'] = $serviceId;
            }
        }

        if (! $this->has('agreed_to_participate')) {
            $toMerge['agreed_to_participate'] = true;
        }

        if (! empty($toMerge)) {
            $this->merge($toMerge);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'session_token' => ['nullable', 'string', 'exists:survey_sessions,token'],
            'client_system' => ['nullable', 'string', 'max:100'],
            'external_transaction_id' => ['nullable', 'string', 'max:100'],
            'webhook_url' => ['nullable', 'url', 'max:2048'],

            'agreed_to_participate' => ['nullable', 'boolean'],
            'respondent_name' => ['nullable', 'string', 'max:255'],
            'respondent_contact_number' => ['nullable', 'string', 'max:50'],
            'center_id' => ['required', 'integer', Rule::exists('form_options', 'id')->where('category', 'center')->where('is_active', true)],
            'center_code' => ['nullable', 'string', Rule::exists('form_options', 'code')->where('category', 'center')->where('is_active', true)],
            'division_office' => ['required', 'string', 'max:255'],
            'client_type' => ['required', Rule::in(['Citizen', 'Business', 'Government(Employee or Another Agency)'])],
            'date_service_availed' => ['required', 'date', 'before_or_equal:today'],
            'sex' => ['required', Rule::in(['Male', 'Female', 'Intersex', 'Prefer not to say'])],
            'age' => ['required', 'integer', 'min:1', 'max:120'],
            'region_id' => ['required', 'integer', Rule::exists('form_options', 'id')->where('category', 'region')->where('is_active', true)],
            'region_code' => ['nullable', 'string', Rule::exists('form_options', 'code')->where('category', 'region')->where('is_active', true)],
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')->where('is_active', true)],
            'service_code' => ['nullable', 'string', Rule::exists('services', 'code')->where('is_active', true)],

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
            'center_id.required' => 'Center is required. Provide center_id or center_code.',
            'region_id.required' => 'Region is required. Provide region_id or region_code.',
            'service_id.required' => 'Service is required. Provide service_id or service_code.',
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
