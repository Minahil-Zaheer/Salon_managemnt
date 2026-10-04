<?php
    session_start();
    if(!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit();
    }
    require_once 'config.php';

    if ($_SESSION['role'] == 'client') {
        echo "<script>window.location.href = 'index.php';</script>";
        exit();
    }

    $clientResult = mysqli_query($conn, "SELECT id, username, email, phone, city, status FROM users WHERE role = 'client' ORDER BY username ASC");
    $clientCount = $clientResult ? mysqli_num_rows($clientResult) : 0;
?>




<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Clients — Elegance Salon</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='88'>💠</text></svg>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
</head>
<body data-page="clients">
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
<div class="app-shell">
  <?php include '_partials/sidebar.php'; ?>

  <div class="app-content">
    <div class="d-flex align-items-center gap-2 mb-4">
      <button class="btn btn-outline-accent btn-sm d-lg-none" id="sidebarToggle" type="button"><i class="bi bi-list"></i></button>
      <div class="flex-grow-1">
        <h4 class="mb-0">Client Management</h4>
        <small class="text-muted">Centralized database — search, add, edit and remove client profiles</small>
      </div>
      <a class="btn btn-accent btn-sm" href="register.php"><i class="bi bi-person-plus me-1"></i>Register Client</a>
    </div>

    <div class="card p-3">
      <div class="d-flex flex-wrap gap-2 mb-3">
        <input type="search" id="clientSearch" class="form-control form-control-sm w-auto flex-grow-1" placeholder="Search by name, email or phone…">
        <span class="badge bg-gold align-self-center"><?php echo $clientCount; ?> clients</span>
      </div>
      <div class="table-scroll">
        <table class="table table-hover align-middle mb-0">
          <thead><tr><th>Client</th><th>Email</th><th>Phone</th><th>City</th><th>Status</th></tr></thead>
          <tbody id="clientBody">
            <?php if ($clientResult && mysqli_num_rows($clientResult) > 0) { ?>
              <?php while ($client = mysqli_fetch_assoc($clientResult)) { ?>
                <tr>
                  <td><?php echo $client['username']; ?></td>
                  <td><?php echo $client['email']; ?></td>
                  <td><?php echo $client['phone']; ?></td>
                  <td><?php echo $client['city']; ?></td>
                  <td><?php echo ucfirst($client['status']); ?></td>
                </tr>
              <?php } ?>
            <?php } else { ?>
              <tr><td colspan="5" class="text-center text-muted">No clients registered yet.</td></tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

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
<script>
  document.getElementById('clientSearch').addEventListener('input', function () {
    var search = this.value.toLowerCase();
    document.querySelectorAll('#clientBody tr').forEach(function (row) {
      row.style.display = row.innerText.toLowerCase().indexOf(search) != -1 ? '' : 'none';
    });
  });
</script>


  <script src="js/app.js"></script>
</body>
</html>
