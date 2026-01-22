@extends('layouts.admin')

@section('title', 'Billings')
 <link rel="icon" href="{{ asset('images/MAGALLANES_LOGO.png') }}" type="image/x-icon">

    @section('content')
    <h1 class="mt-4">Billings</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{url('admin/dashboard')}}">Dashboard</a></li>
        <li class="breadcrumb-item active">Billings</li>
    </ol>
    <div class="mb-3 text-end">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBillingModal">
        <i class="fas fa-plus"></i> Add Billing
    </button>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            Below is the table showing all billing records.
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Billing Records
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-bordered">
                <thead>
                    <tr>
                        <th>Billing ID</th>
                        <th>Meter No.</th>
                        <th>Client Full Name</th>
                        <th>Barangay</th>
                        <th>Purok</th>
                        <th>Billing Date</th>
                        <th>Previous Read</th>
                        <th>Present Read</th>
                        <th>Consumed</th>
                        <th>Current Bill</th>
                        <th>Total Penalty</th>
                        <th>Maintenance Cost</th>
                        <th>Total Amount</th>
                        <th>Installation Fee</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($billings as $billing)
                        <tr>
                            <td>{{ $billing->billing_id }}</td>
                            <td>{{ $billing->client->meter_no }}</td>
                            <td>{{ $billing->client->full_name }}</td>
                            <td>{{ $billing->client->barangay }}</td>
                            <td>{{ $billing->client->purok }}</td>
                            <td>{{ $billing->billing_date }}</td>
                            <td>{{ $billing->previous_reading}}</td>
                            <td>{{ $billing->present_reading }}</td>
                            <td>{{ $billing->consumed }}</td>
                            <td>{{ $billing->current_bill }}</td>
                            <td>{{ $billing->total_penalty }}</td>
                            <td>{{ $billing->maintenance_cost }}</td>
                            <td>{{ $billing->total_amount }}</td>
                            <td>{{$billing->installation_fee}}</td>
                            <td class="text-center">
                                <!-- View (Eye Icon) -->
                                <button class="btn btn-sm btn-info" title="View" data-bs-toggle="modal" data-bs-target="#viewBillingModal{{ $billing->id }}">
                                    <i class="fas fa-eye"></i>
                                </button>

                                <!-- Print (Printer Icon) -->
                               <a href="{{ route('admin.billings.print', $billing->id) }}" target="_blank" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-print"></i>
                                </a>


                                <!-- Delete (Trash Icon) -->
                               <form action="{{ route('admin.billings.destroy', $billing->id) }}" 
                                    method="POST" 
                                    class="d-inline delete-billing-form"
                                    data-no-loader = "1">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                            </form>

                            </td>
                        </tr>
                        <!-- View Billing Modal -->
                        <div class="modal fade" id="viewBillingModal{{ $billing->id }}" tabindex="-1" aria-labelledby="viewBillingModalLabel{{ $billing->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Billing Details - {{ $billing->billing_id }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body row">
                                        <div class="col-md-6 mb-2"><strong>Client Name:</strong> {{ $billing->client->full_name ?? 'N/A' }}</div>
                                        <div class="col-md-6 mb-2"><strong>Meter No:</strong> {{ $billing->client->meter_no ?? 'N/A' }}</div>
                                        <div class="col-md-6 mb-2"><strong>Billing Date:</strong> {{ $billing->billing_date }}</div>
                                        <div class="col-md-6 mb-2"><strong>Previous Reading:</strong> {{ $billing->previous_reading }}</div>
                                        <div class="col-md-6 mb-2"><strong>Present Reading:</strong> {{ $billing->present_reading }}</div>
                                        <div class="col-md-6 mb-2"><strong>Consumed:</strong> {{ $billing->consumed }}</div>
                                        <div class="col-md-6 mb-2"><strong>Current Bill:</strong> {{ $billing->current_bill }}</div>
                                        <div class="col-md-6 mb-2"><strong>Total Penalty:</strong> {{ $billing->total_penalty }}</div>
                                        <div class="col-md-6 mb-2"><strong>Maintenance Cost:</strong> {{ $billing->maintenance_cost }}</div>
                                        <div class="col-md-6 mb-2"><strong>Total Amount:</strong> {{ $billing->total_amount }}</div>
                                        <div class="col-md-6 mb-2"><strong>Installation Fee:</strong> {{ $billing->installation_fee }}</div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @endforeach
                </tbody>
            </table>

                <!-- Add Billing Modal -->
            <div class="modal fade" id="addBillingModal" tabindex="-1" aria-labelledby="addBillingModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form action="{{ route('admin.billings.store') }}" method="POST">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add New Billing</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Select Client *</label>
                                        <select class="form-select" name="client_id" id="clientSelect" required>
                                            <option value="">-- Select Client --</option>
                                            @foreach($clients as $client)
                                                @if($client->status !== 'CUT')
                                                    <option value="{{ $client->id }}"
                                                        data-meter="{{ $client->meter_no }}"
                                                        data-fullname="{{ $client->full_name }}"
                                                        data-barangay="{{ $client->barangay }}"
                                                        data-purok="{{ $client->purok }}">
                                                        {{ $client->full_name }} ({{ $client->meter_no }})
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Billing ID *</label>
                                        <input type="text" name="billing_id" id="billing_id" class="form-control" readonly>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Billing Date *</label>
                                        <input type="date"  name="billing_date" id="billing_date" class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Due Date</label>
                                        <input type="date" id="due_date" name="due_date" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Reading Date</label>
                                        <input type="date" id="reading_date" name="reading_date" class="form-control">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Meter No.</label>
                                        <input type="text" id="meter_no" class="form-control" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" id="full_name" class="form-control" readonly>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Barangay</label>
                                        <input type="text" id="barangay" class="form-control" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Purok</label>
                                        <input type="text" id="purok" class="form-control" readonly>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Previous Reading *</label>
                                        <input type="number" name="previous_reading" id="previous_reading" class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Present Reading *</label>
                                        <input type="number" name="present_reading" id="present_reading" class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Consumed</label>
                                        <input type="number" name="consumed" id="consumed" class="form-control" readonly>
                                    </div>
                                </div>

                                <hr>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Arrears</label>
                                        <input type="text" name="arrears" id="arrears" class="form-control" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Total Penalty</label>
                                        <input type="number" name="total_penalty" id="total_penalty" class="form-control" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Current Bill *</label>
                                        <input type="number" name="current_bill" class="form-control" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Installation Fee</label>
                                        <input type="number" name="installation_fee" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Maintenance Cost</label>
                                        <input type="number" name="maintenance_cost" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Total Amount</label>
                                        <input type="number" name="total_amount" id="total_amount" class="form-control" readonly>
                                    </div>
                                </div>

                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success">Save Billing</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>



            <div class="mt-3">
                {{ $billings->links() }}
            </div>
        </div>
    </div>
    @endsection

    @vite('resources/css/billings.css')
    
    @section('scripts')
    <!-- DataTables Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
    <script src="{{ asset('admin/js/datatables-simple-demo.js') }}"></script>
    <input type="hidden" id="hasSuccess" value="{{ session('success') ? '1' : '0' }}">
    <input type="hidden" id="successMessage" value="{{ session('success') }}">
    <input type="hidden" id="hasErrors" value="{{ $errors->any() ? '1' : '0' }}">
    <input type="hidden" id="errorMessages" value="{{ implode(' | ', $errors->all()) }}">
    @vite('resources/js/billings.js')
@endsection



