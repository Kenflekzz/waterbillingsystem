@extends('layouts.admin')

@section('title', 'Admin Dashboard')
<link rel="icon" href="{{ asset('images/MAGALLANES_LOGO.png') }}" type="image/x-icon">

<style>
    #behavior-chart {
        min-height: 300px;
    }

    .chart-wrapper {
        width: 100%;
        height: 440px;
        min-height: 280px;
        background: #fff;
        border-radius: 12px;
        padding: 12px;
        position: relative;
        box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.04);
        overflow: auto;
    }

    .resizer-handle {
        position: absolute;
        bottom: 8px;
        right: 8px;
        width: 16px;
        height: 16px;
        background: #741aac;
        border-radius: 4px;
        cursor: se-resize;
        z-index: 10;
    }

    @media (max-width: 991.98px) {
        .row.mt-4 {
            flex-direction: column;
        }
    }
</style>

@section('content')
    <h1 class="mt-4">Dashboard</h1>

    {{-- KPI cards --}}
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">Total No. of Subscribers</div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('admin.total_subscribers') }}">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">Total No. of Unpaid</div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('admin.total_unpaid') }}">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">Total No. of Paid</div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('admin.total_paid') }}">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">Disconnected</div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('admin.totalDisconnected') }}">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Behavioral Analysis Section --}}
    <div class="row mt-4">
        {{-- Chart Section (now wider) --}}
        <div class="col-md-9">
            <div class="card shadow-lg border-0 mb-4"
                 style="border-radius: 20px; background: linear-gradient(135deg, #741aac, #9c6ade); color: white;">
                <div class="card-header d-flex justify-content-between align-items-center"
                     style="background: rgba(255, 255, 255, 0.1); border-top-left-radius: 20px; border-top-right-radius: 20px;">
                    <h5 class="mb-0 fw-bold">Behavioral Analysis</h5>
                    <div class="filters text-end">
                        <select id="filter-year" class="form-select form-select-sm d-inline-block w-auto me-2">
                            <option value="">All Years</option>
                            @for ($y = 2010; $y <= now()->year; $y++)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                        <select id="filter-month" class="form-select form-select-sm d-inline-block w-auto me-2">
                            <option value="">All Months</option>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                            @endfor
                        </select>
                        <select id="filter-week" class="form-select form-select-sm d-inline-block w-auto me-2">
                            <option value="">All Weeks</option>
                            @for ($w = 1; $w <= 52; $w++)
                                <option value="{{ $w }}">Week {{ $w }}</option>
                            @endfor
                        </select>
                        <select id="filter-day" class="form-select form-select-sm d-inline-block w-auto me-2">
                            <option value="">All Days</option>
                            @for ($d = 1; $d <= 31; $d++)
                                <option value="{{ $d }}">{{ $d }}</option>
                            @endfor
                        </select>
                        <select id="filter-barangay" class="form-select form-select-sm d-inline-block w-auto me-2">
                            <option value="">All Barangays</option>
                            @if (!empty($barangays))
                                @foreach ($barangays as $b)
                                    <option value="{{ $b->name }}">{{ $b->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        <select id="filter-consumer" class="form-select form-select-sm d-inline-block w-auto">
                            <option value="">All Consumers</option>
                            @if (!empty($consumers))
                                @foreach ($consumers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                <div class="card-body bg-white rounded-bottom"
                     style="border-bottom-left-radius: 20px; border-bottom-right-radius: 20px;">
                    <div class="chart-wrapper" id="chart-wrapper">
                        <canvas id="behavior-chart" height="220"></canvas>
                        <div class="resizer-handle" id="resizer-handle"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Feed Demo Data Section (smaller width) --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-lg mb-4" style="border-radius: 20px;">
                <div class="card-header fw-bold text-center"
                     style="background-color: #741aac; color: white; border-top-left-radius: 20px; border-top-right-radius: 20px;">
                    Feed Demo Data
                </div>
                <div class="card-body text-center">
                    <button id="feed-random" class="btn btn-primary w-75"
                            style="background-color: #741aac; border-color: #741aac;">
                        <i class="fas fa-random me-2"></i>Feed Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    @vite('resources/js/behavioral.js')
@endsection
