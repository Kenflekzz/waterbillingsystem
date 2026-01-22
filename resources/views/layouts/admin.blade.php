<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'Admin Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SB Admin CSS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="{{ asset('admin/css/styles.css') }}" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <!-- Custom CSS -->
    @vite(['resources/css/admindashboard.css', 'resources/css/adminloader.css'])
</head>

<body class="sb-nav-fixed">

    <!-- Global Water Droplet Loader -->
    <div id="global-loader" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.8);display:flex;justify-content:center;align-items:center;z-index:9999;opacity:0;transition:opacity 0.3s;">
        <div style="width:40px;height:40px;background:#007bff;border-radius:50% 50% 60% 60%;animation:drop 0.8s infinite ease-in-out;"></div>
    </div>

    <style>
    @keyframes drop {
      0% { transform: translateY(-15px) scale(1); opacity:0.9; }
      50% { transform: translateY(0px) scale(0.85); opacity:1; }
      100% { transform: translateY(15px) scale(1); opacity:0.9; }
    }
    </style>

    @include('admin.partials.adminnavbar')

    <div id="layoutSidenav">
        @include('admin.partials.adminsidebar')

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    @yield('content')
                    @yield('scripts')
                </div>
            </main>

            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">© Your Website {{ date('Y') }}</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <a href="#">Terms & Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Custom JS -->
    @vite('resources/js/adminloader.js')
</body>
</html>
