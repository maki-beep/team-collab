<?php
// =============================================================
// iSkout — listing.php
// Individual listing view and reporting modal.
// Displays full listing details from the database.
// =============================================================

require_once 'includes/auth_guard.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    redirect('dashboard.php');
}

$stmt = $conn->prepare(
    "SELECT l.*, 
            GROUP_CONCAT(DISTINCT c.category) as categories,
            GROUP_CONCAT(DISTINCT t.tag) as tags
     FROM listings l
     LEFT JOIN listing_categories c ON l.id = c.listing_id
     LEFT JOIN listing_tags t ON l.id = t.listing_id
     WHERE l.id = ?
     GROUP BY l.id"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$listing = $stmt->get_result()->fetch_assoc();

if (!$listing) {
    redirect('dashboard.php');
}

$isOpen = is_open_now($listing['open_time'], $listing['close_time']);
$status = $isOpen ? 'Open Now' : 'Closed';
$hours  = format_time($listing['open_time']) . ' – ' . format_time($listing['close_time']);
$image  = $listing['image_path'] ? APP_URL . '/' . $listing['image_path'] : '';
$tags   = $listing['tags'] ? explode(',', $listing['tags']) : [];

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= sanitize($listing['name']) ?> — iSkout</title>
  <link rel="icon" type="image/png" href="assets/images/iSkout_Logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <link rel="stylesheet" href="assets/css/styles.css"/>
  <style>
    .flash-alert { display:flex; align-items:center; gap:8px; font-size:12px; font-weight:600; border-radius:8px; padding:8px 12px; margin-bottom:10px; margin-top:20px; }
    .flash-success { background:#e8f5e9; border:1px solid #a5d6a7; color:#2e7d32; }
    .flash-error   { background:#fff0f0; border:1px solid #f5caca; color:#d32f2f; }
  </style>
</head>
<body>
  <div class="mobile-container">
    <div class="screen active" style="overflow-y:auto">
      
      <div class="listing-hero-wrapper">
        <?php if ($image): ?>
          <img src="<?= sanitize($image) ?>" alt="<?= sanitize($listing['name']) ?>" class="listing-hero-image" />
        <?php else: ?>
          <div class="listing-hero-fallback">
            <i class="fas fa-image"></i>
          </div>
        <?php endif; ?>
        <div class="listing-hero-actions">
          <button class="hero-icon-btn hero-left-btn" onclick="window.history.back()"><i class="fas fa-arrow-left"></i></button>
          <div class="hero-right-actions">
            <button class="hero-icon-btn hero-fav-btn" data-listing="<?= sanitize($listing['name']) ?>" onclick="toggleFavoriteListing(this)" aria-label="Favorite Listing"><i class="far fa-heart"></i></button>
            <button class="hero-icon-btn hero-report-btn" onclick="openModal('modal-report-listing')" aria-label="Report Listing"><i class="fas fa-flag"></i></button>
          </div>
        </div>
      </div>

      <div class="listing-detail-content">
        <?php if ($flash): ?>
          <div class="flash-alert flash-<?= $flash['type'] ?>">
            <i class="fas <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
            <?= sanitize($flash['msg']) ?>
          </div>
        <?php endif; ?>

        <h1 style="font-size:24px; font-weight:800; margin:0 0 8px; color:var(--text-dark)"><?= sanitize($listing['name']) ?></h1>
        
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:16px;">
          <span style="font-size:12px; font-weight:700; color:<?= $isOpen ? '#0076ff' : '#d32f2f' ?>"><?= $status ?></span>
          <span style="font-size:12px; color:var(--text-muted)">| <?= $hours ?></span>
        </div>

        <p style="font-size:14px; color:var(--text-muted); line-height:1.5; margin-bottom:16px;">
          <?= nl2br(sanitize($listing['description'] ?? 'No description provided.')) ?>
        </p>

        <div style="display:flex; align-items:center; gap:8px; margin-bottom:24px; color:var(--text-muted); font-size:14px;">
          <i class="fas fa-map-marker-alt" style="color:var(--maroon-main)"></i>
          <span><?= sanitize($listing['location']) ?> (<?= ucfirst($listing['zone']) ?> Campus)</span>
        </div>

        <?php if ($tags): ?>
          <div style="margin-bottom:24px">
            <h3 style="font-size:13px; font-weight:700; text-transform:uppercase; color:#888; margin-bottom:8px">Features & Tags</h3>
            <div style="display:flex; flex-wrap:wrap; gap:8px;">
              <?php foreach ($tags as $tag): ?>
                <span style="font-size:12px; padding:4px 12px; background:#f4f5f7; border:1px solid #e2e2e7; border-radius:12px; font-weight:600; color:var(--text-dark)">
                  <?= sanitize($tag) ?>
                </span>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <hr style="border:0; border-top:1px solid #e2e2e7; margin:24px 0" />

      </div>
    </div>

    <!-- REPORT MODAL -->
    <div id="modal-report-listing" class="modal-overlay report-modal-overlay">
      <div class="modal-content report-modal">
        <button class="modal-back-btn-round" onclick="closeModal('modal-report-listing')"><i class="fas fa-times"></i></button>
        <div class="report-modal-header">
          <h3>Report Listing</h3>
          <p>Help us keep iSkout accurate. What's wrong with <strong><?= sanitize($listing['name']) ?></strong>?</p>
        </div>
        <form action="actions/submit_report.php" method="POST" class="report-form">
          <input type="hidden" name="listing_id" value="<?= $id ?>" />
          <label class="form-label" for="report-reason">Select a reason</label>
          <select id="report-reason" name="reason" class="custom-form-select" required>
            <option value="" disabled selected>Select a reason...</option>
            <option value="Closed Permanently">Store is permanently closed</option>
            <option value="Wrong Location">Location is incorrect</option>
            <option value="Inaccurate Hours">Store hours are wrong</option>
            <option value="Spam/Fake">Fake listing / Spam</option>
            <option value="Other">Other</option>
          </select>
          <label class="form-label" for="report-details">Additional details</label>
          <textarea id="report-details" name="details" rows="4" class="custom-form-textarea" placeholder="Additional details (optional)"></textarea>
          <button type="submit" class="btn btn-maroon btn-full">Submit Report</button>
        </form>
      </div>
    </div>

  </div><!-- /.mobile-container -->

  <script src="assets/js/script.js"></script>
  <script>
    // Initialize favorite button state on page load
    document.addEventListener('DOMContentLoaded', function() {
      const favBtn = document.querySelector('.hero-fav-btn');
      if (favBtn) {
        const listingName = favBtn.dataset.listing;
        if (listingName) {
          // Load directly from localStorage to ensure it's available
          const savedFavorites = localStorage.getItem('userFavorites');
          let favorites = [];
          try {
            favorites = savedFavorites ? JSON.parse(savedFavorites) : [];
          } catch (e) {
            favorites = [];
          }
          
          if (favorites.includes(listingName)) {
            const icon = favBtn.querySelector('i');
            icon.classList.replace('far', 'fas');
            icon.style.color = '#ff6b6b';
          }
        }
      }
      // Ensure all favorite icons on page are synced
      if (typeof initializeFavoriteIcons === 'function') {
        initializeFavoriteIcons();
      }
    });
  </script>
</body>
</html>
