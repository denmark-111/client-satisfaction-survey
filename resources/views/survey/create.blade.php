<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>National Dairy Authority - Client Satisfaction Survey</title>
    @vite(['resources/css/app.css'])
</head>
<body>
<video class="background-video" autoplay muted loop playsinline preload="auto" aria-hidden="true">
    <source src="{{ asset('grass-field.mp4') }}" type="video/mp4">
</video>
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
        <div class="form-step satisfaction-step" data-step="5">
            <div class="satisfaction-shell">
                <div class="satisfaction-header">Overall Satisfaction to the Services of NDA</div>
                <div class="satisfaction-intro">
                    <p>Please rate your overall satisfaction for the services provided by NDA in 2024.</p>
                    <p>Please use this rating scale below from 1 to 10, where a rating of "1" or close to 1 means that you are Very Dissatisfied with the service provision of NDA and a rating of "10" or close to 10 means that you are Very Satisfied.</p>
                </div>

                <div class="satisfaction-grid">
                    <div class="satisfaction-panel rating-panel">
                        <div class="panel-title">OVERALL SATISFACTION</div>
                        <div class="rating-box">
                            @php($satisfactionRating = (int) old('overall_satisfaction', 5))
                            <div class="satisfaction-meter" data-satisfaction-meter>
                                <div class="meter-scale">
                                    <span class="scale-caption top">VERY<br>SATISFIED</span>
                                    <div class="scale-track">
                                        <div class="slider-wrap">
                                            <input class="satisfaction-range" type="range" min="1" max="10" step="1" value="{{ $satisfactionRating }}" aria-label="Drag to choose satisfaction rating">
                                        </div>
                                        <div class="scale-ticks" aria-label="Satisfaction rating choices">
                                            @for($i = 10; $i >= 1; $i--)
                                                <label class="scale-tick {{ $satisfactionRating === $i ? 'active' : '' }}">
                                                    <input type="radio" name="overall_satisfaction" value="{{ $i }}" {{ $satisfactionRating === $i ? 'checked' : '' }} required>
                                                    <span>{{ $i }}</span>
                                                </label>
                                            @endfor
                                        </div>
                                    </div>
                                    <span class="scale-caption bottom">VERY<br>DISSATISFIED</span>
                                </div>

                                <div class="milk-bottle-wrap" aria-live="polite">
                                    <div class="milk-bottle">
                                        <div class="milk-fill" style="height: {{ $satisfactionRating * 10 }}%;"></div>
                                    </div>
                                    <svg class="milk-bottle-outline" viewBox="0 0 104 230" aria-hidden="true" focusable="false">
                                        <path d="M40 2h24v23c0 4 4 7 11 11 8 5 13 10 13 19v151c0 10-7 18-16 20H32c-9-2-16-10-16-20V55c0-9 5-14 13-19 7-4 11-7 11-11V2Z" />
                                    </svg>
                                    <div class="milk-bottle-label"><span data-rating-value>{{ $satisfactionRating }}</span>/10</div>
                                    <div class="milk-bottle-cap"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="satisfaction-panel remarks-panel">
                        <div class="panel-title">REMARKS</div>
                        <textarea name="remarks" rows="6" placeholder="Your answer">{{ old('remarks') }}</textarea>
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
                                @foreach([
                                    'sqd0_overall' => 'SQD0. I am satisfied with the service I availed.',
                                    'sqd1_responsiveness' => 'SQD1. I spent an acceptable amount of time to complete my transaction (Responsiveness)',
                                    'sqd2_reliability' => 'SQD2. The office accurately informed and followed the transaction\'s requirements and steps (Reliability)',
                                    'sqd3_access_facilities' => 'SQD3. My online transaction (including steps and payment) was simple and convenient (Access and Facilities)',
                                    'sqd4_communication' => 'SQD4. I easily found information about my transaction from the office or its website (Communication)',
                                    'sqd5_costs' => 'SQD5. I paid an acceptable amount of fees for my transaction (Costs)',
                                    'sqd6_integrity' => 'SQD6. I am confident my online transaction was secure (Integrity)',
                                    'sqd7_assurance' => 'SQD7. The office\'s online support was available, or (if asked questions) online support was quick to respond (Assurance)',
                                    'sqd8_outcome' => 'SQD8. I got what I needed from the government office (Outcome)'
                                ] as $field => $text)
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
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </form>
</div>

<script>
    let currentStep = 1;
    const totalSteps = 7;

    document.querySelectorAll('[data-satisfaction-meter]').forEach(meter => {
        const range = meter.querySelector('.satisfaction-range');
        const radios = meter.querySelectorAll('input[name="overall_satisfaction"]');
        const fill = meter.querySelector('.milk-fill');
        const valueLabel = meter.querySelector('[data-rating-value]');
        const ticks = meter.querySelectorAll('.scale-tick');

        function updateSatisfactionMeter(value) {
            const rating = Number(value);
            range.value = rating;
            fill.style.height = `${rating * 10}%`;
            valueLabel.textContent = rating;

            radios.forEach(radio => {
                radio.checked = Number(radio.value) === rating;
                radio.closest('.scale-tick').classList.toggle('active', radio.checked);
            });
        }

        range.addEventListener('input', event => updateSatisfactionMeter(event.target.value));
        radios.forEach(radio => radio.addEventListener('change', event => updateSatisfactionMeter(event.target.value)));
        updateSatisfactionMeter(range.value);
    });

    function showStep(step) {
        document.querySelectorAll('.form-step').forEach(el => el.classList.remove('active'));
        const target = document.querySelector(`.form-step[data-step="${step}"]`);
        if (target) {
            target.classList.add('active');

            const container = document.querySelector('.container');
            if (container) {
                const targetTop = target.offsetTop;
                container.scrollTo({
                    top: targetTop - 20,
                    behavior: 'smooth'
                });
            }
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