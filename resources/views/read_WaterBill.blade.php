<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>How to Read Your Water Bill - {{ $homepage->header_title ?? 'Magallanes Water Billing System' }}</title>
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
        .bill-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .bill-highlight {
            position: relative;
            border: 3px solid #0C6170;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            background: #f8f9fa;
        }
        .bill-highlight::before {
            content: attr(data-label);
            position: absolute;
            top: -12px;
            left: 15px;
            background: #0C6170;
            color: white;
            padding: 2px 10px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        .amount-due {
            background: #dc3545;
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
        }
        .info-icon {
            width: 40px;
            height: 40px;
            background: #0C6170;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
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
                    <div class="col-lg-10">
                        {{-- Header --}}
                        <div class="text-center mb-5">
                            <h1 class="display-5 fw-bold" style="color: #0C6170;">
                                <i class="bi bi-receipt me-2"></i>
                                How to Read Your Water Bill
                            </h1>
                            <p class="lead text-muted">Understanding your Magallanes Water Provider bill statement</p>
                        </div>

                        {{-- Sample Bill Image --}}
                        <div class="card shadow-lg mb-5 border-0">
                            <div class="card-header text-white" style="background-color: #0C6170;">
                                <h5 class="mb-0">
                                    <i class="bi bi-image me-2"></i>
                                    Sample Bill Statement
                                </h5>
                            </div>
                            <div class="card-body p-0 text-center">
                                <img src="{{ asset('images/waterbill.jpg') }}" 
                                     alt="Water Bill Sample" 
                                     class="img-fluid"
                                     style="max-height: 600px;"
                                     onerror="this.src='{{ asset('images/MAGALLANES_LOGO.png') }}'; this.style.opacity='0.3';">
                            </div>
                        </div>

                        {{-- Bill Sections Explained --}}
                        <div class="bill-section">
                            <h4 class="mb-4" style="color: #0C6170;">
                                <i class="bi bi-person-badge me-2"></i>
                                1. Consumer Information
                            </h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="bill-highlight" data-label="NAME">
                                        <strong>CASAÑA, MARIFI</strong>
                                        <p class="mb-0 text-muted small">The name of the water consumer/bill owner</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bill-highlight" data-label="PUROK/BRGY">
                                        <strong>5/MARCOS, MAGALLANES AGUSAN DEL NORTE</strong>
                                        <p class="mb-0 text-muted small">Your address location (Purok/Barangay)</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Billing Period --}}
                        <div class="bill-section">
                            <h4 class="mb-4" style="color: #0C6170;">
                                <i class="bi bi-calendar-event me-2"></i>
                                2. Billing Period Information
                            </h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="bill-highlight" data-label="BILLING DATE">
                                        <strong>Jan-2025</strong>
                                        <p class="mb-0 text-muted small">The month/year of this billing statement</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bill-highlight" data-label="DATE CREATED">
                                        <strong>Feb 5, 2025</strong>
                                        <p class="mb-0 text-muted small">When this bill was generated/printed</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bill-highlight" data-label="READING DATE">
                                        <strong>Feb 4, 2025</strong>
                                        <p class="mb-0 text-muted small">When your meter was last read</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Meter Information --}}
                        <div class="bill-section">
                            <h4 class="mb-4" style="color: #0C6170;">
                                <i class="bi bi-speedometer2 me-2"></i>
                                3. Meter Reading Details
                            </h4>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="bill-highlight" data-label="METER NO.">
                                        <strong>5-0905035</strong>
                                        <p class="mb-0 text-muted small">Your unique meter identification number</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bill-highlight" data-label="PREVIOUS READING">
                                        <strong>233</strong>
                                        <p class="mb-0 text-muted small">Last month's meter reading</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bill-highlight" data-label="PRESENT READING">
                                        <strong>332</strong>
                                        <p class="mb-0 text-muted small">Current meter reading</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bill-highlight" data-label="CU.M CONSUMED">
                                        <strong>99</strong>
                                        <p class="mb-0 text-muted small">Total cubic meters used this month (332 - 233 = 99)</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Charges Breakdown --}}
                        <div class="bill-section">
                            <h4 class="mb-4" style="color: #0C6170;">
                                <i class="bi bi-cash-stack me-2"></i>
                                4. Charges Breakdown
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold">Current Billing</td>
                                            <td class="text-end">₱ 2,820.00</td>
                                            <td class="text-muted small">Water consumption charges for this month</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Total Prior Unpaid</td>
                                            <td class="text-end">₱ 1,915.47</td>
                                            <td class="text-muted small">Previous unpaid balance/arrears</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Maintenance Cost</td>
                                            <td class="text-end">₱ 20.00</td>
                                            <td class="text-muted small">Monthly maintenance fee</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Installation Cost</td>
                                            <td class="text-end">₱ 0.00</td>
                                            <td class="text-muted small">One-time installation fee (if applicable)</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Excess Hose</td>
                                            <td class="text-end">₱ 0.00</td>
                                            <td class="text-muted small">Additional charges for excess hose length</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Total Amount Due --}}
                        <div class="bill-section">
                            <h4 class="mb-4" style="color: #0C6170;">
                                <i class="bi bi-calculator me-2"></i>
                                5. Total Amount Due
                            </h4>
                            <div class="amount-due mb-3">
                                ₱ 4,755.47
                            </div>
                            <p class="text-center text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                This is the total amount you need to pay before the due date
                            </p>
                        </div>

                        {{-- Important Notes --}}
                        <div class="bill-section">
                            <h4 class="mb-4" style="color: #dc3545;">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                6. Important Reminders
                            </h4>
                            <div class="alert alert-danger">
                                <ol class="mb-0">
                                    <li class="mb-2">
                                        <strong>Two (2) Consecutive UNPAID Billings follows the DISCONNECTION.</strong>
                                        <br><small class="text-muted">If you don't pay for 2 months, your water service will be disconnected.</small>
                                    </li>
                                    <li class="mb-2">
                                        <strong>This account must be settled on or before: Feb 20, 2025</strong>
                                        <br><small class="text-muted">Pay before this date to avoid penalty charges.</small>
                                    </li>
                                    <li>
                                        <strong>Water service will be disconnected without prior notice.</strong>
                                        <br><small class="text-muted">No warning will be given before disconnection for non-payment.</small>
                                    </li>
                                </ol>
                            </div>
                            <div class="alert alert-warning mt-3">
                                <i class="bi bi-sticky me-2"></i>
                                <strong>NOTE:</strong> Please disregard if the payment has been made.
                            </div>
                        </div>

                        {{-- Authorized Personnel --}}
                        <div class="bill-section">
                            <h4 class="mb-4" style="color: #0C6170;">
                                <i class="bi bi-people me-2"></i>
                                7. Authorized Personnel
                            </h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="border rounded p-3 text-center">
                                        <p class="mb-1"><strong>GERLIE AGUELO</strong></p>
                                        <p class="text-muted small mb-0">Prepared by</p>
                                        <p class="text-muted small">(SIGNATURE)</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-3 text-center">
                                        <p class="mb-1"><strong>CASAÑA, MARIFI</strong></p>
                                        <p class="text-muted small mb-0">Received by</p>
                                        <p class="text-muted small">(SIGNATURE)</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Quick Tips --}}
                        <div class="card shadow mb-4">
                            <div class="card-header text-white" style="background-color: #198754;">
                                <h5 class="mb-0">
                                    <i class="bi bi-lightbulb me-2"></i>
                                    Quick Tips for Understanding Your Bill
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="d-flex">
                                            <div class="info-icon me-3">
                                                <i class="bi bi-1-circle"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-bold">Check Your Consumption</h6>
                                                <p class="text-muted small mb-0">Compare your current reading with previous months to track your water usage.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex">
                                            <div class="info-icon me-3">
                                                <i class="bi bi-2-circle"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-bold">Pay Before Due Date</h6>
                                                <p class="text-muted small mb-0">Avoid penalties by paying on or before the specified due date.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex">
                                            <div class="info-icon me-3">
                                                <i class="bi bi-3-circle"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-bold">Keep Your Receipt</h6>
                                                <p class="text-muted small mb-0">Always keep proof of payment for future reference.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex">
                                            <div class="info-icon me-3">
                                                <i class="bi bi-4-circle"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-bold">Report Discrepancies</h6>
                                                <p class="text-muted small mb-0">If you notice unusual readings, contact us immediately.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- FAQ Section --}}
                        <div class="card shadow mb-4">
                            <div class="card-header text-dark" style="background-color: #ffc107;">
                                <h5 class="mb-0">
                                    <i class="bi bi-question-circle me-2"></i>
                                    Frequently Asked Questions
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="accordion" id="billFAQ">
                                    {{-- FAQ 1 --}}
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#billFaq1">
                                                Why is my bill higher than usual?
                                            </button>
                                        </h2>
                                        <div id="billFaq1" class="accordion-collapse collapse show" data-bs-parent="#billFAQ">
                                            <div class="accordion-body">
                                                Higher bills may be due to: increased water consumption, unpaid previous balances, leaks in your plumbing, or additional charges. Check your "CU.M CONSUMED" to see if you used more water than usual.
                                            </div>
                                        </div>
                                    </div>

                                    {{-- FAQ 2 --}}
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#billFaq2">
                                                What happens if I pay after the due date?
                                            </button>
                                        </h2>
                                        <div id="billFaq2" class="accordion-collapse collapse" data-bs-parent="#billFAQ">
                                            <div class="accordion-body">
                                                Late payments may incur penalty charges. If unpaid for 2 consecutive months, your water service will be disconnected without prior notice.
                                            </div>
                                        </div>
                                    </div>

                                    {{-- FAQ 3 --}}
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#billFaq3">
                                                How is my water consumption calculated?
                                            </button>
                                        </h2>
                                        <div id="billFaq3" class="accordion-collapse collapse" data-bs-parent="#billFAQ">
                                            <div class="accordion-body">
                                                Your consumption is calculated by subtracting the Previous Reading from the Present Reading. Example: 332 - 233 = 99 cubic meters consumed.
                                            </div>
                                        </div>
                                    </div>

                                    {{-- FAQ 4 --}}
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#billFaq4">
                                                What does "Total Prior Unpaid" mean?
                                            </button>
                                        </h2>
                                        <div id="billFaq4" class="accordion-collapse collapse" data-bs-parent="#billFAQ">
                                            <div class="accordion-body">
                                                This is the accumulated unpaid balance from previous billing periods. It includes any remaining balance from past bills that haven't been settled yet.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Need Help --}}
                        <div class="card shadow border-0 text-white mb-4" style="background-color: #0C6170;">
                            <div class="card-body text-center py-5">
                                <h3 class="mb-3">Still Have Questions?</h3>
                                <p class="mb-4">Our support team is ready to help you understand your bill.</p>
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
                            <a href="{{ url('/') }}" class="btn btn-outline-secondary">
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