<?php
    session_start();
    if(!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit();
    }
    require_once 'config.php';

    $userId = (int)$_SESSION['user_id'];
    $userRole = $_SESSION['role'];
    if ($userRole == 'client') {
        echo "<script>window.location.href = 'dashboard.php';</script>";
        exit();
    }
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS notifications (
        id INT(11) NOT NULL AUTO_INCREMENT,
        user_id INT(11) NOT NULL,
        notification_type VARCHAR(40) NOT NULL,
        reference_id INT(11) NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) NOT NULL DEFAULT 0,
        is_cleared TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY user_notice (user_id, notification_type, reference_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['mark_all_read'])) {
            mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE user_id = $userId AND is_cleared = 0");
        } elseif (isset($_POST['clear_notifications'])) {
            mysqli_query($conn, "UPDATE notifications SET is_cleared = 1 WHERE user_id = $userId");
        } elseif (isset($_POST['mark_one_read']) && isset($_POST['notification_id'])) {
            $notificationId = (int)$_POST['notification_id'];
            mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE id = $notificationId AND user_id = $userId");
        }
        echo "<script>window.location.href = 'notifications.php';</script>";
        exit();
    }

    function saveNotice($conn, $recipientId, $type, $referenceId, $message) {
        $recipientId = (int)$recipientId;
        $referenceId = (int)$referenceId;
        $type = mysqli_real_escape_string($conn, $type);
        $message = mysqli_real_escape_string($conn, $message);
        mysqli_query($conn, "INSERT INTO notifications (user_id, notification_type, reference_id, message)
            VALUES ($recipientId, '$type', $referenceId, '$message') ON DUPLICATE KEY UPDATE id = id");
    }

    if ($_SESSION['role'] == 'client') {
        $noticeAppointmentQuery = "SELECT appointments.*, users.username AS client_name FROM appointments LEFT JOIN users ON appointments.client_id = users.id WHERE appointments.client_id = '" . mysqli_real_escape_string($conn, $_SESSION['user_id']) . "' AND appointments.appointment_date >= CURDATE() AND appointments.status != 'cancelled' ORDER BY appointments.appointment_date, appointments.appointment_time";
    } elseif ($_SESSION['role'] == 'stylist') {
        $noticeAppointmentQuery = "SELECT appointments.*, users.username AS client_name FROM appointments LEFT JOIN users ON appointments.client_id = users.id WHERE appointments.stylist_id = '" . mysqli_real_escape_string($conn, $_SESSION['user_id']) . "' AND appointments.appointment_date >= CURDATE() AND appointments.status != 'cancelled' ORDER BY appointments.appointment_date, appointments.appointment_time";
    } else {
        $noticeAppointmentQuery = "SELECT appointments.*, users.username AS client_name FROM appointments LEFT JOIN users ON appointments.client_id = users.id WHERE appointments.appointment_date >= CURDATE() AND appointments.status != 'cancelled' ORDER BY appointments.appointment_date, appointments.appointment_time";
    }
    $noticeAppointmentResult = mysqli_query($conn, $noticeAppointmentQuery);
    if ($noticeAppointmentResult) {
        while ($appointment = mysqli_fetch_assoc($noticeAppointmentResult)) {
            $message = $appointment['client_name'] . ' - ' . $appointment['service'] . ' on ' . $appointment['appointment_date'] . ' at ' . substr($appointment['appointment_time'], 0, 5) . '. Status: ' . $appointment['status'];
            if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'receptionist') {
                $recipients = mysqli_query($conn, "SELECT id FROM users WHERE role IN ('admin', 'receptionist') AND status = 'approved'");
                if ($recipients) while ($recipient = mysqli_fetch_assoc($recipients)) saveNotice($conn, $recipient['id'], 'appointment', $appointment['id'], $message);
            } else {
                saveNotice($conn, $userId, 'appointment', $appointment['id'], $message);
            }
        }
        mysqli_data_seek($noticeAppointmentResult, 0);
    }
    if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'receptionist') {
        $stock = mysqli_query($conn, "SELECT id, item_name, quantity, min_quantity FROM inventory WHERE quantity <= min_quantity");
        if ($stock) while ($item = mysqli_fetch_assoc($stock)) saveNotice($conn, $userId, 'low_stock', $item['id'], $item['item_name'] . ' has ' . $item['quantity'] . ' left; minimum level is ' . $item['min_quantity'] . '.');
        $payments = mysqli_query($conn, "SELECT id, service, amount FROM payments WHERE status != 'paid'");
        if ($payments) while ($payment = mysqli_fetch_assoc($payments)) saveNotice($conn, $userId, 'pending_payment', $payment['id'], 'Payment for ' . $payment['service'] . ' (' . $payment['amount'] . ') needs attention.');
    }
    $noticeRows = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id = $userId AND is_cleared = 0 ORDER BY is_read ASC, created_at DESC, id DESC");
    $unreadQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM notifications WHERE user_id = $userId AND is_cleared = 0 AND is_read = 0");
    $unreadRow = $unreadQuery ? mysqli_fetch_assoc($unreadQuery) : array('total' => 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Notifications — Elegance Salon</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='88'>💠</text></svg>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
</head>
<body data-page="notifications">
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
        <h4 class="mb-0">Notifications &amp; Reminders</h4>
        <small class="text-muted">Appointment reminders, low-stock alerts and payment updates</small>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <form method="post"><button class="btn btn-outline-accent btn-sm" type="submit" name="mark_all_read" value="1"><i class="bi bi-check2-all me-1"></i>Mark all as read</button></form>
        <form method="post"><button class="btn btn-outline-danger btn-sm" type="submit" name="clear_notifications" value="1"><i class="bi bi-trash me-1"></i>Clear</button></form>
        <a class="btn btn-outline-accent btn-sm" href="notifications.php"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</a>
      </div>
    </div>

    <div class="small text-muted mb-3"><?php echo (int)$unreadRow['total']; ?> unread notification(s)</div>
    <div id="notifList">
      <?php if ($noticeRows && mysqli_num_rows($noticeRows) > 0) { ?>
        <?php while ($notice = mysqli_fetch_assoc($noticeRows)) { ?>
          <div class="card p-3 mb-3 border-start border-4 <?php echo $notice['is_read'] ? 'border-secondary' : 'border-warning'; ?>">
            <div class="d-flex justify-content-between align-items-start gap-3">
              <div>
                <strong><?php if ($notice['notification_type'] == 'appointment') { ?><i class="bi bi-calendar-check me-2 text-accent"></i>Upcoming appointment<?php } elseif ($notice['notification_type'] == 'low_stock') { ?><i class="bi bi-exclamation-triangle me-2 text-danger"></i>Low inventory<?php } else { ?><i class="bi bi-receipt me-2 text-info"></i>Pending payment<?php } ?></strong>
                <span class="d-block mt-1"><?php echo $notice['message']; ?></span>
                <small class="text-muted mt-1 d-block"><?php echo $notice['is_read'] ? 'Read' : 'Unread'; ?> · <?php echo $notice['created_at']; ?></small>
              </div>
              <?php if (!$notice['is_read']) { ?><form method="post"><input type="hidden" name="notification_id" value="<?php echo (int)$notice['id']; ?>"><button class="btn btn-sm btn-outline-accent" type="submit" name="mark_one_read" value="1">Mark as read</button></form><?php } ?>
            </div>
          </div>
        <?php } ?>
      <?php } else { ?>
        <div class="card p-4 text-center text-muted">No current notifications.</div>
      <?php } ?>
    </div>

    <div class="card p-3 mt-2">
      <h6 class="px-1 mb-2"><i class="bi bi-info-circle text-accent me-2"></i>About these alerts</h6>
      <p class="small text-muted mb-0 px-2">Alerts reflect current appointments, inventory, and payment records. This page does not send email or SMS messages.</p>
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

  <script src="js/app.js"></script>
</body>
</html>
