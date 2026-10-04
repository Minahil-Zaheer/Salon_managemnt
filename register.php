<?php
require_once "config.php";

$error = "";

if (isset($_POST['register'])) {

    $username = mysqli_real_escape_string($conn, isset($_POST['username']) ? trim($_POST['username']) : '');
    $email = mysqli_real_escape_string($conn, isset($_POST['email']) ? trim($_POST['email']) : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $phone = mysqli_real_escape_string($conn, isset($_POST['phone']) ? trim($_POST['phone']) : '');
    $city = mysqli_real_escape_string($conn, isset($_POST['city']) ? trim($_POST['city']) : '');
    $gender = mysqli_real_escape_string($conn, isset($_POST['gender']) ? trim($_POST['gender']) : '');
    $address = mysqli_real_escape_string($conn, isset($_POST['address']) ? trim($_POST['address']) : '');
    $role = isset($_POST['role']) ? $_POST['role'] : '';

    if ($username === '' || $email === '' || $password === '' || $confirm_password === '' || $phone === '' || $city === '' || $gender === '' || $address === '' || $role === '') {
        $error = "Please fill in all required fields.";
    } elseif (!isset($_POST['terms'])) {
        $error = "Please accept the terms before registering.";
    } elseif ($role != "client" && $role != "stylist" && $role != "receptionist") {

        $error = "Invalid role selected.";

    } elseif ($password != $confirm_password) {

        $error = "Passwords do not match.";

    } else {

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $checkEmailQuery = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $checkEmailQuery);

        if (mysqli_num_rows($result) > 0) {

            $error = "User Already Exists";

        } else {

            // Client is automatically approved
            // Staff needs admin approval
            if ($role == "client") {
                $status = "approved";
            } else {
                $status = "pending";
            }

            $registerUser = "INSERT INTO users
            (username, email, password, phone, city, gender, address, role, status)
            VALUES
            ('$username', '$email', '$hashedPassword', '$phone', '$city', '$gender', '$address', '$role', '$status')";

            $result = mysqli_query($conn, $registerUser);

            if ($result) {

                if ($role == "client") {

                    echo "<script>
                        alert('Registration Successful!');
                        window.location.href = 'login.php';
                    </script>";

                } else {

                    echo "<script>
                        alert('Registration submitted! Please wait for admin approval.');
                        window.location.href = 'login.php';
                    </script>";

                }

                exit();

            } else {

                $error = "Registration Failed!";
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
<title>Create Account — Elegance Salon</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='88'>💠</text></svg>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
</head>
<body data-page="register">
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
          <a class="btn btn-outline-accent btn-sm rounded-pill px-3 me-1" href="register.php"><i class="bi bi-person-plus me-1"></i>Sign Up</a>
        </li>
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
        <span class="brand-icon mx-auto" style="width:56px;height:56px;font-size:1.5rem"><i class="bi bi-person-plus-fill"></i></span>
        <h3 class="mt-3 mb-1">Create Your Account</h3>
        <p class="text-muted small mb-0">Register to book appointments, manage clients and run operations</p>
      </div>


      <div class="tab-content">
        <!-- CLIENT TAB -->
<div class="tab-pane fade show active" id="pane-client" role="tabpanel">
        <?php
if ($error !== '') { ?>
  <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
<?php }
?>
  <form id="registerClient" method="post" class="needs-validation" novalidate>

    <div class="row g-3">

      <!-- USERNAME -->
      <div class="col-md-6">
        <label class="form-label" for="rcUsername">
          Username:
        </label>

        <input
          type="text"
          id="rcUsername"
          class="form-control"
          name="username"
          required
          minlength="3"
          maxlength="100"
          pattern="[a-zA-Z0-9_]+"
          placeholder="janedoe"
        >

        <div class="invalid-feedback">
          3-100 letters, numbers or underscore.
        </div>
      </div>


      <!-- EMAIL -->
      <div class="col-md-6">
        <label class="form-label" for="rcEmail">
          Email:
        </label>

        <input
          type="email"
          id="rcEmail"
          name="email"
          class="form-control"
          required
          maxlength="100"
          placeholder="jane@example.com"
        >

        <div class="invalid-feedback">
          A valid email is required.
        </div>
      </div>


      <!-- PASSWORD -->
      <div class="col-md-6">
        <label class="form-label" for="rcPassword">
          Password:
          <small class="text-muted">(min 6 chars)</small>
        </label>

        <div class="input-group">

          <span class="input-group-text bg-soft">
            <i class="bi bi-lock"></i>
          </span>

          <input
            type="password"
            id="rcPassword"
            name="password"
            class="form-control"
            required
            minlength="6"
            maxlength="255"
            placeholder="Enter password"
          >

          <button
            type="button"
            class="btn btn-outline-accent"
            data-toggle-password="rcPassword"
            aria-label="Show password"
          >
            <i class="bi bi-eye"></i>
          </button>

          <div class="invalid-feedback">
            Password must be at least 6 characters.
          </div>

        </div>
      </div>


      <!-- CONFIRM PASSWORD -->
      <div class="col-md-6">
        <label class="form-label" for="rcPassword2">
          Confirm Password:
        </label>

        <div class="input-group">
          <input
            type="password"
            id="rcPassword2"
            name="confirm_password"
            class="form-control"
            required
            minlength="6"
            maxlength="255"
            placeholder="Confirm password"
          >
          <button type="button" class="btn btn-outline-accent" data-toggle-password="rcPassword2" aria-label="Show confirmation password">
            <i class="bi bi-eye"></i>
          </button>
        </div>

        <div class="invalid-feedback">
          Passwords do not match.
        </div>
      </div>


      <!-- PHONE -->
      <div class="col-md-6">
        <label class="form-label" for="rcPhone">
          Phone:
        </label>

        <input
          type="tel"
          id="rcPhone"
          class="form-control"
          name="phone"
          required
          maxlength="11"
          pattern="[0-9]{11}"
          placeholder="03001234567"
        >

        <div class="invalid-feedback">
          Enter a valid 11-digit phone number.
        </div>
      </div>


      <!-- CITY -->
      <div class="col-md-6">
        <label class="form-label" for="rcCity">
          City:
        </label>

        <input
          type="text"
          id="rcCity"
          class="form-control"
          name="city"
          maxlength="100"
          required
          placeholder="Karachi"
        >

        <div class="invalid-feedback">
          Please enter your city.
        </div>
      </div>


      <!-- GENDER -->
      <div class="col-md-6">
        <label class="form-label" for="rcGender">
          Gender:
        </label>

        <select
          id="rcGender"
          name="gender"
          class="form-select"
          required
        >

          <option value="" selected disabled>
            Select Gender
          </option>

          <option value="Male">
            Male
          </option>

          <option value="Female">
            Female
          </option>

          <option value="Other">
            Other
          </option>

        </select>

        <div class="invalid-feedback">
          Please select your gender.
        </div>
      </div>


      <!-- ADDRESS -->
      <div class="col-md-6">
        <label class="form-label" for="rcAddress">
          Address:
        </label>

        <input
          type="text"
          id="rcAddress"
          class="form-control"
          name="address"
          maxlength="300"
          required
          placeholder="House 12, Main Street"
        >

        <div class="invalid-feedback">
          Please enter your address.
        </div>
      </div>


     
     <!-- ROLE -->
<div class="col-md-6">
  <label class="form-label" for="rcRole">
    Register As:
  </label>

  <select
    id="rcRole"
    name="role"
    class="form-select"
    autocomplete="off"
    required
  >

    <option value="" <?php echo (!isset($role) || $role === '') ? 'selected' : ''; ?> disabled>
      Select Role
    </option>

    <option value="client" <?php echo (isset($role) && $role === 'client') ? 'selected' : ''; ?>>
      Client
    </option>

    <option value="stylist" <?php echo (isset($role) && $role === 'stylist') ? 'selected' : ''; ?>>
      Stylist
    </option>

    <option value="receptionist" <?php echo (isset($role) && $role === 'receptionist') ? 'selected' : ''; ?>>
      Receptionist
    </option>

  </select>

  <div class="invalid-feedback">
    Please select your role.
  </div>

</div>


      <!-- TERMS -->
      <div class="col-12">

        <div class="form-check">

          <input
            class="form-check-input"
            type="checkbox"
            id="rcTerms"
            name="terms"
            required
          >

          <label
            class="form-check-label"
            for="rcTerms"
          >
            I agree to the

            <a
              href="terms.php"
              class="text-accent"
              target="_blank"
              rel="noopener"
            >
              Terms of Service
            </a>

            and

            <a
              href="privacy.php"
              class="text-accent"
              target="_blank"
              rel="noopener"
            >
              Privacy Policy
            </a>.

            *

          </label>

          <div class="invalid-feedback">
            You must agree before registering.
          </div>

        </div>

      </div>


      <!-- REGISTER BUTTON -->
      <div class="col-12 d-grid">
        <button
          type="submit"name="register"class="btn btn-accent btn-lg"><i class="bi bi-person-check me-1"></i>Create Account</button>
      </div>

    </div>

  </form>

</div>

      <hr class="my-4">
      <p class="text-center small mb-2">
        Already have an account?
        <a href="login.php" class="text-accent fw-semibold">Sign in</a>
      </p>
      <p class="text-center small text-muted mb-0 mt-2"><a href="index.php" class="text-accent"><i class="bi bi-arrow-left me-1"></i>Back to website</a></p>
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
      </div>
      <div class="col-6 col-lg-2">
        <h6 class="footer-title">Quick Links</h6>
        <ul class="list-unstyled footer-links small">
          <li><a href="index.php">Home</a></li>
          <li><a href="about.php">About</a></li>
          <li><a href="login.php">Login</a></li>
          <li><a href="register.php">Register</a></li>
          <li><a href="contact.php">Contact</a></li>
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


<script type="module" src="https://static.cloudflareinsights.com/beacon.min.js/v31edd6df95cf4e85bb4c19e7a9bdbcba1788362987495" integrity="sha512-iIg7k2xntmwu6/uSb5tpc/hySgZc4eoL31yB29W6tJFo2akwjPWcEqnCEdJvGexCL0KEQwVYv5BlowfhVz26hg==" data-cf-beacon='{"version":"2024.11.0","token":"4edd5f8ec12a48cfa682ab8261b80a79","spa":2}' crossorigin="anonymous"></script>
</body>
</html>
