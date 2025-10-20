<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <!-- Navbar Brand-->
   <!-- Navbar Brand-->
<a class="navbar-brand ps-3 d-flex align-items-center" >
    <img src="{{ asset('images/MAGALLANES_LOGO.png') }}" 
         alt="Logo" 
         style="height: 35px; width: auto; margin-right: 10px;">
    <span>Magallanes Water Billing System </span>
</a>


    <!-- Sidebar Toggle-->
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" 
            id="sidebarToggle" href="#!">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Right-side nav -->
    <ul class="navbar-nav ms-auto me-3 me-lg-4">
        
        <!-- Edit Homepage -->
        <li class="nav-item">
            <a class="nav-link text-warning fw-bold" href="{{ route('admin.homepage.edit') }}">
                <i class="bi bi-pencil-square"></i> Edit Homepage
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link text-info fw-bold" href="{{ route('admin.messages') }}">
                <i class="bi bi-chat-dots"></i> Create Messages
            </a>
        </li>


        <!-- User Dropdown -->
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" 
               data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user fa-fw"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="#!">Settings</a></li>
                <li><a class="dropdown-item" href="#!">Activity Log</a></li>
                <li><hr class="dropdown-divider" /></li>
                <li>
                    <a class="dropdown-item" href="#" 
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        Logout
                    </a>
                    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </li>
    </ul>
</nav>

