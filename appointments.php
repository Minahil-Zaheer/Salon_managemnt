<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
  echo "<script>window.location.href = 'login.php';</script>";
  exit();
}

if ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'receptionist' && $_SESSION['role'] != 'stylist' && $_SESSION['role'] != 'client') {
  echo "<script>window.location.href = 'index.php';</script>";
  exit();
}

$service_options = array('Haircut', 'Hair Styling', 'Hair Coloring', 'Facial', 'Manicure', 'Pedicure', 'Makeup');
$selected_service = '';
if (isset($_GET['service']) && is_string($_GET['service']) && in_array($_GET['service'], $service_options)) {
  $selected_service = $_GET['service'];
}

// ADD APPOINTMENT
if (isset($_POST['add_appointment'])) {
  $result = false;

  if ($_SESSION['role'] == 'client') {
    $client_id = $_SESSION['user_id'];
  } else {
    $client_id = isset($_POST['client_id']) ? $_POST['client_id'] : '';
  }
  $stylist_id = isset($_POST['stylist_id']) ? $_POST['stylist_id'] : '';
  $appointment_date = isset($_POST['appointment_date']) ? $_POST['appointment_date'] : '';
  $appointment_time = isset($_POST['appointment_time']) ? $_POST['appointment_time'] : '';
  $service = isset($_POST['service']) ? $_POST['service'] : '';
  if ($_SESSION['role'] == 'client') {
    $status = 'scheduled';
  } else {
    $status = isset($_POST['status']) ? $_POST['status'] : '';
  }
  $valid_services = array('Haircut', 'Hair Styling', 'Hair Coloring', 'Facial', 'Manicure', 'Pedicure', 'Makeup');
  $valid_statuses = array('scheduled', 'confirmed', 'completed', 'cancelled');
  $date_parts = explode('-', $appointment_date);
  $valid_date = count($date_parts) == 3 && checkdate((int)$date_parts[1], (int)$date_parts[2], (int)$date_parts[0]);
  $valid_time = preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $appointment_time);
  $valid_stylist = true;
  if ($stylist_id != '') {
    $stylist_check_id = mysqli_real_escape_string($conn, $stylist_id);
    $stylistCheck = mysqli_query($conn, "SELECT id FROM users WHERE id = '$stylist_check_id' AND role = 'stylist' AND status = 'approved'");
    $valid_stylist = $stylistCheck && mysqli_num_rows($stylistCheck) === 1;
  }

  if (!ctype_digit((string)$client_id) || ($stylist_id != '' && !ctype_digit((string)$stylist_id)) || !$valid_stylist || !$valid_date || !$valid_time || !in_array($service, $valid_services) || !in_array($status, $valid_statuses)) {
    echo "<script>alert('Please enter valid appointment details.');</script>";
  } else {
    $client_id = mysqli_real_escape_string($conn, $client_id);
    $stylist_value = $stylist_id == '' ? "NULL" : "'" . mysqli_real_escape_string($conn, $stylist_id) . "'";
    $appointment_date = mysqli_real_escape_string($conn, $appointment_date);
    $appointment_time = mysqli_real_escape_string($conn, $appointment_time);
    $service = mysqli_real_escape_string($conn, $service);
    $status = mysqli_real_escape_string($conn, $status);

    $query = "INSERT INTO appointments
                (client_id, stylist_id, appointment_date, appointment_time, service, status)
                VALUES
                ('$client_id', $stylist_value, '$appointment_date', '$appointment_time', '$service', '$status')";

    $result = mysqli_query($conn, $query);
  }

  if ($result) {
    echo "<script>
                alert('Appointment booked successfully!');
                window.location.href = 'appointments.php';
              </script>";
    exit();
  } else {
    echo "<script>
                alert('Appointment booking failed.');
              </script>";
  }
}

