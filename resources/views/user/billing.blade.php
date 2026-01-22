@extends('layouts.user')
<link rel="icon" href="{{ asset('images/MAGALLANES_LOGO.png') }}" type="image/x-icon">

@section('content')
<div class="container py-5">

    {{-- 3-month disconnection warning --}}
@php
    // newest 3 bills
    $latestThree = $billings->take(3);

    // count how many of them are still unpaid
    $unpaidInLastThree = $latestThree->whereIn('status', ['unpaid', 'Overdue'])->count();

    // show banner only if we have 3 bills and all are unpaid
    $showDisconnectWarning = $latestThree->count() === 3 && $unpaidInLastThree === 3;
@endphp

@if($showDisconnectWarning)
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        You are subject for disconnection 3 consecutive unpaid bills please settle your balance to avoid disconnection.
    </div>
@endif

    {{-- Hidden inputs for JS --}}
    <input type="hidden" id="flash-success" value="{{ session('success') }}">
    <input type="hidden" id="flash-error" value="{{ session('error') }}">
    <input type="hidden" id="flash-pdfUrl" value="{{ session('pdfUrl') }}">

    {{-- Header --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
            <h2 class="fw-bold text-primary">My Billing Statements</h2>
        </div>
    </div>

    @if($billings->isEmpty())
        <div class="text-center py-5">
            <h5 class="mt-3 text-muted">No billing records found.</h5>
            <p class="text-secondary">Your bills will appear here once generated.</p>
        </div>
    @else
        @php
            $latestBilling = $billings->first(); // Newest first
            $previousBillings = $billings->slice(1);
        @endphp

        {{-- Latest Bill --}}
        <div class="row justify-content-center mb-5">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card shadow-lg border-0 rounded-4 p-4 pop-out-card latest-bill-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="fw-bold text-primary">Latest Bill #{{ $latestBilling->bill_number }}</h4>
                            <span class="badge 
                                @if($latestBilling->status === 'paid') bg-success
                                @elseif($latestBilling->status === 'Partially Paid') bg-warning text-dark
                                @elseif($latestBilling->status === 'unpaid') bg-danger
                                @elseif($latestBilling->status === 'Disconnected') bg-dark
                                @else bg-secondary @endif"
                                id="bill-status-{{ $latestBilling->id }}">
                                {{ $latestBilling->status }}
                            </span>
                        </div>

                        <hr>
                        <p class="mb-1"><strong>Current Bill:</strong> ₱{{ number_format($latestBilling->current_bill, 2) }}</p>
                        <p class="mb-1"><strong>Arrears:</strong> ₱{{ number_format($latestBilling->arrears ?? 0, 2) }}</p>
                        <p class="mb-1"><strong>Penalty:</strong> ₱{{ number_format($latestBilling->penalty ?? 0, 2) }}</p>
                        <p class="mb-1"><strong>Consumed:</strong> {{ number_format($latestBilling->consumed ?? 0, 2) }} c.u</p>
                        <p class="mb-1"><strong>Total Amount:</strong> ₱{{ number_format($latestBilling->amount_due, 2) }}</p>
                        <p class="mb-1"><strong>Due Date:</strong> {{ \Carbon\Carbon::parse($latestBilling->due_date)->format('F d, Y') }}</p>
                        <p class="mb-1"><strong>Maintenance Cost:</strong> ₱{{ number_format($latestBilling->maintenance_cost ?? 0, 2) }}</p>
                        <p class="mb-1"><strong>Installation Fee:</strong> ₱{{ number_format($latestBilling->installation_fee ?? 0, 2) }}</p>

                        @if($latestBilling->status == 'paid')
                            <p class="mb-1"><strong>Paid On:</strong> {{ \Carbon\Carbon::parse($latestBilling->payment_date)->format('F d, Y') }}</p>
                            <p class="mb-3"><strong>Payment Method:</strong> {{ $latestBilling->payment_method ?? '—' }}</p>
                        @else
                            <p class="mb-3 text-danger small">Please pay before the due date to avoid disconnection.</p>
                        @endif

                        <div class="d-flex flex-column gap-2">
                            <a href="{{ route('user.billing.print', $latestBilling->id) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-printer"></i> Print Bill
                            </a>

                            @if(in_array($latestBilling->status, ['unpaid', 'Overdue']))
                                <form class="gcash-form" data-bill-id="{{ $latestBilling->id }}" action="{{ route('user.billing.gcash', ['id' => $latestBilling->id]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm w-100">
                                        <i class="bi bi-wallet2"></i> Full payment with GCash
                                    </button>
                                </form>

                                @if($latestBilling->arrears > 0)
                                    <form action="{{ route('user.billing.pay.arrears', $latestBilling->id) }}" method="POST" class="gcash-form" data-bill-id="{{ $latestBilling->id }}">
                                        @csrf
                                        <button type="submit" class="btn btn-warning btn-sm w-100">
                                            Pay Arrears Only with GCash (₱{{ number_format($latestBilling->arrears, 2) }})
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Previous Bills Toggle --}}
        @if($previousBillings->isNotEmpty())
            <div class="text-left mb-3">
                <button class="btn btn-outline-primary"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#previousBillsCollapse"
                        aria-expanded="false"
                        aria-controls="previousBillsCollapse">
                    <i class="bi bi-chevron-down"></i> Show Previous Bills
                </button>
            </div>

            <div class="collapse" id="previousBillsCollapse">
                <div class="row g-4">
                    @foreach($previousBillings as $billing)
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card shadow-sm border-0 rounded-4 h-100 p-3 pop-out-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold mb-0 text-primary">Bill #{{ $billing->bill_number }}</h6>
                                        <span class="badge 
                                            @if($billing->status === 'paid') bg-success
                                            @elseif($billing->status === 'Partially Paid') bg-warning text-dark
                                            @elseif($billing->status === 'unpaid') bg-danger
                                            @elseif($billing->status === 'Disconnected') bg-dark
                                            @else bg-secondary @endif"
                                            id="bill-status-{{ $billing->id }}">
                                            {{ $billing->status }}
                                        </span>
                                    </div>
                                    <hr>
                                    <p class="mb-1"><strong>Current Bill:</strong> ₱{{ number_format($billing->current_bill, 2) }}</p>
                                    <p class="mb-1"><strong>Arrears:</strong> ₱{{ number_format($billing->arrears ?? 0, 2) }}</p>
                                    <p class="mb-1"><strong>Total Amount:</strong> ₱{{ number_format($billing->amount_due, 2) }}</p>
                                    <p class="mb-1"><strong>Due Date:</strong> {{ \Carbon\Carbon::parse($billing->due_date)->format('F d, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Pagination --}}
        <div class="mt-4 d-flex justify-content-center">
            {{ $billings->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

{{-- QR Modal --}}
<div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4 text-center">
            <h3 id="qrModalTitle"></h3>
            <p><strong>Amount Due:</strong> <span id="qrModalAmount"></span></p>
            <img id="qrModalImage" src="" alt="GCash QR Code" class="mb-3" style="width:250px; margin-left:100px;">
            <a id="qrModalLink" href="#" class="btn btn-primary w-100 mt-3">
                <i class="bi bi-phone"></i> Pay with GCash
            </a>
        </div>
    </div>
</div>

@vite(['resources/css/user-billing.css', 'resources/js/user-billing.js'])
@endsection
