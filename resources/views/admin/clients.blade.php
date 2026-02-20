@extends('layouts.admin')

@section('title', 'Clients')
<link rel="icon" href="{{ asset('images/MAGALLANES_LOGO.png') }}" type="image/x-icon">

@section('content')
    <h1 class="mt-4">Clients</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Clients</li>
    </ol>

    {{-- Success Message (Keep this) --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Edit/Update Error Messages (Not related to Add) --}}
    @if(session('error') && !session('add_client_error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

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
                <thead>
                    <tr>
                        <th>Group</th>
                        <th>Meter No.</th>
                        <th>Old Meter No.</th>
                        <th>Meter Status</th>
                        <th>Replacement Date</th>
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
                <tbody>
                    @foreach($clients as $client)
                        <tr>
                            <td>{{ $client->group }}</td>
                            <td><span class="fw-bold">{{ $client->meter_no }}</span></td>
                            <td>
                                @if($client->old_meter_no)
                                    <span class="text-muted text-decoration-line-through small">{{ $client->old_meter_no }}</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>{{ ucfirst($client->meter_status ?? 'old') }}</td>
                            <td>{{ $client->replacement_date ? \Carbon\Carbon::parse($client->replacement_date)->format('M d, Y') : '—' }}</td>
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
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewClientModal{{ $client->id }}" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editClientModal{{ $client->id }}" title="Edit">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <form action="{{ route('admin.clients.destroy', $client->id) }}" method="POST" class="d-inline delete-client-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this client?');">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        {{-- Edit Modal --}}
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
                                            @if($errors->any() && old('_method') == 'PUT' && old('client_id') == $client->id)
                                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                    <ul class="mb-0">
                                                        @foreach($errors->all() as $error)
                                                            <li>{{ $error }}</li>
                                                        @endforeach
                                                    </ul>
                                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                                </div>
                                            @endif

                                            <input type="hidden" name="client_id" value="{{ $client->id }}">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Full Name</label>
                                                <input type="text" class="form-control @error('full_name') is-invalid @enderror" name="full_name" value="{{ old('full_name', $client->full_name) }}" required>
                                                @error('full_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Current Meter No.</label>
                                                <input type="text" class="form-control @error('meter_no') is-invalid @enderror" name="meter_no" value="{{ old('meter_no', $client->meter_no) }}" required>
                                                @error('meter_no')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                @if($client->old_meter_no)
                                                    <div class="form-text text-muted">
                                                        Previous: <span class="text-decoration-line-through">{{ $client->old_meter_no }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Group</label>
                                                <input type="text" class="form-control @error('group') is-invalid @enderror" name="group" value="{{ old('group', $client->group) }}" required>
                                                @error('group')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Barangay</label>
                                                <input type="text" class="form-control @error('barangay') is-invalid @enderror" name="barangay" value="{{ old('barangay', $client->barangay) }}" required>
                                                @error('barangay')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Purok</label>
                                                <input type="text" class="form-control @error('purok') is-invalid @enderror" name="purok" value="{{ old('purok', $client->purok) }}" required>
                                                @error('purok')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Contact Number</label>
                                                <input type="text" class="form-control @error('contact_number') is-invalid @enderror" name="contact_number" value="{{ old('contact_number', $client->contact_number) }}" required>
                                                @error('contact_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Date Cut</label>
                                                <input type="date" class="form-control @error('date_cut') is-invalid @enderror" name="date_cut" value="{{ old('date_cut', $client->date_cut) }}">
                                                @error('date_cut')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Installation Date</label>
                                                <input type="date" class="form-control @error('installation_date') is-invalid @enderror" name="installation_date" value="{{ old('installation_date', $client->installation_date) }}" required>
                                                @error('installation_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Meter Series</label>
                                                <input type="text" class="form-control @error('meter_series') is-invalid @enderror" name="meter_series" value="{{ old('meter_series', $client->meter_series) }}" required>
                                                @error('meter_series')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Meter Status</label>
                                                <select class="form-select @error('meter_status') is-invalid @enderror" name="meter_status" required>
                                                    <option value="old" {{ old('meter_status', $client->meter_status ?? 'old') === 'old' ? 'selected' : '' }}>Old</option>
                                                    <option value="replacement" {{ old('meter_status', $client->meter_status ?? 'old') === 'replacement' ? 'selected' : '' }}>Replacement</option>
                                                </select>
                                                @error('meter_status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Replacement Date</label>
                                                <input type="date" class="form-control @error('replacement_date') is-invalid @enderror" name="replacement_date" value="{{ old('replacement_date', $client->replacement_date) }}">
                                                @error('replacement_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select class="form-select @error('status') is-invalid @enderror" name="status" required>
                                                    <option value="CURC" {{ old('status', $client->status) == 'CURC' ? 'selected' : '' }}>CURC</option>
                                                    <option value="CUT" {{ old('status', $client->status) == 'CUT' ? 'selected' : '' }}>CUT</option>
                                                </select>
                                                @error('status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
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

    <!-- Add Client Modal - ALL ERRORS HERE ONLY -->
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
                        {{-- Validation Errors (Add Form Only) --}}
                        @if($errors->any() && !old('_method'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong><i class="fas fa-exclamation-triangle me-2"></i> Please correct the following errors:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        {{-- Database Duplicate Errors --}}
                        @if(session('duplicate_contact'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong><i class="fas fa-database me-2"></i> Database Error:</strong>
                                The value '{{ session('duplicate_contact') }}' already exists for clients_contact_number_unique. Please use a unique value.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('duplicate_meter'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong><i class="fas fa-database me-2"></i> Database Error:</strong>
                                The value '{{ session('duplicate_meter') }}' already exists for clients_meter_no_unique. Please use a unique value.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        {{-- General Database Error (Add Form Only) --}}
                        @if(session('add_client_error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong><i class="fas fa-exclamation-circle me-2"></i> Error:</strong>
                                {{ session('add_client_error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control @error('full_name') is-invalid @enderror" name="full_name" value="{{ old('full_name') }}" required>
                            @error('full_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Meter Number</label>
                            <input type="text" class="form-control @error('meter_no') is-invalid @enderror @if(session('duplicate_meter')) is-invalid @endif" name="meter_no" value="{{ old('meter_no') }}" required>
                            @error('meter_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if(session('duplicate_meter'))
                                <div class="invalid-feedback">This meter number is already registered.</div>
                            @endif
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Group</label>
                            <input type="text" class="form-control @error('group') is-invalid @enderror" name="group" value="{{ old('group') }}" required>
                            @error('group')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Barangay</label>
                            <input type="text" class="form-control @error('barangay') is-invalid @enderror" name="barangay" value="{{ old('barangay') }}" required>
                            @error('barangay')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Purok</label>
                            <input type="text" class="form-control @error('purok') is-invalid @enderror" name="purok" value="{{ old('purok') }}" required>
                            @error('purok')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contact Number</label>
                            <input type="text" class="form-control @error('contact_number') is-invalid @enderror @if(session('duplicate_contact')) is-invalid @endif" name="contact_number" value="{{ old('contact_number') }}" required>
                            @error('contact_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if(session('duplicate_contact'))
                                <div class="invalid-feedback">This contact number is already registered.</div>
                            @endif
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Installation Date</label>
                            <input type="date" class="form-control @error('installation_date') is-invalid @enderror" name="installation_date" value="{{ old('installation_date') }}">
                            @error('installation_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Meter Series</label>
                            <input type="text" class="form-control @error('meter_series') is-invalid @enderror" name="meter_series" value="{{ old('meter_series') }}" required>
                            @error('meter_series')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
    @vite('resources/js/clients.js')
@endsection