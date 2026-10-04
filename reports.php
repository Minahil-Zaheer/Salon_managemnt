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

    $bookingLabels = array();
    $bookingValues = array();
    $bookingResult = mysqli_query($conn, "SELECT DATE_FORMAT(appointment_date, '%a %e') AS day_label, COUNT(*) AS total FROM appointments WHERE appointment_date >= CURDATE() AND appointment_date < DATE_ADD(CURDATE(), INTERVAL 7 DAY) GROUP BY appointment_date ORDER BY appointment_date");
    if ($bookingResult) { while ($row = mysqli_fetch_assoc($bookingResult)) { $bookingLabels[] = $row['day_label']; $bookingValues[] = (int)$row['total']; } }

    $revenueLabels = array();
    $revenueValues = array();
    $revenueResult = mysqli_query($conn, "SELECT service, SUM(amount) AS total FROM payments WHERE status = 'paid' GROUP BY service ORDER BY total DESC");
    if ($revenueResult) { while ($row = mysqli_fetch_assoc($revenueResult)) { $revenueLabels[] = $row['service']; $revenueValues[] = (float)$row['total']; } }

    $inventoryLabels = array();
    $inventoryValues = array();
    $inventoryResult = mysqli_query($conn, "SELECT category, SUM(quantity) AS total FROM inventory GROUP BY category ORDER BY category");
    if ($inventoryResult) { while ($row = mysqli_fetch_assoc($inventoryResult)) { $inventoryLabels[] = $row['category']; $inventoryValues[] = (int)$row['total']; } }

    $staffLabels = array();
    $staffValues = array();
    $staffResult = mysqli_query($conn, "SELECT users.username, COUNT(appointments.id) AS total FROM users LEFT JOIN appointments ON appointments.stylist_id = users.id AND appointments.status = 'completed' WHERE users.role = 'stylist' AND users.status = 'approved' GROUP BY users.id, users.username ORDER BY total DESC");
    if ($staffResult) { while ($row = mysqli_fetch_assoc($staffResult)) { $staffLabels[] = $row['username']; $staffValues[] = (int)$row['total']; } }

    $popularResult = mysqli_query($conn, "SELECT service, COUNT(*) AS total FROM appointments GROUP BY service ORDER BY total DESC LIMIT 5");
    $reportSummary = mysqli_query($conn, "SELECT COUNT(*) AS total_bookings FROM appointments WHERE appointment_date >= CURDATE() AND appointment_date < DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
    $reportSummaryRow = $reportSummary ? mysqli_fetch_assoc($reportSummary) : array('total_bookings' => 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reports & Analytics — Elegance Salon</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='88'>💠</text></svg>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
</head>
<body data-page="reports">
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
      <div>
        <h4 class="mb-0">Reporting &amp; Analytics</h4>
        <small class="text-muted">Appointments, sales, inventory usage &amp; staff performance</small>
      </div>
      <button class="btn btn-outline-accent btn-sm ms-auto" type="button" id="downloadReport"><i class="bi bi-download me-1"></i>Download Report</button>
    </div>

    <div class="row g-4">
      <div class="col-lg-7">
        <div class="card p-3 h-100">
          <h6 class="px-1">Bookings — Next 7 Days</h6>
          <canvas id="chartBookings" height="150"></canvas>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="card p-3 h-100">
          <h6 class="px-1">Revenue by Service (paid)</h6>
          <canvas id="chartRevenue" height="200"></canvas>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="card p-3 h-100">
          <h6 class="px-1">Inventory Quantity by Category</h6>
          <canvas id="chartInventory" height="200"></canvas>
        </div>
      </div>
      <div class="col-lg-7">
        <div class="card p-3 h-100">
          <h6 class="px-1">Staff Performance — Services Performed</h6>
          <canvas id="chartStaff" height="170"></canvas>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card p-3 h-100">
          <h6 class="px-1 mb-2"><i class="bi bi-trophy text-accent me-2"></i>Most Popular Services</h6>
          <div id="topServices">
            <?php if ($popularResult && mysqli_num_rows($popularResult) > 0) { ?>
              <ol class="mb-0">
                <?php while ($popular = mysqli_fetch_assoc($popularResult)) { ?>
                  <li class="mb-2"><?php echo $popular['service']; ?> <span class="text-muted">(<?php echo $popular['total']; ?> booking(s))</span></li>
                <?php } ?>
              </ol>
            <?php } else { ?><p class="text-muted mb-0">No booking data yet.</p><?php } ?>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card p-3 h-100">
          <h6 class="px-1 mb-2"><i class="bi bi-lightbulb text-accent me-2"></i>Key Insights</h6>
          <ul class="list-unstyled small mb-0 px-2" id="insightBox">
            <li class="mb-2"><i class="bi bi-calendar-check text-accent me-2"></i><?php echo $reportSummaryRow['total_bookings']; ?> appointment(s) scheduled in the next seven days.</li>
            <li class="mb-2"><i class="bi bi-currency-dollar text-accent me-2"></i>Revenue chart uses paid payment records only.</li>
            <li><i class="bi bi-box-seam text-accent me-2"></i>Inventory chart shows current quantities grouped by category.</li>
          </ul>
        </div>
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
          <li><a href="appointments.php">Appointments</a></li>
          <?php if (!isset($_SESSION['role']) || $_SESSION['role'] != 'client') { ?><li><a href="reports.php">Reports</a></li><?php } ?>
          <li><a href="about.php">About</a></li>
          <li><a href="contact.php">Contact</a></li>
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
  document.getElementById('downloadReport').addEventListener('click', function () {
    var rows = [['Report', 'Item', 'Value']];
    var sections = [
      ['Bookings next 7 days', <?php echo json_encode($bookingLabels); ?>, <?php echo json_encode($bookingValues); ?>],
      ['Revenue by service (paid)', <?php echo json_encode($revenueLabels); ?>, <?php echo json_encode($revenueValues); ?>],
      ['Inventory quantity by category', <?php echo json_encode($inventoryLabels); ?>, <?php echo json_encode($inventoryValues); ?>],
      ['Staff performance', <?php echo json_encode($staffLabels); ?>, <?php echo json_encode($staffValues); ?>]
    ];
    sections.forEach(function (section) {
      section[1].forEach(function (label, index) { rows.push([section[0], label, section[2][index]]); });
    });
    var csv = rows.map(function (row) {
      return row.map(function (value) { return '"' + String(value).replace(/"/g, '""') + '"'; }).join(',');
    }).join('\r\n');
    var link = document.createElement('a');
    link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
    link.download = 'salon-report.csv';
    link.click();
    URL.revokeObjectURL(link.href);
  });

  function makeReportChart(elementId, chartType, labels, values, label) {
    var canvas = document.getElementById(elementId);
    if (!canvas) { return; }
    new Chart(canvas, {
      type: chartType,
      data: { labels: labels, datasets: [{ label: label, data: values, backgroundColor: ['#c59d5f', '#b4656f', '#4b4038', '#d8bd91', '#81917c', '#6f8198'], borderColor: '#fff', borderWidth: 2 }] },
      options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: chartType == 'doughnut' ? 'bottom' : 'top' } }, scales: chartType == 'doughnut' ? {} : { y: { beginAtZero: true } } }
    });
  }
  makeReportChart('chartBookings', 'bar', <?php echo json_encode($bookingLabels); ?>, <?php echo json_encode($bookingValues); ?>, 'Bookings');
  makeReportChart('chartRevenue', 'doughnut', <?php echo json_encode($revenueLabels); ?>, <?php echo json_encode($revenueValues); ?>, 'Revenue');
  makeReportChart('chartInventory', 'bar', <?php echo json_encode($inventoryLabels); ?>, <?php echo json_encode($inventoryValues); ?>, 'Quantity');
  makeReportChart('chartStaff', 'bar', <?php echo json_encode($staffLabels); ?>, <?php echo json_encode($staffValues); ?>, 'Completed appointments');
</script>


  <script src="js/app.js"></script>
</body>
</html>
