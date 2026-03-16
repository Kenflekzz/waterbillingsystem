<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BehavioralData;
use App\Models\Billings;
use Illuminate\Support\Facades\DB;
use App\Events\SensorDataReceived;
use App\Models\Clients;
use App\Models\FlowReading;

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

    $timezone = 'Asia/Manila';

    // 1. Behavioral Data — unchanged
    $behaviorData = BehavioralData::leftJoin('clients', 'behavioral_data.user_id', '=', 'clients.id')
        ->select(
            'behavioral_data.created_at',
            'behavioral_data.value',
            'behavioral_data.user_id',
            'clients.barangay',
            DB::raw("'behavioral' as source")
        );

    if ($consumer) $behaviorData->where('behavioral_data.user_id', $consumer);
    if ($barangay) $behaviorData->where('clients.barangay', $barangay);
    if ($year)     $behaviorData->whereYear(DB::raw("CONVERT_TZ(behavioral_data.created_at, '+00:00', '$timezone')"), $year);
    if ($month)    $behaviorData->whereMonth(DB::raw("CONVERT_TZ(behavioral_data.created_at, '+00:00', '$timezone')"), $month);
    if ($day)      $behaviorData->whereDay(DB::raw("CONVERT_TZ(behavioral_data.created_at, '+00:00', '$timezone')"), $day);
    if ($week)     $behaviorData->whereRaw("WEEK(CONVERT_TZ(behavioral_data.created_at, '+00:00', '$timezone'), 1) = ?", [$week]);

    $behaviorData = $behaviorData->orderBy('behavioral_data.created_at', 'asc')->get();

    // 2. Billing Data — unchanged
    $billingData = Billings::join('clients', 'billings.client_id', '=', 'clients.id')
        ->select(
            'billings.billing_date as created_at',
            'billings.consumed as value',
            'clients.id as user_id',
            'clients.barangay',
            DB::raw("'billing' as source")
        );

    if ($consumer) $billingData->where('billings.client_id', $consumer);
    if ($barangay) $billingData->where('clients.barangay', $barangay);
    if ($year)     $billingData->whereYear(DB::raw("CONVERT_TZ(billings.billing_date, '+00:00', '$timezone')"), $year);
    if ($month)    $billingData->whereMonth(DB::raw("CONVERT_TZ(billings.billing_date, '+00:00', '$timezone')"), $month);
    if ($day)      $billingData->whereDay(DB::raw("CONVERT_TZ(billings.billing_date, '+00:00', '$timezone')"), $day);
    if ($week)     $billingData->whereRaw("WEEK(CONVERT_TZ(billings.billing_date, '+00:00', '$timezone'), 1) = ?", [$week]);

    $billingData = $billingData->orderBy('billings.billing_date', 'asc')->get();

    // 3. Flow Meter (Sensor) Data — NEW
    $flowData = \App\Models\FlowReading::join('clients', 'flow_readings.client_id', '=', 'clients.id')
        ->select(
            'flow_readings.created_at',
            DB::raw('flow_readings.cubic_meter as value'), // use cubic_meter as the value
            'flow_readings.client_id as user_id',
            'clients.barangay',
            DB::raw("'flow_meter' as source")
        );

    if ($consumer) $flowData->where('flow_readings.client_id', $consumer);
    if ($barangay) $flowData->where('clients.barangay', $barangay);
    if ($year)     $flowData->whereYear(DB::raw("CONVERT_TZ(flow_readings.created_at, '+00:00', '$timezone')"), $year);
    if ($month)    $flowData->whereMonth(DB::raw("CONVERT_TZ(flow_readings.created_at, '+00:00', '$timezone')"), $month);
    if ($day)      $flowData->whereDay(DB::raw("CONVERT_TZ(flow_readings.created_at, '+00:00', '$timezone')"), $day);
    if ($week)     $flowData->whereRaw("WEEK(CONVERT_TZ(flow_readings.created_at, '+00:00', '$timezone'), 1) = ?", [$week]);

    $flowData = $flowData->orderBy('flow_readings.created_at', 'asc')->get();

    // 4. Merge all three datasets
    $merged = $behaviorData
        ->merge($billingData)
        ->merge($flowData)       // ← sensor data added here
        ->sortBy('created_at')
        ->values();

    // Format dates to ISO8601
    $merged->transform(function ($item) {
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
