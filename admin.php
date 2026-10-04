<?php

session_start();
require_once "config.php";

if(!isset($_SESSION['user_id'])){
    echo "<script>
            window.location.href = 'login.php';
          </script>";
    exit();
}

if($_SESSION['role'] != 'admin'){
    echo "<script>
            window.location.href = 'index.php';
          </script>";
    exit();
}

$error = "";
$success = "";
$showModal = false;

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS site_content (
    content_key VARCHAR(60) NOT NULL PRIMARY KEY,
    content_value TEXT NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
mysqli_query($conn, "INSERT IGNORE INTO site_content (content_key, content_value) VALUES
    ('hero_title', 'Where Beauty Meets Elegance'),
    ('hero_subtitle', 'Hair styling, manicures, pedicures and signature facials'),
    ('about_paragraph', 'Welcome to Elegance Salon. Our team offers personalized beauty services in a warm, comfortable setting.')");

if (isset($_POST['save_content'])) {
    $content = array(
        'hero_title' => trim(isset($_POST['hero_title']) ? $_POST['hero_title'] : ''),
        'hero_subtitle' => trim(isset($_POST['hero_subtitle']) ? $_POST['hero_subtitle'] : ''),
        'about_paragraph' => trim(isset($_POST['about_paragraph']) ? $_POST['about_paragraph'] : '')
    );
    if (strlen($content['hero_title']) < 4 || strlen($content['hero_subtitle']) < 10 || strlen($content['about_paragraph']) < 20) {
        $error = 'Enter a title (4+ characters), subtitle (10+), and about paragraph (20+).';
    } else {
        $contentSaved = true;
        foreach ($content as $key => $value) {
            $safeKey = mysqli_real_escape_string($conn, $key);
            $safeValue = mysqli_real_escape_string($conn, $value);
            if (!mysqli_query($conn, "INSERT INTO site_content (content_key, content_value) VALUES ('$safeKey', '$safeValue') ON DUPLICATE KEY UPDATE content_value = VALUES(content_value)")) {
                $contentSaved = false;
                break;
            }
        }
        if ($contentSaved) {
            echo "<script>window.location.href = 'admin.php?content=saved#tabContent';</script>";
            exit();
        }
        $error = 'Content could not be saved.';
    }
}

$contentValues = array('hero_title' => '', 'hero_subtitle' => '', 'about_paragraph' => '');
$contentResult = mysqli_query($conn, "SELECT content_key, content_value FROM site_content");
if ($contentResult) while ($contentRow = mysqli_fetch_assoc($contentResult)) { $contentValues[$contentRow['content_key']] = $contentRow['content_value']; }


// ==========================================
// APPROVE STAFF ACCOUNT
// ==========================================

if(isset($_POST['approve_user'])){

    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';

    if (ctype_digit((string)$user_id)) {
        $query = "UPDATE users
                  SET status = 'approved'
                  WHERE id = '$user_id'
                  AND (role = 'stylist' OR role = 'receptionist')";

        $result = mysqli_query($conn, $query);
    } else {
        $result = false;
    }

    if($result){

        echo "<script>
                window.location.href = 'admin.php#tabUsers';
              </script>";
        exit();

    } else {

        $error = "Staff approval failed.";

    }
}


// ==========================================
// TOTAL CLIENTS
// ==========================================

$clientQuery = "SELECT * FROM users WHERE role = 'client'";

$clientResult = mysqli_query($conn, $clientQuery);

$totalClients = mysqli_num_rows($clientResult);


// ==========================================
// TOTAL STAFF
// ==========================================

$staffQuery = "SELECT * FROM users 
               WHERE role = 'stylist' 
               OR role = 'receptionist'";

$staffResult = mysqli_query($conn, $staffQuery);

$totalStaff = mysqli_num_rows($staffResult);


// ==========================================
// CREATE STAFF ACCOUNT
// ==========================================

if(isset($_POST['create_user'])){

    $showModal = true;

    $username = mysqli_real_escape_string($conn, isset($_POST['username']) ? trim($_POST['username']) : '');
    $email = mysqli_real_escape_string($conn, isset($_POST['email']) ? trim($_POST['email']) : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $role = isset($_POST['role']) ? $_POST['role'] : '';

    if($role != "stylist" && $role != "receptionist"){

        $error = "Invalid staff role.";

    } else {

        $checkEmail = "SELECT * FROM users WHERE email = '$email'";

        $result = mysqli_query($conn, $checkEmail);

        if(mysqli_num_rows($result) > 0){

            $error = "User Already Exists.";

        } else {

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Staff created by admin is automatically approved
            $query = "INSERT INTO users
            (username, email, password, role, status)
            VALUES
            ('$username', '$email', '$hashedPassword', '$role', 'approved')";

            $result = mysqli_query($conn, $query);

            if($result){

                $success = "Staff account created successfully.";

            } else {

                $error = "Staff account creation failed.";

            }
        }
    }
}


// ==========================================
// DELETE STAFF
// ==========================================

if(isset($_POST['delete_user'])){

    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';

    if (ctype_digit((string)$user_id)) {
        $query = "DELETE FROM users
                  WHERE id = '$user_id'
                  AND (role = 'stylist' OR role = 'receptionist')";

        $result = mysqli_query($conn, $query);
    } else {
        $result = false;
    }

    if($result){

        echo "<script>
                window.location.href = 'admin.php';
              </script>";
        exit();
    }
}

if (isset($_POST['delete_feedback'])) {
    $feedback_id = isset($_POST['feedback_id']) ? $_POST['feedback_id'] : '';
    if (ctype_digit((string)$feedback_id)) {
        mysqli_query($conn, "DELETE FROM feedback WHERE id = '$feedback_id'");
    }
    echo "<script>window.location.href = 'admin.php';</script>";
    exit();
}

$adminFeedbackResult = mysqli_query($conn, "SELECT id, username, rating, comments, created_at FROM feedback ORDER BY created_at DESC");
$adminInventoryResult = mysqli_query($conn, "SELECT id, item_name, category, quantity, min_quantity, unit_cost, supplier FROM inventory ORDER BY item_name");

?>


<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Admin Panel — Elegance Salon</title>

<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='88'>💠</text></svg>">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">

<link href="css/style.css" rel="stylesheet">

<style>
    #adminTabs .nav-link {
        color: var(--es-ink-soft);
        background: transparent;
        border: 1px solid transparent;
        border-radius: .7rem;
    }

    #adminTabs .nav-link:hover {
        color: var(--es-gold-dark);
        background: rgba(197, 157, 95, .09);
    }

    #adminTabs .nav-link.active,
    #adminTabs .show > .nav-link {
        color: #fff;
        background: var(--es-gold);
        border-color: var(--es-gold);
    }

    #adminTabs .nav-link:focus-visible {
        outline: 3px solid rgba(197, 157, 95, .35);
        outline-offset: 2px;
    }
