@extends('layouts.admin')

@section('title', 'Messages')
<link rel="icon" href="{{ asset('images/MAGALLANES_LOGO.png') }}" type="image/x-icon">

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Messaging Dashboard</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <!-- General Message -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-broadcast"></i> General Message
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.messages.sendGeneral') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="general_title" class="form-label">Title</label>
                            <input type="text" name="title" id="general_title" class="form-control" placeholder="Enter message title" required>
                        </div>
                        <div class="mb-3">
                            <label for="general_body" class="form-label">Message</label>
                            <textarea name="body" id="general_body" class="form-control" rows="4" placeholder="Enter message body" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send to All Clients</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Personal Message -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-person-lines-fill"></i> Personal Message
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.messages.sendPersonal') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="client_id" class="form-label">Select Client</label>
                            <select name="client_id" id="client_id" class="form-select" required>
                                <option value="">-- Choose Client --</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->contact_number }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="personal_title" class="form-label">Title</label>
                            <input type="text" name="title" id="personal_title" class="form-control" placeholder="Enter message title" required>
                        </div>
                        <div class="mb-3">
                            <label for="personal_body" class="form-label">Message</label>
                            <textarea name="body" id="personal_body" class="form-control" rows="4" placeholder="Enter message body" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Send to Selected Client</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
