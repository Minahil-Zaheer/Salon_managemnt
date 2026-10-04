<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'client') {
    echo "<script>window.location.href = 'login.php';</script>";
    exit();
}

$payment_id = isset($_GET['id']) ? $_GET['id'] : '';
$payment = false;
if (ctype_digit((string)$payment_id)) {
    $paymentResult = mysqli_query($conn, "SELECT payments.*, users.username, users.email FROM payments LEFT JOIN users ON payments.client_id = users.id WHERE payments.id = '$payment_id'");
    if ($paymentResult) {
        $payment = mysqli_fetch_assoc($paymentResult);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Payment Receipt</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
  <style>@media print { .no-print { display: none !important; } body { background: #fff; } .receipt { box-shadow: none !important; border: 0 !important; } }</style>
</head>
<body>
  <main class="container py-5">
    <?php if ($payment) { ?>
      <article class="receipt card p-4 mx-auto" style="max-width:600px">
        <div class="d-flex justify-content-between align-items-start"><div><h1 class="h3">Elegance Salon</h1><p class="text-muted mb-0">Payment receipt</p></div><strong>Receipt #<?php echo $payment['id']; ?></strong></div>
        <hr>
        <p><strong>Client:</strong> <?php echo $payment['username']; ?><br><strong>Email:</strong> <?php echo $payment['email']; ?></p>
        <p><strong>Service/Product:</strong> <?php echo $payment['service']; ?><br><strong>Payment method:</strong> <?php echo $payment['payment_method']; ?><br><strong>Date:</strong> <?php echo $payment['payment_date']; ?></p>
        <div class="d-flex justify-content-between border-top pt-3"><strong>Amount paid</strong><strong>$<?php echo number_format((float)$payment['amount'], 2); ?></strong></div>
        <p class="small text-muted mt-4 mb-0">Status: <?php echo ucfirst($payment['status']); ?></p>
      </article>
      <div class="text-center mt-3 no-print"><button class="btn btn-accent" type="button" onclick="window.print()">Print receipt</button> <a class="btn btn-outline-accent" href="payments.php">Back to payments</a></div>
    <?php } else { ?>
      <div class="card p-4 text-center"><h1 class="h4">Receipt not found</h1><a href="payments.php">Back to payments</a></div>
    <?php } ?>
  </main>
</body>
</html>
