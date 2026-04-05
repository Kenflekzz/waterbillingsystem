@extends('layouts.admin')
<link rel="icon" href="{{ asset('images/MAGALLANES_LOGO.png') }}" type="image/x-icon">
@section('title', 'Flow Meter')

@section('content')
<h1 class="mt-4">Flow Meter</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Flow Meter</li>
</ol>

{{-- Add Device Button --}}
<div class="mb-3 text-end">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDeviceModal">
        <i class="fas fa-plus"></i> Add IoT Device
    </button>
</div>

{{-- Live Chart Section --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <i class="fas fa-tint me-1"></i>
                    Live Flow Rate Chart
                    <span id="live-status-dot" class="ms-2 rounded-circle d-inline-block"
                          style="width:10px; height:10px; background:#f44336; vertical-align:middle;"></span>
                    <span id="live-status-text" class="ms-1" style="font-size:0.75rem; color:#f44336;">Disconnected</span>
                </div>
                <div style="min-width: 250px;">
                    <select id="deviceSelect" class="form-select form-select-sm">
                        <option value="">-- Select Device --</option>
                        @foreach($devices as $device)
                            <option value="{{ $device->id }}"
                                    data-name="{{ $device->device_name }}">
                                {{ $device->device_name }}
                                ({{ $device->client?->full_name ?? 'Unassigned' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background: #e3f2fd;">
                            <div style="font-size: 2rem; font-weight: 700; color: #1565c0;" id="live-flow-rate">--</div>
                            <div style="font-size: 0.8rem; color: #666;">L/min</div>
                            <div style="font-size: 0.75rem; color: #999;">Flow Rate</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background: #e8f5e9;">
                            <div style="font-size: 2rem; font-weight: 700; color: #2e7d32;" id="live-total-volume">--</div>
                            <div style="font-size: 0.8rem; color: #666;">Liters</div>
                            <div style="font-size: 0.75rem; color: #999;">Total Volume</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background: #fff3e0;">
                            <div style="font-size: 2rem; font-weight: 700; color: #e65100;" id="live-cubic-meter">--</div>
                            <div style="font-size: 0.8rem; color: #666;">Cu.m</div>
                            <div style="font-size: 0.75rem; color: #999;">Cubic Meter</div>
                        </div>
                    </div>
                </div>
                <canvas id="flowChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Consumer Consumption Table --}}
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        Consumer Consumption (Cu.m)
    </div>
    <div class="card-body">
        <table class="table table-bordered" id="consumptionTable">
            <thead>
                <tr>
                    <th>Device Name</th>
                    <th>Consumer</th>
                    <th>Barangay</th>
                    <th>Purok</th>
                    <th>Total Volume (L)</th>
                    <th>Total (Cu.m)</th>
                    <th>Last Reading</th>
                </tr>
            </thead>
            <tbody>
                @forelse($consumptions as $consumption)
                    <tr>
                        <td>{{ $consumption->iotDevice?->device_name ?? 'N/A' }}</td>
                        <td>{{ $consumption->client?->full_name ?? 'Unassigned' }}</td>
                        <td>{{ $consumption->client?->barangay ?? '--' }}</td>
                        <td>{{ $consumption->client?->purok ?? '--' }}</td>
                        <td>{{ number_format($consumption->total_volume ?? 0, 2) }}</td>
                        <td><strong>{{ number_format($consumption->total_cubic_meter ?? 0, 4) }}</strong></td>
                        <td>{{ $consumption->updated_at ?? '--' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No readings yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Registered IoT Devices Table --}}
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-microchip me-1"></i>
        Registered IoT Devices
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Device Name</th>
                    <th>IP Address</th>
                    <th>Port</th>
                    <th>Assigned Consumer</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($devices as $device)
                    <tr>
                        <td>{{ $device->device_name }}</td>
                        <td>{{ $device->ip_address }}</td>
                        <td>{{ $device->port }}</td>
                        <td>{{ $device->client?->full_name ?? 'Unassigned' }}</td>
                        <td>
                            <span class="badge bg-{{ $device->is_active ? 'success' : 'secondary' }}">
                                {{ $device->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            {{-- Assign Consumer --}}
                            <button class="btn btn-sm btn-warning assign-btn"
                                    data-device-id="{{ $device->id }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#assignModal{{ $device->id }}">
                                <i class="fas fa-user-plus"></i> Assign
                            </button>

                            {{-- Delete --}}
                            <form action="{{ url('admin/flowmeter/devices/' . $device->id) }}"
                                  method="POST" class="d-inline delete-device-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    {{-- Assign Modal per device --}}
                    <div class="modal fade" id="assignModal{{ $device->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Assign Consumer — {{ $device->device_name }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <label class="form-label">Select Consumer</label>
                                    <select class="form-select assign-client-select" data-device-id="{{ $device->id }}">
                                        <option value="">-- Select Consumer --</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}"
                                                {{ $device->client_id == $client->id ? 'selected' : '' }}>
                                                {{ $client->full_name }} ({{ $client->meter_no }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-success save-assign-btn"
                                            data-device-id="{{ $device->id }}">
                                        Save
                                    </button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>

                @empty
                    <tr>
                        <td colspan="6" class="text-center">No devices registered yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Device Modal --}}
<div class="modal fade" id="addDeviceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ url('admin/flowmeter/devices') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add IoT Device</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Device Name *</label>
                        <input type="text" name="device_name" class="form-control" placeholder="e.g. ESP32-001" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">IP Address *</label>
                        <input type="text" name="ip_address" class="form-control" placeholder="e.g. 192.168.1.105" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Port *</label>
                        <input type="number" name="port" class="form-control" value="81" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assign Consumer (optional)</label>
                        <select name="client_id" class="form-select">
                            <option value="">-- Select Consumer --</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">
                                    {{ $client->full_name }} ({{ $client->meter_no }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save Device</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@vite('resources/js/flowmeter.js')
<input type="hidden" id="hasSuccess" value="{{ !$errors->any() && session('success') ? '1' : '0' }}">
<input type="hidden" id="successMessage" value="{{ session('success') }}">
<input type="hidden" id="hasErrors" value="{{ $errors->any() ? '1' : '0' }}">
<input type="hidden" id="errorMessages" value="{{ implode(' | ', $errors->all()) }}">
@endsection