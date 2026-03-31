<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IotDevice;
use App\Models\Clients;
use App\Models\FlowReading;

class IotDeviceController extends Controller
{
    // Show the flow meter page
    public function index()
    {
        $devices = IotDevice::with('client')->get();
        $clients = Clients::where('status', '!=', 'CUT')->get();

        // Get total volume and total cubic meter per consumer per device
        $consumptions = FlowReading::with(['client', 'iotDevice'])
            ->selectRaw('
                client_id,
                iot_device_id,
                SUM(total_volume)  as total_volume,
                SUM(cubic_meter)   as total_cubic_meter,
                MAX(updated_at)    as updated_at
            ')
            ->groupBy('client_id', 'iot_device_id')
            ->get();

        return view('admin.flowmeter', compact('devices', 'clients', 'consumptions'));
    }

    // Add new IoT device
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_name' => 'required|string|max:255',
            'ip_address'  => 'required|ip',
            'port'        => 'required|integer|min:1|max:65535',
            'client_id'   => 'nullable|exists:clients,id',
        ]);

        IotDevice::create($validated);

        return redirect()->back()->with('success', 'IoT Device added successfully.');
    }

    // Assign device to a consumer
    public function assign(Request $request, $deviceId)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
        ]);

        $device = IotDevice::findOrFail($deviceId);
        $device->update(['client_id' => $validated['client_id']]);

        return response()->json([
            'success' => true,
            'message' => 'Device assigned successfully.',
        ]);
    }

    // Delete device
    public function destroy($deviceId)
    {
        IotDevice::findOrFail($deviceId)->delete();
        return redirect()->back()->with('success', 'Device deleted successfully.');
    }

    // Get all devices as JSON (for JS)
    public function getDevices()
    {
        $devices = IotDevice::with('client')
            ->where('is_active', true)
            ->get()
            ->map(fn($d) => [
                'id'          => $d->id,
                'device_name' => $d->device_name,
                'ws_url'      => $d->ws_url,
                'client_name' => $d->client?->full_name ?? 'Unassigned',
                'client_id'   => $d->client_id,
            ]);

        return response()->json($devices);
    }

    public function consumptions()
    {
        $consumptions = FlowReading::with(['client', 'iotDevice'])
            ->selectRaw('
                client_id,
                iot_device_id,
                SUM(total_volume)  as total_volume,
                SUM(cubic_meter)   as total_cubic_meter,
                MAX(updated_at)    as updated_at
            ')
            ->groupBy('client_id', 'iot_device_id')
            ->get()
            ->map(fn($c) => [
                'device_name'       => $c->iotDevice?->device_name ?? 'N/A',
                'consumer'          => $c->client?->full_name ?? 'Unassigned',
                'barangay'          => $c->client?->barangay ?? '--',
                'purok'             => $c->client?->purok ?? '--',
                'total_volume'      => $c->total_volume ?? 0,
                'total_cubic_meter' => $c->total_cubic_meter ?? 0,
                'updated_at'        => $c->updated_at ?? '--',
            ]);

        return response()->json($consumptions);
    }
}