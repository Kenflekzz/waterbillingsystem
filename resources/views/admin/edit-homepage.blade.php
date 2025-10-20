@extends('layouts.admin')
<link rel="icon" href="{{ asset($homepage->favicon ?? 'images/MAGALLANES_LOGO.png') }}" type="image/x-icon">

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Edit Homepage</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <!-- FORM COLUMN -->
        <div class="col-md-6">
            <form method="POST" action="{{ route('admin.homepage.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- HERO -->
                <div class="mb-4">
                    <label>Hero Title</label>
                    <input type="text" name="hero_title"
                           class="form-control live-update"
                           data-target="preview-hero-title"
                           value="{{ $homepage->hero_title ?? '' }}">
                </div>
                <div class="mb-4">
                    <label>Hero Subtitle</label>
                    <input type="text" name="hero_subtitle"
                           class="form-control live-update"
                           data-target="preview-hero-subtitle"
                           value="{{ $homepage->hero_subtitle ?? '' }}">
                </div>

                <!-- ANNOUNCEMENT -->
                <div class="mb-4">
                    <label>Announcement Heading</label>
                    <input type="text" name="announcement_heading"
                           class="form-control live-update"
                           data-target="preview-announcement-heading"
                           value="{{ $homepage->announcement_heading ?? '' }}">
                </div>
                <div class="mb-4">
                    <label>Announcement Text</label>
                    <textarea name="announcement_text"
                              class="form-control live-update"
                              data-target="preview-announcement-text">{{ $homepage->announcement_text ?? '' }}</textarea>
                </div>
                <div class="mb-4">
                    <label>Announcement Image</label>
                    <input type="file" name="announcement_image"
                           class="form-control image-preview"
                           data-target="preview-announcement-image"
                           data-existing="{{ !empty($homepage->announcement_image) ? asset('storage/'.$homepage->announcement_image) : '' }}">
                </div>

                <!-- ADVISORIES -->
                <h4 class="mt-4">Advisories</h4>
                @for($i=0; $i<3; $i++)
                    <div class="mb-3 border p-2 rounded">
                        <label>Advisory {{ $i+1 }} Title</label>
                        <input type="text" name="advisories[{{ $i }}][title]"
                               class="form-control live-update"
                               data-target="preview-advisory-title-{{ $i }}"
                               value="{{ $homepage->advisories[$i]['title'] ?? '' }}">

                        <label>Advisory {{ $i+1 }} Text</label>
                        <textarea name="advisories[{{ $i }}][text]"
                                  class="form-control live-update"
                                  data-target="preview-advisory-text-{{ $i }}">{{ $homepage->advisories[$i]['text'] ?? '' }}</textarea>

                        <label>Advisory {{ $i+1 }} Image</label>
                        <input type="file" name="advisories[{{ $i }}][image]"
                               class="form-control image-preview"
                               data-target="preview-advisory-image-{{ $i }}"
                               data-existing="{{ !empty($homepage->advisories[$i]['image']) ? asset($homepage->advisories[$i]['image']) : '' }}">
                    </div>
                @endfor

                <!-- CONNECT -->
                <h4 class="mt-4">Connect</h4>
                <div class="mb-3">
                    <label>Connect Title</label>
                    <input type="text" name="connect_title"
                           class="form-control live-update"
                           data-target="preview-connect-title"
                           value="{{ $homepage->connect_title ?? '' }}">
                </div>
                @for($i=0; $i<2; $i++)
                    <div class="mb-3 border p-2 rounded">
                        <label>Connect Image {{ $i+1 }}</label>
                        <input type="file" name="connect_images[{{ $i }}]"
                               class="form-control image-preview"
                               data-target="preview-connect-image-{{ $i }}"
                               data-existing="{{ !empty($homepage->connect_images[$i]) ? asset($homepage->connect_images[$i]) : '' }}">
                    </div>
                @endfor

                <!-- FOOTER -->
                <h4 class="mt-4">Footer</h4>
                <div class="mb-3">
                    <label>Footer Title</label>
                    <input type="text" name="footer_title"
                           class="form-control live-update"
                           data-target="preview-footer-title"
                           value="{{ $homepage->footer_title ?? '' }}">
                </div>
                <div class="mb-3">
                    <label>Footer Address</label>
                    <input type="text" name="footer_address"
                           class="form-control live-update"
                           data-target="preview-footer-address"
                           value="{{ $homepage->footer_address ?? '' }}">
                </div>
                <div class="mb-3">
                    <label>Footer Contact</label>
                    <input type="text" name="footer_contact"
                           class="form-control live-update"
                           data-target="preview-footer-contact"
                           value="{{ $homepage->footer_contact ?? '' }}">
                </div>
                <div class="mb-3">
                    <label>Footer Email</label>
                    <input type="email" name="footer_email"
                           class="form-control live-update"
                           data-target="preview-footer-email"
                           value="{{ $homepage->footer_email ?? '' }}">
                </div>

                <button type="submit" class="btn btn-primary w-100">Save Homepage</button>
            </form>
        </div>

        <!-- PREVIEW COLUMN -->
        <div class="col-md-6">
            <h2 class="mb-3">Live Preview</h2>
            <div id="homepagePreview" class="p-4 border rounded bg-light">
                <!-- HERO -->
                <section class="mb-5 text-center">
                    <h1 id="preview-hero-title" data-default="Welcome, Water Billing User">
                        {{ $homepage->hero_title ?? 'Welcome, Water Billing User' }}
                    </h1>
                    <p id="preview-hero-subtitle" data-default="Manage your account, view your bills, and monitor water usage anytime.">
                        {{ $homepage->hero_subtitle ?? 'Manage your account, view your bills, and monitor water usage anytime.' }}
                    </p>
                </section>

                <!-- ANNOUNCEMENT -->
                <section class="mb-5">
                    <h2 id="preview-announcement-heading" data-default="Latest Announcement">
                        {{ $homepage->announcement_heading ?? 'Latest Announcement' }}
                    </h2>
                    <p id="preview-announcement-text" data-default="Stay updated with the latest news.">
                        {{ $homepage->announcement_text ?? 'Stay updated with the latest news.' }}
                    </p>
                    <img id="preview-announcement-image"
                         src="{{ !empty($homepage->announcement_image) ? asset('storage/'.$homepage->announcement_image) : '' }}"
                         style="max-width:200px; {{ !empty($homepage->announcement_image) ? '' : 'display:none;' }}">
                </section>

                <!-- ADVISORIES -->
                <section class="mb-5">
                    <h3>Advisories</h3>
                    <div class="row">
                        @for($i=0; $i<3; $i++)
                            <div class="col-md-4 text-center">
                                <img id="preview-advisory-image-{{ $i }}" src="{{ !empty($homepage->advisories[$i]['image']) ? asset($homepage->advisories[$i]['image']) : '' }}" style="max-width:100%; height:150px; object-fit:cover; {{ !empty($homepage->advisories[$i]['image']) ? '' : 'display:none;' }}">
                                <h5 id="preview-advisory-title-{{ $i }}" data-default="Advisory {{ $i+1 }}">
                                    {{ $homepage->advisories[$i]['title'] ?? 'Advisory '.($i+1) }}
                                </h5>
                                <p id="preview-advisory-text-{{ $i }}" data-default="Advisory description here.">
                                    {{ $homepage->advisories[$i]['text'] ?? 'Advisory description here.' }}
                                </p>
                            </div>
                        @endfor
                    </div>
                </section>

                <!-- CONNECT -->
                <section class="mb-5 text-center">
                    <h3 id="preview-connect-title" data-default="Connect With Us">
                        {{ $homepage->connect_title ?? 'Connect With Us' }}
                    </h3>
                    <div class="d-flex justify-content-center gap-3">
                        @for($i=0; $i<2; $i++)
                            <img id="preview-connect-image-{{ $i }}" src="{{ !empty($homepage->connect_images[$i]) ? asset($homepage->connect_images[$i]) : '' }}" style="width:150px; height:100px; object-fit:cover; {{ !empty($homepage->connect_images[$i]) ? '' : 'display:none;' }}">
                        @endfor
                    </div>
                </section>

                <!-- FOOTER -->
                <footer class="border-top pt-3">
                    <h5 id="preview-footer-title" data-default="Water Billing System">
                        {{ $homepage->footer_title ?? 'Water Billing System' }}
                    </h5>
                    <p id="preview-footer-address" data-default="Poblacion, Magallanes, Agusan del Norte">
                        {{ $homepage->footer_address ?? 'Poblacion, Magallanes, Agusan del Norte' }}
                    </p>
                    <p id="preview-footer-contact" data-default="(085) 123-4567">
                        {{ $homepage->footer_contact ?? '(085) 123-4567' }}
                    </p>
                    <p>
                        <a href="mailto:{{ $homepage->footer_email ?? 'info@waterbilling.com' }}"
                           id="preview-footer-email"
                           data-default="info@waterbilling.com"
                           class="text-decoration-none">
                            {{ $homepage->footer_email ?? 'info@waterbilling.com' }}
                        </a>
                    </p>
                </footer>
            </div>
        </div>
    </div>
</div>
@endsection

@vite('resources/js/homepage-preview.js')
