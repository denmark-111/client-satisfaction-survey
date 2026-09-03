<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>National Dairy Authority - Client Satisfaction Survey</title>
    @vite(['resources/css/app.css'])
</head>
<body class="landing-body">
    <!-- Background Video -->
    <video class="background-video" autoplay muted loop playsinline preload="auto" aria-hidden="true">
        <source src="{{ asset('grass-field.mp4') }}" type="video/mp4">
    </video>

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
</body>
</html>

