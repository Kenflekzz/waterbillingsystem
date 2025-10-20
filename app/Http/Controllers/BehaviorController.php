<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BehavioralData;
use App\Events\SensorDataReceived; // fixed typo in class name

class BehaviorController extends Controller
{
    public function data(Request $request)
    {
        $limit    = (int) $request->get('limit', 50);

        $year     = $request->get('year');
        $month    = $request->get('month');
        $week     = $request->get('week');
        $day      = $request->get('day');
        $barangay = $request->get('barangay');
        $consumer = $request->get('consumer');

        $query = BehavioralData::orderBy('created_at', 'asc');

        if ($year) {
            $query->whereYear('created_at', $year);
        }
        if ($month) {
            $query->whereMonth('created_at', $month);
        }
        if ($week) {
            $query->whereRaw('WEEK(created_at, 1) = ?', [$week]);
        }
        if ($day) {
            $query->whereDay('created_at', $day);
        }
        if ($barangay) {
            $query->where('barangay', $barangay);
        }
        if ($consumer) {
            $query->where('client_id', $consumer);
        }

        return response()->json($query->limit($limit)->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'metric_name' => 'nullable|string',
            'value'       => 'required|numeric',
            'meta'        => 'nullable|array'
        ]);

        $row = BehavioralData::create([
            'metric_name' => $validated['metric_name'] ?? 'manual',
            'value'       => $validated['value'],
            'meta'        => $validated['meta'] ?? null,
        ]);

        // push to websocket channel “sensor-data”
        event(new SensorDataReceived($row));

        return response()->json($row);
    }
}
