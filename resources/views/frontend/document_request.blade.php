<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css"
        integrity="sha512-10/jx2EXwxxWqCLX/hHth/vu2KY3jCF70dCQB8TSgNjbCVAC/8vai53GfMDrO2Emgwccf2pJqxct9ehpzG+MTw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  
</head>

<body>
    <header class="header-section">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container">
                <a class="navbar-brand" href="#">
                    <img src="{{ asset('uploads/images/logo/lgu.png') }}" alt="Logo" height="50"> Requisition and Appointment System
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        @guest
                            <li class="nav-item">
                                <a class="nav-link active" href="{{ route('login') }}">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">Register</a>
                            </li>
                        @endguest

                        @auth
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                            </li>
                        @endauth

                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div class="container">
        <div class="booking-container">
            <div class="booking-header">
                <h2><i class="bi bi-file-earmark-text"></i> Document Request</h2>
                <p class="mb-0">Request your document in a few simple steps</p>
            </div>

            <div class="booking-steps position-relative">
                <div class="step active" data-step="1">
                    <div class="step-number">1</div>
                    <div class="step-title">Document Type</div>
                </div>
                <div class="step" data-step="2">
                    <div class="step-number">2</div>
                    <div class="step-title">For Whom?</div>
                </div>
                <div class="step" data-step="3">
                    <div class="step-number">3</div>
                    <div class="step-title">Application Form</div>
                </div>
                <div class="step" data-step="4">
                    <div class="step-number">4</div>
                    <div class="step-title">Purpose & Contact</div>
                </div>
                <div class="step" data-step="5">
                    <div class="step-number">5</div>
                    <div class="step-title">Claiming Date/Time</div>
                </div>
                <div class="step" data-step="6">
                    <div class="step-number">6</div>
                    <div class="step-title">Review</div>
                </div>
                <div class="progress-bar-steps">
                    <div class="progress"></div>
                </div>
            </div>

            <div class="booking-content">
                <!-- Step 1: Document Type -->
                <div class="booking-step active" id="doc-step1">
                    <h3 class="mb-4">Select Document Type</h3>
                    <div class="row" id="document-types-container">
                        <!-- Document types will be inserted here by JS -->
                    </div>
                </div>
                <!-- Step 2: For Whom -->
                <div class="booking-step" id="doc-step2">
                    <h3 class="mb-4">Request For</h3>
                    <div class="row" id="for-whom-container">
                        <!-- For whom will be inserted here by JS -->
                    </div>
                </div>
                <!-- Step 3: Application Form -->
                <div class="booking-step" id="doc-step3">
                    <h3 class="mb-4">Application Form</h3>
                    <form id="application-form" class="row g-3"></form>
                </div>
                <!-- Step 4: Purpose & Contact -->
                <div class="booking-step" id="doc-step4">
                    <h3 class="mb-4">Purpose & Contact Info</h3>
                    <form id="purpose-contact-form" class="row g-3">
                        <div class="col-md-6">
                            <label for="purpose" class="form-label">Purpose</label>
                            <input type="text" class="form-control" id="purpose" required>
                        </div>
                        <div class="col-md-6">
                            <label for="delivery_method" class="form-label">Delivery Method</label>
                            <select class="form-select" id="delivery_method" required>
                                <option value="pickup">Pickup</option>
                                <option value="courier">Courier</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="contact_name" class="form-label">Contact Name</label>
                            <input type="text" class="form-control" id="contact_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="contact_phone" class="form-label">Contact Phone</label>
                            <input type="tel" class="form-control" id="contact_phone" required>
                        </div>
                        <div class="col-md-12">
                            <label for="contact_email" class="form-label">Contact Email</label>
                            <input type="email" class="form-control" id="contact_email" required>
                        </div>
                    </form>
                </div>
                <!-- Step 5: Claiming Date/Time -->
                <div class="booking-step" id="doc-step5">
                    <h3 class="mb-4">Preferred Date/Time for Claiming</h3>
                    <input type="date" id="claim_date" class="form-control mb-2" min="{{ now()->toDateString() }}" required>
                    <input type="time" id="claim_time" class="form-control" required>
                </div>
                <!-- Step 6: Review -->
                <div class="booking-step" id="doc-step6">
                    <h3 class="mb-4">Review Application</h3>
                    <div id="application-summary"></div>
                    <button id="submit-request" class="btn btn-success mt-3">Submit Request</button>
                </div>
                <!-- Confirmation Modal -->
                <div class="modal fade" id="docRequestSuccessModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title">Request Submitted!</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center p-4">
                                <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                                <h4 class="mt-3">Thank You!</h4>
                                <p>Your document request has been submitted.</p>
                                <div class="alert alert-info mt-3">
                                    <p class="mb-0">Reference #: <span id="doc-ref-number"></span></p>
                                    <p class="mb-0">A confirmation has been sent to your email.</p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="booking-footer">
                <button class="btn btn-outline-secondary" id="doc-prev-step" disabled>
                    <i class="bi bi-arrow-left"></i> Previous
                </button>
                <button class="btn btn-primary" id="doc-next-step">
                    Next <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>

    <footer>
        <div class="container pb-2">
            <div class="row text-center">
            <span>Designed & Developed by <a target="_blank" href="https://web.facebook.com/kurtjoshryl.bascogil">Kurt Joshryl Gil</a></span>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.documentTypes = @json($documentTypes ?? []);
        window.documentRequestStoreUrl = "{{ route('document.request.store') }}";
        window.csrfToken = "{{ csrf_token() }}";
    </script>
    <script src="{{ asset('assets/js/document_request.js') }}"></script>
    
</body>

</html>