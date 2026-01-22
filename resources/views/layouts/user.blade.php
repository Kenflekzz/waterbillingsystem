<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>User Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    @vite(['resources/css/user.css'])
</head>
<body>
    <!-- 🔵 Global Loader -->
<div id="global-loader">
    <div class="droplet"></div>
</div>

    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="{{ route('user.home') }}">
            <img src="{{ asset('images/MAGALLANES_LOGO.png') }}" alt="Logo" style="width:50px; height:50px;">
            User Portal
            </a>
            <div class="d-flex">
               @php
                    $user = Auth::guard('user')->user();
                    $isNew = session('is_new_user');
                @endphp

                <span class="navbar-text me-3 nav-item-size greeting-text">
                    @if($isNew)
                        Welcome, {{ $user->first_name }}!
                    @else
                        Welcome back, {{ $user->first_name }}!
                    @endif
                </span>
                <!-- Notification Bell -->
                    <div class="dropdown me-3">
                        <button class="btn btn-light btn-sm nav-item-size position-relative" id="notifBellBtn" data-bs-toggle="dropdown">
                            <i class="bi bi-bell-fill fs-5"></i>

                            <!-- Notification Count -->
                            <span id="notifCount" 
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                0
                            </span>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end p-2" style="width: 300px;" id="notifList">
                            <li class="text-center text-muted small">Loading...</li>
                        </ul>
                    </div>

                    <!-- Report Button -->
                    <button class="btn btn-danger btn-sm nav-item-size"
                            data-bs-toggle="modal" 
                            data-bs-target="#reportModal">
                        <i class="bi bi-exclamation-circle"></i> Report Problem
                    </button>

                    <!-- My Reports Button -->
                    <button class="btn btn-info btn-sm nav-item-size"
                            id="btnMyReports" 
                            data-reports-url="{{ route('user.reports.list') }}">
                        <i class="bi bi-chat-left-text"></i> My Reports
                    </button>
            </div>
        </div>
    </nav>

    <div class="sidebar p-3">

    <!-- Profile Card -->
    <div class="sidebar-profile card shadow-sm border-0 p-3 text-center mb-4">
        <!-- Profile Picture -->
        <img src="{{ asset('storage/' . $user->profile_image) }}" 
             class="rounded-circle mb-2 mx-auto d-block"
             width="70" height="70"
             style="object-fit: cover;">

        <!-- Name -->
      <h6 class="fw-bold mb-0">{{ $user->first_name }} {{ $user->last_name }}</h6>

        <!-- Role -->
        <small class="text-muted d-block">User</small>

        <!-- Meter Number -->
        <small class="text-primary d-block mt-1">
            Meter No: <strong>{{ $user->meter_number }}</strong>
        </small>
    </div>

    <!-- Menu Title -->
    <h6 class="text-uppercase text-muted fw-bold small mb-3 ps-2">User Menu</h6>

    <!-- Navigation Items -->
    <ul class="nav flex-column">

        <li class="nav-item mb-1">
            <a href="{{ route('user.home') }}"
                class="nav-link {{ request()->routeIs('user.home') ? 'active' : '' }}">
                <i class="bi bi-house"></i>
                Home
            </a>
        </li>

        <li class="nav-item mb-1">
            <a href="{{ route('user.consumption') }}" 
                class="nav-link {{ request()->routeIs('user.consumption') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                Consumption
            </a>
        </li>

        <li class="nav-item mb-1">
            <a href="{{ route('user.profile') }}" 
                class="nav-link {{ request()->routeIs('user.profile') ? 'active' : '' }}">
                <i class="bi bi-person"></i>
                Profile
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('user.billing') }}" 
                class="nav-link {{ request()->routeIs('user.billing') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i>
                Billing
            </a>
        </li>

    </ul>
    <!-- Logout Button -->
<div class="mt-auto pt-3">
    <form id="user-logout-form" action="{{ route('user.logout') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-danger w-100 d-flex justify-content-center align-items-center">
            <i class="bi bi-box-arrow-right me-2"></i> Logout
        </button>
    </form>
</div>

</div>
{{-- REPORT MODAL --}}
<div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <h5 class="mb-3">Report a Problem / Request</h5>

            <form action="{{ route('user.report.problem') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="report_subject" class="form-label">Subject</label>
                    <input type="text" name="subject" id="report_subject" class="form-control" required placeholder="Short title (e.g. Wrong reading)">
                </div>

                <div class="mb-3">
                    <label for="report_message" class="form-label">Message</label>
                    <textarea name="message" id="report_message" rows="5" class="form-control" required placeholder="Describe your issue or request..."></textarea>
                </div>

                <div class="mb-3">
                    <label for="report_image" class="form-label">Attach Image (optional)</label>
                    <input type="file" name="image" id="report_image" class="form-control" accept="image/*">
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger w-100">Send Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- My Reports Modal -->
<div class="modal fade" id="myReportsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content p-4">
            <h5 class="mb-3">My Submitted Reports</h5>
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Date Submitted</th>
                        <th>View</th>
                    </tr>
                </thead>
                <tbody id="myReportsTableBody">
                    <tr>
                        <td colspan="4" class="text-center text-muted">Loading your reports...</td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <nav>
                <ul class="pagination justify-content-center mt-2" id="reportsPagination"></ul>
            </nav>
        </div>
    </div>
</div>

<!-- Report Details Modal -->
<div class="modal fade" id="reportDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content p-4">
            <h5 class="mb-3" id="detailsSubject"></h5>
            <p><strong>Status:</strong> <span id="detailsStatus"></span></p>
            <p><strong>Date Submitted:</strong> <span id="detailsDate"></span></p>
            <p><strong>Description:</strong></p>
            <p id="detailsDescription"></p>
            <img id="detailsImage" src="" alt="" class="img-fluid d-none mb-3" />
            <div class="text-end">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<!-- Pagination container -->
<nav>
    <ul class="pagination justify-content-center mt-2" id="reportsPagination">
        <!-- JS will populate this -->
    </ul>
</nav>

    {{-- Content --}}
    <div class="content">
        @yield('content')
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@vite(['resources/js/user.js', 'resources/js/usernotification.js'])
</body>
</html>
