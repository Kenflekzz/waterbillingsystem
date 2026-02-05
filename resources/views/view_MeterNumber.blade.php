<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>How to Find Your Meter Number - {{ $homepage->header_title ?? 'Magallanes Water Billing System' }}</title>
    <link rel="icon" href="{{ asset($homepage->favicon ?? 'images/MAGALLANES_LOGO.png') }}" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    @vite('resources/css/welcome.css')
    <style>
        .content-section {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .step-number {
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
            font-weight: bold;
            background-color: #0C6170;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .accordion-button:not(.collapsed) {
            background-color: #fff3cd;
            color: #856404;
        }
        .accordion-button:focus {
            box-shadow: none;
            border-color: rgba(0,0,0,.125);
        }
    </style>
</head>
<body>
    <div class="overlay d-flex flex-column">
        <!-- Header (Same as Homepage) -->
        <header style="background-color: {{ $homepage->header_bg ?? '#0C6170' }}; backdrop-filter: blur(10px); color: white;" class="py-3">
            <div class="container d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <img src="{{ asset($homepage->logo ?? 'images/MAGALLANES_LOGO.png') }}" alt="Logo" class="me-3" style="height: 50px;">
                    <h1 class="h3 mb-0">{{ $homepage->header_title ?? 'Magallanes Water Billing System' }}</h1>
                </div>
                <nav>
                    <ul class="nav">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ url('/') }}#contact">Contact Us</a>
                        </li>
                        <li class="nav-item">
                            <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#loginChoiceModal">
                                {{ $homepage->sign_in_text ?? 'Sign In' }}
                            </button>
                        </li>
                    </ul>
                </nav>
            </div>
        </header>
        
        <!-- Content Section -->
        <section class="content-section py-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        {{-- Header --}}
                        <div class="text-center mb-5">
                            <h1 class="display-5 fw-bold" style="color: #0C6170;">
                                <i class="bi bi-info-circle me-2"></i>
                                How to Find Your Meter Number
                            </h1>
                            <p class="lead text-muted">A simple guide to locate your meter number on your water bill statement</p>
                        </div>

                        {{-- Bill Preview Card --}}
                        <div class="card shadow-lg mb-4 border-0">
                            <div class="card-header text-white" style="background-color: #0C6170;">
                                <h5 class="mb-0">
                                    <i class="bi bi-file-earmark-text me-2"></i>
                                    Your Water Bill Statement
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                {{-- Annotated Bill Image --}}
                                <div class="position-relative">
                                    <img src="{{ asset('images/sample-water-bill.jpg') }}" 
                                         alt="Water Bill Sample" 
                                         class="img-fluid w-100"
                                         onerror="this.src='{{ asset('images/waterbill.jpg') }}'">
                                    
                                    {{-- Highlight Box for Meter Number --}}
                                    <div class="position-absolute border border-danger border-3 rounded" 
                                         style="top: 31%; right: 8%; width: 22%; height: 7%; background-color: rgba(255,0,0,0.1);">
                                    </div>
                                    
                                    {{-- Label --}}
                                    <div class="position-absolute bg-danger text-white px-3 py-1 rounded-pill fw-bold shadow"
                                         style="top: 34.5%; right: -10%; transform: translateY(-50%);">
                                        <i class="bi bi-arrow-left me-1"></i>
                                        METER NO.
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Step by Step Guide --}}
                        <div class="card shadow mb-4">
                            <div class="card-header text-white" style="background-color: #198754;">
                                <h5 class="mb-0">
                                    <i class="bi bi-list-ol me-2"></i>
                                    Step-by-Step Guide
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    {{-- Step 1 --}}
                                    <div class="col-md-6">
                                        <div class="d-flex">
                                            <div class="step-number">1</div>
                                            <div class="ms-3">
                                                <h5 class="fw-bold">Locate Your Bill</h5>
                                                <p class="text-muted mb-0">Find your latest <strong>Magallanes Water Provider</strong> bill statement. It should have a header with the official logo.</p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Step 2 --}}
                                    <div class="col-md-6">
                                        <div class="d-flex">
                                            <div class="step-number">2</div>
                                            <div class="ms-3">
                                                <h5 class="fw-bold">Find the Meter Section</h5>
                                                <p class="text-muted mb-0">Look at the <strong>top right area</strong> of your bill. You'll see a section labeled <strong>"METER NO."</strong></p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Step 3 --}}
                                    <div class="col-md-6">
                                        <div class="d-flex">
                                            <div class="step-number">3</div>
                                            <div class="ms-3">
                                                <h5 class="fw-bold">Read the Number</h5>
                                                <p class="text-muted mb-0">Your meter number is displayed below the "METER NO." label. It typically looks like: <code>5-0905035</code></p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Step 4 --}}
                                    <div class="col-md-6">
                                        <div class="d-flex">
                                            <div class="step-number">4</div>
                                            <div class="ms-3">
                                                <h5 class="fw-bold">Use for Register</h5>
                                                <p class="text-muted mb-0">Enter this exact meter number when creating your <strong>Magallanes Water Billing System</strong> account.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Sample Meter Numbers --}}
                        <div class="card shadow mb-4">
                            <div class="card-header text-white" style="background-color: #0dcaf0;">
                                <h5 class="mb-0">
                                    <i class="bi bi-fingerprint me-2"></i>
                                    What Does a Meter Number Look Like?
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-3">Meter numbers typically follow these formats:</p>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="border rounded p-3 text-center bg-light">
                                            <code class="fs-5">5-0905035</code>
                                            <small class="d-block text-muted mt-1">Standard Format</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border rounded p-3 text-center bg-light">
                                            <code class="fs-5">4-1234567</code>
                                            <small class="d-block text-muted mt-1">Older Format</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border rounded p-3 text-center bg-light">
                                            <code class="fs-5">09123456789</code>
                                            <small class="d-block text-muted mt-1">Phone-style Format</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-warning mt-3 mb-0">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>Important:</strong> Enter the meter number <strong>exactly</strong> as it appears on your bill, including any dashes (-).
                                </div>
                            </div>
                        </div>

                        {{-- Common Issues --}}
                        <div class="card shadow mb-4">
                            <div class="card-header text-dark" style="background-color: #ffc107;">
                                <h5 class="mb-0">
                                    <i class="bi bi-question-circle me-2"></i>
                                    Common Issues & Solutions
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="accordion" id="meterFAQ">
                                    {{-- FAQ 1 --}}
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                                I can't find "METER NO." on my bill
                                            </button>
                                        </h2>
                                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#meterFAQ">
                                            <div class="accordion-body">
                                                Look in the <strong>top right section</strong> of your bill, near "METER TYPE". It's usually in the same row as your billing date information. If you still can't find it, contact our support team.
                                            </div>
                                        </div>
                                    </div>

                                    {{-- FAQ 2 --}}
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                                My meter number has changed
                                            </button>
                                        </h2>
                                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#meterFAQ">
                                            <div class="accordion-body">
                                                If your meter was recently replaced, your meter number may have changed. Check the <strong>"METER STATUS"</strong> field on your bill - if it says "replacement", use the new number shown. You will also receive a notification about this change.
                                            </div>
                                        </div>
                                    </div>

                                    {{-- FAQ 3 --}}
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                                The system says my meter number is invalid
                                            </button>
                                        </h2>
                                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#meterFAQ">
                                            <div class="accordion-body">
                                                Make sure you're entering the number exactly as shown:
                                                <ul class="mb-0 mt-2">
                                                    <li>Include any <strong>dashes (-)</strong></li>
                                                    <li>Don't add extra spaces</li>
                                                    <li>Use <strong>numbers only</strong> (no letters)</li>
                                                    <li>Check for confusing characters: <strong>0 (zero)</strong> vs <strong>O (letter O)</strong></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Need Help --}}
                        <div class="card shadow border-0 text-white mb-4" style="background-color: #0C6170;">
                            <div class="card-body text-center py-5">
                                <h3 class="mb-3">Still Need Help?</h3>
                                <p class="mb-4">Our support team is ready to assist you in finding your meter number.</p>
                                <div class="d-flex justify-content-center gap-3">
                                    <a href="tel:0851234567" class="btn btn-light btn-lg">
                                        <i class="bi bi-telephone me-2"></i>
                                        Call Support
                                    </a>
                                    <a href="mailto:{{ $homepage->footer_email ?? 'magallaneswaterbilling@gmail.com' }}" class="btn btn-outline-light btn-lg">
                                        <i class="bi bi-envelope me-2"></i>
                                        Send Email
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Back to Home --}}
                        <div class="text-center mt-4">
                            <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>
                                Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer (Same as Homepage) -->
        <footer id="contact" style="background-color: {{ $homepage->footer_bg ?? 'black' }}; backdrop-filter: blur(5px); color: white;" class="py-5">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 mb-4 mb-md-0">
                        <div class="d-flex align-items-center mb-3">
                            <img src="{{ asset($homepage->logo ?? 'images/MAGALLANES_LOGO.png') }}" alt="Logo" style="height: 100px; width: 100px;" class="me-3">
                            <h5 class="mb-0">{{ $homepage->footer_title ?? 'Magallanes Water Billing System' }}</h5>
                        </div>
                        <p class="mb-1"><strong>Address:</strong> {{ $homepage->footer_address ?? 'Magallanes, Agusan del Norte' }}</p>
                        <p class="mb-1"><strong>Contact:</strong> {{ $homepage->footer_contact ?? '(085) 123-4567' }}</p>
                        <p class="mb-0"><strong>Email:</strong> <a href="mailto:{{ $homepage->footer_email ?? 'magallaneswaterbilling@gmail.com' }}" class="text-white text-decoration-underline">{{ $homepage->footer_email ?? 'magallaneswaterbilling@gmail.com' }}</a></p>
                    </div>

                    <div class="col-md-6">
                        <h5>{{ $homepage->footer_quicklinks_title ?? 'Quick Links' }}</h5>
                        <ul class="list-unstyled">
                            @php
                                $quickLinks = $homepage->footer_quicklinks ?? [];
                                if(empty($quickLinks)) {
                                    $quickLinks = [
                                        ['title' => 'How to find your meter number', 'link' => route('ViewMeterNumber')],
                                        ['title' => 'How to read your water bill', 'link' => route('ReadWaterBill')],
                                        ['title' => 'Our Company', 'link' => '#'],
                                        ['title' => 'Terms of Use', 'link' => '#'],
                                        ['title' => 'Privacy Policy', 'link' => '#'],
                                    ];
                                }   
                            @endphp
                            @foreach($quickLinks as $link)
                                <li><a href="{{ $link['link'] }}" class="text-white text-decoration-none">{{ $link['title'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="text-center mt-4 border-top pt-3">
                    &copy; {{ date('Y') }} {{ $homepage->footer_title ?? 'Water Billing System' }}. All rights reserved.
                </div>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({ behavior: 'smooth' });
            });
        });
    </script>
</body>

<!-- Login Choice Modal -->
<div class="modal fade" id="loginChoiceModal" tabindex="-1" aria-labelledby="loginChoiceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow">
      <div class="modal-header">
        <h5 class="modal-title" id="loginChoiceModalLabel">Sign In As</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <p>{{ $homepage->login_choice_text ?? 'Please choose your login type:' }}</p>
        <div class="d-flex justify-content-around mt-3">
          <a href="{{ route('admin.login') }}" class="btn btn-outline-primary px-4">{{ $homepage->login_admin_text ?? 'Admin' }}</a>
          <a href="{{ route('user.login') }}" class="btn btn-outline-success px-4">{{ $homepage->login_user_text ?? 'User' }}</a>
        </div>
      </div>
    </div>
  </div>
</div>
</html>