// DELETE APPOINTMENT
if (isset($_POST['delete_appointment']) && $_SESSION['role'] != 'client') {
  $result = false;

  $appointment_id = isset($_POST['appointment_id']) ? $_POST['appointment_id'] : '';

  if (!ctype_digit((string)$appointment_id)) {
    // Ignore invalid delete requests without showing a browser popup.
  } else {
    $appointment_id = mysqli_real_escape_string($conn, $appointment_id);

    $query = "DELETE FROM appointments WHERE id = '$appointment_id'";

    $result = mysqli_query($conn, $query);

    if ($result) {
      echo "<script>
                window.location.href = 'appointments.php';
              </script>";
      exit();
    }
  }
}

// GET CLIENTS
$clientQuery = "SELECT * FROM users WHERE role = 'client' AND status = 'approved'";
$clientResult = mysqli_query($conn, $clientQuery);

// GET STYLISTS
$stylistQuery = "SELECT * FROM users 
                 WHERE role = 'stylist' 
                 AND status = 'approved'";

$stylistResult = mysqli_query($conn, $stylistQuery);

// GET APPOINTMENTS
$appointmentQuery = "SELECT appointments.*,
                     clients.username AS client_name,
                     stylists.username AS stylist_name
                     FROM appointments
                     LEFT JOIN users AS clients
                     ON appointments.client_id = clients.id
                     LEFT JOIN users AS stylists
                     ON appointments.stylist_id = stylists.id
                     " . ($_SESSION['role'] == 'client' ? "WHERE appointments.client_id = '" . mysqli_real_escape_string($conn, $_SESSION['user_id']) . "' " : "") . "
                     ORDER BY appointment_date ASC, appointment_time ASC, appointments.id ASC";

$appointmentResult = mysqli_query($conn, $appointmentQuery);
?>






<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Appointments — Elegance Salon</title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='88'>💠</text></svg>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>

