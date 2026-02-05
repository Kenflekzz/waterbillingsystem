<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $homepage->title ?? 'Water Billing Homepage' }}</title>
    <link rel="icon" href="{{ asset($homepage->favicon ?? 'images/MAGALLANES_LOGO.png') }}" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    @vite('resources/css/welcome.css')
</head>
<body>
    <div class="overlay d-flex flex-column">
        <!-- Header -->
        <header style="background-color: {{ $homepage->header_bg ?? '#0C6170' }}; backdrop-filter: blur(10px); color: white;" class="py-3">
            <div class="container d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <img src="{{ asset($homepage->logo ?? 'images/MAGALLANES_LOGO.png') }}" alt="Logo" class="me-3" style="height: 50px;">
                    <h1 class="h3 mb-0">{{ $homepage->header_title ?? 'Magallanes Water Billing System' }}</h1>
                </div>
                <nav>
                    <ul class="nav">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#contact">{{ $homepage->nav_contact ?? 'Contact Us' }}</a>
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
        
        <!-- Hero Section -->
        <section class="hero d-flex align-items-center justify-content-center py-5">
            <div class="text-center text-white p-5">
                <h2 class="mb-3">{{ $homepage->hero_title ?? 'Welcome, Water Billing User' }}</h2>
                <p class="lead">{{ $homepage->hero_subtitle ?? 'Manage your account, view your bills, and monitor water usage anytime.' }}</p>
            </div>
        </section>

        <!-- Announcement Section -->
        <section class="section-padding announcement-section text-white">
            <div class="container">
               <div class="row align-items-center">
                    <div class="col-md-6 mb-4 mb-md-0">
                        @if(!empty($homepage->announcement_image))
                            <img src="{{ asset('storage/' . $homepage->announcement_image) }}" alt="Announcement" class="img-fluid rounded shadow">
                        @else
                            <img src="{{ asset('images/360_F_645229820_kYP6uF8VtHwO5whqL58Z8J5fFIgnJA9H.webp') }}" alt="Announcement" class="img-fluid rounded shadow">
                        @endif
                    </div>

                    <div class="col-md-6">
                        <h3>{{ $homepage->announcement_heading ?? 'Latest Announcement' }}</h3>
                        <p>{{ $homepage->announcement_text ?? 'Stay updated with the latest news and announcements regarding your water billing.' }}</p>
                        <ul class="list-unstyled">
                            @if(!empty($homepage->announcement_list))
                                @foreach($homepage->announcement_list as $item)
                                    <li>{!! $item !!}</li>
                                @endforeach
                            @else
                                <li><strong>New Billing Cycle:</strong> Starting from next month.</li>
                                <li><strong>Payment Deadline:</strong> 15th of every month.</li>
                                <li><strong>Contact Support:</strong> For any queries, reach out to our support team.</li>
                            @endif
                        </ul>
                        <a href="{{ $homepage->announcement_link ?? '#' }}" class="btn btn-outline-primary mt-3">{{ $homepage->announcement_link_text ?? 'Learn More' }}</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Advisories Section -->
        <section class="section-padding bg-light">
            <div class="container">
                <h3 class="text-center mb-5">{{ $homepage->advisories_title ?? 'Advisories' }}</h3>
                <div class="row">
                    @php
                        $advisories = $homepage->advisories ?? [];
                        if(empty($advisories)) {
                            $advisories = [
                                [
                                    'image' => 'images/sb7b46709a8d64e879ae9e10852eee530_optimized.webp',
                                    'title' => 'Water Interruption Notice',
                                    'text' => 'There will be a temporary water service interruption on June 18 due to pipeline maintenance.'
                                ],
                                [
                                    'image' => 'images/Payment-Reminders.webp',
                                    'title' => 'Billing Reminder',
                                    'text' => 'Kindly settle your bills before the 15th to avoid penalties or service disconnection.'
                                ],
                                [
                                    'image' => 'images/new-cropped.jpg',
                                    'title' => 'Customer Support Update',
                                    'text' => 'Support hotline will be available from 8AM–5PM, Monday to Saturday, starting this week.'
                                ]
                            ];
                        }
                    @endphp

                    @foreach($advisories as $advisory)
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <img src="{{ asset($advisory['image']) }}" class="card-img-top" alt="{{ $advisory['title'] }}">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $advisory['title'] }}</h5>
                                    <p class="card-text">{{ $advisory['text'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Connect Section -->
        <section class="connect-section text-white position-relative">
            <div class="split-background position-relative">
                <div class="bg-left"></div>
                <div class="bg-right"></div>
                <div class="position-relative z-1 container py-5">
                    <div class="text-center mb-5">
                        <h3 class="text-white">{{ $homepage->connect_title ?? 'Connect With Us' }}</h3>
                    </div>

                    <div class="row text-center mb-4">
                        @php
                            $connectImages = $homepage->connect_images ?? [];
                            if(empty($connectImages)) {
                                $connectImages = [
                                    'images/OIP.webp',
                                    'images/verizon-WITS-3-Customer-Service-Center-User-Guide_1.webp'
                                ];
                            }
                        @endphp
                        @foreach($connectImages as $img)
                            <div class="col-6 d-flex justify-content-center">
                                <img src="{{ asset($img) }}" class="border-white" style="width: 350px; height: 250px; object-fit: cover;">
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-center gap-4">
                        <a href="{{ $homepage->facebook_link ?? '#' }}" class="text-white fs-1"><i class="bi bi-facebook"></i></a>
                        <a href="{{ $homepage->twitter_link ?? '#' }}" class="text-white fs-1"><i class="bi bi-twitter"></i></a>
                        <a href="mailto:{{ $homepage->email ?? 'email@example.com' }}" class="text-white fs-1"><i class="bi bi-envelope-fill"></i></a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
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
