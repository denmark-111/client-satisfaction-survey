<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Satisfaction Survey - National Dairy Authority</title>
    @vite(['resources/css/app.css'])
</head>
<body class="confirmation-body">
<video class="background-video" autoplay muted loop playsinline preload="auto" aria-hidden="true">
    <source src="{{ asset('grass-field.mp4') }}" type="video/mp4">
</video>
<div class="confirmation-container">
    <div class="confirmation-island">
        <h1 class="confirmation-title">Client Satisfaction Survey</h1>
        <p class="confirmation-text">
            Your response has been recorded. Thank you for helping the National Dairy Authority improve its services.
        </p>
        <div class="confirmation-actions">
            <a href="{{ route('survey.create') }}" class="btn btn-primary">
                Submit another response
            </a>
        </div>
        <p class="confirmation-countdown">
            Redirecting to home in <span id="countdown-timer">10</span> seconds...
        </p>
    </div>
</div>

<script>
    let secondsLeft = 10;
    const countdownEl = document.getElementById('countdown-timer');
    const redirectUrl = "{{ route('landing') }}";

    const countdownTimer = setInterval(() => {
        secondsLeft--;
        if (countdownEl) {
            countdownEl.textContent = secondsLeft;
        }
        if (secondsLeft <= 0) {
            clearInterval(countdownTimer);
            window.location.href = redirectUrl;
        }
    }, 1000);
</script>
</body>
</html>