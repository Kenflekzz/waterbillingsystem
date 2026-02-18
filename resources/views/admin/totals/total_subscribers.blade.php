@extends('layouts.admin')

@section('title', 'Subscribers')
<link rel="icon" href="{{ asset('images/MAGALLANES_LOGO.png') }}" type="image/x-icon">

@section('content')
<h1 class="mt-4">Total Subscribers ({{ $subscribers->count() }})</h1>

<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Subscribers</li>
</ol>


<div class="mb-3">
    <form method="GET" action="{{ route('admin.total_subscribers') }}" class="form-inline">
        
        <label for="status" class="form-label me-2">Filter by Status:</label>
        <select name="status" id="status" class="form-select d-inline-block w-auto me-3">
            <option value="all" {{ $currentFilter == 'all' ? 'selected' : '' }}>All Status</option>
            <option value="CUT" {{ $currentFilter == 'CUT' ? 'selected' : '' }}>CUT</option>
            <option value="CURC" {{ $currentFilter == 'CURC' ? 'selected' : '' }}>CURC</option>
        </select>

        <button type="submit" class="btn btn-primary me-2">
            <i class="fas fa-filter"></i> Filter
        </button>

        <a href="{{ route('admin.total_subscribers') }}" class="btn btn-secondary">
            Reset
        </a>
    </form>
</div>

<div class="mb-3 text-end">
    <a 
        href="{{ route('admin.print_subscribers', request()->query()) }}" 
        target="_blank" 
        class="btn btn-secondary">
        <i class="fas fa-print"></i> Print Filtered Result
    </a>
</div>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-table me-1"></i> Subscribers List
    </div>
    <div class="card-body">
        <table id="datatablesSimple" class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Meter No.</th>
                    <th>Barangay</th>
                    <th>Purok</th>
                    <th>Contact</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscribers as $subscriber)
                <tr>
                    <td>{{ $subscriber->id }}</td>
                    <td>{{ $subscriber->full_name }}</td>
                    <td>{{ $subscriber->meter_no }}</td>
                    <td>{{ $subscriber->barangay }}</td>
                    <td>{{ $subscriber->purok }}</td>
                    <td>{{ $subscriber->contact_number ?? 'N/A' }}</td>
                    <td>
                        <span class="badge bg-{{ $subscriber->status == 'CUT' ? 'danger' : 'success' }}">
                            {{ $subscriber->status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">No subscribers found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
<script src="{{ asset('admin/js/datatables-simple-demo.js') }}"></script>
@endsection