<?php
session_start();
if(!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit();
}
require_once 'config.php';

if ($_SESSION['role'] == 'client') {
    $clientId = (int)$_SESSION['user_id'];
    $clientAppointments = mysqli_query($conn, "SELECT id, service, appointment_date, appointment_time, status FROM appointments WHERE client_id = $clientId ORDER BY appointment_date DESC, appointment_time DESC");
    $clientAppointmentCount = $clientAppointments ? mysqli_num_rows($clientAppointments) : 0;
} elseif ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'receptionist' && $_SESSION['role'] != 'stylist') {
    echo "<script>window.location.href = 'index.php';</script>";
    exit();
}

if ($_SESSION['role'] != 'client') {
// Total Clients
$clientQuery = "SELECT * FROM users WHERE role = 'client'";
$clientResult = mysqli_query($conn, $clientQuery);
$totalClients = mysqli_num_rows($clientResult);

// Total Staff
$staffQuery = "SELECT * FROM users 
               WHERE role = 'stylist' 
               OR role = 'receptionist'";
$staffResult = mysqli_query($conn, $staffQuery);
$totalStaff = mysqli_num_rows($staffResult);

// Upcoming Appointments
$appointmentQuery = "SELECT * FROM appointments 
                     WHERE appointment_date >= CURDATE()
                     AND status IN ('scheduled', 'confirmed')";

$appointmentResult = mysqli_query($conn, $appointmentQuery);
$totalAppointments = mysqli_num_rows($appointmentResult);

// Revenue Collected
$revenueQuery = "SELECT SUM(amount) AS total_revenue 
                 FROM payments 
                 WHERE status = 'paid'";

$revenueResult = mysqli_query($conn, $revenueQuery);
$revenueData = mysqli_fetch_assoc($revenueResult);

$totalRevenue = $revenueData['total_revenue'];

if ($totalRevenue == NULL) {
    $totalRevenue = 0;
}
}

// Low Stock Items
$stockQuery = "SELECT * FROM inventory 
               WHERE quantity <= min_quantity";

$stockResult = mysqli_query($conn, $stockQuery);
$totalLowStock = mysqli_num_rows($stockResult);


?>




<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard — Elegance Salon</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='88'>💠</text></svg>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
</head>
<body data-page="dashboard">
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
            <i class="bi bi-person-circle fs-5 me-1"></i></i><?php echo $_SESSION['username']; ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userMenu">
            <li><span class="dropdown-item-text small text-muted"><i class="bi bi-shield-check me-1"></i></i><?php echo $_SESSION['role']; ?></span></li>
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
<?php if ($_SESSION['role'] == 'client') { ?>
<div class="container py-5">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div><p class="overline mb-1">Client Area</p><h2 class="mb-1">Welcome, <?php echo $_SESSION['username']; ?></h2><p class="text-muted mb-0">Your appointments and salon account.</p></div>
    <a class="btn btn-accent" href="appointments.php"><i class="bi bi-calendar-plus me-2"></i>Book an appointment</a>
  </div>
  <div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3"><h4 class="mb-0">My appointments</h4><span class="badge text-bg-light"><?php echo (int)$clientAppointmentCount; ?> total</span></div>
    <?php if ($clientAppointmentCount > 0) { ?>
      <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Service</th><th>Date</th><th>Time</th><th>Status</th></tr></thead><tbody>
      <?php while ($clientAppointment = mysqli_fetch_assoc($clientAppointments)) { ?><tr><td><?php echo $clientAppointment['service']; ?></td><td><?php echo $clientAppointment['appointment_date']; ?></td><td><?php echo substr($clientAppointment['appointment_time'], 0, 5); ?></td><td><?php echo ucfirst($clientAppointment['status']); ?></td></tr><?php } ?>
      </tbody></table></div>
    <?php } else { ?><p class="text-muted mb-0">You have no appointments yet. Choose a service and book your first visit.</p><?php } ?>
  </div>
</div>
<?php } else { ?>
<div class="app-shell">
  <?php include '_partials/sidebar.php'; ?>

  <div class="app-content">
    <div class="d-flex align-items-center gap-2 mb-4">
      <button class="btn btn-outline-accent btn-sm d-lg-none" id="sidebarToggle" type="button"><i class="bi bi-list"></i></button>
      <div>
        <h4 class="mb-0">Dashboard</h4>
        <small class="text-muted">Overview of today at Elegance Salon</small>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-gold"><small>Total Clients</small><h3><?php echo $totalClients; ?></h3><span>active client records</span><i class="bi bi-people"></i></div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-green">
          <small>Total Staff</small>
       <h3><?php echo $totalStaff; ?></h3>
        <span>stylists & receptionists</span>
        <i class="bi bi-scissors"></i>
      </div>
</div>
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-rose"><small>Upcoming Appointments</small><h3><?php echo $totalAppointments; ?></h3><span>today &amp; scheduled ahead</span><i class="bi bi-calendar-check"></i></div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-ink"><small>Revenue Collected</small><h3>$<?php echo number_format($totalRevenue, 2); ?></h3><span>paid invoices</span><i class="bi bi-cash-stack"></i></div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-green"><small>Low Stock Items</small><h3><?php echo $totalLowStock; ?></h3><span>auto purchase orders drafted</span><i class="bi bi-box-seam"></i></div>
      </div>

     
    </div>

    <div class="row g-4">
      <div class="col-lg-4">
        <div class="card p-3">
          <div class="d-flex justify-content-between align-items-center mb-2 px-1">
            <h6 class="mb-0">Mini Calendar</h6>
            <span class="overline" id="miniCalLabel"></span>
          </div>
          <div class="cal-grid" id="miniCalendar"></div>
          <a href="appointments.php" class="btn btn-outline-accent btn-sm mt-3">Open full calendar</a>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card p-3 h-100">
          <h6 class="px-1">Today &amp; Upcoming</h6>
          <div class="timeline px-2" id="dashAgenda"></div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card p-3 h-100">
          <div class="d-flex justify-content-between align-items-center mb-2 px-1">
            <h6 class="mb-0">Reminders &amp; Alerts</h6>
            <a href="notifications.php" class="btn btn-sm btn-outline-accent">View all</a>
          </div>
          <div id="dashReminders"></div>
        </div>
      </div>
    </div>

    <div class="row g-3 mt-1">
      <div class="col-12">
        <div class="card p-3">
          <h6 class="px-1 mb-3">Quick Actions</h6>
          <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-accent btn-sm" href="appointments.php"><i class="bi bi-calendar-plus me-1"></i>Book Appointment</a>
            <a class="btn btn-outline-accent btn-sm" href="clients.php"><i class="bi bi-person-plus me-1"></i>Add Client</a>
            <a class="btn btn-outline-accent btn-sm" href="payments.php"><i class="bi bi-receipt me-1"></i>Record Payment</a>
            <a class="btn btn-outline-accent btn-sm" href="inventory.php"><i class="bi bi-box-seam me-1"></i>Check Inventory</a>
            <a class="btn btn-outline-accent btn-sm" href="reports.php"><i class="bi bi-graph-up me-1"></i>View Reports</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php } ?>

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
