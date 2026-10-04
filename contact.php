<?php
  session_start();
  if(!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit();
    }
    require_once 'config.php';

    $contact_message = '';
    if (isset($_POST['send_contact_message'])) {
        $name = isset($_POST['contact_name']) ? trim($_POST['contact_name']) : '';
        $email = isset($_POST['contact_email']) ? trim($_POST['contact_email']) : '';
        $subject = isset($_POST['contact_subject']) ? trim($_POST['contact_subject']) : '';
        $message_text = isset($_POST['contact_message']) ? trim($_POST['contact_message']) : '';
        if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($subject) < 4 || strlen($message_text) < 10) {
            $contact_message = 'Please complete every field with valid details.';
        } else {
            $name = mysqli_real_escape_string($conn, $name);
            $email = mysqli_real_escape_string($conn, $email);
            $subject = mysqli_real_escape_string($conn, $subject);
            $message_text = mysqli_real_escape_string($conn, $message_text);
            $user_id = mysqli_real_escape_string($conn, $_SESSION['user_id']);
            $query = "INSERT INTO contact_messages (user_id, name, email, subject, message) VALUES ('$user_id', '$name', '$email', '$subject', '$message_text')";
            if (mysqli_query($conn, $query)) {
                echo "<script>window.location.href = 'contact.php?sent=1';</script>";
                exit();
            }
            $contact_message = 'Your message could not be saved.';
        }
    }
?>







<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Contact Us — Elegance Salon</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='88'>💠</text></svg>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
</head>
<body data-page="contact">
<a class="visually-hidden-focusable" href="#main">Skip to content</a>

<nav class="navbar navbar-expand-lg sticky-top main-nav">
  <div class="container">
    <a class="navbar-brand" href="index.php">
      <span class="brand-icon"><i class="bi bi-flower1"></i></span>
      Elegance <span class="text-accent">Salon</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
        <li class="nav-item"><a class="nav-link" href="index.php" data-nav="index">Home</a></li>
        <li class="nav-item admin-only" data-nav-item="admin"><?php if($_SESSION['role'] == 'admin'): ?><a class="nav-link" href="admin.php" data-nav="admin">Admin</a><?php endif; ?></li>
        <li class="nav-item"><a class="nav-link" href="contact.php" data-nav="contact">Contact Us</a></li>
        <li class="nav-item"><a class="nav-link" href="feedback.php" data-nav="feedback">Feedback</a></li>
        <li class="nav-item dropdown auth-only role-hidden">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle fs-5 me-1"></i><?php echo $_SESSION['username'];  ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userMenu">
            <li><span class="dropdown-item-text small text-muted"><i class="bi bi-shield-check me-1"></i></i><?php echo $_SESSION['role']; ?></span></span></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
            <li><a class="dropdown-item text-danger" href="logout.php" id="logoutBtn"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
