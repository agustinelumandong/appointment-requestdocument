<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Appointment System - Municipality of Santo Tomas</title>
    <meta name="description" content="Book your appointments easily at MTO, MCRO, and BPLS offices in Santo Tomas." />
    <meta name="keywords" content="Santo Tomas, Appointment, MTO, MCRO, BPLS, Municipality" />

    <!-- Bootstrap CSS & icons from CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Mulish&display=swap" rel="stylesheet">
    <style>
    main {
      font-family: 'Mulish', sans-serif;
    }

    .note-arrow {
    position: absolute;
    left: -100px;
    bottom: -80px;
    text-align: center;
    width: 120px;
}

.arrow-text {
    position: absolute;
    top: 50px; /* moves it below the arrow tail */
    left: 100px;
    font-size: 1rem;
    color: #001f3f;
    font-style: italic;
    font-family: 'Mulish', sans-serif;
    width: 100%;
}

.service-card {
  transition: all 0.4s ease;
  cursor: pointer;
  overflow: hidden;
  background-color: transparent;
  border: none;
  position: relative;
  min-height: 250px;
}

.service-card .service-description {
  opacity: 0;
  max-height: 0;
  transition: all 0.4s ease;
  overflow: hidden;
}

.service-card:hover .service-description {
  opacity: 1;
  max-height: 100px;
}

.icon-wrapper {
  display: inline-block;
  border-radius: 50%;
  transition: box-shadow 0.4s ease, transform 0.4s ease;
  padding: 1rem;
}

.icon-style {
  font-size: 7rem;
  transition: transform 0.4s ease;
}

.service-card:hover .icon-wrapper {
  box-shadow: 0 0 25px rgba(0, 123, 255, 0.3);
  transform: scale(1.1);
}

.spacer-section {
  height: 100px; /* Adjust this to control the scroll length */
}

  </style>

  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
</head>

<body>
    <header style="background-color: #2c3e50; color: white; padding: 1rem;">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <img src="{{ asset('uploads/images/logo/lgu.png') }}" alt="Logo" height="100">
            <div class="text-white" style="line-height: 1.3;">
    <div>Republic of the Philippines</div>
    <hr class="border-white opacity-75 my-1" style="max-width: 200px;" />
    <div style="font-size: 1.5rem;">Santo Tomas, Davao del Norte</div>
    <div><strong>Katawhan ang UNA</strong></div>
</div>
        </div>

        <nav>
            <ul class="nav">
                @guest
                    <li class="nav-item">
                        <a href="{{ route('login') }}" class="nav-link text-white">Login</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('register') }}" class="nav-link text-white">Register</a>
                    </li>
                @endguest
                @auth
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link text-white">Dashboard</a>
                    </li>
                @endauth
            </ul>
        </nav>
    </div>
</header>

    <main style="background-color: #ffffff; color: #000000; padding: 4rem 1rem;">
    <div class="container d-flex flex-column flex-lg-row align-items-center justify-content-between">
        <div class="text-center text-lg-start mb-5 mb-lg-0">
            <h1 style="font-size: 3rem; font-weight: bold; line-height: 1.2;">
                Book Your Municipal <br>Appointment Online.
            </h1>
            <p class="mt-3" style="font-size: 1.2rem;">
                Access government services faster and easier!<br>
                with our new digital scheduling system. Set Up Your Appointment Today.
            </p>
            <div class="mt-4 d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start">

                <div class="position-relative d-inline-block">
    <a href="{{ route('home') }}" class="btn btn-danger btn-lg rounded-pill px-4">
        <i class="bi bi-person-fill"></i> Book an Appointment
    </a>

    <div class="note-arrow">
        <svg width="80" height="80" viewBox="0 0 100 100" class="curved-svg">
            <path d="M90,70 Q20,40 80,10" stroke="#001f3f" stroke-width="2" fill="none" marker-end="url(#arrowhead)" />
            <defs>
                <marker id="arrowhead" markerWidth="10" markerHeight="7"
                        refX="0" refY="3.5" orient="auto">
                    <polygon points="0 0, 10 3.5, 0 7" fill="#001f3f" />
                </marker>
            </defs>
        </svg>
        <div class="arrow-text">No login needed</div>
    </div>
</div>
                <a href="{{ route('document.request') }}" class="btn btn-outline-dark btn-lg rounded-pill px-4"> Request a Document</a>
            </div>
        </div>
        <div class="text-center">
            <img src="{{ asset('uploads/images/logo/municipal.jpg') }}" alt="Doctor" class="img-fluid" style="max-height: 400px;">
        </div>
    </div>

</main>

<section class="spacer-section bg-white"></section>

