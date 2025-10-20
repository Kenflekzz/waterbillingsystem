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

    <!-- 🌐 Global Loader -->
   <div id="global-loader">
    <div class="loader">
        <!-- Circular Progress SVG -->
        <svg width="100" height="100">
            <circle cx="50" cy="50" r="45"></circle>
        </svg>
        <!-- Logo inside -->
        <img src="{{ asset('images/MAGALLANES_LOGO.png') }}" alt="Logo">
    </div>
</div>



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