</style>

</head>


<body data-page="admin">

<a class="visually-hidden-focusable" href="#main">
    Skip to content
</a>


<!-- ========================================== -->
<!-- NAVBAR -->
<!-- ========================================== -->

<nav class="navbar navbar-expand-lg sticky-top main-nav">

    <div class="container">

        <a class="navbar-brand" href="index.php">

            <span class="brand-icon">
                <i class="bi bi-flower1"></i>
            </span>

            Elegance <span class="text-accent">Salon</span>

        </a>


        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNav"
            aria-controls="mainNav"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >

            <span class="navbar-toggler-icon"></span>

        </button>


        <div class="collapse navbar-collapse" id="mainNav">

            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">

                <li class="nav-item">
                    <a class="nav-link" href="index.php" data-nav="index">
                        Home
                    </a>
                </li>


                <li class="nav-item admin-only" data-nav-item="admin">

                    <a class="nav-link" href="admin.php" data-nav="admin">
                        Admin
                    </a>

                </li>


                <li class="nav-item">

                    <a class="nav-link" href="contact.php" data-nav="contact">
                        Contact Us
                    </a>

                </li>


                <li class="nav-item">

                    <a class="nav-link" href="feedback.php" data-nav="feedback">
                        Feedback
                    </a>

                </li>


                <li class="nav-item dropdown auth-only role-hidden">

                    <a
                        class="nav-link dropdown-toggle d-flex align-items-center"
                        href="#"
                        id="userMenu"
                        role="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                    >

                        <i class="bi bi-person-circle fs-5 me-1"></i>

                       <?php echo $_SESSION['username']; ?>

                    </a>


                    <ul
                        class="dropdown-menu dropdown-menu-end shadow-sm"
                        aria-labelledby="userMenu"
                    >

                        <li>

                            <span class="dropdown-item-text small text-muted">

                                <i class="bi bi-shield-check me-1"></i>
                                    <?php echo $_SESSION['role']; ?>

                            </span>

                        </li>


                        <li>
                            <hr class="dropdown-divider">
                        </li>


                        <li>

                            <a
                                class="dropdown-item"
                                href="dashboard.php"
                            >

                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard

                            </a>

                        </li>


                        <li>

                            <a
                                class="dropdown-item text-danger"
                                href="logout.php"
                                id="logoutBtn"
                            >

                                <i class="bi bi-box-arrow-right me-2"></i>
                                Logout

                            </a>

                        </li>

                    </ul>

                </li>

            </ul>

        </div>

    </div>

