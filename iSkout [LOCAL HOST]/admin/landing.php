<?php
// =============================================================
// iSkout Admin — landing.php
// Separate admin landing page shown after successful login.
// =============================================================

require_once '../includes/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id']) || empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . APP_URL . '/admin/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>iSkout Admin Landing</title>
  <link rel="icon" type="image/png" href="../assets/images/iSkout_Logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
  <div class="admin-landing-wrapper active">
    <main class="admin-landing-card">
      <div class="logo-container">
        <img src="../assets/images/PUP_Logo.png" alt="Polytechnic University of the Philippines" class="pup-logo" />
      </div>

      <header class="welcome-header">
        <h1>Welcome to <span class="brand-text">iSkout Admin!</span></h1>
        <p class="subtitle">Monitor real-time campus activity, manage master merchant datasets, and track community engagement insights.</p>
      </header>

      <div class="action-container">
        <button class="btn btn-maroon" onclick="window.location.href='dashboard.php'">Check Analytics Now! <i class="fa-solid fa-chart-line" style="margin-left: 8px; font-size: 14px;"></i></button>
      </div>
    </main>
  </div>
</body>
</html>
