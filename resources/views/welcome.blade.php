<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Water Billing Homepage</title>
    <link rel="icon" href="{{ asset('images/MAGALLANES_LOGO.png') }}" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    @vite('resources/css/welcome.css')
</head>
<body>
    <div class="overlay d-flex flex-column">
        <!-- Header -->
        <header style="background-color: #0C6170; backdrop-filter: blur(10px); color: white;" class="py-3">
            <div class="container d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <img src="{{ asset('images/MAGALLANES_LOGO.png') }}" alt="Logo" class="me-3" style="height: 50px;">
                    <h1 class="h3 mb-0">Water Billing System</h1>
                </div>
                <nav>
                    <ul class="nav">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{route('home')}}">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#">Bills</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#">Contact Us</a>
                        </li>
                       <li class="nav-item">
                        <!-- Trigger modal instead of direct link -->
                        <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#loginChoiceModal">
                            Sign In
                        </button>
                    </li>

                    </ul>
                </nav>
            </div>
        </header>
        
        <!-- Hero Section (top background) -->
        <section class="hero d-flex align-items-center justify-content-center py-5">
            <div class="text-center  text-white p-5">
                <h2 class="mb-3">Welcome, Water Billing User</h2>
                <p class="lead">Manage your account, view your bills, and monitor water usage anytime.</p>
                
            </div>
        </section>


        <section class="section-padding announcement-section text-white">
            <div class="container">
               <div class="row align-items-center">
                    <div class="col-md-6 mb-4 mb-md-0">
                        <img src="{{asset('images/360_F_645229820_kYP6uF8VtHwO5whqL58Z8J5fFIgnJA9H.webp')}}" alt="Announcement" class="img-fluid rounded shadow">
                    </div>

                    <div class="col md-6">
                        <h3>Latest Announcement</h3>
                        <p>Stay updated with the latest news and announcements regarding your water billing.</p>
                        <ul class="list-unstyled">
                            <li><strong>New Billing Cycle:</strong> Starting from next month.</li>
                            <li><strong>Payment Deadline:</strong> 15th of every month.</li>
                            <li><strong>Contact Support:</strong> For any queries, reach out to our support team.</li>
                             <a href="#" class="btn btn-outline-primary mt-3">Learn More</a>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Advisories Section -->
            <section class="section-padding bg-light">
                <div class="container">
                    <h3 class="text-center mb-5">Advisories</h3>
                    <div class="row">
                        <!-- Card 1 -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <img src="{{ asset('images/sb7b46709a8d64e879ae9e10852eee530_optimized.webp') }}" class="card-img-top" alt="Advisory 1">
                                <div class="card-body">
                                    <h5 class="card-title">Water Interruption Notice</h5>
                                    <p class="card-text">There will be a temporary water service interruption on June 18 due to pipeline maintenance.</p>
                                </div>
                            </div>
                        </div>
                        <!-- Card 2 -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <img src="{{ asset('images/Payment-Reminders.webp') }}" class="card-img-top" alt="Advisory 2">
                                <div class="card-body">
                                    <h5 class="card-title">Billing Reminder</h5>
                                    <p class="card-text">Kindly settle your bills before the 15th to avoid penalties or service disconnection.</p>
                                </div>
                            </div>
                        </div>
                        <!-- Card 3 -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <img src="{{ asset('images/new-cropped.jpg') }}" class="card-img-top" alt="Advisory 3">
                                <div class="card-body">
                                    <h5 class="card-title">Customer Support Update</h5>
                                    <p class="card-text">Support hotline will be available from 8AM–5PM, Monday to Saturday, starting this week.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>



        <!-- Connect With Us Section -->
        <section class="connect-section text-white position-relative">
            <div class="split-background position-relative">
                <!-- Split background colors -->
                <div class="bg-left"></div>
                <div class="bg-right"></div>

                <!-- Content over the background -->
                <div class="position-relative z-1 container py-5">
                    <!-- Title -->
                    <div class="text-center mb-5">
                        <h3 class="text-white">Connect With Us</h3>
                    </div>

                        <!-- Two Images (Left and Right) -->
                        <div class="row text-center mb-4">
                            <div class="col-6 d-flex justify-content-center">
                                <img src="{{ asset('images/OIP.webp') }}"
                                    class=" border-white"
                                    style="width: 350px; height: 250px; object-fit: cover;">
                            </div>
                            <div class="col-6 d-flex justify-content-center">
                                <img src="{{ asset('images/verizon-WITS-3-Customer-Service-Center-User-Guide_1.webp') }}"
                                    class=" border-white"
                                    style="width: 350px; height:250px; object-fit: cover;">
                            </div>
                        </div>


                    <!-- Icons under the images -->
                    <div class="d-flex justify-content-center gap-4">
                        <a href="#" class="text-white fs-1"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white fs-1"><i class="bi bi-twitter"></i></a>
                        <a href="mailto:email@example.com" class="text-white fs-1"><i class="bi bi-envelope-fill"></i></a>
                    </div>
                </div>
            </div>
        </section>



        <!-- Footer -->
       <footer style="background-color: black; backdrop-filter: blur(5px); color: white;" class="py-5">
            <div class="container">
                <div class="row">
                    <!-- Left Section: Logo and Contact Info -->
                    <div class="col-md-6 mb-4 mb-md-0">
                        <div class="d-flex align-items-center mb-3">
                            <img src="{{ asset('images/MAGALLANES_LOGO.png') }}" alt="Logo" style="height: 100px; width: 100px;" class="me-3">
                            <h5 class="mb-0">Water Billing System</h5>
                        </div>
                        <p class="mb-1"><strong>Address:</strong> Poblacion, Magallanes, Agusan del Norte</p>
                        <p class="mb-1"><strong>Contact:</strong> (085) 123-4567</p>
                        <p class="mb-0"><strong>Email:</strong> <a href="mailto:info@waterbilling.com" class="text-white text-decoration-underline">info@waterbilling.com</a></p>
                    </div>

                    <!-- Right Section: Quick Links -->
                    <div class="col-md-6">
                        <h5>Quick Links</h5>
                        <ul class="list-unstyled">
                            <li><a href="#" class="text-white text-decoration-none">How to find your meter number</a></li>
                            <li><a href="#" class="text-white text-decoration-none">How to read your water bill</a></li>
                            <li><a href="#" class="text-white text-decoration-none">Our Company</a></li>
                            <li><a href="#" class="text-white text-decoration-none">Terms of Use</a></li>
                            <li><a href="#" class="text-white text-decoration-none">Privacy Policy</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Bottom Note -->
                <div class="text-center mt-4 border-top pt-3">
                    &copy; {{ date('Y') }} Water Billing System. All rights reserved.
                </div>
            </div>
        </footer>

            </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
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
        <p>Please choose your login type:</p>
        <div class="d-flex justify-content-around mt-3">
          <!-- Admin Login -->
          <a href="{{ route('admin.login') }}" class="btn btn-outline-primary px-4">Admin</a>
          <!-- User Login -->
          <a href="{{ route('user.login') }}" class="btn btn-outline-success px-4">User</a>
        </div>
      </div>
    </div>
  </div>
</div>

</html>
