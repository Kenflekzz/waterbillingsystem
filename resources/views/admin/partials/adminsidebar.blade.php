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
                <a class="nav-link" href="{{ url('admin/dashboard') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>

                <!-- Payment -->
                <a class="nav-link" href="{{ url('admin/payments') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-credit-card"></i></div>
                    Payment
                </a>

                <!-- Billing -->
                <a class="nav-link" href="{{ url('admin/billings') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                    Billing
                </a>

                <!-- Clients -->
                <a class="nav-link" href="{{ url('admin/clients') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                    Clients
                </a>

                <a class="nav-link" href="{{ url('admin/reports') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                    Reports
                </a>

                <!--Admins -->
                <a class="nav-link" href="{{ url('admin/admins') }}">
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
<style>
    .sb-sidenav .nav-link {
    font-size: 18px;
    padding: 12px 20px;
}

.sb-sidenav .sb-nav-link-icon i {
    font-size: 1.5rem;
}

.sb-sidenav-menu {
        display: flex;
        flex-direction: column;
        height: 100%;
}

.sb-sidenav-menu .nav {
        flex-grow: 1;
}

    /* Optional: Slight gap between items */
.sb-sidenav .nav-link + .nav-link {
        margin-top: 20px;
}
</style>