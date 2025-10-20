<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
        }
        .navbar-custom {
            background-color: #007bff; /* Primary color */
        }
        .navbar-custom .navbar-brand,
        .navbar-custom .nav-link,
        .navbar-custom .navbar-text {
            color: white !important;
        }
        .sidebar {
            height: 100vh;
            position: fixed;
            width: 250px;
            top: 0;
            left: 0;
            background-color: #f8f9fa;
            padding-top: 56px; /* height of navbar */
            border-right: 1px solid #ddd;
        }
        .sidebar .nav-link {
            color: #333;
            padding: 12px 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s ease;
        }
        .sidebar .nav-link:hover {
            background-color: #e9ecef;
            color: #007bff;
        }
        .sidebar .nav-link.active {
            background-color: #007bff;
            color: white;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            padding-top: 40px; /* was 76px → reduced to bring greeting higher */
        }
        .page-header {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="{{ route('user.dashboard') }}">
                <i class="bi bi-lightning-fill"></i> User Portal
            </a>
            <div class="d-flex">
                <span class="navbar-text me-3">
                    Welcome, {{ Auth::guard('user')->user()->first_name }}
                </span>
                <form action="{{ route('user.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-light btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    {{-- Sidebar --}}
    <div class="sidebar p-3">
        <h5 class="text-center mb-4">User Menu</h5>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="{{ route('user.dashboard') }}" 
                   class="nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
                   <i class="bi bi-house-door"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                   <i class="bi bi-person"></i> Profile
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                   <i class="bi bi-receipt"></i> Billing
                </a>
            </li>
        </ul>
    </div>

    {{-- Content --}}
    <div class="content">
        {{-- Greeting / Title Section --}}
        <div class="page-header">
            <h2 class="fw-bold">Welcome back, {{ Auth::guard('user')->user()->first_name }}!</h2>
            <p class="text-muted">Here’s your dashboard overview.</p>
        </div>

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
