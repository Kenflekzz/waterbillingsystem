@extends('layouts.admin')

@section('title', 'Total Paid Consumers')
<link rel="icon" href="{{ asset('images/MAGALLANES_LOGO.png') }}" type="image/x-icon">

@section('content')
<h1 class="mt-4">Total Paid Consumers ({{ $totalPaid->count() }})</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Total Paid Consumers</li>
</ol>

<div class="mb-3">
   <form method="GET" action="{{ route('admin.filter_paid_consumers') }}" class="form-inline">

        <label for="name" class="form-label me-2">Consumer Name:</label>
        <input
            type="text"
            name="name"
            id="name"
            class="form-control d-inline-block w-auto me-3"
            placeholder="Search name"
            value="{{ request('name') }}">

        <label for="billing_month" class="form-label me-2">Billing Month:</label>
        <input
            type="month"
            name="billing_month"
            id="billing_month"
            class="form-control d-inline-block w-auto me-3"
            value="{{ request('billing_month') }}">

        <label for="status" class="form-label me-2">Status:</label>
        <select name="status" id="status" class="form-select d-inline-block w-auto me-2">
            <option value="">-- All --</option>
            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
            <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
            <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
            <option value="gcash" {{ request('status') == 'gcash' ? 'selected' : '' }}>Paid via GCash</option>
        </select>

        <button class="btn btn-primary me-2">
            <i class="fas fa-filter"></i> Filter
        </button>

        <a href="{{ route('admin.total_paid') }}" class="btn btn-secondary">
            Reset
        </a>

    </form>
</div>

<div class="mb-3 text-end">
    <a
        href="{{ route('admin.print_paid_consumers', request()->query()) }}"
        target="_blank"
        class="btn btn-secondary">
        <i class="fas fa-print"></i> Print Filtered Result
    </a>
</div>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        Paid Payment Records
    </div>
    <div class="card-body">
        <table id="datatablesSimple" class="table table-bordered">
            <thead>
                <tr>
                    <th>Client Full Name</th>
                    <th>Barangay</th>
                    <th>Purok</th>
                    <th>Billing Month</th>
                    <th>Current Bill</th>
                    <th>Arrears</th>
                    <th>Partial Payment Amount</th>
                    <th>Payment Type</th>
                    <th>Penalty</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($totalPaid as $payment)
                <tr>
                    <td>{{ $payment->client->full_name ?? 'N/A' }}</td>
                    <td>{{ $payment->client->barangay ?? 'N/A' }}</td>
                    <td>{{ $payment->client->purok ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($payment->billing_month)->format('M Y') }}</td>
                    <td>₱{{ number_format($payment->current_bill, 2) }}</td>
                    <td>₱{{ number_format($payment->arrears, 2) }}</td>
                    <td>₱{{ number_format($payment->partial_payment_amount ?? 0, 2) }}</td>
                    <td>{{ $payment->payment_type_label }}</td>
                    <td>₱{{ number_format($payment->penalty, 2) }}</td>
                    <td><strong>₱{{ number_format($payment->total_amount, 2) }}</strong></td>
                    <td>
                        @if($payment->payment_type === 'gcash')
                            <span class="badge bg-info">Paid via GCash</span>
                        @elseif($payment->status === 'paid')
                            <span class="badge bg-success">Paid</span>
                        @elseif($payment->status === 'partial')
                            <span class="badge bg-warning text-dark">Partial</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($payment->status) }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@vite('resources/css/billings.css')

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
<script src="{{ asset('admin/js/datatables-simple-demo.js') }}"></script>
@endsection
