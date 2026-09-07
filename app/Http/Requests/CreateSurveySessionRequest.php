<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateSurveySessionRequest extends FormRequest
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
            'client_system' => ['nullable', 'string', 'max:100'],
            'external_transaction_id' => ['nullable', 'string', 'max:100'],
            'respondent_name' => ['nullable', 'string', 'max:255'],
            'respondent_contact_number' => ['nullable', 'string', 'max:50'],
            'division_office' => ['nullable', 'string', 'max:255'],
            'client_type' => ['nullable', Rule::in(['Citizen', 'Business', 'Government(Employee or Another Agency)'])],
            'date_service_availed' => ['nullable', 'date', 'before_or_equal:today'],
            'sex' => ['nullable', Rule::in(['Male', 'Female', 'Intersex', 'Prefer not to say'])],
            'age' => ['nullable', 'integer', 'min:1', 'max:120'],
            'center_id' => [
                'nullable',
                'integer',
                Rule::exists('form_options', 'id')->where('category', 'center')->where('is_active', true),
            ],
            'center_code' => [
                'nullable',
                'string',
                Rule::exists('form_options', 'code')->where('category', 'center')->where('is_active', true),
            ],
            'region_id' => [
                'nullable',
                'integer',
                Rule::exists('form_options', 'id')->where('category', 'region')->where('is_active', true),
            ],
            'region_code' => [
                'nullable',
                'string',
                Rule::exists('form_options', 'code')->where('category', 'region')->where('is_active', true),
            ],
            'service_id' => [
                'nullable',
                'integer',
                Rule::exists('services', 'id')->where('is_active', true),
            ],
            'service_code' => [
                'nullable',
                'string',
                Rule::exists('services', 'code')->where('is_active', true),
            ],
            'expires_in_hours' => ['nullable', 'integer', 'min:1', 'max:720'],
        ];
    }
}
