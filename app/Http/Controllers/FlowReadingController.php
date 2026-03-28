<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FlowReading;
use App\Models\IotDevice;
use App\Models\Clients;

class FlowReadingController extends Controller
{
    // Save reading from sidebar JS
    public function store(Request $request)
    {
        $validated = $request->validate([
            'iot_device_id' => 'required|exists:iot_devices,id',
            'flow_rate'     => 'required|numeric|min:0',
            'total_volume'  => 'required|numeric|min:0',
        ]);

        // Get client_id from the assigned device
        $device = IotDevice::findOrFail($validated['iot_device_id']);

        // Convert liters to cubic meters (÷ 1000)
        $cubicMeter = round($validated['total_volume'] / 1000, 4);

        $reading = FlowReading::create([
            'client_id'     => $device->client_id,
            'iot_device_id' => $device->id,
            'flow_rate'     => $validated['flow_rate'],
            'total_volume'  => $validated['total_volume'],
            'cubic_meter'   => $cubicMeter,
        ]);

        return response()->json([
            'success'     => true,
            'data'        => $reading,
            'cubic_meter' => $cubicMeter,
        ]);
    }

    // Get latest reading for a specific device
    public function latest($deviceId)
    {
        $reading = FlowReading::where('iot_device_id', $deviceId)
            ->latest()
            ->first();

        return response()->json($reading);
    }

    public function chartData(Request $request)
    {
        $readings = FlowReading::with('client')
            ->select('client_id', 'cubic_meter', 'created_at')
            ->get()
            ->map(fn($r) => [
                'user_id'    => $r->client_id,
                'value'      => $r->cubic_meter,
                'created_at' => $r->created_at,
                'barangay'   => $r->client?->barangay,
            ]);

        return response()->json($readings);
    }
}