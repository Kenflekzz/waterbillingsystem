<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FlowReading;
use App\Models\IotDevice;
use App\Models\Clients;
use App\Models\Users;
use App\Mail\HighFlowAlert;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class FlowReadingController extends Controller
{
    // Flow rate threshold in L/min
    const FLOW_THRESHOLD = 50.0;

    // Cooldown tracker — 1 email per device per 10 minutes
    protected static $lastAlertSent = [];

    // Save reading from JS
    public function store(Request $request)
    {
        $validated = $request->validate([
            'iot_device_id' => 'required|exists:iot_devices,id',
            'flow_rate'     => 'required|numeric|min:0',
            'total_volume'  => 'required|numeric|min:0',
        ]);

        $device     = IotDevice::findOrFail($validated['iot_device_id']);
        $cubicMeter = round($validated['total_volume'] / 1000, 4);

        $reading = FlowReading::create([
            'client_id'     => $device->client_id,
            'iot_device_id' => $device->id,
            'flow_rate'     => $validated['flow_rate'],
            'total_volume'  => $validated['total_volume'],
            'cubic_meter'   => $cubicMeter,
        ]);

        Log::info('FlowReading store called', [
        'flow_rate'    => $validated['flow_rate'],
        'threshold'    => self::FLOW_THRESHOLD,
        'exceeds'      => $validated['flow_rate'] >= self::FLOW_THRESHOLD,
        'client_id'    => $device->client_id,
    ]);

        // ← This was missing in your version
        $this->checkThresholdAndAlert(
            $device,
            $validated['flow_rate'],
            $cubicMeter
        );

        return response()->json([
            'success'     => true,
            'data'        => $reading,
            'cubic_meter' => $cubicMeter,
            'alert_sent'  => $validated['flow_rate'] >= self::FLOW_THRESHOLD,
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

    protected function checkThresholdAndAlert($device, $flowRate, $cubicMeter)
{
    if ($flowRate < self::FLOW_THRESHOLD) return;
    if (!$device->client_id) return;

    $client = Clients::find($device->client_id);
    if (!$client) {
        \Log::info('Client not found', ['client_id' => $device->client_id]);
        return;
    }

    // Use user_id from clients table to find the user ← key fix
    $user = Users::find($client->user_id);
    if (!$user) {
        \Log::info('User not found', ['user_id' => $client->user_id]);
        return;
    }
    if (!$user->email) {
        \Log::info('User has no email', ['user_id' => $user->id]);
        return;
    }

    $deviceId = $device->id;
    $now      = time();
    $cooldown = 10 * 60;

    if (
        isset(self::$lastAlertSent[$deviceId]) &&
        ($now - self::$lastAlertSent[$deviceId]) < $cooldown
    ) {
        \Log::info('Cooldown active, skipping email');
        return;
    }

    try {
        Mail::to($user->email)->send(new HighFlowAlert(
            $user->first_name . ' ' . $user->last_name,
            $flowRate,
            $cubicMeter,
            self::FLOW_THRESHOLD
        ));
        \Log::info('Email sent successfully to ' . $user->email);
    } catch (\Exception $e) {
        \Log::error('Email failed: ' . $e->getMessage());
    }

    self::$lastAlertSent[$deviceId] = $now;
}
}