@extends('layouts.user')
<link rel="icon" href="{{ asset('images/MAGALLANES_LOGO.png') }}" type="image/x-icon">

@section('content')
<div class="container py-5 text-center">
    <h2 class="text-muted">Here's a summary of your water consumption.</h2>

    {{-- ESTIMATED BILL BANNER --}}
    <div class="row justify-content-center mt-3 mb-2">
        <div class="col-lg-9">
            <div class="alert border-0 shadow-sm p-4"
                 style="background: linear-gradient(135deg, #1565c0, #0d47a1); border-radius: 16px;">
                <div class="row align-items-center text-white">

                    <div class="col-md-4 border-end border-white border-opacity-25 mb-3 mb-md-0">
                        <div style="font-size: 0.8rem; opacity: 0.8;">Estimated Consumption</div>
                        <div style="font-size: 2rem; font-weight: 700;">
                            {{ number_format($estimatedCubicMeters, 4) }}
                        </div>
                        <div style="font-size: 0.75rem; opacity: 0.7;">Cubic Meters (m³)</div>
                    </div>

                    <div class="col-md-4 border-end border-white border-opacity-25 mb-3 mb-md-0">
                        <div style="font-size: 0.8rem; opacity: 0.8;">Estimated Bill</div>
                        <div style="font-size: 2rem; font-weight: 700;">
                            ₱{{ number_format($estimatedBill, 2) }}
                        </div>
                        <div style="font-size: 0.75rem; opacity: 0.7;">Based on current flow readings</div>
                    </div>

                    <div class="col-md-4">
                        <div style="font-size: 0.8rem; opacity: 0.8;">Previous Reading</div>
                        <div style="font-size: 2rem; font-weight: 700;">
                            {{ number_format($previousReading, 2) }}
                        </div>
                        <div style="font-size: 0.75rem; opacity: 0.7;">Last Billed Reading (m³)</div>
                    </div>

                </div>

                <div class="mt-3 pt-2 border-top border-white border-opacity-25">
                    <small style="opacity: 0.7; color: white;">
                        <i class="fas fa-info-circle me-1"></i>
                        This is an <strong>estimate only</strong> based on your IoT sensor readings
                        since your last billing. Actual bill may vary depending on penalties,
                        arrears, and maintenance fees.
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- DATE FILTERS --}}
    {{-- ... rest of blade unchanged ... --}}
<div class="row justify-content-center mb-4">
    <div class="col-lg-9">
        <div class="filter-pill-container">

            <form method="GET" action="{{ route('user.consumption') }}" class="d-flex flex-nowrap align-items-center justify-content-center gap-3 filter-row">

                {{-- YEAR --}}
                <select name="year" id="filter-year"
                    class="form-select filter-pill {{ request('year') ? 'active' : '' }}">
                    <option value="">Year</option>
                    @for ($y = now()->year; $y >= 2010; $y--)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>

                {{-- MONTH --}}
                <select name="month" id="filter-month"
                    class="form-select filter-pill {{ request('month') ? 'active' : '' }}">
                    <option value="">Month</option>
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endfor
                </select>

                {{-- DAY --}}
                <select name="day" id="filter-day"
                    class="form-select filter-pill {{ request('day') ? 'active' : '' }}">
                    <option value="">Day</option>
                    @for ($d = 1; $d <= 31; $d++)
                        <option value="{{ $d }}" {{ request('day') == $d ? 'selected' : '' }}>
                            {{ $d }}
                        </option>
                    @endfor
                </select>

                {{-- CLEAR --}}
                @if(request()->anyFilled(['year','month','day']))
                    <a href="{{ route('user.consumption') }}" class="btn btn-outline-secondary filter-clear">
                        Clear
                    </a>
                @endif

            </form>
        </div>
    </div>
</div>



    {{--  YESTERDAY vs TODAY COMPARISON CARDS  --}}
<div class="row justify-content-center mt-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <h5 class="text-secondary">Past Consumption</h5>
                <small class="text-muted d-block">{{ $yesterdayDate }}</small>
                <h3 class="fw-bold text-{{ $yesterdayStatus }}">
                    {{ number_format($previousConsumption, 2) }} C.U
                </h3>
                <span class="badge bg-{{ $yesterdayStatus }}">
                    {{ $yesterdayText }}
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <h5 class="text-primary">Today's Consumption</h5>
                <small class="text-muted d-block">{{ $todayDate }}</small>
                <h3 class="fw-bold text-{{ $todayStatus }}">
                    {{ number_format($currentConsumption, 2) }} C.U
                </h3>
                <span class="badge bg-{{ $todayStatus }}">
                    {{ $todayText }}
                </span>
            </div>
        </div>
    </div>
</div>

    {{--  CHART WITH LIMIT BANDS (same as admin)  --}}
    <div class="row justify-content-center mt-4">
        <div class="col-lg-10">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white">
                    Consumption Pattern
                </div>
                <div class="card-body" style="height: 350px;">
                    <canvas id="behavior-chart" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{--  HIDDEN CONSUMER ID FOR JS  --}}
<input type="hidden" id="filter-consumer" value="{{ $consumerId }}">

@vite('resources/js/behavioral.js','resources/css/consumption.css')
<script>window.IS_USER_PAGE = true;</script>
<script>
window.addEventListener('DOMContentLoaded', () => {
  const yearIn = document.getElementById('filter-year');
  const monthIn = document.getElementById('filter-month');
  const dayIn = document.getElementById('filter-day');
  if (!yearIn || !monthIn || !dayIn) return;

  /* ----------  AUTO-SUBMIT FORM WHEN ANY FILTER CHANGES  ---------- */
  [yearIn, monthIn, dayIn].forEach(input => {
    input.addEventListener('change', () => {
      input.form.requestSubmit();          // native submit
    });
  });

  /* ----------  DRAW AFTER NEW DATA ARRIVES  ---------- */
    window.consumerRows = @json($rows);
  window.chartConsumer  = "{{ $consumerId }}";

  if (window.consumerRows && window.consumerRows.length) {
    window.allConsumerData = {};          // clear cache
    window.consumerRows.forEach(r => {
      window.addDataPoint(window.chartConsumer, r.date, r.value, r.source);
    });
    window.updateChartDisplay(window.chartConsumer);
  }

  ['filter-year','filter-month','filter-day'].forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;

    el.addEventListener('change', () => {
      el.classList.add('active');

      // slight delay for animation
      setTimeout(() => {
        el.form.requestSubmit();
      }, 150);
    });
  });
});
</script>
@endsection