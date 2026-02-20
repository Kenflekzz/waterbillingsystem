@extends('layouts.admin')
@section('title','Consumer Consumption Report')
<link rel="icon" href="{{ asset('images/MAGALLANES_LOGO.png') }}" type="image/x-icon">

@section('content')
<div class="container-fluid px-0 overflow-hidden">
    <h4 class="mt-4 px-4"><i class="fas fa-tint"></i> Consumer Consumption Report</h4>

    {{-- filter bar --}}
    <form method="GET" action="{{ route('admin.consumption-report') }}" class="row g-2 align-items-center mb-3 px-4">
        {{-- search --}}
        <div class="col-auto">
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="Search name / meter" value="{{ request('search') }}">
        </div>

        {{-- status dropdown --}}
        <div class="col-auto">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- All Status --</option>
                <option value="CURC" {{ request('status') == 'CURC' ? 'selected' : '' }}>CURC</option>
                <option value="CUT"  {{ request('status') == 'CUT'  ? 'selected' : '' }}>CUT</option>
            </select>
        </div>

        {{-- date --}}
        <div class="col-auto">
            <input type="date" name="bill_date" class="form-control form-control-sm"
                   value="{{ request('bill_date') }}" title="Bill issued on">
        </div>

        {{-- buttons --}}
        <div class="col-auto">
            <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="bi bi-funnel"></i> Filter</button>
        </div>
        @if(request('bill_date'))
            <div class="col-auto">
                <a href="{{ route('admin.consumption-report') }}" class="btn btn-sm btn-outline-warning">Clear Date</a>
            </div>
        @endif
        <div class="col-auto ms-auto">
            <a href="{{ request()->fullUrlWithQuery(['print' => 1]) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-printer"></i> Print PDF</a>
        </div>
    </form>

    {{-- Active filter indicator --}}
    @if($billDate)
        <div class="alert alert-info mx-4">
            <i class="fas fa-calendar-day"></i> 
            Showing bills for: <strong>{{ \Carbon\Carbon::parse($billDate)->format('F d, Y') }}</strong>
        </div>
    @endif

    {{-- print styles --}}
    @if(request('print'))
        <style>
            @media print {
                @page { margin: 0; size: auto; }
                header, footer, nav, .btn, .alert, h4.mt-4, form.row.g-2, .pagination { display: none !important; }
                .card, .card-body { width: 100vw !important; margin: 0 !important; padding: 0 !important; }
                .card-header { background: #fff !important; color: #000 !important; }
                .table { width: 100% !important; font-size: 9pt !important; }
                .table th, .table td { padding: 4px 6px !important; }
            }
        </style>
    @endif

    {{-- Results --}}
    @if($viewMode == 'bills_by_date')
        {{-- MODE 1: Individual bills for specific date --}}
        @if($bills->isEmpty())
            <div class="alert alert-info mx-4">No bills found for {{ \Carbon\Carbon::parse($billDate)->format('F d, Y') }}.</div>
        @else
            <div class="card mb-4 rounded-0 border-0">
                <div class="card-header bg-primary text-white rounded-0">
                    Bills for {{ \Carbon\Carbon::parse($billDate)->format('F d, Y') }}
                </div>
                <div class="card-body">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width:5%">#</th>
                                <th style="width:25%">Client Name</th>
                                <th style="width:15%">Meter No.</th>
                                <th style="width:10%">Status</th>
                                <th style="width:20%">Bill Date</th>
                                <th style="width:25%">Consumption (c.u)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bills as $bill)
                                <tr>
                                    <td>{{ $loop->iteration + ($bills->currentPage() - 1) * $bills->perPage() }}</td>
                                    <td>{{ $bill->client->full_name }}</td>
                                    <td>{{ $bill->client->meter_no }}</td>
                                    <td>{{ $bill->client->status }}</td>
                                    <td>{{ \Carbon\Carbon::parse($bill->billing_date)->format('d-M-Y') }}</td>
                                    <td><strong>{{ number_format($bill->consumed, 2) }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-info">
                                <th colspan="5" class="text-end">TOTAL CONSUMPTION</th>
                                <th>{{ number_format($bills->sum('consumed'), 2) }} c.u</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @if(!request('print'))
                <div class="px-4">{{ $bills->appends(request()->only(['search','status','bill_date']))->links('pagination::bootstrap-5') }}</div>
            @endif
        @endif

    @else
        {{-- MODE 2: Grouped consumers (original view) --}}
        @if($consumers->isEmpty())
            <div class="alert alert-info mx-4">No consumers found.</div>
        @else
            <div class="card mb-4 rounded-0 border-0">
                <div class="card-header bg-primary text-white rounded-0">List of Consumers & Total Consumption</div>
                <div class="card-body">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width:5%">#</th>
                                <th style="width:25%">Client Name</th>
                                <th style="width:15%">Meter No.</th>
                                <th style="width:10%">Status</th>
                                <th style="width:20%">Latest Bill Date</th>
                                <th style="width:25%">Total Consumption (c.u)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($consumers as $c)
                                <tr>
                                    <td>{{ $loop->iteration + ($consumers->currentPage() - 1) * $consumers->perPage() }}</td>
                                    <td>{{ $c->full_name }}</td>
                                    <td>{{ $c->meter_no }}</td>
                                    <td>{{ $c->status }}</td>
                                    <td>{{ \Carbon\Carbon::parse(optional($c->billings->first())->billing_date ?? now())->format('d-M-Y') }}</td>
                                    <td><strong>{{ number_format($c->total_consumption, 2) }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-info">
                                <th colspan="5" class="text-end">GRAND TOTAL</th>
                                <th>{{ number_format($consumers->sum('total_consumption'), 2) }} c.u</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @if(!request('print'))
                <div class="px-4">{{ $consumers->appends(request()->only(['search','status','bill_date']))->links('pagination::bootstrap-5') }}</div>
            @endif
        @endif
    @endif
</div>

@if(request('print'))
    <script> window.onload = function () { window.print(); }; </script>
@endif
@endsection