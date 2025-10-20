@extends('layouts.user')

@section('content')
<div class="container mt-5 pt-4">
    <div class="row">
        <!-- Profile Card -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-lg rounded-3 text-center">
                <div class="card-body">
                    <img src="https://via.placeholder.com/150" class="rounded-circle mb-3" alt="User Avatar">
                    <h4>{{ $user->first_name }} {{ $user->last_name }}</h4>
                    <p class="text-muted">User</p>
                    <a href="{{ route('user.logout') }}" 
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                       class="btn btn-danger btn-sm mt-2">
                        Logout
                    </a>
                    <form id="logout-form" action="{{ route('user.logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        </div>

        <!-- User Details -->
        <div class="col-md-8">
            <div class="card shadow-lg rounded-3">
                <div class="card-body">
                    <h3 class="mb-4 text-primary">Account Information</h3>
                    <p><strong>Meter Number:</strong> {{ $user->meter_number }}</p>
                    <p><strong>Email:</strong> {{ $user->email }}</p>
                    <p><strong>Phone:</strong> {{ $user->phone_number }}</p>
                    <hr>
                    <p class="text-muted">Dashboard last updated: {{ now()->format('F d, Y h:i A') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
