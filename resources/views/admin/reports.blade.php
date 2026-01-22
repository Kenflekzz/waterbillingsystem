@extends('layouts.admin')

@section('title', 'Reports')
<link rel="icon" href="{{ asset('images/MAGALLANES_LOGO.png') }}" type="image/x-icon">

@section('content')
@vite('resources/css/reports.css')
<h1 class="mt-4">Reports</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Reports</li>
</ol>

<div class="mb-3">
    <form method="GET" action="{{ route('admin.admin_reports') }}" class="form-inline" id="filterForm">
        <label for="status" class="form-label me-2">Filter by Status:</label>
        <select name="status" id="status" class="form-select d-inline-block w-auto me-2">
            <option value="">-- All --</option>
            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
            <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
            <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
            <option value="disconnected" {{ request('status') == 'disconnected' ? 'selected' : '' }}>Disconnected</option>
        </select>
       <label for="billing_date" class="form-label me-2">Billing Date:</label>
        <input type="date" name="billing_date" id="billing_date" class="form-control d-inline-block w-auto me-2"
            value="{{ request('billing_date') }}">
    </form>
</div>

<div class="mb-3 text-end">
    <a href="{{route('admin.print_reports',request()->query())}}" target="_blank"  class="btn btn-secondary">
        <i class="fas fa-print"></i> Print Report Table
    </a>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="fas fa-table me-1"></i>Report Records</div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="datatablesSimple" class="table table-bordered table-sm text-center">
                <thead class="table-light">
                    <tr>
                        <th>Billing ID</th>
                        <th>Meter No.</th>
                        <th>Client Full Name</th>
                        <th>Billing Date</th>
                        <th>Previous Read</th>
                        <th>Present Read</th>
                        <th>Consumed</th>
                        <th>Current Bill</th>
                        <th>Arrears</th>
                        <th>Total Penalty</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td>{{ $report->billing_id }}</td>
                            <td>{{ $report->meter_no }}</td>
                            <td>{{ $report->full_name }}</td>
                            <td>{{ \Carbon\Carbon::parse($report->billing_date)->format('F d, Y') }}</td>
                            <td>{{ $report->previous_reading }}</td>
                            <td>{{ $report->present_reading }}</td>
                            <td>{{ $report->consumed }}</td>
                            <td>₱{{ number_format($report->current_bill, 2) }}</td>
                            <td>₱{{ number_format($report->arrears ?? 0, 2) }}</td>
                            <td>₱{{ number_format($report->total_penalty, 2) }}</td>
                            <td>₱{{ number_format($report->total_amount, 2) }}</td>
                           <td>
                                @php
                                    $status = strtolower($report->status);
                                @endphp

                               <span class="badge 
                                    @if($status === 'paid' || $status === 'paid via gcash')
                                        bg-success text-white
                                    @elseif($status === 'unpaid')
                                        bg-danger text-white
                                    @elseif(in_array($status, ['partial', 'partially paid']))
                                        bg-warning text-dark
                                    @elseif($status === 'disconnected')   // << NEW
                                        bg-danger text-white              // red
                                    @else
                                        bg-secondary text-white
                                    @endif
                                ">
                                    {{ ucfirst($report->status ?? 'N/A') }}
                                </span>
                            </td>

                        </tr>
                    @empty
                        <tr><td colspan="12">No records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
    <script src="{{ asset('admin/js/datatables-simple-demo.js') }}"></script>
    @vite('resources/js/reports.js')
@endsection