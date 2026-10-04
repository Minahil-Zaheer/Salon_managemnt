<?php

session_start();
require_once "config.php";

$error = "";

if (isset($_POST['login'])) {

    $email = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (trim($email) === '' || $password === '') {
        $error = "Please enter your email and password.";
    } else {
    $query = "SELECT * FROM users WHERE email = '$email'";

    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 0) {

        $error = "User not registered.";

    } else {

        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {

            // Check if staff account is still pending
            if (($user['role'] == 'stylist' || $user['role'] == 'receptionist')
                && $user['status'] == 'pending') {

                $error = "Your account is waiting for admin approval.";

            } else {

                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                echo "<script>
                        window.location.href = 'index.php';
                      </script>";
                exit();
            }

        } else {

            $error = "Invalid Password.";
        }
    }
    }
}

?>




<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign In — Elegance Salon</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='88'>💠</text></svg>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
</head>
<body data-page="login">
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
        <li class="nav-item"><a class="nav-link" href="contact.php" data-nav="contact">Contact Us</a></li>
        <li class="nav-item"><a class="nav-link" href="feedback.php" data-nav="feedback">Feedback</a></li>
        <li class="nav-item guest-only ms-lg-2 my-2 my-lg-0">
          <a class="btn btn-accent btn-sm rounded-pill px-3" href="login.php"><i class="bi bi-box-arrow-in-right me-1"></i>Login</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
<main id="main">
<section class="login-wrap py-5">
  <div class="container">
    <div class="login-card mx-auto p-4 p-md-5">
      <div class="text-center mb-4">
        <span class="brand-icon mx-auto" style="width:56px;height:56px;font-size:1.5rem"><i class="bi bi-flower1"></i></span>
        <h3 class="mt-3 mb-1">Welcome back</h3>
        <p class="text-muted small mb-0">Sign in to the Elegance Salon management portal</p>
      </div>
        <?php if ($error != "") { ?>

                <div class="alert alert-danger">
                   <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>

          <?php } ?>
      <form method="post" id="loginForm" class="needs-validation" novalidate>

    <!-- Email -->
    <div class="mb-3">
        <label class="form-label" for="loginUser">
            Email
        </label>

        <div class="input-group">
            <span class="input-group-text bg-soft">
                <i class="bi bi-person"></i>
            </span>

            <input
                type="email"
                id="loginUser"
                class="form-control"
                name="email"
                required
                placeholder="Enter your email address"
            >

            <div class="invalid-feedback">
                Please enter your email.
            </div>
        </div>
    </div>


    <!-- Password -->
    <div class="mb-1">
        <label class="form-label" for="loginPass">
            Password
        </label>

        <div class="input-group">
            <span class="input-group-text bg-soft">
                <i class="bi bi-lock"></i>
            </span>

            <input
                type="password"
                id="loginPass"
                class="form-control"
                name="password"
                required
                placeholder="Enter your password"
            >

            <button type="button" class="btn btn-outline-accent" data-toggle-password="loginPass" aria-label="Show password">
                <i class="bi bi-eye"></i>
            </button>

            <div class="invalid-feedback">
                Please enter your password.
            </div>
        </div>
    </div>


    <!-- Remember Me + Forgot Password -->
    <div class="d-flex justify-content-between align-items-center small mb-3">

        <div class="form-check">
            <input
                class="form-check-input"
                type="checkbox"
                id="rememberMe"
            >

            <label class="form-check-label" for="rememberMe">
                Remember me
            </label>
        </div>

        <a href="mailto:hello@elegancesalon.com?subject=Account%20access%20help" class="text-accent">
            Forgot password? Contact the salon
        </a>

    </div>


    <!-- Login Button -->
    <button
        class="btn btn-accent w-100 py-2"
        type="submit"
        name="login"
    >
        Sign In
        <i class="bi bi-arrow-right ms-1"></i>
    </button>

</form>


<!-- Register -->
<p class="text-center small mt-3 mb-0">
    Don't have an account?
    <a href="register.php" class="text-accent">
        Sign up
    </a>
</p>

</div>


<!-- Back to Website -->
<p class="text-center small mt-3 mb-0">
    <a href="index.php" class="text-accent">
        <i class="bi bi-arrow-left me-1"></i>
        Back to website
    </a>
</p>
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
<script src="js/app.js"></script>

</body>
</html>
