<?php
// =============================================================
// iSkout — dashboard.php
// Main listing discovery screen. Fetches from DB.
// =============================================================

require_once 'includes/auth_guard.php';
// Auth guard gives us $conn and ensures user is student

// Get categories for filters
$categories = ['food', 'study', 'print', 'rentals', 'supplies', 'repair'];

// We'll output the raw listings as a JSON object so JS can handle the live search
// exactly like the prototype did, but with real data.
$stmt = $conn->prepare(
    "SELECT l.id, l.name as title, l.location, c.category, l.zone, l.image_path as image, l.open_time, l.close_time, 
            GROUP_CONCAT(t.tag) as tags
     FROM listings l
     LEFT JOIN listing_categories c ON l.id = c.listing_id
     LEFT JOIN listing_tags t ON l.id = t.listing_id
     GROUP BY l.id, c.category"
);
$stmt->execute();
$res = $stmt->get_result();

$allListings = [];
while ($row = $res->fetch_assoc()) {
    $isOpen = is_open_now($row['open_time'], $row['close_time']);
    $status = $isOpen ? 'Open Now' : 'Closed';
    
    // Fallback image if none
    $image = $row['image'] ? APP_URL . '/' . $row['image'] : '';

    // Handle null tags safely
    $tagsArray = $row['tags'] ? explode(',', $row['tags']) : [];

    $allListings[] = [
        'id'       => $row['id'],
        'title'    => $row['title'],
        'location' => $row['location'],
        'category' => $row['category'],
        'zone'     => ucfirst($row['zone']),
        'image'    => $image,
        'status'   => $status,
        'hours'    => format_time($row['open_time']) . ' – ' . format_time($row['close_time']),
        'tags'     => $tagsArray,
        'modalId'  => 'modal-listing-' . $row['id']
    ];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard — iSkout</title>
  <link rel="icon" type="image/png" href="assets/images/iSkout_Logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <link rel="stylesheet" href="assets/css/styles.css"/>
</head>
<body>
  <div class="mobile-container">
    
    <!-- ═══════════════════════════ DASHBOARD MAIN ═══════════════════════ -->
    <div id="screen-dashboard" class="screen active">
      <div class="home-header-block">
        <div class="home-header-top-row">
          <span class="brand-name-light">iSkout</span>
          <div class="home-header-actions">
            <div class="avatar-circle-light" onclick="navigateTo('screen-profile')">
              <i class="fas fa-user-circle"></i>
            </div>
          </div>
        </div>
        <div class="search-container-light" onclick="navigateTo('screen-filters')">
          <i class="fas fa-search search-icon-muted"></i>
          <input type="text" placeholder='Try searching "Sizzling", "Piso pri...."' readonly />
        </div>
      </div>

      <div class="dashboard-content" style="padding-top:10px; padding-bottom:60px">
        <h3 class="section-title-bold">What do you need today?</h3>
        <div class="category-grid-three">
          <div class="category-item-card" onclick="openCategoryPage('Food Stores')">
            <div class="category-card-icon"><i class="fas fa-store-alt"></i></div>
            <span>Food</span>
          </div>
          <div class="category-item-card" onclick="openCategoryPage('Study & Workspaces')">
            <div class="category-card-icon"><i class="fas fa-book"></i></div>
            <span>Study</span>
          </div>
          <div class="category-item-card" onclick="openCategoryPage('Print')">
            <div class="category-card-icon"><i class="fas fa-print"></i></div>
            <span>Print</span>
          </div>
          <div class="category-item-card" onclick="openCategoryPage('Rentals')">
            <div class="category-card-icon"><i class="fas fa-hands-helping"></i></div>
            <span>Rentals</span>
          </div>
          <div class="category-item-card" onclick="openCategoryPage('Supplies')">
            <div class="category-card-icon"><i class="fas fa-shopping-basket"></i></div>
            <span>Supplies</span>
          </div>
          <div class="category-item-card" onclick="openCategoryPage('Repair & Tech')">
            <div class="category-card-icon"><i class="fas fa-tools"></i></div>
            <span>Repair</span>
          </div>
        </div>

        <button class="btn btn-maroon btn-full" style="margin-bottom:18px;" onclick="navigateTo('screen-create-post')">
          <i class="fas fa-plus" style="margin-right:8px;"></i> Add Listing
        </button>

        <h3 class="section-title-bold">Pop-Up Sinta</h3>
        <div class="live-scroll" id="popup-sinta-container">
          <!-- Populated by JS using REAL_LISTINGS -->
        </div>

        <h3 class="section-title-bold" style="margin-top:25px">Newest Listings</h3>
        <div class="places-list" id="newest-listings-container">
          <!-- Populated by JS using REAL_LISTINGS -->
        </div>
      </div>
      <div class="bottom-nav-bar">
        <button class="floating-add-btn" onclick="navigateTo('screen-create-post')">
          <i class="fas fa-plus"></i>
        </button>
      </div>
    </div>

    <!-- ═══════════════════════════ CATEGORY VIEW ═══════════════════════ -->
    <div id="screen-food-category" class="screen">
      <header class="category-custom-header">
        <button class="icon-btn-back" onclick="navigateTo('screen-dashboard')"><i class="fas fa-arrow-left"></i></button>
        <div class="search-container flex-grow" onclick="openFilterFromCategory()">
          <i class="fas fa-search search-icon"></i>
          <input type="text" placeholder='Search...' readonly />
        </div>
      </header>
      <div class="dashboard-content" style="padding-top:10px">
        <h2 class="category-display-heading" id="current-category-name">Category</h2>

        <h3 class="filter-group-label">Zone Matrix</h3>
        <div class="filter-primary-row design-pill-row">
          <button class="selection-tag icon-tag" onclick="openFilterFromCategory()"><i class="fas fa-sliders-h"></i></button>
          <button class="selection-tag active" onclick="filterZoneMatrix(this,'All')">All</button>
          <button class="selection-tag" onclick="filterZoneMatrix(this,'Outside')">Outside</button>
          <button class="selection-tag" onclick="filterZoneMatrix(this,'Inside')">Inside</button>
        </div>

        <div id="category-tag-filters" style="margin-bottom:15px; display:none">
          <h3 class="filter-group-label">Filter by Tag</h3>
          <div class="filter-primary-row design-pill-row" id="category-tag-pills"></div>
        </div>

        <div class="places-list" id="category-listings-container" style="margin-top:10px"></div>
        <div id="category-empty-state" class="empty-state-block" style="display:none">
          <i class="fas fa-store-slash empty-state-icon"></i>
          <p class="empty-state-title">No listings here yet</p>
        </div>
      </div>
    </div>

    <!-- ═══════════════════════════ FILTERS SCREEN ═══════════════════════ -->
    <div id="screen-filters" class="screen">
      <header class="dashboard-header no-border">
        <button class="icon-btn" onclick="backToPreviousScreen()"><i class="fas fa-arrow-left"></i></button>
        <div class="search-container flex-grow filter-search-box">
          <i class="fas fa-search search-icon"></i>
          <input type="text" id="live-search-input" placeholder='Search listings, tags, categories...' />
        </div>
      </header>

      <div class="filter-tabs-scroller" id="filter-category-tabs">
        <span class="filter-tab-item active" onclick="switchFilterCategory(this,'all')">All</span>
        <span class="filter-tab-item" onclick="switchFilterCategory(this,'food')"><i class="fas fa-store-alt"></i> Food</span>
        <span class="filter-tab-item" onclick="switchFilterCategory(this,'study')"><i class="fas fa-book"></i> Study</span>
        <span class="filter-tab-item" onclick="switchFilterCategory(this,'print')"><i class="fas fa-print"></i> Print</span>
        <span class="filter-tab-item" onclick="switchFilterCategory(this,'rentals')"><i class="fas fa-hands-helping"></i> Rentals</span>
        <span class="filter-tab-item" onclick="switchFilterCategory(this,'supplies')"><i class="fas fa-shopping-basket"></i> Supplies</span>
        <span class="filter-tab-item" onclick="switchFilterCategory(this,'repair')"><i class="fas fa-tools"></i> Repair</span>
      </div>

      <div class="filter-screen-content" id="filter-screen-content">
        <h3 class="filter-group-title">Zone Matrix</h3>
        <div class="filter-primary-row" style="margin-bottom:15px" id="zone-filter-row">
          <button class="selection-tag active" onclick="toggleZoneFilter(this,'All')">All</button>
          <button class="selection-tag" onclick="toggleZoneFilter(this,'Outside')">Outside</button>
          <button class="selection-tag" onclick="toggleZoneFilter(this,'Inside')">Inside</button>
        </div>
        <hr style="border:0;border-top:1px solid var(--border-gray);margin:15px 0" />
        <div id="dynamic-tags-section" style="display:none">
          <h3 class="filter-group-title">Tags</h3>
          <div class="filter-primary-row" id="placeholder-tags-container"></div>
        </div>
      </div>

      <div id="search-results-panel" style="display:none; padding:0 15px 20px 15px">
        <h3 class="filter-group-title" style="margin-bottom:12px">Results</h3>
        <div id="search-results-list" class="places-list" style="margin-top:0"></div>
        <div id="search-empty-state" class="empty-state-block" style="display:none">
          <i class="fas fa-search empty-state-icon"></i>
          <p class="empty-state-title">No results found</p>
          <p class="empty-state-sub">Try a different keyword or browse by category.</p>
        </div>
      </div>
    </div>

    <!-- ═══════════════════════════ CREATE LISTING SCREEN ═══════════════════════ -->
    <div id="screen-create-post" class="screen">
      <header class="dashboard-header-maroon">
        <button class="icon-btn-plain" onclick="navigateTo('screen-dashboard')"><i class="fas fa-arrow-left"></i></button>
        <span class="screen-header-title-light">Create Listing</span>
        <button class="btn-post-submit" onclick="handlePostSubmit()">Post</button>
      </header>
      <div class="post-creation-body">
        <div class="photo-uploader-container">
          <span class="section-subtitle-label">Add Photos <span class="muted-label-text">(up to 5, main photo first)</span></span>
            <input type="file" id="post-photo-upload" name="image" accept="image/*" multiple style="display:none" />
          <div class="photo-upload-grid" id="photo-upload-grid">
            <div class="photo-box main-photo" onclick="handlePhotoBoxClick(0)"><i class="fas fa-camera"></i></div>
            <div class="photo-box" onclick="handlePhotoBoxClick(1)"><i class="fas fa-plus"></i></div>
            <div class="photo-box" onclick="handlePhotoBoxClick(2)"><i class="fas fa-plus"></i></div>
            <div class="photo-box" onclick="handlePhotoBoxClick(3)"><i class="fas fa-plus"></i></div>
            <div class="photo-box" onclick="handlePhotoBoxClick(4)"><i class="fas fa-plus"></i></div>
          </div>
        </div>

        <div class="form-input-block">
          <label class="field-label">Product/Service Name</label>
          <input type="text" id="post-name" class="custom-form-input" placeholder="What are you offering?" />
        </div>
        <div class="form-input-block">
          <label class="field-label">Price</label>
          <input type="text" id="post-price" class="custom-form-input" placeholder="e.g., 30.00 or 50-100" />
        </div>
        <div class="form-input-block">
          <label class="field-label">Category</label>
          <select id="post-category" class="custom-form-select">
            <option value="food">Food</option>
            <option value="study">Study</option>
            <option value="print">Print</option>
            <option value="rentals">Rentals</option>
            <option value="supplies">Supplies</option>
            <option value="repair">Repair</option>
          </select>
        </div>
        <div class="form-input-block">
          <label class="field-label">Description</label>
          <textarea id="post-description" class="custom-form-textarea" placeholder="Provide extra details..."></textarea>
        </div>
        <div class="form-input-block">
          <label class="field-label">Location</label>
          <input type="text" id="post-location" class="custom-form-input" placeholder="e.g., Lagoon Area" />
        </div>
        <div class="form-input-block">
          <label class="field-label">Campus Zone</label>
          <select id="post-zone" class="custom-form-select">
            <option value="Inside">Inside Campus</option>
            <option value="Outside">Outside Campus</option>
          </select>
        </div>
        <div class="form-input-row">
          <div class="form-input-block split-half">
            <label class="field-label">Stock Status</label>
            <select id="post-stock" class="custom-form-select">
              <option>Available</option>
              <option>High Stock</option>
              <option>Low Stock</option>
              <option>Last Unit</option>
              <option>Pre-order</option>
              <option>Out of Stock</option>
            </select>
          </div>
          <div class="form-input-block split-half">
            <label class="field-label">Active Duration</label>
            <select class="custom-form-select" id="form-active-duration" onchange="handleDurationChange(this)">
              <option>Until supplies last</option>
              <option>Next 1 hour</option>
              <option>Next 2 hours</option>
              <option>All day</option>
              <option value="Others">Others...</option>
            </select>
            <input type="hidden" id="custom-time-range-input" />
            <div id="custom-interval-display-block" style="display:none; font-size:11px; color:var(--maroon-main); margin-top:6px; font-weight:700;"></div>
          </div>
        </div>
        <div class="form-input-block" style="margin-top:5px">
          <label class="field-label">Contact Information</label>
          <input type="text" id="post-contact" class="custom-form-input" placeholder="Paste FB/Messenger link here..." />
        </div>
        <div class="form-input-block" style="margin-top:10px">
          <label class="field-label" style="margin-bottom:6px; display:block">Payment Methods</label>
          <div class="payment-checkbox-group">
            <label class="checkbox-capsule"><input type="checkbox" name="pay_method" value="Cash" class="pay-method-checkbox" /><span>Cash</span></label>
            <label class="checkbox-capsule"><input type="checkbox" name="pay_method" value="GCash" class="pay-method-checkbox" /><span>GCash</span></label>
            <label class="checkbox-capsule"><input type="checkbox" name="pay_method" value="Credit/Debit QR Code" class="pay-method-checkbox" /><span>Credit/Debit QR Code</span></label>
            <label class="checkbox-capsule select-all-capsule"><input type="checkbox" id="pay-method-all" class="pay-method-all-toggle" /><span>All Payment Methods Accepted</span></label>
          </div>
        </div>
        <div id="post-success-toast" class="post-success-toast" style="display:none"><i class="fas fa-check-circle"></i> Your listing has been posted!</div>
      </div>
    </div>

    <!-- ═══════════════════════════ PROFILE SCREEN ═══════════════════════ -->
    <div id="screen-profile" class="screen">
      <header class="dashboard-header-maroon">
        <button class="icon-btn-plain" onclick="navigateTo('screen-dashboard')"><i class="fas fa-arrow-left"></i></button>
        <span class="screen-header-title-light">iSkout</span>
        <div style="width:24px"></div>
      </header>
      <div class="profile-screen-content">
        <div class="account-profile-container">
          <div class="account-avatar-block"><i class="fas fa-user-circle"></i></div>
          <div class="account-details-block">
            <h2 class="account-display-name">Iskolar</h2>
            <p class="account-student-email"><?= sanitize($_SESSION['webmail']) ?></p>
          </div>
        </div>
        <hr class="profile-separator-line" />
        <div class="profile-accordion-menu">
          <div class="accordion-item open">
            <div class="accordion-header" onclick="toggleAccordion(this)">
              <span>Business Catalog Connected</span><i class="fas fa-chevron-down"></i>
            </div>
            <div class="accordion-content" id="business-catalog-content">
              <div class="favorites-empty-state">No business catalog entries managed yet.</div>
            </div>
          </div>
          <div class="accordion-item open">
            <div class="accordion-header" onclick="toggleAccordion(this)">
              <span>Favorites &amp; Saved</span><i class="fas fa-chevron-down"></i>
            </div>
            <div class="accordion-content" id="favorites-accordion-content">
              <div class="favorites-empty-state">You haven't saved any listings yet.</div>
            </div>
          </div>
        </div>
        <div class="logout-container-block">
          <button class="btn-logout-action" onclick="window.location.href='actions/logout.php'">
            <span>Logout</span><i class="fas fa-external-link-alt"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- We load all dynamic modals here via a JS fetch in script.js (or pre-rendered here) -->
    <!-- For speed and simplicity since there are only a few listings, we'll pre-render them. -->
    <div id="modals-container"></div>

  </div><!-- /.mobile-container -->

  <!-- Inject DB data into JS -->
  <script>
    const REAL_LISTINGS = <?= json_encode($allListings) ?>;
  </script>
  <script src="assets/js/script.js"></script>
</body>
</html>
