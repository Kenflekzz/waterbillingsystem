<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">User Portal</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarContent" aria-controls="navbarContent"
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('user.consumption') }}">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Billing</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Consumption</a></li>
               <li class="nav-item">
                    <form id="logout-form" action="{{ route('user.logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="nav-link btn btn-link text-white" style="text-decoration:none;">
                            Logout
                        </button>
                    </form>
                </li>

            </ul>
        </div>
    </div>
</nav>

@vite('resources/css/usernavbar.css')