</nav>


<!-- ========================================== -->
<!-- MAIN -->
<!-- ========================================== -->

<main id="main">

<div class="app-shell">


<!-- ========================================== -->
<!-- SIDEBAR -->
<!-- ========================================== -->

<?php include '_partials/sidebar.php'; ?>


<!-- ========================================== -->
<!-- CONTENT -->
<!-- ========================================== -->

<div class="app-content">


<div class="d-flex align-items-center gap-2 mb-4">

    <button
        class="btn btn-outline-accent btn-sm d-lg-none"
        id="sidebarToggle"
        type="button"
    >

        <i class="bi bi-list"></i>

    </button>


    <div class="flex-grow-1">

        <h4 class="mb-0">

            <i class="bi bi-shield-lock text-accent me-2"></i>

            Admin Panel

        </h4>


        <small class="text-muted">

            Stock &amp; inventory, user accounts and content updates

        </small>

    </div>


    <span class="role-chip">

        <i class="bi bi-shield-check"></i>

        Administrator access only

    </span>

</div>


<!-- ========================================== -->
<!-- ADMIN TABS -->
<!-- ========================================== -->

<ul
    class="nav nav-pills mb-4 gap-2"
    id="adminTabs"
    role="tablist"
>


    <li class="nav-item" role="presentation">

        <button
            class="nav-link active"
            data-bs-toggle="pill"
            data-bs-target="#tabStock"
            type="button"
            role="tab"
        >

            <i class="bi bi-box-seam me-1"></i>

            Stock &amp; Inventory

        </button>

    </li>


    <li class="nav-item" role="presentation">

        <button
            class="nav-link"
            data-bs-toggle="pill"
            data-bs-target="#tabUsers"
            type="button"
            role="tab"
        >

            <i class="bi bi-people me-1"></i>

            User Accounts

        </button>

    </li>


    <li class="nav-item" role="presentation">

        <button
            class="nav-link"
            data-bs-toggle="pill"
            data-bs-target="#tabContent"
            type="button"
            role="tab"
        >

            <i class="bi bi-pencil-square me-1"></i>

            Content Updates

        </button>

    </li>


    <li class="nav-item" role="presentation">

        <button
            class="nav-link"
            data-bs-toggle="pill"
            data-bs-target="#tabFeedback"
            type="button"
            role="tab"
        >

            <i class="bi bi-chat-quote me-1"></i>

            Feedback Review

        </button>

    </li>

</ul>


<div class="tab-content">


<!-- ========================================== -->
<!-- STOCK TAB -->
<!-- ========================================== -->

<div
    class="tab-section tab-pane fade show active"
    id="tabStock"
    role="tabpanel"
