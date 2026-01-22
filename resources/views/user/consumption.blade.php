@extends('layouts.user')
<link rel="icon" href="{{ asset('images/MAGALLANES_LOGO.png') }}" type="image/x-icon">

@section('content')
<div class="container py-5 text-center">
    <h2 class="text-muted">Here’s a summary of your water consumption.</h2>

    {{-- DATE FILTERS --}}
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