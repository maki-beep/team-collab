<?php
// =============================================================
// iSkout — verify.php
// OTP Email Verification Page
// Requires $_SESSION['pending_webmail'] to be set by register.php
// =============================================================

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// If already verified student, redirect to dashboard
if (!empty($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'student') {
    redirect('dashboard.php');
}

// Must have a pending webmail to show this page
if (empty($_SESSION['pending_webmail'])) {
    redirect('index.php');
}

$pending_email = sanitize($_SESSION['pending_webmail']);
$flash         = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Email Verification — iSkout</title>
  <meta name="description" content="Verify your PUP Webmail to complete iSkout registration.">
  <link rel="icon" type="image/png" href="assets/images/iSkout_Logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <link rel="stylesheet" href="assets/css/styles.css"/>
  <style>
    .flash-alert { display:flex; align-items:center; gap:8px; font-size:12px; font-weight:600; border-radius:8px; padding:8px 12px; margin-bottom:10px; }
    .flash-success { background:#e8f5e9; border:1px solid #a5d6a7; color:#2e7d32; }
    .flash-error   { background:#fff0f0; border:1px solid #f5caca; color:#d32f2f; }
    .verify-email-hint { font-size:12px; color:#888; font-weight:500; text-align:center; margin-bottom:16px; line-height:1.4; }
    .verify-email-hint strong { color:#730000; }
  </style>
</head>
<body>
  <div class="mobile-container">
    <div id="screen-verify" class="screen active">
      <div class="form-container text-center">
        <h2 class="form-title">Email<br/>Verification</h2>
        <p class="form-subtitle">Enter the 4-digit code sent to your email.</p>

        <p class="verify-email-hint">Code sent to<br><strong><?= $pending_email ?></strong></p>

        <?php if ($flash): ?>
          <div class="flash-alert flash-<?= $flash['type'] ?>">
            <i class="fas <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
            <?= sanitize($flash['msg']) ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="actions/verify_otp.php" id="otp-form" novalidate>
          <div class="verification-code-grid">
            <input type="text" inputmode="numeric" maxlength="1" class="code-box" pattern="\d" name="d1" id="d1" autocomplete="one-time-code" />
            <input type="text" inputmode="numeric" maxlength="1" class="code-box" pattern="\d" name="d2" id="d2" />
            <input type="text" inputmode="numeric" maxlength="1" class="code-box" pattern="\d" name="d3" id="d3" />
            <input type="text" inputmode="numeric" maxlength="1" class="code-box" pattern="\d" name="d4" id="d4" />
          </div>
          <p id="verify-error" class="inline-error-msg" style="display:none">
            <i class="fas fa-exclamation-circle"></i> Incorrect code. Please try again.
          </p>
          <p class="resend-text">
            Didn't receive code?<br/>
            <span id="resend-link" onclick="resendCode()">Resend Code</span>
          </p>
          <button type="submit" class="btn btn-maroon btn-full">Verify</button>
        </form>

        <p class="switch-form" style="margin-top:16px">
          Back to <a href="index.php" style="color:var(--maroon-main);font-weight:700;text-decoration:none">Log in</a>
        </p>
      </div>
    </div>
  </div>

  <script src="assets/js/script.js"></script>
  <script>
    // OTP box auto-advance
    const boxes = document.querySelectorAll('.code-box');
    boxes.forEach((box, i) => {
      box.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '');
        if (this.value && i < boxes.length - 1) boxes[i+1].focus();
      });
      box.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && !this.value && i > 0) boxes[i-1].focus();
      });
      box.addEventListener('paste', function(e) {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'').slice(0,4);
        pasted.split('').forEach((c, idx) => { if(boxes[idx]) boxes[idx].value = c; });
        const last = Math.min(pasted.length, boxes.length) - 1;
        if (boxes[last]) boxes[last].focus();
      });
    });

    // Resend via fetch
    function resendCode() {
      const link = document.getElementById('resend-link');
      link.textContent = 'Sending...';
      fetch('actions/resend_otp.php', { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'} })
        .then(r => r.json())
        .then(data => {
          link.textContent = data.success ? 'Code resent! Check your email.' : 'Failed. Try again.';
          setTimeout(() => link.textContent = 'Resend Code', 4000);
        })
        .catch(() => { link.textContent = 'Error. Try again.'; });
    }
  </script>
</body>
</html>