<section class="py-5 bg-white">
  <div class="container">
    <h2 class="text-center fw-bold mb-5" data-aos="fade-up">How It Works</h2>
    <div class="row text-center">
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
        <i class="bi bi-calendar2-check-fill text-primary mb-3" style="font-size: 3rem;"></i>
        <h5 class="fw-bold">Set Appointment</h5>
        <p class="text-muted">Choose your preferred office and date.<br> No login required.</p>
      </div>
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
        <i class="bi bi-envelope-paper-fill text-success mb-3" style="font-size: 3rem;"></i>
        <h5 class="fw-bold">Get Confirmation</h5>
        <p class="text-muted">Receive an SMS or email <br>confirming your appointment details.</p>
      </div>
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
        <i class="bi bi-person-check-fill text-danger mb-3" style="font-size: 3rem;"></i>
        <h5 class="fw-bold">Visit the Office</h5>
        <p class="text-muted">Show your confirmation and proceed <br> with your transaction hassle-free.</p>
      </div>
    </div>
  </div>
</section>

<section class="spacer-section bg-white"></section>

<section class="py-5 bg-white">
  <div class="container">
    <h2 class="text-center fw-bold mb-5" data-aos="fade-up">Our Municipal Offices</h2>
    <div class="row g-4">
      <!-- MCRO -->
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
        <div class="service-card text-center p-4">
          <div class="icon-wrapper mb-3">
            <i class="bi bi-person-vcard icon-style text-primary"></i>
          </div>
          <h5 class="fw-bold">Municipal Civil Registrar</h5>
          <p class="service-description text-muted mt-2">Handles birth certificates, marriage licenses, and other civil registry documents.</p>
        </div>
      </div>

      <!-- BPLS -->
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
        <div class="service-card text-center p-4">
          <div class="icon-wrapper mb-3">
            <i class="bi bi-briefcase-fill icon-style text-success"></i>
          </div>
          <h5 class="fw-bold">Business Permit and Licensing Section</h5>
          <p class="service-description text-muted mt-2">Assists with securing business permits, renewals, and compliance certificates.</p>
        </div>
      </div>

      <!-- Treasurer -->
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
        <div class="service-card text-center p-4">
          <div class="icon-wrapper mb-3">
            <i class="bi bi-cash-stack icon-style text-danger"></i>
          </div>
          <h5 class="fw-bold">Municipal Treasurer's Office</h5>
          <p class="service-description text-muted mt-2">Responsible for tax payments, assessments, and other municipal financial services.</p>
        </div>
      </div>
    </div>
  </div>
</section>


<section class="py-5 bg-white text-black text-center">
  <div class="container">
    <h2 class="fw-bold mb-3" data-aos="fade-up">Ready to Book?</h2>
    <p class="mb-4" data-aos="fade-up" data-aos-delay="100">Join thousands of Santo Tomas residents making their government visits easier.</p>
    <a href="{{ route('home') }}" class="btn btn-primary btn-lg rounded-pill px-5" data-aos="zoom-in" data-aos-delay="200">
      Book an Appointment Now
    </a>
  </div>
</section>

<section class="py-5 bg-light">
  <div class="container text-center">
    <p class="text-muted">More services coming soon. Stay tuned!</p>
  </div>
</section>

    <footer class="bg-dark text-white py-4 mt-auto">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3">
                <h5>Municipality of Santo Tomas</h5>
                <p class="small">
                    Empowering citizens with accessible government services through a modern appointment system.
                </p>
            </div>
            <div class="col-md-4 mb-3">
                <h6>Quick Links</h6>
                <ul class="list-unstyled small">
                    <li><a href="{{ route('login') }}" class="text-white text-decoration-none">Login</a></li>
                    <li><a href="{{ route('register') }}" class="text-white text-decoration-none">Register</a></li>
                    <li><a href="#" class="text-white text-decoration-none">About Us</a></li>
                    <li><a href="#" class="text-white text-decoration-none">Help Center</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-3">
                <h6>Contact Us</h6>
                <p class="small mb-1">
                    Santo Tomas Municipal Hall<br />
                    Santo Tomas, Davao del Norte
                </p>
                <p class="small mb-0">
                    <i class="bi bi-envelope"></i> support@stotomasmunicipality.gov.ph<br />
                    <i class="bi bi-phone"></i> 09913724619
                </p>
            </div>
        </div>
        <hr class="border-secondary" />
        <div class="text-center small">
            &copy; {{ date('Y') }} Municipality of Santo Tomas. All rights reserved.
        </div>
    </div>
</footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
  AOS.init({
    duration: 800,
    once: true
  });
</script>
</body>

</html>