<body data-page="appointments">
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
          <li class="nav-item admin-only" data-nav-item="admin"><?php if ($_SESSION['role'] == 'admin'): ?><a class="nav-link" href="admin.php" data-nav="admin">Admin</a><?php endif; ?></li>
          <li class="nav-item"><a class="nav-link" href="contact.php" data-nav="contact">Contact Us</a></li>
          <li class="nav-item"><a class="nav-link" href="feedback.php" data-nav="feedback">Feedback</a></li>
          <li class="nav-item dropdown auth-only role-hidden">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle fs-5 me-1"></i></i><?php echo $_SESSION['username']; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userMenu">
              <li><span class="dropdown-item-text small text-muted"><i class="bi bi-shield-check me-1"></i></i><?php echo $_SESSION['role']; ?></span></li>
              <li>
                <hr class="dropdown-divider">
              </li>
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
            <h4 class="mb-0">Appointment Management</h4>
            <small class="text-muted">Book appointments and review scheduled visits</small>
          </div>
          <button type="button"
            class="btn btn-accent"
            data-bs-toggle="modal"
            data-bs-target="#apptModal">
            <i class="bi bi-plus-lg"></i> New Appointment
          </button>
        </div>

        <div class="row g-4">
          <div class="col-12 order-2">
            <section class="card p-3 p-lg-4 appointment-calendar" aria-labelledby="calendarTitle">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <div>
                  <h5 class="mb-1" id="calendarTitle">Appointment calendar</h5>
                    <small class="text-muted">All appointments are listed first. Choose a day to filter that list.</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <button class="btn btn-outline-accent btn-sm" id="calToday" type="button">Today</button>
                  <button class="btn btn-outline-secondary btn-sm" id="calShowAll" type="button">Show all</button>
                  <div class="btn-group btn-group-sm" aria-label="Calendar month navigation">
                    <button class="btn btn-outline-accent" id="calPrev" type="button" aria-label="Previous month"><i class="bi bi-chevron-left"></i></button>
                    <span class="btn btn-light disabled calendar-month-label" id="calLabel" aria-live="polite">Month</span>
                    <button class="btn btn-outline-accent" id="calNext" type="button" aria-label="Next month"><i class="bi bi-chevron-right"></i></button>
                  </div>
                </div>
              </div>
              <div class="cal-grid" id="monthGrid" aria-label="Appointments by day"></div>
              <div class="d-flex align-items-center gap-2 mt-3 small text-muted"><span class="calendar-legend-dot"></span> Days with appointments</div>
            </section>
          </div>
          <div class="col-12 order-1">
            <div class="card p-3">
              <div class="d-flex flex-wrap gap-2 mb-3">
                <input type="search" id="apptSearch" class="form-control form-control-sm w-auto flex-grow-1" placeholder="Search client, service, stylist…">
                <select id="apptStatusFilter" class="form-select form-select-sm w-auto">
                  <option value="">All statuses</option>
                  <option>Confirmed</option>
                  <option>Scheduled</option>
                  <option>Completed</option>
                  <option>Cancelled</option>
                </select>
              </div>
              <div class="table-scroll">
                <table class="table table-hover align-middle mb-0">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Client</th>
                      <th>Service</th>
                      <th>Stylist</th>
                      <th>Date</th>
                      <th>Time</th>
                      <th>Status</th>
                      <th class="text-end">Actions</th>
                    </tr>
                  </thead>
                  <tbody id="appointmentRows">

                    <?php
                    if (mysqli_num_rows($appointmentResult) > 0) {

                      while ($appointment = mysqli_fetch_assoc($appointmentResult)) {
                    ?>

                        <tr>
                          <td><?php echo $appointment['id']; ?></td>

                          <td><?php echo $appointment['client_name']; ?></td>

                          <td><?php echo $appointment['service']; ?></td>

                          <td>
                            <?php
                            if ($appointment['stylist_name'] != NULL) {
                              echo $appointment['stylist_name'];
                            } else {
                              echo "Not Assigned";
                            }
                            ?>
                          </td>

                          <td><?php echo $appointment['appointment_date']; ?></td>

                          <td><?php echo $appointment['appointment_time']; ?></td>

                          <td><?php echo ucfirst($appointment['status']); ?></td>

                          <td class="text-end">

                            <?php if ($_SESSION['role'] != 'client') { ?>

                            <form method="POST" style="display:inline;">

                              <input type="hidden"
                                name="appointment_id"
                                value="<?php echo $appointment['id']; ?>">

                              <button type="submit"
                                name="delete_appointment"
                                class="btn btn-sm btn-outline-danger"
                                >

                                <i class="bi bi-trash"></i>

                              </button>

                            </form>

                            <?php } ?>

                          </td>
                        </tr>

                      <?php
                      }
                    } else {
                      ?>

                      <tr>
                        <td colspan="8" class="text-center text-muted">
                          No appointments found.
                        </td>
                      </tr>

                    <?php
                    }
                    ?>

                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

 <!-- Book / Reschedule Modal -->
