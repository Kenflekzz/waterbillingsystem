<?php

namespace App\Http\Controllers;

use App\Models\BehavioralData;
use App\Models\Billings;
use App\Models\FlowReading;
use App\Models\Clients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ConsumptionController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();  // users.id
        $tz     = 'Asia/Manila';

        // Get the client record linked to this logged in user
        $client     = Clients::where('user_id', $userId)->first();
        $consumerId = $client?->id; // clients.id — used for billing and flowmeter

        /* ================= BASE QUERIES ================= */
        $billing = Billings::select(
            'billing_date as date',
            'consumed as value',
            DB::raw("'billing' as source")
        )->where('client_id', $consumerId); // ← client_id ✅

        $sensor = BehavioralData::select(
            'created_at as date',
            'value',
            DB::raw("'sensor' as source")
        )->where('user_id', $userId); // ← user_id ✅

        $flowMeter = FlowReading::select(
            'created_at as date',
            'cubic_meter as value',
            DB::raw("'flow_meter' as source")
        )->where('client_id', $consumerId); // ← client_id ✅

        /* ================= FILTERS ================= */
        if ($request->year) {
            $billing->whereYear('billing_date', $request->year);
            $sensor->whereYear('created_at', $request->year);
            $flowMeter->whereYear('created_at', $request->year);
        }
        if ($request->month) {
            $billing->whereMonth('billing_date', $request->month);
            $sensor->whereMonth('created_at', $request->month);
            $flowMeter->whereMonth('created_at', $request->month);
        }
        if ($request->day) {
            $billing->whereDay('billing_date', $request->day);
            $sensor->whereDay('created_at', $request->day);
            $flowMeter->whereDay('created_at', $request->day);
        }

        /* ================= MERGE DATA ================= */
        $rows = $billing->get()
            ->merge($sensor->get())
            ->merge($flowMeter->get())
            ->sortBy('date')
            ->values();

        /* ================= KPI SUMMARY ================= */
        $total = $rows->sum('value');
        $avg   = $rows->avg('value');
        $max   = $rows->max('value');
        $min   = $rows->min('value');
        $count = $rows->count();

        /* ================= TODAY vs YESTERDAY ================= */
        $latestRow = $rows->last();
        $today = $latestRow
            ? Carbon::parse($latestRow->date)->tz($tz)->startOfDay()
            : Carbon::today($tz);

        $yesterday = $today->copy()->subDay();

        $todayRow = $rows->filter(fn ($r) =>
            Carbon::parse($r->date)->tz($tz)->isSameDay($today)
        )->last();

        $yesterdayRow = $rows->filter(fn ($r) =>
            Carbon::parse($r->date)->tz($tz)->isSameDay($yesterday)
        )->last();

        $currentConsumption  = optional($todayRow)->value ?? 0;
        $previousConsumption = optional($yesterdayRow)->value ?? 0;

        $todayDate = $todayRow
            ? Carbon::parse($todayRow->date)->tz($tz)->format('M d, Y g:i A')
            : $today->format('M d, Y').' --:--';

        $yesterdayDate = $yesterdayRow
            ? Carbon::parse($yesterdayRow->date)->tz($tz)->format('M d, Y g:i A')
            : $yesterday->format('M d, Y').' --:--';

        $limit = $client?->limit ?? 30; // ← use $client directly ✅

        $todayStatus = $currentConsumption >= $limit ? 'danger' : 'success';
        $todayText   = $currentConsumption >= $limit
            ? 'HIGH • Above Limit'
            : 'NORMAL • Within Limit';

        $yesterdayStatus = $previousConsumption >= $limit ? 'danger' : 'success';
        $yesterdayText   = $previousConsumption >= $limit
            ? 'HIGH • Above Limit'
            : 'NORMAL • Within Limit';

        /* ================= ESTIMATED BILL ================= */
        $lastBilling = Billings::where('client_id', $consumerId) // ← client_id ✅
            ->orderBy('billing_date', 'desc')
            ->first();

        $previousReading = $lastBilling?->present_reading ?? 0;

        $flowSinceLastBilling = FlowReading::where('client_id', $consumerId) // ← client_id ✅
            ->when($lastBilling, fn($q) =>
                $q->where('created_at', '>', $lastBilling->billing_date)
            )
            ->sum('cubic_meter');

        $estimatedCubicMeters = round($flowSinceLastBilling, 4);

        if ($estimatedCubicMeters <= 10) {
            $estimatedBill = 150;
        } else {
            $subtractedValue = $estimatedCubicMeters - 10;

            if ($subtractedValue <= 10) {
                $rate = 16;
            } elseif ($subtractedValue <= 20) {
                $rate = 19;
            } elseif ($subtractedValue <= 30) {
                $rate = 23;
            } elseif ($subtractedValue <= 50) {
                $rate = 26;
            } else {
                $rate = 30;
            }

            $estimatedBill = 150 + ($subtractedValue * $rate);
        }

        return view('user.consumption', compact(
            'rows',
            'consumerId',
            'total',
            'avg',
            'max',
            'min',
            'count',
            'currentConsumption',
            'previousConsumption',
            'todayDate',
            'todayStatus',
            'todayText',
            'yesterdayDate',
            'yesterdayStatus',
            'yesterdayText',
            'estimatedBill',
            'estimatedCubicMeters',
            'previousReading'
        ));
    }
}