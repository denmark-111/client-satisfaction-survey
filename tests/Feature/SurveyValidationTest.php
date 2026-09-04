<?php

namespace Tests\Feature;

use App\Models\FormOption;
use App\Models\Survey;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveyValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function validSurveyData(array $overrides = []): array
    {
        $center = FormOption::where('category', 'center')->first();
        $region = FormOption::where('category', 'region')->first();
        $service = FormOption::where('category', 'service')->first();

        return array_merge([
            'agreed_to_participate' => '1',
            'respondent_name' => 'John Doe',
            'respondent_contact_number' => '09123456789',
            'center_id' => $center->id,
            'division_office' => 'Main Office',
            'client_type' => 'Citizen',
            'date_service_availed' => Carbon::today()->format('Y-m-d'),
            'sex' => 'Male',
            'age' => 30,
            'region_id' => $region->id,
            'service_id' => $service->id,
            'overall_satisfaction' => 5,
            'remarks' => null,
            'cc1_awareness' => 1,
            'cc2_visibility' => 1,
            'cc3_helpfulness' => 1,
            'sqd0_overall' => 5,
            'sqd1_responsiveness' => 5,
            'sqd2_reliability' => 5,
            'sqd3_access_facilities' => 5,
            'sqd4_communication' => 5,
            'sqd5_costs' => 5,
            'sqd6_integrity' => 5,
            'sqd7_assurance' => 5,
            'sqd8_outcome' => 5,
        ], $overrides);
    }

    public function test_remarks_is_required_when_overall_satisfaction_is_3_or_below(): void
    {
        foreach ([1, 2, 3] as $rating) {
            $data = $this->validSurveyData([
                'overall_satisfaction' => $rating,
                'remarks' => null,
            ]);

            $response = $this->post(route('survey.store'), $data);
            $response->assertSessionHasErrors(['remarks']);
        }
    }

    public function test_remarks_is_valid_when_overall_satisfaction_is_3_or_below_and_remarks_provided(): void
    {
        $data = $this->validSurveyData([
            'overall_satisfaction' => 2,
            'remarks' => 'Could be better organized.',
        ]);

        $response = $this->post(route('survey.store'), $data);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('survey.confirmation'));

        $this->assertDatabaseHas('surveys', [
            'overall_satisfaction' => 2,
            'remarks' => 'Could be better organized.',
        ]);
    }

    public function test_remarks_is_optional_when_overall_satisfaction_is_above_3(): void
    {
        $data = $this->validSurveyData([
            'overall_satisfaction' => 4,
            'remarks' => null,
        ]);

        $response = $this->post(route('survey.store'), $data);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('survey.confirmation'));

        $this->assertDatabaseHas('surveys', [
            'overall_satisfaction' => 4,
            'remarks' => null,
        ]);
    }

    public function test_date_service_availed_rejects_future_dates(): void
    {
        $futureDate = Carbon::tomorrow()->format('Y-m-d');
        $data = $this->validSurveyData([
            'date_service_availed' => $futureDate,
        ]);

        $response = $this->post(route('survey.store'), $data);
        $response->assertSessionHasErrors(['date_service_availed']);
    }

    public function test_date_service_availed_accepts_today_and_past_dates(): void
    {
        $today = Carbon::today()->format('Y-m-d');
        $past = Carbon::yesterday()->format('Y-m-d');

        $dataToday = $this->validSurveyData(['date_service_availed' => $today]);
        $responseToday = $this->post(route('survey.store'), $dataToday);
        $responseToday->assertSessionHasNoErrors();

        $dataPast = $this->validSurveyData(['date_service_availed' => $past]);
        $responsePast = $this->post(route('survey.store'), $dataPast);
        $responsePast->assertSessionHasNoErrors();
    }

    public function test_when_cc1_is_4_cc2_and_cc3_cannot_have_values(): void
    {
        $data = $this->validSurveyData([
            'cc1_awareness' => 4,
            'cc2_visibility' => 1,
            'cc3_helpfulness' => 1,
        ]);

        $response = $this->post(route('survey.store'), $data);
        $response->assertSessionHasErrors(['cc2_visibility', 'cc3_helpfulness']);
    }

    public function test_when_cc1_is_4_and_cc2_cc3_are_omitted_survey_is_saved_with_null_cc2_and_cc3(): void
    {
        $data = $this->validSurveyData([
            'cc1_awareness' => 4,
            'cc2_visibility' => null,
            'cc3_helpfulness' => null,
        ]);

        $response = $this->post(route('survey.store'), $data);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('survey.confirmation'));

        $this->assertDatabaseHas('surveys', [
            'cc1_awareness' => 4,
            'cc2_visibility' => null,
            'cc3_helpfulness' => null,
        ]);
    }
}
