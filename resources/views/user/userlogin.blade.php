<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Login</title>
    <link rel="icon" href="{{ asset('images/MAGALLANES_LOGO.png') }}" type="image/x-icon">
    @vite(['resources/js/app.js'])
     <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div id="app">
        <user-login></user-login> {{-- Your Vue component --}}
    </div>
</body>
</html>