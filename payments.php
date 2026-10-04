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

    $message = '';

    if (isset($_POST['save_payment'])) {
        $client_id = isset($_POST['client_id']) ? $_POST['client_id'] : '';
        $service = isset($_POST['service']) ? trim($_POST['service']) : '';
        $method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';
        $amount = isset($_POST['amount']) ? $_POST['amount'] : '';
        $valid_services = array('Haircut', 'Hair Styling', 'Hair Coloring', 'Facial', 'Manicure', 'Pedicure', 'Makeup', 'Retail Product Sale');
        $valid_methods = array('Cash', 'Card', 'Bank Transfer');

        if (!ctype_digit((string)$client_id) || !is_numeric($amount) || $amount <= 0 || !in_array($service, $valid_services) || !in_array($method, $valid_methods)) {
            $message = 'Enter valid payment details.';
        } else {
            $client_id = mysqli_real_escape_string($conn, $client_id);
            $service = mysqli_real_escape_string($conn, $service);
            $method = mysqli_real_escape_string($conn, $method);
            $amount = mysqli_real_escape_string($conn, $amount);
            $query = "INSERT INTO payments (client_id, amount, payment_date, status, service, payment_method)
                      VALUES ('$client_id', '$amount', CURDATE(), 'paid', '$service', '$method')";
            if (mysqli_query($conn, $query)) {
                echo "<script>window.location.href = 'payments.php';</script>";
                exit();
            }
            $message = 'Payment could not be saved.';
        }
    }

    if (isset($_POST['delete_payment'])) {
        $payment_id = isset($_POST['payment_id']) ? $_POST['payment_id'] : '';
        if (ctype_digit((string)$payment_id) && mysqli_query($conn, "DELETE FROM payments WHERE id = '$payment_id'")) {
            echo "<script>window.location.href = 'payments.php';</script>";
            exit();
        }
        $message = 'Payment could not be deleted.';
    }

    $clientResult = mysqli_query($conn, "SELECT id, username FROM users WHERE role = 'client' AND status = 'approved' ORDER BY username");
    $service_prices = array('Haircut' => '30.00', 'Hair Styling' => '45.00', 'Hair Coloring' => '85.00', 'Facial' => '60.00', 'Manicure' => '25.00', 'Pedicure' => '35.00', 'Makeup' => '70.00');
    $service_options = array('Haircut', 'Hair Styling', 'Hair Coloring', 'Facial', 'Manicure', 'Pedicure', 'Makeup', 'Retail Product Sale');
    $paymentResult = mysqli_query($conn, "SELECT payments.*, users.username AS client_name FROM payments LEFT JOIN users ON payments.client_id = users.id ORDER BY payments.payment_date DESC, payments.id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Payments & Invoicing — Elegance Salon</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='88'>💠</text></svg>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
</head>
<body data-page="payments">
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
        <h4 class="mb-0">Payments &amp; Invoicing</h4>
        <small class="text-muted">Record client payments and generate receipts</small>
      </div>
      <button class="btn btn-accent btn-sm" id="btnNewPay" type="button" data-bs-toggle="modal" data-bs-target="#payModal"><i class="bi bi-plus-lg me-1"></i>Record Payment</button>
    </div>

    <?php if ($message != '') { ?><div class="alert alert-warning"><?php echo $message; ?></div><?php } ?>

    <div class="card p-3">
      <div class="table-scroll">
        <table class="table table-hover align-middle mb-0">
          <thead><tr><th>Invoice</th><th>Client</th><th>Date</th><th>Service</th><th>Method</th><th>Amount</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
          <tbody id="payBody">
            <?php if ($paymentResult && mysqli_num_rows($paymentResult) > 0) { ?>
              <?php while ($payment = mysqli_fetch_assoc($paymentResult)) { ?>
                <tr>
                  <td><?php echo $payment['id']; ?></td>
                  <td><?php echo $payment['client_name']; ?></td>
                  <td><?php echo $payment['payment_date']; ?></td>
                  <td><?php echo $payment['service']; ?></td>
                  <td><?php echo $payment['payment_method']; ?></td>
                  <td><?php echo number_format((float)$payment['amount'], 2); ?></td>
                  <td><?php echo ucfirst($payment['status']); ?></td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-outline-accent" href="invoice.php?id=<?php echo $payment['id']; ?>" target="_blank">Receipt</a>
                    <form method="POST">
                      <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                      <button type="submit" name="delete_payment" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                  </td>
                </tr>
              <?php } ?>
            <?php } else { ?>
              <tr><td colspan="8" class="text-center text-muted">No payments found.</td></tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Payment modal -->
<div class="modal fade" id="payModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="payModalTitle">Record Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="payForm" class="needs-validation" method="POST" novalidate>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="payClient">Client</label>
              <select id="payClient" name="client_id" class="form-select" required>
                <option value="">Select Client</option>
                <?php if ($clientResult) { while ($client = mysqli_fetch_assoc($clientResult)) { ?>
                  <option value="<?php echo $client['id']; ?>"><?php echo $client['username']; ?></option>
                <?php } } ?>
              </select>
              <div class="invalid-feedback">Select a client.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="payService">Service / Product</label>
              <select id="payService" name="service" class="form-select" required>
                <option value="">Select Service / Product</option>
                <?php foreach ($service_options as $service_option) { ?><option value="<?php echo htmlspecialchars($service_option, ENT_QUOTES, 'UTF-8'); ?>" <?php if (isset($service_prices[$service_option])) { ?>data-price="<?php echo $service_prices[$service_option]; ?>"<?php } ?>><?php echo htmlspecialchars($service_option, ENT_QUOTES, 'UTF-8'); ?></option><?php } ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="payMethod">Payment Method</label>
              <select id="payMethod" name="payment_method" class="form-select" required>
                <option value="Cash">Cash</option><option value="Card">Card</option><option value="Bank Transfer">Bank Transfer</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="payAmount">Amount ($)</label>
              <input type="number" id="payAmount" name="amount" class="form-control" required min="0.01" step="0.01" placeholder="Select a service first">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Discard</button>
        <button type="submit" name="save_payment" value="1" form="payForm" class="btn btn-accent" id="paySaveBtn"><i class="bi bi-check-lg me-1"></i>Save Payment</button>
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
  (function () {
    var serviceSelect = document.getElementById('payService');
    var amountInput = document.getElementById('payAmount');
    var amountHelp = document.getElementById('payAmountHelp');
    if (!serviceSelect || !amountInput) return;
    serviceSelect.addEventListener('change', function () {
      var selectedOption = this.options[this.selectedIndex];
      var price = selectedOption ? selectedOption.dataset.price : '';
      if (price) {
        amountInput.value = price;
        amountHelp.textContent = 'Starting price filled in automatically. You can adjust the amount.';
      } else {
        amountInput.value = '';
        amountHelp.textContent = this.value === 'Retail Product Sale' ? 'Retail products have no fixed price here; enter the sale amount.' : 'Choose a service to fill its starting price.';
      }
    });
  })();
</script>

  <script src="js/app.js"></script>
</body>
</html>
