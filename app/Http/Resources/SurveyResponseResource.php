<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyResponseResource extends JsonResource
{
    /**
     * Transform the resource into an array matching the POST /survey-responses schema.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'session_token' => $this->session?->token,
            'client_system' => $this->session?->client_system,
            'external_transaction_id' => $this->session?->external_transaction_id,
            'webhook_url' => $this->session?->webhook_url,
            'agreed_to_participate' => (bool) $this->agreed_to_participate,
            'respondent_name' => $this->respondent_name,
            'respondent_contact_number' => $this->respondent_contact_number,
            'center_id' => $this->center_id !== null ? (int) $this->center_id : null,
            'center_code' => $this->center?->code,
            'division_office' => $this->division_office,
            'client_type' => $this->client_type,
            'date_service_availed' => $this->date_service_availed?->format('Y-m-d'),
            'sex' => $this->sex,
            'age' => $this->age !== null ? (int) $this->age : null,
            'region_id' => $this->region_id !== null ? (int) $this->region_id : null,
            'region_code' => $this->region?->code,
            'service_id' => $this->service_id !== null ? (int) $this->service_id : null,
            'service_code' => $this->service?->code,
            'overall_satisfaction' => $this->overall_satisfaction !== null ? (int) $this->overall_satisfaction : null,
            'remarks' => $this->remarks,
            'cc1_awareness' => $this->cc1_awareness !== null ? (int) $this->cc1_awareness : null,
            'cc2_visibility' => $this->cc2_visibility !== null ? (int) $this->cc2_visibility : null,
            'cc3_helpfulness' => $this->cc3_helpfulness !== null ? (int) $this->cc3_helpfulness : null,
            'sqd0_overall' => $this->sqd0_overall !== null ? (int) $this->sqd0_overall : null,
            'sqd1_responsiveness' => $this->sqd1_responsiveness !== null ? (int) $this->sqd1_responsiveness : null,
            'sqd2_reliability' => $this->sqd2_reliability !== null ? (int) $this->sqd2_reliability : null,
            'sqd3_access_facilities' => $this->sqd3_access_facilities !== null ? (int) $this->sqd3_access_facilities : null,
            'sqd4_communication' => $this->sqd4_communication !== null ? (int) $this->sqd4_communication : null,
            'sqd5_costs' => $this->sqd5_costs !== null ? (int) $this->sqd5_costs : null,
            'sqd6_integrity' => $this->sqd6_integrity !== null ? (int) $this->sqd6_integrity : null,
            'sqd7_assurance' => $this->sqd7_assurance !== null ? (int) $this->sqd7_assurance : null,
            'sqd8_outcome' => $this->sqd8_outcome !== null ? (int) $this->sqd8_outcome : null,
        ];
    }
}
