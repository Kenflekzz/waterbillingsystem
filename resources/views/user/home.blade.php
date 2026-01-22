@extends('layouts.user')
<link rel="icon" href="{{ asset('images/MAGALLANES_LOGO.png') }}" type="image/x-icon">

@section('content')

<div class="container my-4" style="max-width: 60vw;">

    {{-- Announcements --}}
    <h3>Announcements</h3>

    @forelse($announcements as $announcement)
        <div class="card mb-3 shadow">
            <div class="card-body">
                <h5 class="card-title">{{ $announcement->title }}</h5>
                <p class="card-text">{{ $announcement->content }}</p>

                @if(!empty($announcement->announcement_image))
                    <img src="{{ asset('storage/' . $announcement->announcement_image) }}" 
                         alt="Announcement Image" 
                         class="img-fluid mb-2 shadow-sm rounded"
                         style="width: 100%; height: auto;">
                @endif

                @if($announcement->scheduled_date)
                    <small class="text-muted">Scheduled: {{ $announcement->scheduled_date }}</small>
                @endif
            </div>
        </div>
    @empty
        <p class="text-muted">No announcements at the moment.</p>
    @endforelse


    {{-- Advisories --}}
    <h3 class="mt-5">Advisories</h3>

    @php
        $advisories = $homepage->advisories ?? [];
    @endphp

    @foreach($advisories as $advisory)
        <div class="card mb-4 shadow">
            @if(!empty($advisory['image']))
                <img src="{{ asset($advisory['image']) }}" 
                     class="card-img-top shadow-sm" 
                     alt="{{ $advisory['title'] }}">
            @endif

            <div class="card-body">
                <h5 class="card-title">{{ $advisory['title'] ?? '' }}</h5>
                <p class="card-text">{{ $advisory['text'] ?? '' }}</p>
            </div>
        </div>
    @endforeach


    {{-- Connect With Us --}}
    <h3 class="mt-5">Connect With Us</h3>

    @php
        $connectImages = $homepage->connect_images ?? [];
    @endphp

    @foreach($connectImages as $img)
        <div class="mb-4">
            <img src="{{ asset($img) }}" 
                 class="img-fluid border rounded shadow-lg"
                 style="width: 100%; height: auto;">
        </div>
    @endforeach

    <div class="d-flex justify-content-center gap-3 mt-3 fs-2">
        <a href="{{ $homepage->facebook_link ?? '#' }}" class="text-decoration-none">
            <i class="bi bi-facebook"></i>
        </a>

        <a href="{{ $homepage->twitter_link ?? '#' }}" class="text-decoration-none">
            <i class="bi bi-twitter"></i>
        </a>

        <a href="mailto:{{ $homepage->email ?? '#' }}" class="text-decoration-none">
            <i class="bi bi-envelope-fill"></i>
        </a>
    </div>

</div> {{-- END unified container --}}

@endsection
