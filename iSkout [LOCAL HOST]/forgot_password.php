<?php
// =============================================================
// iSkout — forgot_password.php
// Handles forgot password flow: request OTP -> verify OTP -> new password
// =============================================================

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';
require_once 'includes/mailer.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$step  = (int)($_POST['step'] ?? 0);
$flash = get_flash();

// Step 1: Send OTP for Password Reset
if ($step === 1 && !empty($_POST['webmail'])) {
    $webmail = trim($_POST['webmail']);
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE webmail = ?");
    $stmt->bind_param('s', $webmail);
    $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()) {
        $otp    = generate_otp();
        
        $ins = $conn->prepare("INSERT INTO otp_tokens (webmail, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL " . OTP_EXPIRY_MINUTES . " MINUTE))");
        $ins->bind_param('ss', $webmail, $otp);
        $ins->execute();
        
        sendOTP($webmail, $otp, 'iSkout Password Reset Code');
        $_SESSION['reset_webmail'] = $webmail;
        set_flash('success', 'Reset code sent to your email.');
        $step = 2; // Move to OTP entry
    } else {
        set_flash('error', 'Email not found.');
        redirect('index.php?screen=forgot');
    }
}

// Step 2: Verify OTP
if ($step === 2 && !empty($_POST['code']) && !empty($_SESSION['reset_webmail'])) {
    $webmail = $_SESSION['reset_webmail'];
    $code    = trim($_POST['code']);
    
    $stmt = $conn->prepare("SELECT id FROM otp_tokens WHERE webmail = ? AND token = ? AND used = 0 AND expires_at > NOW() ORDER BY id DESC LIMIT 1");
    $stmt->bind_param('ss', $webmail, $code);
    $stmt->execute();
    if ($row = $stmt->get_result()->fetch_assoc()) {
        // Valid OTP
        $upd = $conn->prepare("UPDATE otp_tokens SET used = 1 WHERE id = ?");
        $upd->bind_param('i', $row['id']);
        $upd->execute();
        
        $_SESSION['reset_verified'] = true;
        set_flash('success', 'Code verified. Set a new password.');
        $step = 3; // Move to new password entry
    } else {
        set_flash('error', 'Invalid or expired code.');
        $step = 2;
    }
}

// Step 3: Save New Password
if ($step === 3 && !empty($_POST['new_password']) && !empty($_SESSION['reset_verified'])) {
    $webmail = $_SESSION['reset_webmail'];
    $pass1   = $_POST['new_password'];
    $pass2   = $_POST['confirm_password'] ?? '';
    
    if ($pass1 !== $pass2) {
        set_flash('error', 'Passwords do not match.');
        $step = 3;
    } elseif (strlen($pass1) < 8) {
        set_flash('error', 'Password must be at least 8 characters.');
        $step = 3;
    } else {
        $hash = password_hash($pass1, PASSWORD_DEFAULT);
        $upd  = $conn->prepare("UPDATE users SET password_hash = ? WHERE webmail = ?");
        $upd->bind_param('ss', $hash, $webmail);
        $upd->execute();
        
        unset($_SESSION['reset_webmail'], $_SESSION['reset_verified']);
        set_flash('success', 'Password updated! You can now log in.');
        redirect('index.php');
    }
}

// If navigated here directly without POST, default to showing step 2 if we have a pending reset, else bounce.
if ($step === 0) {
    if (!empty($_SESSION['reset_webmail']) && empty($_SESSION['reset_verified'])) {
        $step = 2;
    } elseif (!empty($_SESSION['reset_verified'])) {
        $step = 3;
    } else {
        redirect('index.php?screen=forgot');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Forgot Password — iSkout</title>
  <link rel="icon" type="image/png" href="assets/images/iSkout_Logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <link rel="stylesheet" href="assets/css/styles.css"/>
  <style>
    .flash-alert { display:flex; align-items:center; gap:8px; font-size:12px; font-weight:600; border-radius:8px; padding:8px 12px; margin-bottom:10px; }
    .flash-success { background:#e8f5e9; border:1px solid #a5d6a7; color:#2e7d32; }
    .flash-error   { background:#fff0f0; border:1px solid #f5caca; color:#d32f2f; }
  </style>
</head>
<body>
  <div class="mobile-container">
    <div class="screen active">
      <div class="form-container">
        
        <?php if ($step === 2): ?>
        <h2 class="form-title">Enter<br/>Reset Code.</h2>
        <p class="form-subtitle">Code sent to <?= sanitize($_SESSION['reset_webmail']) ?></p>
        
        <?php if ($flash): ?>
          <div class="flash-alert flash-<?= $flash['type'] ?>">
            <i class="fas <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
            <?= sanitize($flash['msg']) ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="forgot_password.php">
          <input type="hidden" name="step" value="2">
          <div class="input-capsule" style="margin-bottom:20px;">
            <span class="capsule-icon"><i class="fas fa-key"></i></span>
            <input type="text" name="code" placeholder="4-Digit Code" required autocomplete="off" />
          </div>
          <button type="submit" class="btn btn-maroon btn-full">Verify Code</button>
        </form>

        <?php elseif ($step === 3): ?>
        <h2 class="form-title">New<br/>Password.</h2>
        <p class="form-subtitle">Create a new secure password.</p>

        <?php if ($flash): ?>
          <div class="flash-alert flash-<?= $flash['type'] ?>">
            <i class="fas <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
            <?= sanitize($flash['msg']) ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="forgot_password.php">
          <input type="hidden" name="step" value="3">
          <div class="input-capsule">
            <span class="capsule-icon"><i class="fas fa-lock"></i></span>
            <input type="password" name="new_password" placeholder="New Password (min 8 chars)" required />
            <span class="password-toggle"><i class="far fa-eye-slash"></i></span>
          </div>
          <div class="input-capsule" style="margin-bottom:20px;">
            <span class="capsule-icon"><i class="fas fa-lock"></i></span>
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required />
            <span class="password-toggle"><i class="far fa-eye-slash"></i></span>
          </div>
          <button type="submit" class="btn btn-maroon btn-full">Reset Password</button>
        </form>
        <?php endif; ?>

        <p class="switch-form" style="margin-top:24px">
          Back to <a href="index.php" style="color:var(--maroon-main);font-weight:700;text-decoration:none">Log in</a>
        </p>

      </div>
    </div>
  </div>
  <script src="assets/js/script.js"></script>
</body>
</html>
