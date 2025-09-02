@extends('layouts.admin')
@section('title', 'Subscribers')
<link rel="icon" href="{{ asset('images/MAGALLANES_LOGO.png') }}" type="image/x-icon">

@section('content')
<h1 class="mt-4">Total Subscribers ({{ $subscribers->count() }})</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Subscribers</li>
</ol>
<div class="card mb-4"> 
    <div class="card-body">
        Below is the table showing all subscribers.
    </div>
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
                    <!-- add more columns if you have -->
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
                    <td>{{ $subscriber->status }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">No subscribers found.</td>
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
