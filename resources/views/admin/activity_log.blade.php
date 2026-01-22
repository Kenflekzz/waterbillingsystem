@extends('layouts.admin')
<link rel="icon" href="{{ asset('images/MAGALLANES_LOGO.png') }}" type="image/x-icon">

@section('content')
<div class="container mt-4">
    <h3 class="mb-4"><i class="bi bi-clock-history"></i> Activity Log</h3>

    {{-- Search Form --}}
    <form method="GET" action="{{ route('admin.activity.log') }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search by name, activity, or details" value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
        </div>
    </form>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Full Name</th>
                <th>Activity</th>
                <th>Details</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $log->id }}</td>
                    <td>{{ $log->client->full_name ?? 'System' }}</td>
                    <td>{{ $log->activity }}</td>
                    <td>{{ $log->details }}</td>
                    <td>{{ $log->created_at->format('M d, Y h:i A') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No activity recorded yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center mt-3">
        {{ $logs->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
