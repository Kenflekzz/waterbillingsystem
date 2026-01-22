@extends('layouts.admin')

@section('title', 'Total Disconnected Consumers')
<link rel="icon" href="{{ asset('images/MAGALLANES_LOGO.png') }}" type="image/x-icon">

@section('content')
<h1 class="mt-4">Total Disconnected Consumers ({{ $totalDisconnected->count() }})</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Total Disconnected Consumers</li>
</ol>

{{-- FILTER BAR --------------------------------------------------}}
<div class="mb-3">
    <form method="GET" action="{{ route('admin.filter_disconnected') }}" class="form-inline">
        <label for="name" class="form-label me-2">Consumer Name:</label>
        <input type="text"
               name="name"
               id="name"
               class="form-control d-inline-block w-auto me-3"
               placeholder="Search name"
               value="{{ request('name') }}">

        <label for="billing_month" class="form-label me-2">Billing Month:</label>
        <input type="month"
               name="billing_month"
               id="billing_month"
               class="form-control d-inline-block w-auto me-3"
               value="{{ request('billing_month') }}">

        <button class="btn btn-primary me-2"><i class="fas fa-filter"></i> Filter</button>
        <a href="{{ route('admin.totalDisconnected') }}" class="btn btn-secondary">Reset</a>
    </form>
</div>

<div class="mb-3 text-end">
    <a href="{{ route('admin.print_disconnected', request()->query()) }}"
       target="_blank"
       class="btn btn-secondary">
        <i class="fas fa-print"></i> Print Filtered Result
    </a>
</div>
{{-- END FILTER BAR ---------------------------------------------}}

<div class="card mb-4">
    <div class="card-body">Below is the table showing all disconnected payment records.</div>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="fas fa-table me-1"></i> Disconnected Payment Records</div>
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
                @foreach($totalDisconnected as $payment)
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
                    <td>{{ ucfirst($payment->status) }}</td>
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