<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Satisfaction Survey - National Dairy Authority</title>
    @vite(['resources/css/app.css'])
</head>
<body class="confirmation-body">
<img class="background-video" src="{{ asset('grass-field-gif.gif') }}" alt="" aria-hidden="true">
<div class="confirmation-container">
    <div class="confirmation-island">
        <h1 class="confirmation-title">Client Satisfaction Survey</h1>
        <p class="confirmation-text">
            {{ $sessionError ?? 'The survey link is invalid or does not exist.' }}
        </p>
        <div class="confirmation-actions">
            <a href="{{ route('landing') }}" class="btn btn-primary is-ready">
                Return to Home
            </a>
        </div>
    </div>
</div>
</body>
</html>
