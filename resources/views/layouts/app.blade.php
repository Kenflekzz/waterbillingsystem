<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Billing System</title>

    <!-- Global CSS -->
    <link rel="stylesheet" href="{{ asset('admin/css/app.css') }}">
    @livewireStyles
</head>
<body>

    
    <!-- Global loader -->
<div id="loader" class="loader hide">
    <!-- Custom animation -->
    <img src="{{ asset('admin/images/Faucet.gif') }}" alt="Loading..." style="width:100px; height:100px;">
</div>


    <div id="main-content">
        @yield('content')
    </div>

    @livewireScripts
    <script src="{{ asset('admin/js/loader.js') }}"></script>
</body>
</html>
