<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>National Dairy Authority - Client Satisfaction Survey</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; }
        body { background-color: #f0f4f1; color: #202124; padding: 20px 10px; }
        .container { max-width: 680px; margin: 0 auto; }
        .card { background: #fff; border-radius: 8px; border: 1px solid #dadce0; margin-bottom: 16px; overflow: hidden; }
        .card-header-banner { background-color: #1b5e20; height: 10px; }
        .card-content { padding: 22px; }
        .agency-header { text-align: center; margin-bottom: 12px; }
        .agency-title { font-size: 18px; font-weight: 700; color: #1b5e20; text-transform: uppercase; }
        .agency-subtitle { font-size: 13px; color: #5f6368; margin-bottom: 8px; }
        .survey-title { font-size: 24px; font-weight: 600; margin-bottom: 8px; }
        .survey-desc { font-size: 13px; color: #444; line-height: 1.45; }
        .section-banner { background-color: #1b5e20; color: #fff; font-weight: 600; font-size: 16px; padding: 12px 20px; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 14px; font-weight: 500; margin-bottom: 8px; }
        .required { color: #d93025; }
        .hint { font-size: 12px; color: #5f6368; margin-top: 4px; }
        input[type="text"], input[type="number"], input[type="date"], select, textarea {
            width: 100%; padding: 10px 12px; border: 1px solid #dadce0; border-radius: 4px; font-size: 14px; outline: none; background: #fff;
        }
        input:focus, select:focus, textarea:focus { border-color: #1b5e20; box-shadow: 0 0 0 1px #1b5e20; }
        .radio-group, .checkbox-group { display: flex; flex-direction: column; gap: 10px; margin-top: 8px; }
        .radio-option, .checkbox-option { display: flex; align-items: center; gap: 10px; font-size: 14px; cursor: pointer; }
        .rating-scale { display: flex; justify-content: space-between; align-items: center; margin: 16px 0 8px; }
        .rating-item { display: flex; flex-direction: column; align-items: center; gap: 6px; font-size: 13px; }
        .scale-labels { display: flex; justify-content: space-between; font-size: 12px; color: #5f6368; margin-top: 4px; }
        .table-responsive { overflow-x: auto; margin-top: 12px; }
        table.sqd-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        table.sqd-table th, table.sqd-table td { padding: 10px 8px; text-align: center; border-bottom: 1px solid #e0e0e0; }
        table.sqd-table th:first-child, table.sqd-table td:first-child { text-align: left; width: 45%; }
        table.sqd-table th { background-color: #fafafa; font-weight: 600; color: #444; font-size: 12px; }
        .form-step { display: none; }
        .form-step.active { display: block; }
        .btn-row { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; }
        .btn { padding: 10px 24px; border-radius: 4px; font-size: 14px; font-weight: 500; cursor: pointer; border: none; }
        .btn-primary { background-color: #1b5e20; color: #fff; }
        .btn-secondary { background-color: #fff; color: #1b5e20; border: 1px solid #dadce0; }
        .btn:hover { opacity: 0.9; }
        .error-msg { color: #d93025; font-size: 12px; margin-top: 4px; }
        .errors-summary { background-color: #fdecea; color: #b71c1c; border-radius: 4px; padding: 12px 16px; margin-bottom: 16px; font-size: 14px; }
    </style>
</head>
<body>
<div class="container">

    @if ($errors->any())
        <div class="errors-summary">
            <strong>Please check the form for errors:</strong>
            <ul style="margin-left: 18px; margin-top: 6px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="surveyForm" action="{{ route('survey.store') }}" method="POST">
        @csrf

        <!-- STEP 1: Consent -->
        <div class="form-step active" data-step="1">
            <div class="card">
                <div class="card-header-banner"></div>
                <div class="card-content">
                    <div class="agency-header">
                        <div class="agency-title">National Dairy Authority</div>
                        <div class="agency-subtitle">Help us serve you better!</div>
                    </div>
                    <div class="survey-title">Client Satisfaction Survey</div>
                    <p class="survey-desc">
                        The Client Satisfaction Measurement (CSM) tracks the customer experience of government offices.
                        Your feedback on your recent concluded transaction will help this office provide a better service.
                        Personal information shared will be kept confidential.
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-content">
                    <label class="form-label">Do you agree to participate as a respondent in this survey? <span class="required">*</span></label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="agreed_to_participate" value="1" {{ old('agreed_to_participate', 1) ? 'checked' : '' }} required>
                        I agree to participate in this survey
                    </label>
                </div>
            </div>

            <div class="btn-row" style="justify-content: flex-end;">
                <button type="button" class="btn btn-primary" onclick="nextStep(2)">Next</button>
            </div>
        </div>

        <!-- STEP 2: Respondent Personal Info -->
        <div class="form-step" data-step="2">
            <div class="card">
                <div class="section-banner">Respondent Personal Info</div>
                <div class="card-content">
                    <p class="hint" style="margin-bottom: 16px;">You have an option to keep your identity hidden as it is <strong>not required</strong> to answer this portion.</p>
                    
                    <div class="form-group">
                        <label class="form-label">Respondent Name</label>
                        <input type="text" name="respondent_name" value="{{ old('respondent_name') }}" placeholder="Your answer">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Respondent Contact Number</label>
                        <input type="text" name="respondent_contact_number" value="{{ old('respondent_contact_number') }}" placeholder="Your answer">
                    </div>
                </div>
            </div>

            <div class="btn-row">
                <button type="button" class="btn btn-secondary" onclick="prevStep(1)">Back</button>
                <button type="button" class="btn btn-primary" onclick="nextStep(3)">Next</button>
            </div>
        </div>

        <!-- STEP 3: Respondent Details -->
        <div class="form-step" data-step="3">
            <div class="card">
                <div class="section-banner">Respondent Details</div>
                <div class="card-content">
                    <div class="form-group">
                        <label class="form-label">DAIRY FARMERS LIVELIHOOD CENTER <span class="required">*</span></label>
                        <select name="center_id" required>
                            <option value="">Choose</option>
                            @foreach($centers as $center)
                                <option value="{{ $center->id }}" {{ old('center_id') == $center->id ? 'selected' : '' }}>{{ $center->label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Division/Office <span class="required">*</span></label>
                        <input type="text" name="division_office" value="{{ old('division_office') }}" placeholder="Your answer" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Client Type <span class="required">*</span></label>
                        <div class="radio-group">
                            @foreach(['Citizen', 'Business', 'Government(Employee or Another Agency)'] as $type)
                                <label class="radio-option">
                                    <input type="radio" name="client_type" value="{{ $type }}" {{ old('client_type') == $type ? 'checked' : '' }} required>
                                    {{ $type }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Date Service Availed <span class="required">*</span></label>
                        <input type="date" name="date_service_availed" value="{{ old('date_service_availed') }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Sex <span class="required">*</span></label>
                        <div class="radio-group">
                            @foreach(['Male', 'Female', 'Intersex', 'Prefer not to say'] as $sex)
                                <label class="radio-option">
                                    <input type="radio" name="sex" value="{{ $sex }}" {{ old('sex') == $sex ? 'checked' : '' }} required>
                                    {{ $sex }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Age <span class="required">*</span></label>
                        <input type="number" name="age" min="1" max="120" value="{{ old('age') }}" placeholder="Your answer" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Region of Residence <span class="required">*</span></label>
                        <select name="region_id" required>
                            <option value="">Choose</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}" {{ old('region_id') == $region->id ? 'selected' : '' }}>{{ $region->label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="btn-row">
                <button type="button" class="btn btn-secondary" onclick="prevStep(2)">Back</button>
                <button type="button" class="btn btn-primary" onclick="nextStep(4)">Next</button>
            </div>
        </div>

        <!-- STEP 4: Available Services -->
        <div class="form-step" data-step="4">
            <div class="card">
                <div class="section-banner">Available Services</div>
                <div class="card-content">
                    <p class="hint" style="margin-bottom: 16px;">Please select service availed.</p>
                    <div class="form-group">
                        <label class="form-label">Service Availed <span class="required">*</span></label>
                        <select name="service_id" required>
                            <option value="">Choose</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>{{ $service->label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="btn-row">
                <button type="button" class="btn btn-secondary" onclick="prevStep(3)">Back</button>
                <button type="button" class="btn btn-primary" onclick="nextStep(5)">Next</button>
            </div>
        </div>

        <!-- STEP 5: Overall Satisfaction -->
        <div class="form-step" data-step="5">
            <div class="card">
                <div class="section-banner">Overall Satisfaction to the Services of NDA</div>
                <div class="card-content">
                    <p class="hint" style="margin-bottom: 14px;">
                        Please rate your overall satisfaction from 1 to 10, where "1" means Very Dissatisfied and "10" means Very Satisfied.
                    </p>

                    <label class="form-label">Overall Satisfaction <span class="required">*</span></label>
                    <div class="rating-scale">
                        @for($i = 1; $i <= 10; $i++)
                            <label class="rating-item">
                                <span>{{ $i }}</span>
                                <input type="radio" name="overall_satisfaction" value="{{ $i }}" {{ old('overall_satisfaction') == $i ? 'checked' : '' }} required>
                            </label>
                        @endfor
                    </div>
                    <div class="scale-labels">
                        <span>Very Dissatisfied</span>
                        <span>Very Satisfied</span>
                    </div>

                    <div class="form-group" style="margin-top: 24px;">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" rows="3" placeholder="Your answer">{{ old('remarks') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="btn-row">
                <button type="button" class="btn btn-secondary" onclick="prevStep(4)">Back</button>
                <button type="button" class="btn btn-primary" onclick="nextStep(6)">Next</button>
            </div>
        </div>

        <!-- STEP 6: Citizen's Charter -->
        <div class="form-step" data-step="6">
            <div class="card">
                <div class="section-banner">NDA Citizen's Charter Survey</div>
                <div class="card-content">
                    <div class="form-group">
                        <label class="form-label">CC1. Which of the following best describes your awareness of a CC? <span class="required">*</span></label>
                        <div class="radio-group">
                            <label class="radio-option"><input type="radio" name="cc1_awareness" value="1" {{ old('cc1_awareness') == 1 ? 'checked' : '' }} required> 1. I know what a CC is and I saw this office's CC</label>
                            <label class="radio-option"><input type="radio" name="cc1_awareness" value="2" {{ old('cc1_awareness') == 2 ? 'checked' : '' }}> 2. I know what a CC is but I did not see this office's CC.</label>
                            <label class="radio-option"><input type="radio" name="cc1_awareness" value="3" {{ old('cc1_awareness') == 3 ? 'checked' : '' }}> 3. I learned of the CC only when I saw this office's CC.</label>
                            <label class="radio-option"><input type="radio" name="cc1_awareness" value="4" {{ old('cc1_awareness') == 4 ? 'checked' : '' }}> 4. I do not know what a CC is and I did not see one in this office.</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">CC2. If aware of CC (answered 1-3 in CC1), would you say that the CC of this office was...?</label>
                        <div class="radio-group">
                            <label class="radio-option"><input type="radio" name="cc2_visibility" value="1" {{ old('cc2_visibility') == 1 ? 'checked' : '' }}> 1. Easy to see</label>
                            <label class="radio-option"><input type="radio" name="cc2_visibility" value="2" {{ old('cc2_visibility') == 2 ? 'checked' : '' }}> 2. Somewhat easy to see</label>
                            <label class="radio-option"><input type="radio" name="cc2_visibility" value="3" {{ old('cc2_visibility') == 3 ? 'checked' : '' }}> 3. Difficult to see</label>
                            <label class="radio-option"><input type="radio" name="cc2_visibility" value="4" {{ old('cc2_visibility') == 4 ? 'checked' : '' }}> 4. Not visible at all</label>
                            <label class="radio-option"><input type="radio" name="cc2_visibility" value="5" {{ old('cc2_visibility') == 5 ? 'checked' : '' }}> 5. N/A</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">CC3. If aware of CC (answered 1-3 in CC1), how much did the CC help you in your transaction?</label>
                        <div class="radio-group">
                            <label class="radio-option"><input type="radio" name="cc3_helpfulness" value="1" {{ old('cc3_helpfulness') == 1 ? 'checked' : '' }}> 1. Helped me very much</label>
                            <label class="radio-option"><input type="radio" name="cc3_helpfulness" value="2" {{ old('cc3_helpfulness') == 2 ? 'checked' : '' }}> 2. Somewhat helpful</label>
                            <label class="radio-option"><input type="radio" name="cc3_helpfulness" value="3" {{ old('cc3_helpfulness') == 3 ? 'checked' : '' }}> 3. Did not help</label>
                            <label class="radio-option"><input type="radio" name="cc3_helpfulness" value="4" {{ old('cc3_helpfulness') == 4 ? 'checked' : '' }}> 4. N/A</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="btn-row">
                <button type="button" class="btn btn-secondary" onclick="prevStep(5)">Back</button>
                <button type="button" class="btn btn-primary" onclick="nextStep(7)">Next</button>
            </div>
        </div>

        <!-- STEP 7: Service Quality Dimensions (SQD) -->
        <div class="form-step" data-step="7">
            <div class="card">
                <div class="section-banner">Satisfaction Survey to the Service Availed</div>
                <div class="card-content">
                    <p class="hint" style="margin-bottom: 12px;">Please rate each question from 1 to 5, or select Not Applicable.</p>

                    <div class="table-responsive">
                        <table class="sqd-table">
                            <thead>
                                <tr>
                                    <th>Question</th>
                                    <th>Strongly Agree (5)</th>
                                    <th>Agree (4)</th>
                                    <th>Neither Agree nor Disagree (3)</th>
                                    <th>Disagree (2)</th>
                                    <th>Strongly Disagree (1)</th>
                                    <th>Not Applicable (0)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $sqds = [
                                        'sqd0_overall' => 'SQD0. I am satisfied with the service I availed.',
                                        'sqd1_responsiveness' => 'SQD1. I spent an acceptable amount of time to complete my transaction (Responsiveness)',
                                        'sqd2_reliability' => 'SQD2. The office accurately informed and followed the transaction\'s requirements and steps (Reliability)',
                                        'sqd3_access_facilities' => 'SQD3. My online transaction (including steps and payment) was simple and convenient (Access and Facilities)',
                                        'sqd4_communication' => 'SQD4. I easily found information about my transaction from the office or its website (Communication)',
                                        'sqd5_costs' => 'SQD5. I paid an acceptable amount of fees for my transaction (Costs)',
                                        'sqd6_integrity' => 'SQD6. I am confident my online transaction was secure (Integrity)',
                                        'sqd7_assurance' => 'SQD7. The office\'s online support was available, or (if asked questions) online support was quick to respond (Assurance)',
                                        'sqd8_outcome' => 'SQD8. I got what I needed from the government office (Outcome)'
                                    ];
                                @endphp

                                @foreach($sqds as $field => $text)
                                    <tr>
                                        <td>{{ $text }} <span class="required">*</span></td>
                                        @foreach([5, 4, 3, 2, 1, 0] as $rating)
                                            <td>
                                                <input type="radio" name="{{ $field }}" value="{{ $rating }}" {{ old($field) == (string)$rating ? 'checked' : '' }} required>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="btn-row">
                <button type="button" class="btn btn-secondary" onclick="prevStep(6)">Back</button>
                <button type="submit" class="btn btn-primary" style="background-color: #0b8043;">Submit</button>
            </div>
        </div>
    </form>
</div>

<script>
    let currentStep = 1;
    const totalSteps = 7;

    function showStep(step) {
        document.querySelectorAll('.form-step').forEach(el => el.classList.remove('active'));
        const target = document.querySelector(`.form-step[data-step="${step}"]`);
        if (target) {
            target.classList.add('active');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    function nextStep(step) {
        const activeContainer = document.querySelector(`.form-step[data-step="${currentStep}"]`);
        const requiredInputs = activeContainer.querySelectorAll('[required]');
        
        for (let input of requiredInputs) {
            if (input.type === 'radio') {
                const checked = activeContainer.querySelector(`input[name="${input.name}"]:checked`);
                if (!checked) {
                    alert('Please answer all required questions before proceeding.');
                    input.focus();
                    return;
                }
            } else if (!input.checkValidity()) {
                input.reportValidity();
                return;
            }
        }

        currentStep = step;
        showStep(currentStep);
    }

    function prevStep(step) {
        currentStep = step;
        showStep(currentStep);
    }
</script>
</body>
</html>