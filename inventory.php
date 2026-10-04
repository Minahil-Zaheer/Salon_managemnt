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

    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS inventory_catalog (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        item_name VARCHAR(100) NOT NULL UNIQUE,
        category VARCHAR(100) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS inventory_suppliers (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        supplier_name VARCHAR(150) NOT NULL UNIQUE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    mysqli_query($conn, "INSERT IGNORE INTO inventory_catalog (item_name, category) VALUES
        ('Shampoo', 'Hair Products'), ('Conditioner', 'Hair Products'), ('Hair Color', 'Hair Products'),
        ('Hair Developer', 'Hair Products'), ('Hair Serum', 'Hair Products'), ('Hair Mask', 'Hair Products'),
        ('Heat Protectant', 'Hair Products'), ('Hair Spray', 'Hair Products'),
        ('Facial Cleanser', 'Beauty Supplies'), ('Moisturizer', 'Beauty Supplies'), ('Face Mask', 'Beauty Supplies'),
        ('Cotton Pads', 'Beauty Supplies'), ('Nail Polish', 'Beauty Supplies'), ('Nail Polish Remover', 'Beauty Supplies'),
        ('Disinfectant', 'Beauty Supplies'), ('Gloves', 'Beauty Supplies'),
        ('Hair Dryer', 'Tools & Equipment'), ('Flat Iron', 'Tools & Equipment'), ('Curling Iron', 'Tools & Equipment'),
        ('Hair Clippers', 'Tools & Equipment'), ('Hair Scissors', 'Tools & Equipment'), ('Manicure Kit', 'Tools & Equipment'),
        ('Facial Steamer', 'Tools & Equipment')");
    mysqli_query($conn, "INSERT INTO inventory_catalog (item_name, category) SELECT item_name, MAX(category) FROM inventory GROUP BY item_name ON DUPLICATE KEY UPDATE category = VALUES(category)");
    mysqli_query($conn, "INSERT IGNORE INTO inventory_suppliers (supplier_name) SELECT DISTINCT supplier FROM inventory WHERE TRIM(supplier) != ''");

    $message = '';

    if (isset($_POST['save_inventory'])) {
        $catalog_id = isset($_POST['catalog_id']) ? trim($_POST['catalog_id']) : '';
        $item_name = isset($_POST['new_item_name']) ? trim($_POST['new_item_name']) : '';
        $category = isset($_POST['category']) ? trim($_POST['category']) : '';
        if ($category === 'new') { $category = isset($_POST['new_category']) ? trim($_POST['new_category']) : ''; }
        $supplier_id = isset($_POST['supplier_id']) ? trim($_POST['supplier_id']) : '';
        $supplier = isset($_POST['new_supplier']) ? trim($_POST['new_supplier']) : '';
        $quantity = isset($_POST['quantity']) ? $_POST['quantity'] : '';
        $min_quantity = isset($_POST['min_quantity']) ? $_POST['min_quantity'] : '';
        $unit_cost = isset($_POST['unit_cost']) ? $_POST['unit_cost'] : '';

        if ($catalog_id === 'new') {
            // Item name and category are supplied in the form.
        } elseif (ctype_digit((string)$catalog_id)) {
            $catalogResult = mysqli_query($conn, "SELECT item_name, category FROM inventory_catalog WHERE id = '$catalog_id' LIMIT 1");
            if ($catalogResult && ($catalogItem = mysqli_fetch_assoc($catalogResult))) {
                $item_name = $catalogItem['item_name'];
                $category = $catalogItem['category'];
            } else { $message = 'Choose an item from the saved catalogue.'; }
        } else { $message = 'Choose an inventory item.'; }

        if ($supplier_id === 'new') {
            // Supplier name is supplied in the form.
        } elseif (ctype_digit((string)$supplier_id)) {
            $supplierResult = mysqli_query($conn, "SELECT supplier_name FROM inventory_suppliers WHERE id = '$supplier_id' LIMIT 1");
            if ($supplierResult && ($supplierRow = mysqli_fetch_assoc($supplierResult))) { $supplier = $supplierRow['supplier_name']; }
            else { $message = 'Choose a supplier from the saved list.'; }
        } else { $message = 'Choose a supplier.'; }

        if ($message != '' || $item_name == '' || $category == '' || $supplier == '' || !ctype_digit((string)$quantity) || !ctype_digit((string)$min_quantity) || !is_numeric($unit_cost) || $unit_cost < 0) {
            $message = 'Enter valid details for every inventory field.';
        } else {
            mysqli_begin_transaction($conn);
            $item_name = mysqli_real_escape_string($conn, $item_name);
            $category = mysqli_real_escape_string($conn, $category);
            $supplier = mysqli_real_escape_string($conn, $supplier);
            $unit_cost = mysqli_real_escape_string($conn, $unit_cost);
            $catalogSaved = mysqli_query($conn, "INSERT INTO inventory_catalog (item_name, category) VALUES ('$item_name', '$category') ON DUPLICATE KEY UPDATE category = VALUES(category)");
            $supplierSaved = mysqli_query($conn, "INSERT IGNORE INTO inventory_suppliers (supplier_name) VALUES ('$supplier')");
            $query = "INSERT INTO inventory (item_name, category, supplier, quantity, min_quantity, unit_cost)
                      VALUES ('$item_name', '$category', '$supplier', '$quantity', '$min_quantity', '$unit_cost')";
            if ($catalogSaved && $supplierSaved && mysqli_query($conn, $query)) {
                mysqli_commit($conn);
                echo "<script>window.location.href = 'inventory.php';</script>";
                exit();
            }
            mysqli_rollback($conn);
            $message = 'Inventory item could not be saved.';
        }
    }

    if (isset($_POST['delete_inventory'])) {
        $item_id = isset($_POST['item_id']) ? $_POST['item_id'] : '';
        if (ctype_digit((string)$item_id) && mysqli_query($conn, "DELETE FROM inventory WHERE id = '$item_id'")) {
            echo "<script>window.location.href = 'inventory.php';</script>";
            exit();
        }
        $message = 'Inventory item could not be deleted.';
    }

    $inventoryResult = mysqli_query($conn, "SELECT * FROM inventory ORDER BY category ASC, item_name ASC");
    $lowStockResult = mysqli_query($conn, "SELECT item_name, category, quantity, min_quantity FROM inventory WHERE quantity <= min_quantity ORDER BY category ASC, item_name ASC");
    $catalogResult = mysqli_query($conn, "SELECT id, item_name, category FROM inventory_catalog ORDER BY category, item_name");
    $supplierOptionsResult = mysqli_query($conn, "SELECT id, supplier_name FROM inventory_suppliers ORDER BY supplier_name");
    $categorySummaryResult = mysqli_query($conn, "SELECT category, COUNT(*) AS item_count, SUM(quantity) AS units FROM inventory GROUP BY category ORDER BY category");
    $categoryOptionsResult = mysqli_query($conn, "SELECT DISTINCT category FROM inventory_catalog ORDER BY category");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Inventory — Elegance Salon</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='88'>💠</text></svg>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
</head>
<body data-page="inventory">
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
        <h4 class="mb-0">Inventory Management</h4>
        <small class="text-muted">Reuse saved items and suppliers, view stock by category, and track low-stock alerts</small>
      </div>
      <button class="btn btn-accent btn-sm" id="btnNewInv" type="button" data-bs-toggle="modal" data-bs-target="#invModal"><i class="bi bi-plus-lg me-1"></i>Add Item</button>
    </div>

    <?php if ($message != '') { ?><div class="alert alert-warning"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div><?php } ?>

    <div class="row g-3 mb-4">
      <?php if ($categorySummaryResult && mysqli_num_rows($categorySummaryResult) > 0) { while ($categorySummary = mysqli_fetch_assoc($categorySummaryResult)) { ?>
        <div class="col-sm-6 col-xl-4"><div class="card p-3 h-100"><small class="text-muted"><?php echo htmlspecialchars($categorySummary['category'], ENT_QUOTES, 'UTF-8'); ?></small><div class="h5 mb-0 mt-1"><?php echo (int)$categorySummary['item_count']; ?> item(s) <span class="text-muted fw-normal">· <?php echo (int)$categorySummary['units']; ?> units</span></div></div></div>
      <?php } } else { ?><div class="col-12"><div class="card p-3 text-muted">Inventory totals by category will appear here.</div></div><?php } ?>
    </div>

    <div class="card p-3 mb-4">
      <h6 class="px-1 mb-3"><i class="bi bi-exclamation-triangle text-accent me-2"></i>Low Stock Items</h6>
      <div id="lowStockBox">
        <?php if ($lowStockResult && mysqli_num_rows($lowStockResult) > 0) { ?>
          <ul class="mb-0">
            <?php while ($lowItem = mysqli_fetch_assoc($lowStockResult)) { ?>
              <li><strong><?php echo htmlspecialchars($lowItem['category'], ENT_QUOTES, 'UTF-8'); ?></strong> — <?php echo htmlspecialchars($lowItem['item_name'], ENT_QUOTES, 'UTF-8'); ?>: <?php echo (int)$lowItem['quantity']; ?> left (minimum <?php echo (int)$lowItem['min_quantity']; ?>)</li>
            <?php } ?>
          </ul>
        <?php } else { ?>
          <p class="text-muted mb-0">No low stock items.</p>
        <?php } ?>
      </div>
    </div>

    <div class="card p-3">
      <div class="d-flex flex-wrap gap-2 mb-3">
        <input type="search" id="invSearch" class="form-control form-control-sm w-auto flex-grow-1" placeholder="Search item, category or supplier…">
        <span class="small text-muted align-self-center">Low stock is shown in the status column.</span>
      </div>
      <div class="table-scroll">
        <table class="table table-hover align-middle mb-0">
          <thead><tr><th>Category</th><th>Item</th><th>Qty</th><th>Min</th><th>Unit Cost</th><th>Supplier</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
          <tbody id="invBody">
            <?php if ($inventoryResult && mysqli_num_rows($inventoryResult) > 0) { ?>
              <?php while ($item = mysqli_fetch_assoc($inventoryResult)) { ?>
                <tr>
                  <td><?php echo htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo (int)$item['quantity']; ?></td>
                  <td><?php echo (int)$item['min_quantity']; ?></td>
                  <td><?php echo number_format((float)$item['unit_cost'], 2); ?></td>
                  <td><?php echo htmlspecialchars($item['supplier'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo $item['quantity'] <= $item['min_quantity'] ? 'Low stock' : 'In stock'; ?></td>
                  <td class="text-end">
                    <form method="POST">
                      <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                      <button type="submit" name="delete_inventory" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                  </td>
                </tr>
              <?php } ?>
            <?php } else { ?>
              <tr><td colspan="8" class="text-center text-muted">No inventory items found.</td></tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Inventory modal -->
<div class="modal fade" id="invModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="invModalTitle">Add Inventory Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="invForm" class="needs-validation" method="POST" novalidate>
          <input type="hidden" id="invId">
          <div class="mb-3">
            <label class="form-label" for="invCatalog">Item Name</label>
            <select id="invCatalog" name="catalog_id" class="form-select" required>
              <option value="">Choose saved item</option>
              <?php
                $lastCategory = null;
                if ($catalogResult) { while ($catalog = mysqli_fetch_assoc($catalogResult)) {
                  if ($catalog['category'] !== $lastCategory) {
                    if ($lastCategory !== null) { echo '</optgroup>'; }
                    echo '<optgroup label="' . htmlspecialchars($catalog['category'], ENT_QUOTES, 'UTF-8') . '">';
                    $lastCategory = $catalog['category'];
                  }
              ?>
                <option value="<?php echo (int)$catalog['id']; ?>" data-category="<?php echo htmlspecialchars($catalog['category'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($catalog['item_name'], ENT_QUOTES, 'UTF-8'); ?></option>
              <?php } if ($lastCategory !== null) { echo '</optgroup>'; } } ?>
              <option value="new">+ Add a new item</option>
            </select>
            <div class="invalid-feedback">Choose an item or add a new one.</div>
            <input type="text" id="invName" name="new_item_name" class="form-control mt-2 d-none" minlength="2" placeholder="Enter a new item name">
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="invCategory">Category</label>
              <select id="invCategory" name="category" class="form-select" required>
                <option value="">Choose category</option>
                <?php if ($categoryOptionsResult) { while ($categoryOption = mysqli_fetch_assoc($categoryOptionsResult)) { ?><option value="<?php echo htmlspecialchars($categoryOption['category'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($categoryOption['category'], ENT_QUOTES, 'UTF-8'); ?></option><?php } } ?>
                <option value="new">+ Add a new category</option>
              </select>
              <input type="text" id="invNewCategory" name="new_category" class="form-control mt-2 d-none" placeholder="Enter a new category">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="invSupplier">Supplier</label>
              <select id="invSupplier" name="supplier_id" class="form-select" required>
                <option value="">Choose saved supplier</option>
                <?php if ($supplierOptionsResult) { while ($supplierOption = mysqli_fetch_assoc($supplierOptionsResult)) { ?><option value="<?php echo (int)$supplierOption['id']; ?>"><?php echo htmlspecialchars($supplierOption['supplier_name'], ENT_QUOTES, 'UTF-8'); ?></option><?php } } ?>
                <option value="new">+ Add a new supplier</option>
              </select>
              <input type="text" id="invNewSupplier" name="new_supplier" class="form-control mt-2 d-none" placeholder="Enter supplier name">
              <div class="invalid-feedback">Choose or add a supplier.</div>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="invQty">Quantity</label>
              <input type="number" id="invQty" name="quantity" class="form-control" required min="0" placeholder="0">
              <div class="invalid-feedback">Quantity must be 0 or more.</div>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="invMin">Minimum Level</label>
              <input type="number" id="invMin" name="min_quantity" class="form-control" required min="0" placeholder="5">
              <div class="invalid-feedback">Minimum level is required.</div>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="invCost">Unit Cost ($)</label>
              <input type="number" id="invCost" name="unit_cost" class="form-control" required min="0" step="0.01" placeholder="0.00">
              <div class="invalid-feedback">Unit cost is required.</div>
            </div>
          </div>
          <div class="alert alert-info d-flex align-items-center gap-2 py-2 mt-3 mb-0">
            <i class="bi bi-lightning-charge"></i>
            <small>Items at or below the minimum quantity are marked as low stock in this list.</small>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Discard</button>
        <button type="submit" name="save_inventory" value="1" form="invForm" class="btn btn-accent" id="invSaveBtn"><i class="bi bi-check-lg me-1"></i>Save Item</button>
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
 
          <li><a href="contact.php">Contact</a></li>         <li><a href="appointments.php">Appointments</a></li>
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
  document.getElementById('invSearch').addEventListener('input', function () {
    var search = this.value.toLowerCase();
    document.querySelectorAll('#invBody tr').forEach(function (row) {
      row.style.display = row.innerText.toLowerCase().indexOf(search) != -1 ? '' : 'none';
    });
  });
  var catalogSelect = document.getElementById('invCatalog');
  var itemNameInput = document.getElementById('invName');
  var categorySelect = document.getElementById('invCategory');
  var newCategoryInput = document.getElementById('invNewCategory');
  var supplierSelect = document.getElementById('invSupplier');
  var newSupplierInput = document.getElementById('invNewSupplier');

  catalogSelect.addEventListener('change', function () {
    var isNew = this.value === 'new';
    itemNameInput.classList.toggle('d-none', !isNew);
    itemNameInput.required = isNew;
    if (isNew) {
      itemNameInput.value = '';
      categorySelect.value = '';
    } else if (this.value) {
      var selectedItem = this.options[this.selectedIndex];
      categorySelect.value = selectedItem.dataset.category || '';
    }
  });
  categorySelect.addEventListener('change', function () {
    var isNew = this.value === 'new';
    newCategoryInput.classList.toggle('d-none', !isNew);
    newCategoryInput.required = isNew;
    if (!isNew) newCategoryInput.value = '';
  });
  supplierSelect.addEventListener('change', function () {
    var isNew = this.value === 'new';
    newSupplierInput.classList.toggle('d-none', !isNew);
    newSupplierInput.required = isNew;
    if (!isNew) newSupplierInput.value = '';
  });
</script>


  <script src="js/app.js"></script>
</body>
</html>
