@extends('layouts.user')
<link rel="icon" href="{{ asset('images/MAGALLANES_LOGO.png') }}" type="image/x-icon">

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8">
            <div class="card shadow-lg rounded-3 mb-4 p-4">

                <h3 class="text-primary fw-semibold mb-4 text-center">Edit Profile</h3>

                <!-- MERGED FORM -->
                <form action="{{ route('user.updateProfile') }}" method="POST" enctype="multipart/form-data" class="ajax-form" id="profileForm">
                    @csrf
                    @method('PUT')

                    <!-- PROFILE IMAGE -->
                    <div class="text-center mb-4">
                        <img 
                            src="{{ $user->profile_image ? asset('storage/' . $user->profile_image) : asset('images/canva-user-icon-MAG2DI-JFjM.png') }}" 
                            id="profileImagePreview"
                            class="rounded-circle shadow mb-3 img-fluid" 
                            alt="User Avatar" 
                            style="width: 120px; height: 120px; object-fit: cover;"
                        >
                        <!-- Hidden File Input -->
                            <input type="file" id="profileImageInput" name="profile_image" accept="image/*" class="d-none">

                            <!-- Change Profile Button -->
                            <button type="button" id="changeProfileBtn" class="btn btn-outline-primary btn-sm mt-2">
                                <i class="fa-solid fa-camera"></i> Change Profile
                            </button>

                            <small class="text-muted d-block mt-2">Upload a new profile picture</small>

                    </div>

                    <h4 class="fw-semibold text-primary mb-3 mt-4 text-center">Profile Information</h4>

                    <div class="mb-3">
                        <label class="fw-bold">First Name</label>
                        <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">Last Name</label>
                        <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">Phone Number</label>
                        <input type="text" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" class="form-control">
                    </div>

                    <h4 class="fw-semibold text-primary mb-3 mt-4 text-center">Change Password</h4>

                    @php
                    $passwordFields = [
                        ['label' => 'Current Password', 'name' => 'current_password', 'id' => 'currentPassword'],
                        ['label' => 'New Password', 'name' => 'new_password', 'id' => 'newPassword'],
                        ['label' => 'Confirm New Password', 'name' => 'new_password_confirmation', 'id' => 'confirmPassword'],
                    ];
                    @endphp

                    @foreach($passwordFields as $field)
                    <div class="mb-3">
                        <label class="fw-bold">{{ $field['label'] }}</label>
                        <div class="position-relative">
                            <input type="password" name="{{ $field['name'] }}" class="form-control pe-5 password-field" id="{{ $field['id'] }}">
                            <button type="button" class="btn position-absolute top-0 end-0 h-100 toggle-password" data-target="{{ $field['id'] }}" style="background:transparent; border:none; padding:0 0.75rem; cursor:pointer; display:none;">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach

                    <button type="submit" class="btn btn-primary w-100 mt-3">Save Changes</button>
                </form>

                <p class="text-muted small text-center mt-3">
                    Profile last updated: {{ $user->updated_at->format('F d, Y h:i A') }}
                </p>

                @if(session('success'))
                    <input type="hidden" id="profileSuccessMessage" value="{{ session('success') }}">
                @endif
            </div>
        </div>
    </div>
</div>

@vite('resources/js/UserProfile.js')
@endsection
