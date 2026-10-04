<aside class="app-sidebar" id="appSidebar">
  <h6 class="overline px-2 mb-2"><?php echo $_SESSION['role'] == 'client' ? 'My Account' : 'Management'; ?></h6>
  <ul class="nav flex-column">
    <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
    <li class="nav-item"><a class="nav-link" href="appointments.php"><i class="bi bi-calendar-check me-2"></i>Appointments</a></li>
    <?php if ($_SESSION['role'] != 'client') { ?>
    <li class="nav-item"><a class="nav-link" href="clients.php"><i class="bi bi-people me-2"></i>Clients</a></li>
    <li class="nav-item"><a class="nav-link" href="inventory.php"><i class="bi bi-box-seam me-2"></i>Inventory</a></li>
    <li class="nav-item"><a class="nav-link" href="staff.php"><i class="bi bi-scissors me-2"></i>Staff</a></li>
    <li class="nav-item"><a class="nav-link" href="reports.php"><i class="bi bi-graph-up me-2"></i>Reports</a></li>
    <li class="nav-item"><a class="nav-link" href="payments.php"><i class="bi bi-receipt me-2"></i>Payments</a></li>
    <li class="nav-item"><a class="nav-link" href="notifications.php"><i class="bi bi-bell me-2"></i>Notifications</a></li>
    <li class="nav-item"><a class="nav-link" href="messages.php"><i class="bi bi-envelope me-2"></i>Messages</a></li>
    <?php if ($_SESSION['role'] == 'admin') { ?><li class="nav-item"><a class="nav-link" href="admin.php"><i class="bi bi-shield-lock me-2"></i>Admin Panel</a></li><?php } ?>
    <?php } ?>
    <li class="nav-item"><a class="nav-link" href="feedback.php"><i class="bi bi-chat-quote me-2"></i>Feedback</a></li>
  </ul>
  <hr>
  <a href="index.php" class="btn btn-outline-accent btn-sm w-100"><i class="bi bi-house me-1"></i>Back to Website</a>
</aside>
