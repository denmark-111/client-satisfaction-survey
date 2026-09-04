<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>National Dairy Authority - Client Satisfaction Survey</title>
    @vite(['resources/css/app.css'])
</head>
<body class="landing-body">
    <div class="loading-screen" aria-label="Loading" role="status">
        <div class="loading-bottle-wrap">
            <div class="milk-bottle loading-bottle">
                <div class="loading-milk-fill"></div>
                <img src="{{ asset('nda-logo.png') }}" alt="" class="loading-bottle-logo">
                <span class="loading-percent">0%</span>
            </div>
            <svg class="milk-bottle-outline" viewBox="0 0 104 230" aria-hidden="true" focusable="false">
                <path d="M40 2h24v23c0 4 4 7 11 11 8 5 13 10 13 19v151c0 10-7 18-16 20H32c-9-2-16-10-16-20V55c0-9 5-14 13-19 7-4 11-7 11-11V2Z" />
            </svg>
            <div class="milk-bottle-cap"></div>
        </div>
    </div>
    <img class="background-video" src="{{ asset('grass-field-gif.gif') }}" alt="" aria-hidden="true">

    <main class="landing-stage">
        <!-- Top Left: NDA Logo & Title Lockup -->
        <header class="landing-header-lockup">
            <img src="{{ asset('nda-logo.png') }}" alt="National Dairy Authority Logo" class="nda-logo-img">
            <div class="landing-title-block">
                <h1 class="landing-title-client">CLIENT</h1>
                <h2 class="landing-title-survey">SATISFACTION SURVEY</h2>
            </div>
        </header>

        <!-- Center CTA Button -->
        <div class="landing-cta-container">
            <a href="{{ route('survey.create') }}" class="take-survey-pill-btn">
                TAKE A SURVEY
            </a>
        </div>
    </main>
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
</body>
</html>

