<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] == 'client') {
    echo "<script>window.location.href = 'login.php';</script>";
    exit();
}

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS message_status (
    user_id INT(11) NOT NULL,
    message_id INT(11) NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    is_cleared TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (user_id, message_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$currentUser = (int)$_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['resolve_message'])) {
        $messageId = isset($_POST['message_id']) ? (int)$_POST['message_id'] : 0;
        if ($messageId > 0) {
            mysqli_query($conn, "UPDATE contact_messages SET status = 'resolved' WHERE id = $messageId");
        }
    } elseif (isset($_POST['mark_message_read'])) {
        $messageId = isset($_POST['message_id']) ? (int)$_POST['message_id'] : 0;
        if ($messageId > 0) {
            mysqli_query($conn, "INSERT INTO message_status (user_id, message_id, is_read, is_cleared) VALUES ($currentUser, $messageId, 1, 0) ON DUPLICATE KEY UPDATE is_read = 1");
        }
    } elseif (isset($_POST['mark_all_read'])) {
        mysqli_query($conn, "INSERT INTO message_status (user_id, message_id, is_read, is_cleared)
            SELECT $currentUser, id, 1, 0 FROM contact_messages
            ON DUPLICATE KEY UPDATE is_read = 1");
    } elseif (isset($_POST['clear_messages'])) {
        mysqli_query($conn, "INSERT INTO message_status (user_id, message_id, is_read, is_cleared)
            SELECT $currentUser, id, 1, 1 FROM contact_messages
            ON DUPLICATE KEY UPDATE is_cleared = 1");
    }
    echo "<script>window.location.href = 'messages.php';</script>";
    exit();
}

$messageResult = mysqli_query($conn, "SELECT contact_messages.*, COALESCE(message_status.is_read, 0) AS is_read
    FROM contact_messages LEFT JOIN message_status ON contact_messages.id = message_status.message_id
    AND message_status.user_id = $currentUser
    WHERE COALESCE(message_status.is_cleared, 0) = 0
    ORDER BY message_status.is_read ASC, contact_messages.created_at DESC");
$unreadResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM contact_messages
    LEFT JOIN message_status ON contact_messages.id = message_status.message_id
    AND message_status.user_id = $currentUser
    WHERE COALESCE(message_status.is_read, 0) = 0 AND COALESCE(message_status.is_cleared, 0) = 0");
$unreadCount = $unreadResult ? mysqli_fetch_assoc($unreadResult) : array('total' => 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Contact Messages - Elegance Salon</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body data-page="messages">
  <a class="visually-hidden-focusable" href="#main">Skip to content</a>
  <nav class="navbar navbar-expand-lg sticky-top main-nav">
    <div class="container">
      <a class="navbar-brand" href="index.php"><span class="brand-icon"><i class="bi bi-flower1"></i></span>Elegance <span class="text-accent">Salon</span></a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
          <li class="nav-item"><a class="nav-link" href="index.php" data-nav="index">Home</a></li>
          <?php if ($_SESSION['role'] == 'admin') { ?><li class="nav-item"><a class="nav-link" href="admin.php" data-nav="admin">Admin</a></li><?php } ?>
          <li class="nav-item"><a class="nav-link" href="contact.php" data-nav="contact">Contact Us</a></li>
          <li class="nav-item"><a class="nav-link" href="feedback.php" data-nav="feedback">Feedback</a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-person-circle fs-5 me-1"></i><?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userMenu">
              <li><span class="dropdown-item-text small text-muted"><i class="bi bi-shield-check me-1"></i><?php echo htmlspecialchars($_SESSION['role'], ENT_QUOTES, 'UTF-8'); ?></span></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
              <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
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
          <button class="btn btn-outline-accent btn-sm d-lg-none" id="sidebarToggle" type="button" aria-label="Toggle sidebar"><i class="bi bi-list"></i></button>
          <div class="flex-grow-1"><h4 class="mb-0">Contact Messages</h4><small class="text-muted">Read and manage messages from salon clients</small></div>
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
          <small class="text-muted"><?php echo (int)$unreadCount['total']; ?> unread message(s)</small>
          <div class="d-flex gap-2 flex-wrap">
            <form method="post"><button class="btn btn-outline-accent btn-sm" type="submit" name="mark_all_read" value="1"><i class="bi bi-check2-all me-1"></i>Mark all as read</button></form>
            <form method="post"><button class="btn btn-outline-danger btn-sm" type="submit" name="clear_messages" value="1"><i class="bi bi-trash me-1"></i>Clear</button></form>
            <a class="btn btn-outline-accent btn-sm" href="messages.php"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</a>
          </div>
        </div>
    <?php if ($messageResult && mysqli_num_rows($messageResult) > 0) { ?>
      <?php while ($message = mysqli_fetch_assoc($messageResult)) { ?>
        <article class="card p-4 mb-3 border-start border-4 <?php echo $message['is_read'] ? 'border-secondary' : 'border-warning'; ?>">
          <div class="d-flex flex-wrap justify-content-between gap-2">
            <div><h2 class="h5 mb-1"><?php echo $message['subject']; ?></h2><div><?php echo $message['name']; ?> - <a href="mailto:<?php echo $message['email']; ?>"><?php echo $message['email']; ?></a></div></div>
            <span class="badge <?php echo $message['status'] == 'new' ? 'bg-warning text-dark' : 'bg-success'; ?> align-self-start"><?php echo ucfirst($message['status']); ?></span>
          </div>
          <p class="mt-3 mb-2"><?php echo nl2br($message['message']); ?></p>
          <small class="text-muted d-block"><?php echo $message['is_read'] ? 'Read' : 'Unread'; ?> · <?php echo $message['created_at']; ?></small>
          <div class="d-flex gap-2 mt-3 flex-wrap">
            <?php if (!$message['is_read']) { ?><form method="POST"><input type="hidden" name="message_id" value="<?php echo (int)$message['id']; ?>"><button class="btn btn-sm btn-outline-accent" type="submit" name="mark_message_read" value="1">Mark as read</button></form><?php } ?>
            <?php if ($message['status'] == 'new') { ?><form method="POST"><input type="hidden" name="message_id" value="<?php echo (int)$message['id']; ?>"><button class="btn btn-sm btn-outline-secondary" type="submit" name="resolve_message" value="1">Mark resolved</button></form><?php } ?>
          </div>
        </article>
      <?php } ?>
    <?php } else { ?>
      <div class="card p-4 text-center text-muted">No contact messages yet.</div>
    <?php } ?>
      </div>
    </div>
  </main>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/app.js"></script>
</body>
</html>
