<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Response Recorded - Client Satisfaction Survey</title>
    @vite(['resources/css/app.css'])
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; }
        body { background-color: #f0f4f1; color: #202124; padding: 40px 10px; }
        .container { max-width: 680px; margin: 0 auto; }
        .card { background: #fff; border-radius: 8px; border: 1px solid #dadce0; overflow: hidden; }
        .card-header-banner { background-color: #1b5e20; height: 10px; }
        .card-content { padding: 32px 24px; }
        h1 { font-size: 24px; font-weight: 600; margin-bottom: 12px; color: #202124; }
        p { font-size: 14px; color: #5f6368; line-height: 1.5; margin-bottom: 24px; }
        a.btn {
            display: inline-block;
            text-decoration: none;
            color: #1b5e20;
            font-size: 14px;
            font-weight: 500;
        }
        a.btn:hover { text-decoration: underline; }
    </style>
</head>
<body>
<video class="background-video" autoplay muted loop playsinline preload="auto" aria-hidden="true">
    <source src="{{ asset('grass-field.mp4') }}" type="video/mp4">
</video>
<div class="container">
    <div class="card">
        <div class="card-header-banner"></div>
        <div class="card-content">
            <h1>Client Satisfaction Survey</h1>
            <p>Your response has been recorded. Thank you for helping the National Dairy Authority improve its services.</p>
            <a href="{{ route('survey.create') }}" class="btn">Submit another response</a>
        </div>
    </div>
</div>
</body>
</html>