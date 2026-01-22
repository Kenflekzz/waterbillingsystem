<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BehavioralData;
use App\Models\Billings;
use Illuminate\Support\Facades\DB;
use App\Events\SensorDataReceived;
use App\Models\Clients;

class BehaviorController extends Controller
{
    /**
     * Fetch behavioral data for chart display (sensor + billing combined)
     */
    public function data(Request $request)
{
    $year     = $request->year;
    $month    = $request->month;
    $week     = $request->week;
    $day      = $request->day;
    $barangay = $request->barangay;
    $consumer = $request->get('consumer', session('demo_consumer')); 

    // Define timezone for all queries
    $timezone = 'Asia/Manila';
    
    // 1. Behavioral Data
    $behaviorData = BehavioralData::leftJoin('clients', 'behavioral_data.user_id', '=', 'clients.id')
        ->select(
            'behavioral_data.created_at',
            'behavioral_data.value',
            'behavioral_data.user_id',
            'clients.barangay',
            DB::raw("'behavioral' as source")
        );

    // Apply filters to behavioral data
    if ($consumer) {
        $behaviorData->where('behavioral_data.user_id', $consumer);
    }
    if ($barangay) {
        $behaviorData->where('clients.barangay', $barangay);
    }
    if ($year) {
        $behaviorData->whereYear(DB::raw("CONVERT_TZ(behavioral_data.created_at, '+00:00', '$timezone')"), $year);
    }
    if ($month) {
        $behaviorData->whereMonth(DB::raw("CONVERT_TZ(behavioral_data.created_at, '+00:00', '$timezone')"), $month);
    }
    if ($day) {
        $behaviorData->whereDay(DB::raw("CONVERT_TZ(behavioral_data.created_at, '+00:00', '$timezone')"), $day);
    }
    if ($week) {
        // Adjust the week filter to match ISO standard
        $behaviorData->whereRaw("WEEK(CONVERT_TZ(behavioral_data.created_at, '+00:00', '$timezone'), 1) = ?", [$week]);
    }

    $behaviorData = $behaviorData->orderBy('behavioral_data.created_at', 'asc')->get();

    // 2. Billing Data
    $billingData = Billings::join('clients', 'billings.client_id', '=', 'clients.id')
        ->select(
            'billings.billing_date as created_at',
            'billings.consumed as value',
            'clients.barangay',
            DB::raw("'billing' as source")
        );

    // Apply same filters to billing data
    if ($consumer) {
        $billingData->where('billings.client_id', $consumer);
    }
    if ($barangay) {
        $billingData->where('clients.barangay', $barangay);
    }
    if ($year) {
        $billingData->whereYear(DB::raw("CONVERT_TZ(billings.billing_date, '+00:00', '$timezone')"), $year);
    }
    if ($month) {
        $billingData->whereMonth(DB::raw("CONVERT_TZ(billings.billing_date, '+00:00', '$timezone')"), $month);
    }
    if ($day) {
        $billingData->whereDay(DB::raw("CONVERT_TZ(billings.billing_date, '+00:00', '$timezone')"), $day);
    }
    if ($week) {
        $billingData->whereRaw("WEEK(CONVERT_TZ(billings.billing_date, '+00:00', '$timezone'), 1) = ?", [$week]);
    }

    $billingData = $billingData->orderBy('billings.billing_date', 'asc')->get();

    // 3. Merge both datasets
    $merged = $behaviorData->merge($billingData)
        ->sortBy('created_at')
        ->values();

    // Format the created_at field into ISO8601 format
    $merged->transform(function ($item) {
        // Ensure date is in ISO 8601 format and time zone adjusted
        $item->created_at = \Carbon\Carbon::parse($item->created_at)->toIso8601String();
        return $item;
    });

    return response()->json($merged);
}


    /**
     * Store new behavioral (sensor) data manually or via websocket.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'metric_name' => 'nullable|string',
        'value'       => 'required|numeric',
        'meta'        => 'nullable|array',
        'user_id'     => 'required|exists:users,id',
        'created_at'  => 'nullable|date',   // 1. accept the date
    ]);

    $client = Clients::find($validated['user_id']);

    $row = BehavioralData::create([
        'metric_name' => $validated['metric_name'] ?? 'manual',
        'value'       => $validated['value'],
        'meta'        => $validated['meta'] ?? null,
        'user_id'     => $validated['user_id'],
        'barangay'    => $client->barangay,
        'created_at'  => $validated['created_at'] ?? now(), // 2. use it
    ]);

    event(new SensorDataReceived($row));

    return response()->json($row);
}
/**
 * Remember which consumer the admin is working on.
 * Called from the existing feed-random button.
 */
public function setDemoConsumer(Request $request)
{
    $request->validate(['consumer_id' => 'required|exists:clients,id']);
    session(['demo_consumer' => $request->consumer_id]);
    return response()->noContent();
}
}
