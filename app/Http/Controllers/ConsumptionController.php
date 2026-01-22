<?php

namespace App\Http\Controllers;

use App\Models\BehavioralData;
use App\Models\Billings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ConsumptionController extends Controller
{
    public function index(Request $request)
    {
        $consumerId = Auth::id();
        $tz = 'Asia/Manila';

        /* ================= BASE QUERIES ================= */

        $billing = Billings::select(
            'billing_date as date',
            'consumed as value',
            DB::raw("'billing' as source")
        )->where('client_id', $consumerId);

        $sensor = BehavioralData::select(
            'created_at as date',
            'value',
            DB::raw("'sensor' as source")
        )->where('user_id', $consumerId);

        /* ================= FILTERS ================= */

        if ($request->year) {
            $billing->whereYear('billing_date', $request->year);
            $sensor->whereYear('created_at', $request->year);
        }

        if ($request->month) {
            $billing->whereMonth('billing_date', $request->month);
            $sensor->whereMonth('created_at', $request->month);
        }

        if ($request->day) {
            $billing->whereDay('billing_date', $request->day);
            $sensor->whereDay('created_at', $request->day);
        }

        /* ================= MERGE DATA (NO UNION) ================= */

        $rows = $billing->get()
            ->merge($sensor->get())
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

        $limit = optional(Auth::user()->client)->limit ?? 30;

        $todayStatus = $currentConsumption >= $limit ? 'danger' : 'success';
        $todayText   = $currentConsumption >= $limit
            ? 'HIGH • Above Limit'
            : 'NORMAL • Within Limit';

        $yesterdayStatus = $previousConsumption >= $limit ? 'danger' : 'success';
        $yesterdayText   = $previousConsumption >= $limit
            ? 'HIGH • Above Limit'
            : 'NORMAL • Within Limit';

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
            'yesterdayText'
        ));
    }
}
