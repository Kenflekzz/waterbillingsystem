@extends('layouts.admin')
<link rel="icon" href="{{ asset('images/MAGALLANES_LOGO.png') }}" type="image/x-icon">

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 fw-bold"> <i class="bi bi-chat-left-text me-2"></i>Consumer Reports / Messages</h2>

    {{-- Success message --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Search and filter --}}
    <div class="row mb-3">
        <div class="col-md-6 mb-2">
            <form action="{{ route('admin.reports') }}" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search by name, subject, or status" value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
            </form>
        </div>
        <div class="col-md-6 mb-2 text-md-end">
            <div class="btn-group" role="group" aria-label="Status Filter">
                <a href="{{ route('admin.reports', array_merge(request()->except('status_filter'), ['status_filter' => 'pending'])) }}" class="btn btn-warning {{ request('status_filter') == 'pending' ? 'active' : '' }}">Pending</a>
                <a href="{{ route('admin.reports', array_merge(request()->except('status_filter'), ['status_filter' => 'resolved'])) }}" class="btn btn-success {{ request('status_filter') == 'resolved' ? 'active' : '' }}">Resolved</a>
                <a href="{{ route('admin.reports') }}" class="btn btn-secondary {{ !request('status_filter') ? 'active' : '' }}">All</a>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Client Name</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th style="width:50px;"></th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($reports as $report)
                        <tr style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#reportModal{{ $report->id }}">
                            <td>{{ $report->client->full_name ?? 'Unknown' }}</td>
                            <td>
                                {{ $report->subject }}
                                @if($report->status == 'pending' && $report->created_at->gt(now()->subDays(3)))
                                    <span class="badge bg-danger ms-1">New!</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge 
                                    @if($report->status == 'pending') bg-warning text-dark
                                    @else bg-success
                                    @endif">
                                    {{ ucfirst($report->status) }}
                                </span>
                            </td>
                            <td>{{ $report->created_at->format('F d, Y') }}</td>
                            <td>
                                <i class="bi bi-eye-fill text-primary"></i>
                            </td>
                        </tr>

                        {{-- Modal --}}
                        <div class="modal fade" id="reportModal{{ $report->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content p-3">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ $report->subject }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">
                                        <p><strong>From:</strong> {{ $report->client->full_name ?? "Unknown" }}</p>

                                        <p><strong>Message:</strong></p>
                                        <div class="border p-2 rounded">
                                            {!! nl2br(e($report->description)) !!}
                                        </div>

                                        @if($report->image)
                                            <p class="mt-3"><strong>Attached Image:</strong></p>
                                            <img src="{{ asset('storage/' . $report->image) }}"
                                                 class="img-fluid rounded border"
                                                 alt="Report Image">
                                        @endif

                                        <hr>

                                        <form action="{{ route('admin.reports.update', $report->id) }}" method="POST">
                                            @csrf
                                            <label class="fw-bold">Update Status</label>
                                            <select name="status" class="form-select mb-3">
                                                <option value="pending" {{ $report->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="resolved" {{ $report->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                            </select>
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="bi bi-check2-circle"></i> Save Changes
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- End Modal --}}
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="p-3">
        {{ $reports->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
