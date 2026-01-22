@extends('layouts.admin')

@section('title', 'Clients')
<link rel="icon" href="{{ asset('images/MAGALLANES_LOGO.png') }}" type="image/x-icon">

@section('content')
    <h1 class="mt-4">Clients</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Clients</li>
    </ol>

    <div class="mb-3 text-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClientModal">
            <i class="fas fa-plus"></i> Add Client
        </button>
    </div>
    <div class="mb-3">
    <form method="GET" action="{{ route('admin.clients.index') }}" class="form-inline" id="filterForm">
        <label for="status" class="form-label me-2">Filter by Status:</label>
        <select name="status" id="status" class="form-select d-inline-block w-auto me-2">
            <option value="">-- All --</option>
            <option value="CURC" {{ request('status') == 'CURC' ? 'selected' : '' }}>CURC</option>
            <option value="CUT" {{ request('status') == 'CUT' ? 'selected' : '' }}>CUT</option>
        </select>
    </form>
</div>

<div class="mb-3 text-end">
    <a href="{{ route('admin.print_clients.print', ['status' => request('status')]) }}" target="_blank" class="btn btn-primary">
        <i class="fas fa-print"></i> Print Clients Table
    </a>
</div>


    <div class="card mb-4">
        <div class="card-body">
            Below is the table showing all clients records.
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i> Clients Records
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-bordered">
                {{-- ==========  TABLE HEADERS  ========== --}}
<thead>
    <tr>
        <th>Group</th>
        <th>Meter No.</th>
        <th>Meter Status</th>        {{-- NEW --}}
        <th>Replacement Date</th>    {{-- NEW --}}
        <th>Client Full Name</th>
        <th>Barangay</th>
        <th>Purok</th>
        <th>Contact Number</th>
        <th>Date Cut</th>
        <th>Install Date</th>
        <th>Meter Series</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
</thead>

{{-- ==========  TABLE BODY  ========== --}}
<tbody>
    @foreach($clients as $client)
        <tr>
            <td>{{ $client->group }}</td>
            <td>{{ $client->meter_no }}</td>
            <td>{{ ucfirst($client->meter_status ?? 'old') }}</td>     {{-- NEW --}}
            <td>{{ $client->replacement_date ? \Carbon\Carbon::parse($client->replacement_date)->format('M d, Y') : '—' }}</td> {{-- NEW --}}
            <td>{{ $client->full_name }}</td>
            <td>{{ $client->barangay }}</td>
            <td>{{ $client->purok }}</td>
            <td>{{ $client->contact_number }}</td>
            <td>{{ $client->date_cut }}</td>
            <td>{{ $client->installation_date }}</td>
            <td>{{ $client->meter_series }}</td>
            <td class="text-center">
                @if($client->status == 'CURC')
                    <span class="text-success fw-bold">CURC</span>
                @elseif($client->status == 'CUT')
                    <span class="text-danger fw-bold">CUT</span>
                @else
                    <span class="text-muted">{{ $client->status }}</span>
                @endif
            </td>
            <td class="text-center">
                <!-- View -->
                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewClientModal{{ $client->id }}" title="View">
                    <i class="fas fa-eye"></i>
                </button>
                <!-- Edit -->
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editClientModal{{ $client->id }}" title="Edit">
                    <i class="fas fa-pencil-alt"></i>
                </button>
                <!-- Delete -->
                <form action="{{ route('admin.clients.destroy', $client->id) }}" method="POST" class="d-inline delete-client-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </form>
            </td>
        </tr>

        {{-- ==========  EDIT MODAL  ========== --}}
        <div class="modal fade" id="editClientModal{{ $client->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('admin.clients.update', $client->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Client</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            {{-- existing fields --}}
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="full_name" value="{{ $client->full_name }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Meter No.</label>
                                <input type="text" class="form-control" name="meter_no" value="{{ $client->meter_no }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Group</label>
                                <input type="text" class="form-control" name="group" value="{{ $client->group }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Barangay</label>
                                <input type="text" class="form-control" name="barangay" value="{{ $client->barangay }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Purok</label>
                                <input type="text" class="form-control" name="purok" value="{{ $client->purok }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contact Number</label>
                                <input type="text" class="form-control" name="contact_number" value="{{ $client->contact_number }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date Cut</label>
                                <input type="date" class="form-control" name="date_cut" value="{{ $client->date_cut }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Installation Date</label>
                                <input type="date" class="form-control" name="installation_date" value="{{ $client->installation_date }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Meter Series</label>
                                <input type="text" class="form-control" name="meter_series" value="{{ $client->meter_series }}" required>
                            </div>

                            {{-- NEW FIELDS --}}
                            <div class="mb-3">
                                <label class="form-label">Meter Status</label>
                                <select class="form-select" name="meter_status" required>
                                    <option value="old" {{ ($client->meter_status ?? 'old') === 'old' ? 'selected' : '' }}>Old</option>
                                    <option value="replacement" {{ ($client->meter_status ?? 'old') === 'replacement' ? 'selected' : '' }}>Replacement</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Replacement Date</label>
                                <input type="date" class="form-control" name="replacement_date" value="{{ $client->replacement_date }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="CURC" {{ $client->status == 'CURC' ? 'selected' : '' }}>CURC</option>
                                    <option value="CUT" {{ $client->status == 'CUT' ? 'selected' : '' }}>CUT</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Update</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</tbody>
            </table>
            <div class="mt-3">
                {{ $clients->links() }}
            </div>
        </div>
    </div>

    <!-- Add Client Modal -->
    <div class="modal fade" id="addClientModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.clients.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Client</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" value="{{ old('full_name') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Meter Number</label>
                            <input type="text" class="form-control" name="meter_no" value="{{ old('meter_no') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Group</label>
                            <input type="text" class="form-control" name="group" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Barangay</label>
                            <input type="text" class="form-control" name="barangay" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Purok</label>
                            <input type="text" class="form-control" name="purok" required>
                        </div>
                         <div class="mb-3">
                            <label class="form-label">Contact_Number</label>
                            <input type="text" class="form-control" name="contact_number" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date Cut</label>
                            <input type="date" class="form-control" name="date_cut">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Installation Date</label>
                            <input type="date" class="form-control" name="installation_date">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Meter Series</label>
                            <input type="text" class="form-control" name="meter_series" required>
                        </div>
                        
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Add Client</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@vite('resources/css/clients.css')

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
    <script src="{{ asset('admin/js/datatables-simple-demo.js') }}"></script>
    <script>
        window.showSuccessModal = @json(session('success') ? true : false);
        window.successMessage = @json(session('success') ?? '');
        window.showErrorModal = @json(session('error') ? true : false);
        window.errorMessage = @json(session('error') ?? '');
        window.showAddClientModalOnLoad = @json($errors->any() ? true : false);
    </script>
    @vite('resources/js/clients.js')
@endsection
