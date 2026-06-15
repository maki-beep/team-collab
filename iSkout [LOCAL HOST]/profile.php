<?php
// =============================================================
// iSkout — profile.php
// User profile page with saved/favorites listings
// =============================================================

require_once 'includes/auth_guard.php';
// Auth guard gives us $conn and ensures user is student

// Get user info
$webmail = sanitize($_SESSION['webmail']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Profile — iSkout</title>
  <link rel="icon" type="image/png" href="assets/images/iSkout_Logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <link rel="stylesheet" href="assets/css/styles.css"/>
</head>
<body>
  <div class="mobile-container">
    
    <!-- ═══════════════════════════ PROFILE SCREEN ═══════════════════════ -->
    <div id="screen-profile" class="screen active">
      <header class="dashboard-header-maroon">
        <button class="icon-btn-plain" onclick="window.location.href='dashboard.php'"><i class="fas fa-arrow-left"></i></button>
        <span class="screen-header-title-light">iSkout</span>
        <div style="width:24px"></div>
      </header>
      <div class="profile-screen-content">
        <div class="account-profile-container">
          <div class="account-avatar-block"><i class="fas fa-user-circle"></i></div>
          <div class="account-details-block">
            <h2 class="account-display-name">Iskolar</h2>
            <p class="account-student-email"><?= $webmail ?></p>
          </div>
        </div>
        <hr class="profile-separator-line" />
        <div class="profile-accordion-menu">
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

  </div><!-- /.mobile-container -->

  <script src="assets/js/script.js"></script>
</body>
</html>
