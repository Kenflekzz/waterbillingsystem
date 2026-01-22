<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Water Billing System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('images/MAGALLANES_LOGO.png') }}" type="image/x-icon">
    @vite(['resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        html, body, #app {
            height: 100%;
            margin: 0;
        }

        /* 🔵 Global Water Droplet Loader */
        #global-loader {
            display: none; /* initially hidden */
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(255,255,255,0.8);
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
        }

        #global-loader.show {
            display: flex;
            pointer-events: all;
        }

        #global-loader .droplet {
            width: 40px;
            height: 40px;
            background: #007bff;
            border-radius: 50% 50% 60% 60%;
            animation: drop 0.8s infinite ease-in-out;
        }

        @keyframes drop {
            0% { transform: translateY(-15px) scale(1); opacity:0.9; }
            50% { transform: translateY(0px) scale(0.85); opacity:1; }
            100% { transform: translateY(15px) scale(1); opacity:0.9; }
        }
    </style>
</head>
<body>

    <!-- 🔵 Global Loader -->
    <div id="global-loader">
        <div class="droplet"></div>
    </div>

    <!-- Vue Mount Point -->
    <div id="app"></div>

    <!-- Loader control script -->
    <script>
        const loader = document.getElementById('global-loader');

        window.showLoader = () => {
            if (!loader) return;
            loader.classList.add('show');
            requestAnimationFrame(() => loader.style.opacity = '1');
        };

        window.hideLoader = () => {
            if (!loader) return;
            loader.style.opacity = '0';
            setTimeout(() => loader.classList.remove('show'), 300);
        };
    </script>

</body>
</html>
