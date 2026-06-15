<?php
// =============================================================
// iSkout Admin — dashboard.php
// Main admin panel showing metrics and active listings.
// =============================================================

require_once '../includes/db.php';
require_once '../includes/admin_guard.php';

// Get metrics
$metrics = [
    'total'   => $conn->query("SELECT COUNT(*) as c FROM listings")->fetch_assoc()['c'],
    'outside' => $conn->query("SELECT COUNT(*) as c FROM listings WHERE zone = 'outside'")->fetch_assoc()['c'],
    'inside'  => $conn->query("SELECT COUNT(*) as c FROM listings WHERE zone = 'inside'")->fetch_assoc()['c']
];

// Get listings
$search = $_GET['q'] ?? '';
$sql = "SELECT l.*, GROUP_CONCAT(DISTINCT c.category) as categories, GROUP_CONCAT(DISTINCT t.tag) as tags 
        FROM listings l 
        LEFT JOIN listing_categories c ON l.id = c.listing_id 
        LEFT JOIN listing_tags t ON l.id = t.listing_id ";
if ($search) {
    $sql .= "WHERE l.name LIKE '%" . $conn->real_escape_string($search) . "%' ";
}
$sql .= "GROUP BY l.id ORDER BY l.id DESC";

$listingsRes = $conn->query($sql);
$listings = [];
while ($row = $listingsRes->fetch_assoc()) {
    $listings[] = $row;
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>iSkout Admin Portal - Dashboard</title>
  <link rel="icon" type="image/png" href="../assets/images/iSkout_Logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <style>
    .flash-alert { padding:16px; border-radius:12px; margin-bottom:24px; font-weight:600; font-size:14px; display:flex; align-items:center; gap:8px; }
    .flash-success { background:#e8f5e9; border:1px solid #a5d6a7; color:#2e7d32; }
    .flash-error   { background:#fff0f0; border:1px solid #f5caca; color:#d32f2f; }
    .search-btn { background:var(--maroon-main); color:white; border:none; padding:12px 20px; border-radius:10px; cursor:pointer; font-weight:600; font-family:'Inter',sans-serif; }
  </style>
</head>
<body>

  <div class="desktop-layout active">
    
    <aside class="admin-sidebar">
      <div class="sidebar-brand">
        <h1 class="brand-logo">iSkout</h1>
      </div>
      
      <div class="menu-section">
        <h3 class="menu-title">MANAGEMENT</h3>
        <nav class="sidebar-menu">
          <a href="dashboard.php" class="menu-item active">
            <i class="fa-solid fa-chart-pie"></i> Dashboard
          </a>
          <a href="add_listing.php" class="menu-item">
            <i class="fa-solid fa-plus"></i> Add Listing
          </a>
          <a href="reports.php" class="menu-item content-queue">
            <i class="fa-solid fa-flag"></i> Reports Queue
          </a>
        </nav>
      </div>

      <div class="sidebar-footer">
        <button class="btn-logout" onclick="document.getElementById('logout-modal-overlay').classList.add('show-modal')">
          <span>Logout</span> <i class="fa-solid fa-arrow-right-from-bracket"></i>
        </button>
      </div>
    </aside>

    <main class="admin-main-content">
      
      <header class="admin-header">
        <h2 class="page-title">Dashboard</h2>
        <p class="page-subtitle">Overview of Listings</p>
      </header>

      <?php if ($flash): ?>
        <div class="flash-alert flash-<?= $flash['type'] ?>">
          <i class="fas <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
          <?= htmlspecialchars($flash['msg']) ?>
        </div>
      <?php endif; ?>

      <section class="metrics-row">
        <div class="metric-card card-blue-border">
          <div class="card-body">
            <span class="metric-label">Total Businesses</span>
            <span class="metric-value"><?= $metrics['total'] ?></span>
            <span class="metric-subtext">Active Listings</span>
          </div>
        </div>

        <div class="metric-card">
          <div class="card-body">
            <span class="metric-label">Outside Merchants</span>
            <span class="metric-value"><?= $metrics['outside'] ?></span>
            <span class="metric-subtext">Around Campus</span>
          </div>
        </div>

        <div class="metric-card">
          <div class="card-body">
            <span class="metric-label">Inside Merchants</span>
            <span class="metric-value"><?= $metrics['inside'] ?></span>
            <span class="metric-subtext">Within Campus</span>
          </div>
        </div>
      </section>

      <section class="workspace-card">
        <form method="GET" action="dashboard.php" class="toolbar-controls" style="justify-content:flex-start">
          <div class="search-wrapper">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search Listings..." />
          </div>
          <button type="submit" class="search-btn">Search</button>
          <?php if ($search): ?>
            <a href="dashboard.php" style="color:var(--text-muted); text-decoration:none; font-size:14px; font-weight:600">Clear</a>
          <?php endif; ?>
        </form>

        <hr class="divider-line" />

        <div class="listings-grid">
          <?php foreach ($listings as $l): ?>
            <article class="listing-item-card">
              <div class="card-image-header">
                <img src="<?= $l['image_path'] ? '../'.htmlspecialchars($l['image_path']) : 'https://placehold.co/600x400?text=No+Image' ?>" alt="<?= htmlspecialchars($l['name']) ?>"/>
              </div>
              <div class="card-info-content">
                <h4 class="listing-name"><?= htmlspecialchars($l['name']) ?></h4>
                <p class="listing-location"><?= htmlspecialchars($l['location']) ?></p>
                <p class="listing-hours">
                  <?= date('g:i A', strtotime($l['open_time'])) ?> - <?= date('g:i A', strtotime($l['close_time'])) ?>
                </p>
                <div class="tags-badge-row">
                  <span class="badge-tag gray"><?= ucfirst($l['zone']) ?></span>
                  <?php if ($l['categories']): ?>
                    <?php foreach (explode(',', $l['categories']) as $c): ?>
                      <span class="badge-tag gray"><?= ucfirst($c) ?></span>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
              </div>
              <div class="card-action-footer">
                <button class="btn-action-modify" onclick="window.location.href='edit_listing.php?id=<?= $l['id'] ?>'">Edit</button>
                <form method="POST" action="../actions/admin/delete_listing.php" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this listing?');">
                  <input type="hidden" name="id" value="<?= $l['id'] ?>">
                  <button type="submit" class="btn-action-delete" style="width:100%">Delete</button>
                </form>
              </div>
            </article>
          <?php endforeach; ?>

          <?php if (empty($listings)): ?>
            <div style="grid-column:1/-1; padding:40px; text-align:center; color:var(--text-muted); font-size:16px; font-weight:600">
              <i class="fas fa-store-slash fa-2x" style="margin-bottom:12px"></i><br>
              No listings found.
            </div>
          <?php endif; ?>
        </div>
      </section>
    </main>
  </div>

  <div id="logout-modal-overlay" class="modal-overlay">
    <div class="modal-box">
      <h3 class="modal-title">Confirm Logout</h3>
      <p class="modal-subtitle">This will end your current session.</p>
      <p class="modal-body-text">Are you sure you want to log out of the iSkout Admin Portal?</p>
      <div class="modal-actions-row">
        <button onclick="document.getElementById('logout-modal-overlay').classList.remove('show-modal')" class="btn-modal btn-modal-gray">Cancel</button>
        <button onclick="window.location.href='../actions/logout.php'" class="btn-modal btn-modal-maroon">Logout</button>
      </div>
    </div>
  </div>

</body>
</html>
