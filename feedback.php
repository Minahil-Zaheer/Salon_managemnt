<?php
    session_start();
    if(!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit();
    }
    require_once 'config.php';

    $feedback_message = '';
    if (isset($_POST['submit_feedback'])) {
        $rating = isset($_POST['fbRating']) ? $_POST['fbRating'] : '';
        $comments = isset($_POST['fbComments']) ? trim($_POST['fbComments']) : '';
        if (!ctype_digit((string)$rating) || $rating < 1 || $rating > 5 || strlen($comments) < 10) {
            $feedback_message = 'Choose a rating and write at least 10 characters.';
        } else {
            $comments = mysqli_real_escape_string($conn, $comments);
            $username = mysqli_real_escape_string($conn, $_SESSION['username']);
            $query = "INSERT INTO feedback (user_id, username, rating, comments) VALUES ('" . $_SESSION['user_id'] . "', '$username', '$rating', '$comments')";
            if (mysqli_query($conn, $query)) {
                echo "<script>window.location.href = 'feedback.php';</script>";
                exit();
            }
            $feedback_message = 'Feedback could not be saved.';
        }
    }
    $feedbackResult = mysqli_query($conn, "SELECT username, rating, comments, created_at FROM feedback ORDER BY created_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Feedback — Elegance Salon</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='88'>💠</text></svg>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
</head>
<body data-page="feedback">
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
            <i class="bi bi-person-circle fs-5 me-1"></i><?php echo $_SESSION['username']; ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userMenu">
            <li><span class="dropdown-item-text small text-muted"><i class="bi bi-shield-check me-1"></i><?php echo $_SESSION['role']; ?></span></li>
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
    <span class="eyebrow text-white-50">Help us improve</span>
    <h1 class="mt-2">Submit Feedback</h1>
    <p>Tell us about your experience with the Elegance Salon application.</p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-6">
        <div class="card p-4">
          <h5 class="mb-1">Feedback Form</h5>
          <p class="small text-muted">All fields marked * are required. Input is validated before submission.</p>
          <?php if ($feedback_message != '') { ?><div class="alert alert-warning"><?php echo $feedback_message; ?></div><?php } ?>
          <form id="fbForm" class="needs-validation" method="POST" novalidate>
            <div class="mb-3">
              <label class="form-label" for="fbName">Your Name *</label>
              <input type="text" id="fbName" class="form-control" value="<?php echo $_SESSION['username']; ?>" readonly>
              <div class="invalid-feedback">Please enter your name.</div>
            </div>
            <div class="mb-3">
              <label class="form-label d-block">Overall Rating *</label>
              <div class="star-input" id="fbStars">
                <input type="radio" name="fbRating" id="star5" value="5"><label for="star5" class="bi bi-star-fill" aria-label="5 stars"></label>
                <input type="radio" name="fbRating" id="star4" value="4"><label for="star4" class="bi bi-star-fill" aria-label="4 stars"></label>
                <input type="radio" name="fbRating" id="star3" value="3"><label for="star3" class="bi bi-star-fill" aria-label="3 stars"></label>
                <input type="radio" name="fbRating" id="star2" value="2"><label for="star2" class="bi bi-star-fill" aria-label="2 stars"></label>
                <input type="radio" name="fbRating" id="star1" value="1"><label for="star1" class="bi bi-star-fill" aria-label="1 star"></label>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label" for="fbComments">Comments *</label>
              <textarea id="fbComments" name="fbComments" class="form-control" rows="5" required minlength="10" placeholder="What did you like? What can we improve?"></textarea>
              <div class="invalid-feedback">Please write at least 10 characters.</div>
            </div>
            <button class="btn btn-accent px-4" type="submit" name="submit_feedback" value="1"><i class="bi bi-chat-quote me-1"></i>Submit Feedback</button>
          </form>
        </div>
      </div>

      <div class="col-lg-6">
        <span class="eyebrow">Recent feedback</span>
        <h2 class="display-6 mt-2 mb-4">What Users Are Saying</h2>
        <div id="feedbackList">
          <?php if ($feedbackResult && mysqli_num_rows($feedbackResult) > 0) { ?>
            <?php while ($feedback = mysqli_fetch_assoc($feedbackResult)) { ?>
              <div class="card p-3 mb-3">
                <div class="d-flex justify-content-between"><strong><?php echo $feedback['username']; ?></strong><span class="text-accent"><?php echo $feedback['rating']; ?>/5</span></div>
                <p class="mb-1 mt-2"><?php echo $feedback['comments']; ?></p>
                <small class="text-muted"><?php echo $feedback['created_at']; ?></small>
              </div>
            <?php } ?>
          <?php } else { ?>
            <p class="text-muted">No feedback yet.</p>
          <?php } ?>
        </div>
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
