@extends('layouts.admin')
<link rel="icon" href="{{ asset('images/MAGALLANES_LOGO.png') }}" type="image/x-icon">

@section('content')
<div class="container-fluid px-4">
    <h4 class="mt-4"><i class="bi bi-exclamation-triangle-fill text-warning"></i> Subject for Disconnection</h4>

    {{-- Filter bar : Search + Date range + Print --}}
    <form method="GET" action="{{ route('admin.disconnect.pending') }}" class="row g-2 mb-3 align-items-center">
        {{-- Search --}}
        <div class="col-auto">
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="Search name"
                   value="{{ request('search') }}">
        </div>

        {{-- single "as-of" date --}}
        <div class="col-auto">
            <input type="date" name="as_of" class="form-control form-control-sm"
                value="{{ request('as_of') }}">
        </div>

        {{-- Filter button --}}
        <div class="col-auto">
            <button class="btn btn-sm btn-primary" type="submit">
                <i class="bi bi-funnel"></i> Filter
            </button>
        </div>

        {{-- Print button --}}
        <div class="col-auto">
            <a href="javascript:void(0)" onclick="printTable()" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-printer"></i> Print
            </a>
        </div>
    </form>

    {{-- expanded empty message --------------------------------------- --}}
    @if($consumers->isEmpty())
        @if(request()->filled('as_of') && !request()->has('search'))
            <div class="alert alert-warning">
                No bills issued up to <strong>{{ request('as_of') }}</strong> – try an earlier date.
            </div>
        @else
            <div class="alert alert-info">
                No consumer currently meets the 3-consecutive-unpaid-bills criterion.
            </div>
        @endif
    @else
        <div class="card mb-4" id="printArea">
            <div class="card-header bg-danger text-white">
                List of Consumers (3 consecutive unpaid bills)
                <span class="d-none d-print-block">Date: {{ request('from','-') }} to {{ request('to','-') }}</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Client Name</th>
                            <th>Meter No.</th>
                            <th>Contact Number</th>
                            <th>Email</th>
                            <th>3rd Unpaid Bill Issued</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($consumers as $c)
                            <tr>
                                <td>{{ $loop->iteration + ($consumers->currentPage() - 1) * $consumers->perPage() }}</td>
                                <td>{{ $c->client->full_name }}</td>
                                <td>{{ $c->client->meter_no ?? '—' }}</td>
                                <td>{{ $c->client->contact_number ?? '—' }}</td>
                                <td>{{ $c->email }}</td>
                                <td>
                                   {{ $c->third_unpaid_date ?? '—' }}

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination links --}}
        <div class="d-flex justify-content-center d-print-none">
            {{ $consumers->appends(request()->only(['search','from','to']))->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

{{-- Print helper --}}
<script>
function printTable() {
    const printContent = document.getElementById('printArea').innerHTML;
    const original = document.body.innerHTML;
    document.body.innerHTML = `
        <html>
        <head>
            <title>Subject for Disconnection</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style> body { margin:20px; } .table { font-size:0.85rem; } </style>
        </head>
        <body>${printContent}</body>
        </html>`;
    window.print();
    document.body.innerHTML = original;
    window.location.reload();   // restore original DOM after print
}
</script>
@endsection