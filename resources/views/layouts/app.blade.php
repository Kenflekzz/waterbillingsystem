<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Billing System</title>

    <!-- Global CSS first -->
    <link rel="stylesheet" href="{{ asset('admin/css/app.css') }}">
    @livewireStyles
    @if(app()->environment('local'))
    {{-- Vite dev server --}}
    <script type="module" src="http://{{ env('VITE_HOST') }}:{{ env('VITE_PORT') }}/@vite/client"></script>
    <link rel="stylesheet" href="http://{{ env('VITE_HOST') }}:{{ env('VITE_PORT') }}/resources/css/app.css">
    <script type="module" src="http://{{ env('VITE_HOST') }}:{{ env('VITE_PORT') }}/resources/js/app.js"></script>
@else
    {{-- Production / built assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endif

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
