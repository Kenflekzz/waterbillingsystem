<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Water Billing System</title>
    <link rel="icon" href="{{ asset('images/MAGALLANES_LOGO.png') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    @vite('resources/js/app.js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<style>
    html, body, #app {
        height: 100%;
        margin: 0;
    }
</style>
<body>
    <div id="app"></div>
</body>
</html>
