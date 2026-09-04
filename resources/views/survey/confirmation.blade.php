<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Satisfaction Survey - National Dairy Authority</title>
    @vite(['resources/css/app.css'])
</head>
<body class="confirmation-body">
<div class="loading-screen" aria-label="Loading" role="status">
    <div class="loading-bottle-wrap">
        <div class="milk-bottle loading-bottle">
            <img src="{{ asset('nda-logo.png') }}" alt="" class="loading-bottle-logo">
        </div>
        <svg class="milk-bottle-outline" viewBox="0 0 104 230" aria-hidden="true" focusable="false">
            <path d="M40 2h24v23c0 4 4 7 11 11 8 5 13 10 13 19v151c0 10-7 18-16 20H32c-9-2-16-10-16-20V55c0-9 5-14 13-19 7-4 11-7 11-11V2Z" />
        </svg>
        <div class="milk-bottle-cap"></div>
    </div>
</div>
<script>
    (function () {
        const loadingScreen = document.querySelector('.loading-screen');
        const fill = document.querySelector('.loading-milk-fill');
        const percent = document.querySelector('.loading-percent');
        const start = performance.now();
        const duration = 1450;

        function updateLoading(now) {
            const progress = Math.min((now - start) / duration, 1);
            const value = Math.round(progress * 100);
            fill.style.height = value + '%';
            percent.textContent = value + '%';
            if (progress < 1) {
                requestAnimationFrame(updateLoading);
            } else {
                loadingScreen.classList.add('is-spilling');
            }
        }

        requestAnimationFrame(updateLoading);
    }());
</script>
<img class="background-video" src="{{ asset('grass-field-gif.gif') }}" alt="" aria-hidden="true">
<div class="confirmation-container">
    <div class="confirmation-island">
        <h1 class="confirmation-title">Client Satisfaction Survey</h1>
        <p class="confirmation-text">
            Your response has been recorded. Thank you for helping the National Dairy Authority improve its services.
        </p>
        <div class="confirmation-actions">
            <a href="{{ route('survey.create') }}" class="btn btn-primary is-ready">
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