>

    <div class="card p-3">

        <div class="d-flex flex-wrap gap-2 mb-3">

            <input
                type="search"
                id="adminSearch"
                class="form-control form-control-sm w-auto flex-grow-1"
                placeholder="Search inventory…"
            >


            <a
                class="btn btn-accent btn-sm"
                id="btnAdminInv"
                href="inventory.php"
            >

                <i class="bi bi-plus-lg me-1"></i>

                Manage Stock

            </a>

        </div>


        <div class="table-scroll">

            <table class="table table-hover align-middle mb-0">

                <thead>

                    <tr>

                        <th>Item</th>
                        <th>Category</th>
                        <th>Qty</th>
                        <th>Min</th>
                        <th>Unit Cost</th>
                        <th>Supplier</th>
                        <th class="text-end">Actions</th>

                    </tr>

                </thead>


                <tbody id="adminInvBody">
                    <?php if ($adminInventoryResult && mysqli_num_rows($adminInventoryResult) > 0) { ?>
                        <?php while ($stockRow = mysqli_fetch_assoc($adminInventoryResult)) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($stockRow['item_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($stockRow['category'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo (int)$stockRow['quantity']; ?></td>
                                <td><?php echo (int)$stockRow['min_quantity']; ?></td>
                                <td><?php echo number_format((float)$stockRow['unit_cost'], 2); ?></td>
                                <td><?php echo htmlspecialchars($stockRow['supplier'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-end"><a class="btn btn-sm btn-outline-accent" href="inventory.php">Manage</a></td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr><td colspan="7" class="text-center text-muted">No inventory items. Use Manage Stock to add supplies.</td></tr>
                    <?php } ?>
                </tbody>

            </table>

        </div>

    </div>

</div>


<!-- ========================================== -->
<!-- USER ACCOUNTS TAB -->
<!-- ========================================== -->

<div
    class="tab-section tab-pane fade"
    id="tabUsers"
    role="tabpanel"
>

    <div class="card p-3">


        <div class="d-flex justify-content-end mb-3">

            <button
                class="btn btn-accent btn-sm"
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#userModal"
            >

                <i class="bi bi-person-plus me-1"></i>

                Create Staff

            </button>

        </div>


        <div class="table-scroll">

            <table class="table table-hover align-middle mb-0">


                <thead>

                    <tr>

                        <th>Name</th>

                        <th>Username / Email</th>

                        <th>Role</th>

                        <th>Status</th>

                        <th class="text-end">Actions</th>

                    </tr>

                </thead>


                <tbody>


<?php

$query = "SELECT * FROM users 
          WHERE role = 'stylist' 
          OR role = 'receptionist'
          ORDER BY id DESC";

$result = mysqli_query($conn, $query);


if(mysqli_num_rows($result) > 0){

    while($user = mysqli_fetch_assoc($result)){

?>


<tr>


    <!-- NAME -->

    <td>

        <?php echo $user['username']; ?>

    </td>


    <!-- EMAIL -->

    <td>

        <?php echo $user['username']; ?>

        <br>

        <small class="text-muted">

            <?php echo $user['email']; ?>

        </small>

    </td>


    <!-- ROLE -->

    <td>

        <?php if($user['role'] == 'stylist'){ ?>

            <span class="badge text-bg-primary">

                Stylist

            </span>

        <?php } else { ?>

            <span class="badge text-bg-secondary">

                Receptionist

            </span>

        <?php } ?>

    </td>


    <!-- STATUS -->

    <td>

        <?php if($user['status'] == 'approved'){ ?>

            <span class="badge text-bg-success">

                Approved

            </span>

        <?php } else { ?>

            <span class="badge text-bg-warning">

                Pending

            </span>

        <?php } ?>

    </td>


    <!-- ACTIONS -->

    <td class="text-end">


        <!-- APPROVE BUTTON -->

        <?php if($user['status'] == 'pending'){ ?>

            <form
                method="post"
                style="display:inline;"
            >

                <input
                    type="hidden"
                    name="user_id"
                    value="<?php echo $user['id']; ?>"
                >


                <button
                    type="submit"
                    name="approve_user"
                    class="btn btn-sm btn-success"
                >

                    <i class="bi bi-check-lg"></i>

                    Approve

                </button>

            </form>

        <?php } ?>


        <!-- DELETE BUTTON -->

        <form
            method="post"
            style="display:inline;"
        >

            <input
                type="hidden"
                name="user_id"
                value="<?php echo $user['id']; ?>"
            >


            <button
                type="submit"
                name="delete_user"
                class="btn btn-sm btn-outline-danger"
            >

                <i class="bi bi-trash"></i>

            </button>

        </form>


    </td>


</tr>


<?php

    }

} else {

?>


<tr>

    <td
        colspan="5"
        class="text-center text-muted"
    >

        No staff accounts found.

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


<!-- ========================================== -->
<!-- CONTENT TAB -->
<!-- ========================================== -->

<div
    class="tab-section tab-pane fade"
    id="tabContent"
    role="tabpanel"
>

    <div class="card p-4">

        <h6 class="mb-3">
            Home Page Content
        </h6>


        <?php if (isset($_GET['content']) && $_GET['content'] === 'saved') { ?><div class="alert alert-success">Home page content saved.</div><?php } ?>
        <?php if ($error !== '') { ?><div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php } ?>
        <form id="contentForm" method="post">


            <div class="mb-3">

                <label
                    class="form-label"
                    for="contentHeroTitle"
                >

                    Hero Title *

                </label>


                <input
                    type="text"
                    id="contentHeroTitle" name="hero_title" value="<?php echo htmlspecialchars($contentValues['hero_title'], ENT_QUOTES, 'UTF-8'); ?>"
                    class="form-control"
                    required
                    minlength="4"
                >


                <div class="invalid-feedback">

                    Hero title is required.

                </div>

            </div>


            <div class="mb-3">

                <label
                    class="form-label"
                    for="contentHeroSub"
                >

                    Hero Subtitle *

                </label>


                <textarea
                    id="contentHeroSub" name="hero_subtitle"
                    class="form-control"
                    rows="2"
                    required
                    minlength="10"
                ><?php echo htmlspecialchars($contentValues['hero_subtitle'], ENT_QUOTES, 'UTF-8'); ?></textarea>


                <div class="invalid-feedback">

                    Subtitle is required (min 10 characters).

                </div>

            </div>


            <div class="mb-3">

                <label
                    class="form-label"
                    for="contentAbout"
                >

                    About Paragraph *

                </label>


                <textarea
                    id="contentAbout" name="about_paragraph"
                    class="form-control"
                    rows="4"
                    required
                    minlength="20"
                ><?php echo htmlspecialchars($contentValues['about_paragraph'], ENT_QUOTES, 'UTF-8'); ?></textarea>


                <div class="invalid-feedback">

                    About text is required (min 20 characters).

                </div>

            </div>


            <button
                class="btn btn-accent"
                type="submit" name="save_content" value="1"
            >

                <i class="bi bi-cloud-arrow-up me-1"></i>

                Publish Changes

            </button>


        </form>

    </div>

</div>


<!-- ========================================== -->
<!-- FEEDBACK TAB -->
<!-- ========================================== -->

<div
    class="tab-section tab-pane fade"
    id="tabFeedback"
    role="tabpanel"
>

    <div class="card p-3">


        <div class="table-scroll">

            <table class="table table-hover align-middle mb-0">

                <thead>

                    <tr>

                        <th>From</th>
                        <th>Rating</th>
                        <th>Comments</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>

                    </tr>

                </thead>


                <tbody id="adminFeedbackBody">
                    <?php if ($adminFeedbackResult && mysqli_num_rows($adminFeedbackResult) > 0) { ?>
                        <?php while ($feedbackRow = mysqli_fetch_assoc($adminFeedbackResult)) { ?>
                            <tr>
                                <td><?php echo $feedbackRow['username']; ?></td>
                                <td><?php echo $feedbackRow['rating']; ?>/5</td>
                                <td><?php echo $feedbackRow['comments']; ?></td>
                                <td><?php echo $feedbackRow['created_at']; ?></td>
                                <td class="text-end"><form method="POST"><input type="hidden" name="feedback_id" value="<?php echo $feedbackRow['id']; ?>"><button class="btn btn-sm btn-outline-danger" type="submit" name="delete_feedback">Delete</button></form></td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr><td colspan="5" class="text-center text-muted">No feedback submitted yet.</td></tr>
                    <?php } ?>
                </tbody>

            </table>

        </div>

    </div>

</div>


</div>

</div>

</div>

</main>


<!-- ========================================== -->
<!-- CREATE STAFF MODAL -->
<!-- ========================================== -->

<div
    class="modal fade"
    id="userModal"
    tabindex="-1"
    aria-hidden="true"
>


    <div class="modal-dialog modal-dialog-centered">


        <div class="modal-content">


            <div class="modal-header">

                <h5 class="modal-title">

                    Create Staff Account

                </h5>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>


            <div class="modal-body">


                <?php if($error != "") { ?>

                    <div class="alert alert-danger">

                        <?php echo $error; ?>

                    </div>

                <?php } ?>


                <?php if($success != "") { ?>

                    <div class="alert alert-success">

                        <?php echo $success; ?>

                    </div>

                <?php } ?>


                <form method="post">


                    <div class="mb-3">

                        <label class="form-label">

                            Username

                        </label>


                        <input
                            type="text"
                            name="username"
                            class="form-control"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">

                            Email

                        </label>


                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">

                            Password

                        </label>


                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            required
                            minlength="6"
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">

                            Staff Type

                        </label>


                        <select
                            name="role"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Select Staff Type
                            </option>

                            <option value="stylist">
                                Stylist
                            </option>

                            <option value="receptionist">
                                Receptionist
                            </option>

                        </select>

                    </div>


                    <button
                        type="submit"
                        name="create_user"
                        class="btn btn-accent w-100"
                    >

                        <i class="bi bi-person-plus me-1"></i>

                        Create Staff Account

                    </button>


                </form>

            </div>

        </div>

    </div>

</div>


<!-- ========================================== -->
<!-- FOOTER -->
<!-- ========================================== -->

<footer class="site-footer mt-auto">

    <div class="container py-5">

        <div class="row g-4">


            <div class="col-lg-4">

                <a
                    class="navbar-brand text-white d-inline-flex align-items-center mb-2"
                    href="index.php"
                >

                    <span class="brand-icon">

                        <i class="bi bi-flower1"></i>

                    </span>

                    Elegance
                    <span class="text-accent">
                        Salon
                    </span>

                </a>


                <p class="text-white-50 small pe-lg-4">

                    Salon Management Application —
                    appointments, client relations,
                    inventory control and staff scheduling,
                    all in one place.

                </p>


                <div class="d-flex gap-3 fs-5 footer-social">

                    <a href="#" aria-label="Instagram">
                        <i class="bi bi-instagram"></i>
                    </a>

                    <a href="#" aria-label="Facebook">
                        <i class="bi bi-facebook"></i>
                    </a>

                    <a href="#" aria-label="X">
                        <i class="bi bi-twitter-x"></i>
                    </a>

                    <a href="#" aria-label="YouTube">
                        <i class="bi bi-youtube"></i>
                    </a>

                </div>

            </div>


            <div class="col-6 col-lg-2">

                <h6 class="footer-title">
                    Quick Links
                </h6>


                <ul class="list-unstyled footer-links small">

                    <li>
                        <a href="index.php">
                            Home
                        </a>
                    </li>                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>

                    <li>
                        <a href="appointments.php">
                            Appointments
                        </a>
                    </li>

                    <li>
                        <a href="reports.php">
                            Reports
                        </a>
                    </li>

                    <li>
                        <a href="feedback.php">
                            Feedback
                        </a>
                    </li>

                </ul>

            </div>


            <div class="col-6 col-lg-3">

                <h6 class="footer-title">
                    Opening Hours
                </h6>


                <ul class="list-unstyled small text-white-50 mb-0">

                    <li class="d-flex justify-content-between">

                        <span>Mon – Fri</span>

                        <span>9:00 AM – 7:00 PM</span>

                    </li>


                    <li class="d-flex justify-content-between">

                        <span>Saturday</span>

                        <span>9:00 AM – 5:00 PM</span>

                    </li>


                    <li class="d-flex justify-content-between">

                        <span>Sunday</span>

                        <span>Closed</span>

                    </li>

                </ul>

            </div>


            <div class="col-lg-3">

                <h6 class="footer-title">
                    Get in Touch
                </h6>


                <ul class="list-unstyled small text-white-50 mb-0">

                    <li>

                        <i class="bi bi-geo-alt me-2 text-accent"></i>

                        128 Rosewood Avenue, San Jose, CA

                    </li>


                    <li>

                        <i class="bi bi-telephone me-2 text-accent"></i>

                        +1 (555) 240-8890

                    </li>


                    <li>

                        <i class="bi bi-envelope me-2 text-accent"></i>

                        hello@elegancesalon.com

                    </li>

                </ul>

            </div>

        </div>

    </div>


    <div class="footer-bottom">

        <div
            class="container small d-flex flex-column flex-md-row justify-content-between text-white-50"
        >

            <span>
                © 2026 Elegance Salon.
                Template for demonstration purposes.
            </span>


            

        </div>

    </div>

</footer>


<!-- TOAST -->

<div
    id="toastArea"
    class="toast-container position-fixed top-0 end-0 p-3"
    style="z-index:1090"
></div>


<!-- ========================================== -->
<!-- JAVASCRIPT -->
<!-- ========================================== -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<script>

    // Keep the selected admin section across reloads (including form submissions).
    (function () {
        var tabLinks = document.querySelectorAll('#adminTabs [data-bs-toggle="pill"]');
        var savedTarget = window.location.hash || sessionStorage.getItem('adminActiveTab');
        var selectedTab = document.querySelector('#adminTabs [data-bs-target="' + savedTarget + '"]');

        if (selectedTab) {
            bootstrap.Tab.getOrCreateInstance(selectedTab).show();
        }

        tabLinks.forEach(function (tabLink) {
            tabLink.addEventListener('shown.bs.tab', function (event) {
                var target = event.target.getAttribute('data-bs-target');
                sessionStorage.setItem('adminActiveTab', target);
                history.replaceState(null, '', target);
            });
        });
    })();

<?php if($showModal) { ?>

    var myModal = new bootstrap.Modal(
        document.getElementById('userModal')
    );

    myModal.show();

<?php } ?>

</script>


<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>






  <script src="js/app.js"></script>
</body>

</html>
