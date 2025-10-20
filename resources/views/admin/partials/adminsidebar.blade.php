<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <!-- Logo -->
                <div class="text-center py-3">
                    <img src="{{ asset('images/water-surface.webp') }}" alt="Logo"
                         style="width: 225px; height: 75px; margin-top: -20px"/>
                </div>

                <!-- Dashboard -->
                <a class="nav-link {{ Request::is('admin/dashboard') ? 'active' : '' }}" 
                   href="{{ url('admin/dashboard') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>

                <!-- Payment -->
                <a class="nav-link {{ Request::is('admin/payments*') ? 'active' : '' }}" 
                   href="{{ url('admin/payments') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-credit-card"></i></div>
                    Payment
                </a>

                <!-- Billing -->
                <a class="nav-link {{ Request::is('admin/billings*') ? 'active' : '' }}" 
                   href="{{ url('admin/billings') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                    Billing
                </a>

                <!-- Clients -->
                <a class="nav-link {{ Request::is('admin/clients*') ? 'active' : '' }}" 
                   href="{{ url('admin/clients') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                    Clients
                </a>

                <!-- Reports -->
                <a class="nav-link {{ Request::is('admin/reports*') ? 'active' : '' }}" 
                   href="{{ url('admin/reports') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-list"></i></div>
                    Reports
                </a>

                <!-- Admins -->
                <a class="nav-link {{ Request::is('admin/admins*') ? 'active' : '' }}" 
                   href="{{ url('admin/admins') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-user-cog"></i></div>
                    Admin/s
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="sb-sidenav-footer">
            <div class="small">Logged in as:</div>
            Administrator
        </div>
    </nav>
</div>
@vite(['resources/css/adminsidebar.css'])
