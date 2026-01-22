
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">

<a class="navbar-brand ps-3 d-flex align-items-center" >
    <img src="{{ asset('images/MAGALLANES_LOGO.png') }}" 
         alt="Logo" 
         style="height: 35px; width: auto; margin-right: 10px;">
    <span>Magallanes Water Billing System </span>
</a>


   
    <ul class="navbar-nav ms-auto me-3 me-lg-4">
        
        
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


        
       
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" 
       data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-user fa-fw"></i>
        @if($totalNotification > 0)
            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
                {{ $totalNotification }}
            </span>
    @endif
    </a>

    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">

            <li>
                <a class="dropdown-item position-relative" href="{{route('admin.activity.log')}}" onclick="showLoader()">
                    <i class="bi bi-clock-history me-2"></i> Activity Log

                    @if($unseenLogs > 0)
                        <span class="badge bg-danger position-absolute top-0 end-0">
                            {{ $unseenLogs }}
                        </span>
                    @endif
                </a>
            </li>

  
            <li>
                <a class="dropdown-item position-relative" href="{{ route('admin.reports') }}" onclick="showLoader()">
                    <i class="bi bi-chat-left-text me-2"></i> Read Messages/Reports

                    @if($unreadReports > 0)
                        <span class="badge bg-danger position-absolute top-0 end-0">
                            {{ $unreadReports }}
                        </span>
                    @endif
                </a>
            </li>

            <li>
                <a class="dropdown-item position-relative" href="{{ route('admin.disconnect.pending') }}" onclick="showLoader()">
                    <i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i> Subject for Disconnections
                    @if($disconnectCount > 0)
                        <span class="badge bg-danger position-absolute top-0 end-0">{{ $disconnectCount }}</span>
                    @endif
                </a>
            </li>

            <li><hr class="dropdown-divider" /></li>

            <li>
                <a class="dropdown-item" href="#" 
   onclick="event.preventDefault(); showLoader(); document.getElementById('logout-form').submit();">
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

