<?php
session_start();
require_once 'config.php';
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS site_content (
  content_key VARCHAR(60) NOT NULL PRIMARY KEY,
  content_value TEXT NOT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
mysqli_query($conn, "INSERT IGNORE INTO site_content (content_key, content_value) VALUES
  ('hero_title', 'Where Beauty Meets Elegance'),
  ('hero_subtitle', 'Hair styling, manicures, pedicures and signature facials — every appointment, client and product managed effortlessly through one beautiful salon management application.'),
  ('about_paragraph', 'Welcome to Elegance Salon. Our team offers personalized beauty services in a warm, comfortable setting.')");
$homeContent = array();
$contentResult = mysqli_query($conn, "SELECT content_key, content_value FROM site_content");
if ($contentResult) while ($contentRow = mysqli_fetch_assoc($contentResult)) { $homeContent[$contentRow['content_key']] = $contentRow['content_value']; }
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Elegance Salon</title>
  <link rel="icon"
    href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='88'>💠</text></svg>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=Jost:wght@300;400;500;600&display=swap"
    rel="stylesheet">
  <link href="css/style.css?v=20261002-hero" rel="stylesheet">
</head>

<body data-page="index">
  <a class="visually-hidden-focusable" href="#main">Skip to content</a>

  <nav class="navbar navbar-expand-lg sticky-top main-nav">
    <div class="container">
      <a class="navbar-brand" href="index.php">
        <span class="brand-icon"><i class="bi bi-flower1"></i></span>
        Elegance <span class="text-accent">Salon</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
        aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
          <li class="nav-item"><a class="nav-link" href="index.php" data-nav="index">Home</a></li>
          <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin') { ?>

  <li class="nav-item">
    <a class="nav-link" href="admin.php">
      Admin
    </a>
  </li>

<?php } ?>
          <li class="nav-item"><a class="nav-link" href="contact.php" data-nav="contact">Contact Us</a></li>
          <li class="nav-item"><a class="nav-link" href="feedback.php" data-nav="feedback">Feedback</a></li>
          <?php if(!isset($_SESSION['user_id'])) { ?>

  <li class="nav-item ms-lg-2 my-2 my-lg-0">
    <a class="btn btn-accent btn-sm rounded-pill px-3" href="login.php">
      <i class="bi bi-box-arrow-in-right me-1"></i>Login
    </a>
  </li>

<?php } else { ?>

  <li class="nav-item dropdown ms-lg-2">
    <a class="nav-link dropdown-toggle d-flex align-items-center"
       href="#"
       id="userMenu"
       role="button"
       data-bs-toggle="dropdown"
       aria-expanded="false">

      <i class="bi bi-person-circle fs-5 me-1"></i>

      <?php echo $_SESSION['username']; ?>

    </a>

    <ul class="dropdown-menu dropdown-menu-end shadow-sm"
        aria-labelledby="userMenu">

      <li>
        <span class="dropdown-item-text small text-muted">
          <i class="bi bi-shield-check me-1"></i>

          <?php echo $_SESSION['role']; ?>

        </span>
      </li>

      <li>
        <hr class="dropdown-divider">
      </li>

      <li>
        <a class="dropdown-item" href="dashboard.php">
          <i class="bi bi-speedometer2 me-2"></i>
          Dashboard
        </a>
      </li>

      <li>
        <a class="dropdown-item text-danger" href="logout.php">
          <i class="bi bi-box-arrow-right me-2"></i>
          Logout
        </a>
      </li>

    </ul>
  </li>

<?php } ?>
        </ul>
      </div>
    </div>
  </nav>
  <main id="main">
    <!-- ============ HERO ============ -->
    <section class="hero">
      <div class="container">
        <div class="row">
          <div class="col-lg-8 text-white">
            <span class="eyebrow">Elegance Salon · San Jose, CA</span>
            <h1 class="display-2 mt-3" id="heroTitle"><?php echo htmlspecialchars(isset($homeContent['hero_title']) ? $homeContent['hero_title'] : 'Where Beauty Meets Elegance', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="lead text-white-50 col-lg-9" id="heroSub"><?php echo htmlspecialchars(isset($homeContent['hero_subtitle']) ? $homeContent['hero_subtitle'] : 'Hair styling, manicures, pedicures and signature facials', ENT_QUOTES, 'UTF-8'); ?></p>
            <div class="d-flex flex-wrap gap-3 mt-4">
              <?php if(isset($_SESSION['user_id'])) { ?>

  <a href="appointments.php" class="btn btn-accent btn-lg px-4">
    <i class="bi bi-calendar-check me-2"></i>
    Book an Appointment
  </a>

<?php } else { ?>

  <a href="login.php" class="btn btn-accent btn-lg px-4">
    <i class="bi bi-calendar-check me-2"></i>
    Book an Appointment
  </a>

<?php } ?>
              <a href="services.php" class="btn btn-outline-light btn-lg px-4">All Services</a>
              <a href="about.php" class="btn btn-outline-light btn-lg px-4">About Us</a>
            </div>
            <div class="d-flex flex-wrap gap-5 mt-5">
              <div>
                <h4 class="text-warning mb-0">4.9</h4><small class="text-white-50">Average client rating</small>
              </div>
              <div>
                <h4 class="text-warning mb-0">5,000+</h4><small class="text-white-50">Happy clients served</small>
              </div>
              <div>
                <h4 class="text-warning mb-0">15+</h4><small class="text-white-50">Expert stylists</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ============ MODULES ============ -->
    <section class="py-5 mt-0 mb-3">
      <div class="container">
        <div class="text-center mb-5">
          <span class="eyebrow">Everything in one place</span>
          <h2 class="display-5 mt-2">Salon Management, Simplified</h2>
          <div class="divider-accent mx-auto"></div>
        </div>
        <div class="row g-4">
          <div class="col-md-6 col-lg-4">
            <div class="feature-card">
              <span class="feature-icon"><i class="bi bi-calendar-check"></i></span>
              <h5>Appointment Management</h5>
              <p class="small mb-0">View live time slots, book, reschedule or cancel in seconds, with automated
                email/SMS confirmations for every booking.</p>
            </div>
          </div>
          <div class="col-md-6 col-lg-4">
            <div class="feature-card">
              <span class="feature-icon"><i class="bi bi-people"></i></span>
              <h5>Client Management</h5>
              <p class="small mb-0">A centralized client database with contact details, service history and personal
                preferences for every stylist and treatment.</p>
            </div>
          </div>
          <div class="col-md-6 col-lg-4">
            <div class="feature-card">
              <span class="feature-icon"><i class="bi bi-box-seam"></i></span>
              <h5>Inventory Control</h5>
              <p class="small mb-0">Track salon supplies in real time, get low-stock alerts and let the system draft
                purchase orders to suppliers automatically.</p>
            </div>
          </div>
          <div class="col-md-6 col-lg-4">
            <div class="feature-card">
              <span class="feature-icon"><i class="bi bi-scissors"></i></span>
              <h5>Staff &amp; Scheduling</h5>
              <p class="small mb-0">Manage profiles, shifts and task assignment, and track commissions calculated from
                services each stylist performs.</p>
            </div>
          </div>
          <div class="col-md-6 col-lg-4">
            <div class="feature-card">
              <span class="feature-icon"><i class="bi bi-graph-up-arrow"></i></span>
              <h5>Reports &amp; Analytics</h5>
              <p class="small mb-0">Beautiful dashboards for sales, bookings, popular services, peak hours and inventory
                usage trends.</p>
            </div>
          </div>
          <div class="col-md-6 col-lg-4">
            <div class="feature-card">
              <span class="feature-icon"><i class="bi bi-receipt-cutoff"></i></span>
              <h5>Payments &amp; Invoices</h5>
              <p class="small mb-0">Record card, cash or UPI payments and generate printable invoices and receipts for
                services and products.</p>
            </div>
          </div>
        </div>
        <div class="text-center mt-4"><a class="btn btn-outline-accent" href="services.php">View all services</a></div>
      </div>
    </section>

    <div class="modal fade" id="homeServiceModal" tabindex="-1" aria-labelledby="homeServiceTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header"><h2 class="modal-title h5" id="homeServiceTitle">Service details</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
          <div class="modal-body">
            <p id="homeServiceDescription" class="mb-3"></p>
            <h3 class="h6 mt-3">What is included</h3>
            <p id="homeServiceIncludes" class="text-muted"></p>
            <h3 class="h6 mt-3">Good to know</h3>
            <p id="homeServiceSuitable" class="text-muted"></p>
            <div class="d-flex justify-content-between"><span>Estimated time</span><strong id="homeServiceDuration"></strong></div>
            <div class="d-flex justify-content-between mt-2"><span>Starting price</span><strong id="homeServicePrice"></strong></div>
            <p class="small text-muted mt-3 mb-0">Prices are starting estimates. Final timing and price can vary with hair length, product use, or the treatment selected.</p>
          </div>
          <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button><a class="btn btn-accent" id="homeServiceBookLink" href="<?php echo isset($_SESSION['user_id']) ? 'appointments.php' : 'login.php'; ?>">Book an appointment</a></div>
        </div>
      </div>
    </div>

    <!-- ============ ABOUT ============ -->
    <section class="py-5 bg-soft border-top border-bottom" style="border-color:var(--es-line)!important">
      <div class="container py-3">
        <div class="row align-items-center g-5">
          <div class="col-lg-6">
            <img src="https://sspark.genspark.ai/i/NtCnFrbBhRhV1vHN" alt="Elegance Salon interior"
              class="img-fluid rounded-4 shadow" style="max-height:420px;width:100%;object-fit:cover">
          </div>
          <div class="col-lg-6">
            <span class="eyebrow">About Elegance Salon</span>
            <h2 class="display-6 mt-2 mb-3">A Modern Salon With A Personal Touch</h2>
            <p id="aboutText">For over a decade Elegance Salon has helped clients look and feel their best. Our
              certified stylists and therapists combine premium products with a warm, luxurious atmosphere â€” while
              behind the scenes our management platform keeps every appointment, product and payment perfectly
              organized.</p>
            <ul class="list-unstyled mt-4 small">
              <li class="mb-2"><i class="bi bi-check-circle-fill text-accent me-2"></i>Certified stylists &amp; beauty
                therapists</li>
              <li class="mb-2"><i class="bi bi-check-circle-fill text-accent me-2"></i>Premium, cruelty-free product
                lines</li>
              <li class="mb-2"><i class="bi bi-check-circle-fill text-accent me-2"></i>Online booking with instant
                confirmations</li>
              <li class="mb-2"><i class="bi bi-check-circle-fill text-accent me-2"></i>Strict hygiene &amp; sanitation
                standards</li>
            </ul>
            <?php if(isset($_SESSION['user_id'])) { ?>

  <a href="appointments.php" class="btn btn-accent px-4 mt-2">
    Get Started
  </a>

<?php } else { ?>

  <a href="login.php" class="btn btn-accent px-4 mt-2">
    Get Started
  </a>

<?php } ?>
          </div>
        </div>
      </div>
    </section>

    <!-- ============ SERVICES ============ -->
    <section id="services" class="py-5 my-3">
      <div class="container">
        <div class="text-center mb-5">
          <span class="eyebrow">What we do</span>
          <h2 class="display-5 mt-2">Signature Services</h2>
          <div class="divider-accent mx-auto"></div>
        </div>
        <div class="row g-4">
          <div class="col-md-6 col-lg-3">
            <div class="service-card" role="button" tabindex="0" data-service-title="Hair Styling" data-service-description="A polished blowout or finished style for an everyday refresh or a special occasion." data-service-duration="45 min" data-service-price="$45" data-service-includes="A style consultation, heat protection, blow-dry or styling, and finishing touches." data-service-suitable="Events, celebrations, or a smooth everyday style." data-bs-toggle="modal" data-bs-target="#homeServiceModal">
              <img src="https://sspark.genspark.ai/i/Degjvk4siSvjINZS" alt="Hair styling at the salon">
              <div class="overlay">
                <h5 class="mb-1">Hair Styling</h5><small>from $45 Â· 45 min</small>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-lg-3">
            <div class="service-card" role="button" tabindex="0" data-service-title="Manicure" data-service-description="Nail shaping, cuticle care, hand care, and your choice of a classic polish finish." data-service-duration="40 min" data-service-price="$25" data-service-includes="Nail shaping, cuticle care, hand moisturizer, and classic polish." data-service-suitable="A tidy-up or a simple polish refresh." data-bs-toggle="modal" data-bs-target="#homeServiceModal">
              <img src="https://sspark.genspark.ai/i/xcZCmKR7jNwQ9RrV" alt="Professional manicure">
              <div class="overlay">
                <h5 class="mb-1">Manicure</h5><small>from $25 Â· 40 min</small>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-lg-3">
            <div class="service-card" role="button" tabindex="0" data-service-title="Pedicure" data-service-description="A soothing foot soak, nail care, exfoliation, and a fresh polish finish." data-service-duration="50 min" data-service-price="$35" data-service-includes="A foot soak, nail shaping, cuticle care, gentle exfoliation, and classic polish." data-service-suitable="Routine foot care and a relaxing break." data-bs-toggle="modal" data-bs-target="#homeServiceModal">
              <img src="https://sspark.genspark.ai/i/TuW5E7BIsD2tZuwz" alt="Pedicure and nail care">
              <div class="overlay">
                <h5 class="mb-1">Pedicure</h5><small>from $35 Â· 50 min</small>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-lg-3">
            <div class="service-card" role="button" tabindex="0" data-service-title="Signature Facial" data-book-service="Facial" data-service-description="A relaxing cleanse, gentle exfoliation, and hydrating mask selected for your skin needs." data-service-duration="60 min" data-service-price="$60" data-service-includes="A skin check-in, cleanse, gentle exfoliation, mask, and moisturizer." data-service-suitable="A calm skin refresh. Tell your therapist about sensitivities before treatment." data-bs-toggle="modal" data-bs-target="#homeServiceModal">
              <img src="https://sspark.genspark.ai/i/h2cIpzJmdF5qCOoY" alt="Facial treatment">
              <div class="overlay">
                <h5 class="mb-1">Signature Facial</h5><small>from $60 Â· 60 min</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ============ CTA ============ -->
    <section class="py-5 mb-2">
      <div class="container">
        <div class="rounded-4 p-5 text-center text-white" style="background:linear-gradient(120deg,#1f1713,#4b4038)">
          <h2 class="display-6 text-white">Ready for your makeover?</h2>
          <p class="text-white-50 mb-4">Sign in and secure your slot in under a minute â€” reminders included.</p>
          <?php if(isset($_SESSION['user_id'])) { ?>

  <a href="appointments.php" class="btn btn-accent btn-lg px-4">
    Book Now <i class="bi bi-arrow-right ms-1"></i>
  </a>

<?php } else { ?>

  <a href="login.php" class="btn btn-accent btn-lg px-4">
    Book Now <i class="bi bi-arrow-right ms-1"></i>
  </a>

<?php } ?>
        </div>
      </div>
    </section>

  </main>

  <footer class="site-footer mt-auto">
    <div class="container py-5">
      <div class="row g-4">
        <div class="col-lg-4">
          <a class="navbar-brand text-white d-inline-flex align-items-center mb-2" href="index.php">
            <span class="brand-icon"><i class="bi bi-flower1"></i></span> Elegance <span
              class="text-accent">Salon</span>
          </a>
          <p class="text-white-50 small pe-lg-4">Salon Management Application â€” appointments, client relations,
            inventory control and staff scheduling, all in one place.</p>
          <div class="d-flex gap-3 fs-5 footer-social">
            <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
            <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            <a href="#" aria-label="X"><i class="bi bi-twitter-x"></i></a>
            <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
          </div>
        </div>
        <div class="col-6 col-lg-2">
          <h6 class="footer-title">Quick Links</h6>
          <ul class="list-unstyled footer-links small">
            <li><a href="index.php">Home</a></li>
          <li><a href="about.php">About</a></li>
  
          <li><a href="contact.php">Contact</a></li>          <li><a href="appointments.php">Appointments</a></li>
            <?php if (!isset($_SESSION['role']) || $_SESSION['role'] != 'client') { ?><li><a href="reports.php">Reports</a></li><?php } ?>
            <li><a href="feedback.php">Feedback</a></li>
          </ul>
        </div>
        <div class="col-6 col-lg-3">
          <h6 class="footer-title">Opening Hours</h6>
          <ul class="list-unstyled small text-white-50 mb-0">
            <li class="d-flex justify-content-between"><span>Mon - Fri</span><span>9:00 AM - 7:00 PM</span></li>
            <li class="d-flex justify-content-between"><span>Saturday</span><span>9:00 AM - 5:00 PM</span></li>
            <li class="d-flex justify-content-between"><span>Sunday</span><span>Closed</span></li>
          </ul>
        </div>
        <div class="col-lg-3">
          <h6 class="footer-title">Get in Touch</h6>
          <ul class="list-unstyled small text-white-50 mb-0">
            <li><i class="bi bi-geo-alt me-2 text-accent"></i>128 Rosewood Avenue, San Jose, CA</li>
            <li><i class="bi bi-telephone me-2 text-accent"></i>+1 (555) 240-8890</li>
            <li><i class="bi bi-envelope me-2 text-accent"></i>hello@elegancesalon.com</li>
          </ul>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <div class="container small d-flex flex-column flex-md-row justify-content-between text-white-50">
        <span>Â© 2026 Elegance Salon. Template for demonstration purposes.</span>
      </div>
    </div>
  </footer>

  <div id="toastArea" class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1090"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
  <script>
    document.querySelectorAll('[data-service-title]').forEach(function (button) {
      button.addEventListener('click', function () {
        document.getElementById('homeServiceTitle').textContent = button.getAttribute('data-service-title');
        document.getElementById('homeServiceDescription').textContent = button.getAttribute('data-service-description');
        document.getElementById('homeServiceDuration').textContent = button.getAttribute('data-service-duration');
        document.getElementById('homeServicePrice').textContent = button.getAttribute('data-service-price');
        document.getElementById('homeServiceIncludes').textContent = button.getAttribute('data-service-includes');
        document.getElementById('homeServiceSuitable').textContent = button.getAttribute('data-service-suitable');
        var bookLink = document.getElementById('homeServiceBookLink');
        var baseLink = '<?php echo isset($_SESSION['user_id']) ? 'appointments.php' : 'login.php'; ?>';
        var bookService = button.getAttribute('data-book-service') || button.getAttribute('data-service-title');
        bookLink.href = baseLink + '?service=' + encodeURIComponent(bookService);
      });
      button.addEventListener('keydown', function (event) {
        if (event.key == 'Enter' || event.key == ' ') {
          event.preventDefault();
          button.click();
        }
      });
    });
  </script>


</body>

</html>
