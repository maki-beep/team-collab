<?php
// =============================================================
// iSkout Admin — add_listing.php / edit_listing.php (combined logic)
// Form for creating and editing a listing.
// =============================================================

require_once '../includes/db.php';
require_once '../includes/admin_guard.php';

$id = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;

$listing = [
    'name' => '', 'location' => '', 'description' => '', 'zone' => 'outside',
    'open_time' => '08:00', 'close_time' => '17:00', 'image_path' => ''
];
$activeCategories = [];
$activeTags = '';

if ($isEdit) {
    // Load existing
    $stmt = $conn->prepare("SELECT * FROM listings WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $listing = $stmt->get_result()->fetch_assoc() ?: $listing;
    $stmt->close();
    
    // Load categories
    $stmt = $conn->prepare("SELECT category FROM listing_categories WHERE listing_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $activeCategories[] = $r['category'];
    $stmt->close();
    
    // Load tags
    $stmt = $conn->prepare("SELECT tag FROM listing_tags WHERE listing_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $tags = [];
    while ($r = $res->fetch_assoc()) $tags[] = $r['tag'];
    $activeTags = implode(', ', $tags);
    $stmt->close();
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $isEdit ? 'Edit' : 'Add' ?> Listing - iSkout Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <style>
    .flash-alert { padding:16px; border-radius:12px; margin-bottom:24px; font-weight:600; font-size:14px; display:flex; align-items:center; gap:8px; }
    .flash-error   { background:#fff0f0; border:1px solid #f5caca; color:#d32f2f; }
    
    .form-group { margin-bottom: 20px; }
    .form-group label { display:block; font-weight:700; color:var(--text-dark); margin-bottom:8px; font-size:14px; }
    .form-control { width:100%; padding:12px; border:1px solid #e2e2e7; border-radius:8px; font-family:'Inter',sans-serif; box-sizing:border-box; }
    .btn-maroon { padding:14px 24px; border:none; border-radius:8px; background:var(--maroon-main); color:#fff; font-weight:700; cursor:pointer; }
    .checkbox-group { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; }
    .checkbox-item { display:flex; align-items:center; gap:8px; font-size:14px; }
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
          <a href="dashboard.php" class="menu-item">
            <i class="fa-solid fa-chart-pie"></i> Dashboard
          </a>
          <a href="add_listing.php" class="menu-item <?= !$isEdit ? 'active' : '' ?>">
            <i class="fa-solid fa-plus"></i> Add Listing
          </a>
          <a href="reports.php" class="menu-item content-queue">
            <i class="fa-solid fa-flag"></i> Reports Queue
          </a>
        </nav>
      </div>

      <div class="sidebar-footer">
        <button class="btn-logout" onclick="window.location.href='../actions/logout.php'">
          <span>Logout</span> <i class="fa-solid fa-arrow-right-from-bracket"></i>
        </button>
      </div>
    </aside>

    <main class="admin-main-content">
      <header class="admin-header">
        <h2 class="page-title"><?= $isEdit ? 'Edit' : 'Add New' ?> Listing</h2>
        <p class="page-subtitle"><?= $isEdit ? 'Update existing merchant details' : 'Create a new merchant entry in the directory' ?></p>
      </header>

      <?php if ($flash): ?>
        <div class="flash-alert flash-error">
          <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($flash['msg']) ?>
        </div>
      <?php endif; ?>

      <section class="workspace-card" style="max-width:800px">
        <form method="POST" action="../actions/admin/save_listing.php" enctype="multipart/form-data">
          <input type="hidden" name="id" value="<?= $id ?>">
          
          <div class="form-group">
            <label>Business Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($listing['name']) ?>" required />
          </div>
          
          <div class="form-group">
            <label>Location / Address</label>
            <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($listing['location']) ?>" required />
          </div>
          
          <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($listing['description']) ?></textarea>
          </div>
          
          <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px;">
            <div class="form-group">
              <label>Zone Matrix</label>
              <select name="zone" class="form-control">
                <option value="outside" <?= $listing['zone'] === 'outside' ? 'selected' : '' ?>>Outside Campus</option>
                <option value="inside" <?= $listing['zone'] === 'inside' ? 'selected' : '' ?>>Inside Campus</option>
              </select>
            </div>
            <div class="form-group">
              <label>Opening Time</label>
              <input type="time" name="open_time" class="form-control" value="<?= substr($listing['open_time'], 0, 5) ?>" required />
            </div>
            <div class="form-group">
              <label>Closing Time</label>
              <input type="time" name="close_time" class="form-control" value="<?= substr($listing['close_time'], 0, 5) ?>" required />
            </div>
          </div>

          <div class="form-group">
            <label>Categories</label>
            <div class="checkbox-group">
              <?php foreach (['food','study','print','rentals','supplies','repair'] as $cat): ?>
                <label class="checkbox-item">
                  <input type="checkbox" name="categories[]" value="<?= $cat ?>" <?= in_array($cat, $activeCategories) ? 'checked' : '' ?>> <?= ucfirst($cat) ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="form-group">
            <label>Tags (Comma separated)</label>
            <input type="text" name="tags" class="form-control" value="<?= htmlspecialchars($activeTags) ?>" placeholder="e.g. Free WiFi, Piso Print, Airconditioned" />
          </div>

          <div class="form-group">
            <label>Listing Photo</label>
            <?php if ($listing['image_path']): ?>
              <div style="margin-bottom:10px">
                <img src="../<?= htmlspecialchars($listing['image_path']) ?>" style="width:150px; border-radius:8px" />
              </div>
            <?php endif; ?>
            <input type="file" name="image" class="form-control" accept="image/jpeg, image/png, image/webp" />
            <span style="font-size:12px; color:#888; display:block; margin-top:4px">Leave blank to keep current photo. Max 2MB.</span>
          </div>

          <hr style="border:0;border-top:1px solid #e2e2e7; margin:30px 0" />

          <button type="submit" class="btn-maroon"><?= $isEdit ? 'Save Changes' : 'Create Listing' ?></button>
          <a href="dashboard.php" style="margin-left:16px; color:var(--text-muted); font-weight:600; text-decoration:none">Cancel</a>
        </form>
      </section>

    </main>
  </div>
</body>
</html>