<div class="modal fade" id="apptModal" tabindex="-1" aria-labelledby="apptModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="apptModalTitle">
          Book New Appointment
        </h5>

        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
          aria-label="Close">
        </button>
      </div>

      <form method="POST" id="apptForm">

        <div class="modal-body">

          <!-- Used when editing/rescheduling -->
          <input
            type="hidden"
            name="appointment_id"
            id="apptModalId"
            value=""
          >

          <div class="row g-3">

            <!-- Client -->
            <div class="col-md-6">
              <label class="form-label" for="apptClient">
                Client
              </label>

              <?php if ($_SESSION['role'] == 'client') { ?>
                <input type="text" class="form-control" value="<?php echo $_SESSION['username']; ?>" readonly>
                <input type="hidden" name="client_id" value="<?php echo $_SESSION['user_id']; ?>">
              <?php } else { ?>
                <select name="client_id" id="apptClient" class="form-select" required>
                  <option value="">Select Client</option>
                  <?php
                  mysqli_data_seek($clientResult, 0);
                  while ($client = mysqli_fetch_assoc($clientResult)) {
                  ?>
                    <option value="<?php echo $client['id']; ?>">
                      <?php echo $client['username']; ?>
                    </option>
                  <?php } ?>
                </select>
                <div class="invalid-feedback">Select a client.</div>
              <?php } ?>
            </div>


            <!-- Service -->
            <div class="col-md-6">
              <label class="form-label" for="apptService">
                Service
              </label>

              <select
                name="service"
                id="apptService"
                class="form-select"
                required
              >
                <option value="">Select Service</option>
                <?php foreach ($service_options as $service_option) { ?>
                  <option value="<?php echo $service_option; ?>" <?php if ($selected_service == $service_option) { echo 'selected'; } ?>><?php echo $service_option; ?></option>
                <?php } ?>
              </select>

              <div class="invalid-feedback">
                Select a service.
              </div>
            </div>


            <!-- Stylist -->
            <div class="col-md-4">
              <label class="form-label" for="apptStylist">
                Stylist
              </label>

              <select
                name="stylist_id"
                id="apptStylist"
                class="form-select"
              >
                <option value="">No preference</option>

                <?php
                mysqli_data_seek($stylistResult, 0);

                while ($stylist = mysqli_fetch_assoc($stylistResult)) {
                ?>
                  <option value="<?php echo htmlspecialchars($stylist['id'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($stylist['username'], ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php
                }
                ?>
              </select>
            </div>


            <!-- Date -->
            <div class="col-md-4">
              <label class="form-label" for="apptDate">
                Date
              </label>

              <input
                type="date"
                name="appointment_date"
                id="apptDate"
                class="form-control"
                required
              >

              <div class="invalid-feedback">
                Pick a date.
              </div>
            </div>


            <!-- Status -->
            <?php if ($_SESSION['role'] != 'client') { ?>
            <div class="col-md-4">
              <label class="form-label" for="apptStatus">
                Status
              </label>

              <select
                name="status"
                id="apptStatus"
                class="form-select"
              >
                <option value="scheduled">Scheduled</option>
                <option value="confirmed">Confirmed</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <?php } ?>


            <!-- Time -->
            <div class="col-12">
              <label class="form-label" for="apptTime">
                Appointment Time
              </label>

              <input
                type="time"
                name="appointment_time"
                id="apptTime"
                class="form-control"
                required
              >

              <div class="invalid-feedback">
                Select an appointment time.
              </div>

              <small class="text-muted">
                Choose an available appointment time.
              </small>
            </div>


            <!-- Notification -->
            <div class="col-12">
              <div
                class="alert alert-info d-flex align-items-center gap-2 py-2 mb-0"
                id="apptNotify"
              >
                <i class="bi bi-envelope-check"></i>

                <small>Booking details are saved in the salon system. Email and SMS notices are not configured.</small>
              </div>
            </div>

          </div>
        </div>


        <!-- Modal Footer -->
        <div class="modal-footer">

          <button
            type="button"
            class="btn btn-light"
            data-bs-dismiss="modal"
          >
            Discard
          </button>

          <button
            type="submit"
            name="add_appointment"
            value="1"
            class="btn btn-accent"
          >
            <i class="bi bi-check-lg me-1"></i>
            Save Appointment
          </button>

        </div>

      </form>

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
  
          <li><a href="contact.php">Contact</a></li>          <li><a href="appointments.php">Appointments</a></li>
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
    var calendarAppointments = <?php
      $calendarRows = array();
      if ($appointmentResult) {
        mysqli_data_seek($appointmentResult, 0);
        while ($calendarAppointment = mysqli_fetch_assoc($appointmentResult)) {
          $calendarRows[] = array(
            'date' => $calendarAppointment['appointment_date'],
            'time' => $calendarAppointment['appointment_time'],
            'client' => $calendarAppointment['client_name'],
            'service' => $calendarAppointment['service'],
            'status' => $calendarAppointment['status']
          );
        }
      }
      echo json_encode($calendarRows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    ?>;
    var selectedDate = '';
    (function () {
      var grid = document.getElementById('monthGrid');
      var label = document.getElementById('calLabel');
      if (!grid || !label) return;
      var visibleMonth = new Date();
      visibleMonth.setDate(1);
      var weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
      function pad(value) { return String(value).padStart(2, '0'); }
      function renderCalendar() {
        var year = visibleMonth.getFullYear();
        var month = visibleMonth.getMonth();
        var firstDay = new Date(year, month, 1).getDay();
        var daysInMonth = new Date(year, month + 1, 0).getDate();
        var today = new Date();
        label.textContent = visibleMonth.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
        grid.innerHTML = weekdays.map(function (day) { return '<div class="cal-dow" role="columnheader">' + day + '</div>'; }).join('');
        for (var blank = 0; blank < firstDay; blank++) grid.insertAdjacentHTML('beforeend', '<div class="cal-cell empty" aria-hidden="true"></div>');
        for (var day = 1; day <= daysInMonth; day++) {
          var dateKey = year + '-' + pad(month + 1) + '-' + pad(day);
          var events = calendarAppointments.filter(function (item) { return item.date === dateKey; });
          var isToday = today.getFullYear() === year && today.getMonth() === month && today.getDate() === day;
          var cell = document.createElement('button');
          cell.type = 'button';
          cell.className = 'cal-cell' + (isToday ? ' today' : '') + (events.length ? ' has-appointments' : '');
          cell.dataset.date = dateKey;
          if (selectedDate === dateKey) cell.classList.add('selected');
          cell.setAttribute('aria-label', dateKey + ', ' + events.length + ' appointment' + (events.length === 1 ? '' : 's'));
          var content = '<span class="day-num">' + day + '</span>';
          events.slice(0, 2).forEach(function (item) {
            var time = item.time ? item.time.slice(0, 5) : '';
            content += '<span class="appt" title="' + time + ' · ' + item.service + '">' + time + ' · ' + item.service + '</span>';
          });
          if (events.length > 2) content += '<span class="calendar-more">+' + (events.length - 2) + ' more</span>';
          cell.innerHTML = content;
          cell.addEventListener('click', function () {
            selectedDate = selectedDate === this.dataset.date ? '' : this.dataset.date;
            renderCalendar();
            filterAppointments();
            if (selectedDate) {
              var table = document.getElementById('appointmentRows');
              if (table) table.closest('.card').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
          });
          grid.appendChild(cell);
        }
      }
      document.getElementById('calPrev').addEventListener('click', function () { visibleMonth.setMonth(visibleMonth.getMonth() - 1); renderCalendar(); });
      document.getElementById('calNext').addEventListener('click', function () { visibleMonth.setMonth(visibleMonth.getMonth() + 1); renderCalendar(); });
      document.getElementById('calToday').addEventListener('click', function () { visibleMonth = new Date(); visibleMonth.setDate(1); renderCalendar(); });
      document.getElementById('calShowAll').addEventListener('click', function () { selectedDate = ''; renderCalendar(); filterAppointments(); });
      renderCalendar();
    })();
    function filterAppointments() {
      var search = document.getElementById('apptSearch').value.toLowerCase();
      var status = document.getElementById('apptStatusFilter').value.toLowerCase();
      document.querySelectorAll('#appointmentRows tr').forEach(function (row) {
        var matchesSearch = row.innerText.toLowerCase().indexOf(search) != -1;
        var rowStatus = row.cells.length > 6 ? row.cells[6].innerText.toLowerCase() : '';
        var matchesStatus = status == '' || rowStatus == status;
        var matchesDate = selectedDate == '' || (row.cells.length > 4 && row.cells[4].innerText.trim() === selectedDate);
        row.style.display = matchesSearch && matchesStatus && matchesDate ? '' : 'none';
      });
    }
    document.getElementById('apptSearch').addEventListener('input', filterAppointments);
    document.getElementById('apptStatusFilter').addEventListener('change', filterAppointments);
  </script>



  <script src="js/app.js"></script>
</body>

</html>
