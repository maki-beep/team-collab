<?php
// =============================================================
// iSkout Admin — index.php
// Login page for Admin Portal
// Hardcoded credentials checked against config.php
// =============================================================

require_once '../includes/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!empty($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'admin') {
    header('Location: ' . APP_URL . '/admin/landing.php');
    exit;
}

$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>iSkout Admin Portal</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

  <div class="admin-login-wrapper active">
    <main class="admin-login-card">
      <div style="margin-bottom:24px">
        <img src="../assets/images/PUP_Logo.png" alt="PUP Logo" class="pup-logo" />
      </div>
      <h1 class="page-title" style="margin-bottom:8px;">Admin Sign In</h1>
      <p class="subtitle" style="margin-bottom:24px;">Enter your administrator credentials to access the portal.</p>

      <?php if ($error): ?>
        <div class="error-msg"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="../actions/admin/admin_login.php">
        <div class="input-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" required autocomplete="username" />
        </div>
        <div class="input-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required autocomplete="current-password" />
        </div>
        <button type="submit" class="btn btn-maroon" style="width:100%;">Log In</button>
      </form>
    </main>
  </div>

</body>
</html>