<main id="main">
<section class="page-title-band">
  <div class="container">
    <span class="eyebrow text-white-50">We'd love to hear from you</span>
    <h1 class="mt-2">Contact Us</h1>
    <p>Questions about the Elegance Salon application? Reach the development team directly.</p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-5">
        <span class="eyebrow">Development Team</span>
        <h2 class="display-6 mt-2 mb-4">Get In Touch</h2>

        <div class="contact-line">
          <span class="feature-icon"><i class="bi bi-building"></i></span>
          <div>
            <h6 class="mb-1">Organization</h6>
            <p class="mb-0 small">Softworks Solutions Pvt. Ltd.<br>The team who developed the Elegance Salon Management Application.</p>
          </div>
        </div>
        <div class="contact-line">
          <span class="feature-icon"><i class="bi bi-envelope"></i></span>
          <div>
            <h6 class="mb-1">Email</h6>
            <p class="mb-0 small"><a href="mailto:dev@softworkssolutions.com" class="text-accent">dev@softworkssolutions.com</a><br>Support: support@softworkssolutions.com</p>
          </div>
        </div>
        <div class="contact-line">
          <span class="feature-icon"><i class="bi bi-telephone"></i></span>
          <div>
            <h6 class="mb-1">Phone</h6>
            <p class="mb-0 small">+1 (555) 901-7788<br>Mon – Fri · 9:00 AM – 6:00 PM</p>
          </div>
        </div>
        <div class="contact-line">
          <span class="feature-icon"><i class="bi bi-geo-alt"></i></span>
          <div>
            <h6 class="mb-1">Address</h6>
            <p class="mb-0 small">Softworks Solutions Pvt. Ltd.<br>4th Floor, Tech Park One, 2500 Innovation Drive,<br>San Jose, CA 95134, USA</p>
          </div>
        </div>
      </div>

      <div class="col-lg-7">
        <div class="card p-4">
          <h5 class="mb-1">Send a Message</h5>
          <p class="small text-muted">Send a message to the salon team. We will review it and get back to you.</p>
          <?php if (isset($_GET['sent'])) { ?><div class="alert alert-success">Your message has been received.</div><?php } ?>
          <?php if ($contact_message != '') { ?><div class="alert alert-warning"><?php echo $contact_message; ?></div><?php } ?>
          <form id="contactForm" class="needs-validation" method="POST" novalidate>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label" for="cName">Your Name *</label>
                <input type="text" id="cName" name="contact_name" class="form-control" required minlength="2" value="<?php echo $_SESSION['username']; ?>">
                <div class="invalid-feedback">Please enter your name.</div>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="cEmail">Email *</label>
                <input type="email" id="cEmail" name="contact_email" class="form-control" required value="<?php echo isset($_SESSION['email']) ? $_SESSION['email'] : ''; ?>">
                <div class="invalid-feedback">A valid email is required.</div>
              </div>
              <div class="col-12">
                <label class="form-label" for="cSubject">Subject *</label>
                <input type="text" id="cSubject" name="contact_subject" class="form-control" required minlength="4">
                <div class="invalid-feedback">Subject is required.</div>
              </div>
              <div class="col-12">
                <label class="form-label" for="cMsg">Message *</label>
                <textarea id="cMsg" name="contact_message" class="form-control" rows="5" required minlength="10"></textarea>
                <div class="invalid-feedback">Message must be at least 10 characters.</div>
              </div>
              <div class="col-12">
                <button class="btn btn-accent px-4" type="submit" name="send_contact_message" value="1"><i class="bi bi-send me-1"></i>Send Message</button>
              </div>
            </div>
          </form>
        </div>
        <iframe class="map-frame mt-4" loading="lazy" title="Office location map"
          src="https://www.openstreetmap.org/export/embed.html?bbox=-121.9483%2C37.3688%2C-121.9083%2C37.3988&layer=mapnik"></iframe>
      </div>
    </div>
  </div>
</section>

</main>

<footer class="site-footer mt-auto">
  <div class="container py-5">
    <div class="row g-4">
      <div class="col-lg-4">
        <a class="navbar-brand text-white d-inline-flex align-items-center mb-2" href="index.php">
          <span class="brand-icon"><i class="bi bi-flower1"></i></span> Elegance <span class="text-accent">Salon</span>
        </a>
        <p class="text-white-50 small pe-lg-4">Salon Management Application — appointments, client relations, inventory control and staff scheduling, all in one place.</p>
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
  
          <li><a href="contact.php">Contact</a></li>        <li><a href="appointments.php">Appointments</a></li>
          <?php if (!isset($_SESSION['role']) || $_SESSION['role'] != 'client') { ?><li><a href="reports.php">Reports</a></li><?php } ?>
          <li><a href="feedback.php">Feedback</a></li>
        </ul>
      </div>
      <div class="col-6 col-lg-3">
        <h6 class="footer-title">Opening Hours</h6>
        <ul class="list-unstyled small text-white-50 mb-0">
          <li class="d-flex justify-content-between"><span>Mon – Fri</span><span>9:00 AM – 7:00 PM</span></li>
          <li class="d-flex justify-content-between"><span>Saturday</span><span>9:00 AM – 5:00 PM</span></li>
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
      <span>© 2026 Elegance Salon. Template for demonstration purposes.</span>
    </div>
  </div>
</footer>

<div id="toastArea" class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1090"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>


</body>
</html>
