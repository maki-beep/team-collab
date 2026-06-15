<?php
// =============================================================
// iSkout — index.php
// Splash page + Login + Register hub
// No link to admin portal (per PRD requirement).
// =============================================================

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// If already logged in as student, go to dashboard
if (!empty($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'student') {
  redirect('dashboard.php');
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>iSkout</title>
  <meta name="description" content="iSkout helps PUP Sta. Mesa students discover nearby food stalls, printing shops, study spots, and campus merchants.">
  <link rel="icon" type="image/png" href="assets/images/iSkout_Logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/styles.css" />
  <style>
    .flash-alert {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 12px;
      font-weight: 600;
      border-radius: 8px;
      padding: 8px 12px;
      margin-bottom: 10px;
    }

    .flash-success {
      background: #e8f5e9;
      border: 1px solid #a5d6a7;
      color: #2e7d32;
    }

    .flash-error {
      background: #fff0f0;
      border: 1px solid #f5caca;
      color: #d32f2f;
    }
  </style>
</head>

<body>
  <div class="mobile-container">

    <!-- ═══════════════════════════ SPLASH ═══════════════════════ -->
    <div id="screen-splash" class="screen active">
      <div class="splash-content">
        <div class="logo-container">
          <img src="assets/images/iSkout_Logo.png" alt="iSkout Logo" style="width:180px;height:auto;object-fit:contain" />
        </div>
        <h1 class="welcome-title">Welcome to<br /><span class="brand-text">iSkout!</span></h1>
        <p class="welcome-subtitle">Finding what you need around the Sintang Paaralan just got easier.</p>
        <div class="welcome-buttons">
          <button class="btn btn-maroon" onclick="navigateTo('screen-login')">Log in</button>
          <button class="btn btn-gray" onclick="navigateTo('screen-register')">Sign up</button>
        </div>
        <!-- Admin login link intentionally removed per PRD §7.1 -->
      </div>
    </div>

    <!-- ═══════════════════════════ LOGIN ════════════════════════ -->
    <div id="screen-login" class="screen">
      <div class="form-container">
        <h2 class="form-title">Welcome<br />back, Iskolar!</h2>
        <p class="form-subtitle">Let's continue the journey.</p>

        <?php if ($flash && in_array($flash['type'], ['success', 'error'])): ?>
          <div class="flash-alert flash-<?= $flash['type'] ?>">
            <i class="fas <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
            <?= sanitize($flash['msg']) ?>
          </div>
        <?php endif; ?>

        <form id="login-form" method="POST" action="actions/login.php" novalidate>
          <div class="input-capsule">
            <span class="capsule-icon"><i class="fas fa-id-card-alt"></i></span>
            <input type="email" name="webmail" id="login-username" placeholder="PUP Webmail" autocomplete="email" required />
          </div>
          <div class="input-capsule">
            <span class="capsule-icon"><i class="fas fa-lock"></i></span>
            <input type="password" name="password" id="login-password" placeholder="Password" autocomplete="current-password" required />
            <span class="password-toggle"><i class="far fa-eye-slash"></i></span>
          </div>
          <p id="login-inline-error" class="inline-error-msg" style="display:none">
            <i class="fas fa-exclamation-circle"></i> <span></span>
          </p>
          <div class="form-meta-row">
            <div class="remember-me">
              <input type="checkbox" id="remember-login" name="remember_me" value="1" />
              <label for="remember-login">Remember me</label>
            </div>
            <span class="forgot-password-link" onclick="navigateTo('screen-forgot')">Forgot Password?</span>
          </div>
          <button type="submit" class="btn btn-maroon btn-full" id="login-submit-btn">Log in</button>
        </form>
        <p class="switch-form">Don't have an account? <span onclick="navigateTo('screen-register')">Sign Up</span></p>
      </div>
    </div>

    <!-- ═══════════════════════════ REGISTER ═════════════════════ -->
    <div id="screen-register" class="screen">
      <div class="form-container">
        <h2 class="form-title">Create an<br />Account.</h2>
        <p class="form-subtitle">Sign up to get started.</p>

        <form id="register-form" method="POST" action="actions/register.php" novalidate>
          <div class="input-capsule">
            <span class="capsule-icon"><i class="fas fa-id-card-alt"></i></span>
            <input type="email" name="webmail" id="reg-webmail" placeholder="PUP Webmail (e.g. @pup.edu.ph)" autocomplete="email" required />
          </div>
          <div class="input-capsule">
            <span class="capsule-icon"><i class="fas fa-lock"></i></span>
            <input type="password" name="password" id="reg-password" placeholder="Password (min 8 chars)" autocomplete="new-password" required />
            <span class="password-toggle"><i class="far fa-eye-slash"></i></span>
          </div>
          <div class="input-capsule">
            <span class="capsule-icon"><i class="fas fa-lock"></i></span>
            <input type="password" name="confirm_password" id="reg-confirm-password" placeholder="Confirm Password" autocomplete="new-password" required />
            <span class="password-toggle"><i class="far fa-eye-slash"></i></span>
          </div>
          <p id="reg-inline-error" class="inline-error-msg" style="display:none">
            <i class="fas fa-exclamation-circle"></i> <span></span>
          </p>
          <div class="terms-agreement">
            <input type="checkbox" id="register-terms" />
            <label for="register-terms">By clicking Sign Up, you agree to our
              <span class="legal-link" onclick="openModal('modal-tos')">[Terms of Service]</span>
              and
              <span class="legal-link" onclick="openModal('modal-privacy')">[Privacy Policy]</span>
            </label>
          </div>
          <button type="submit" class="btn btn-maroon btn-full" id="signup-submit-action" disabled>Sign Up</button>
        </form>
        <p class="switch-form">Already have an account? <span onclick="navigateTo('screen-login')">Log in</span></p>
      </div>
    </div>

    <!-- ═══════════════════════════ FORGOT PASSWORD ═══════════════ -->
    <div id="screen-forgot" class="screen">
      <div class="form-container">
        <h2 class="form-title">Reset<br />Password.</h2>
        <p class="form-subtitle">We'll send a code to your PUP Webmail.</p>
        <p id="forgot-inline-error" class="inline-error-msg" style="display:none">
          <i class="fas fa-exclamation-circle"></i> <span></span>
        </p>
        <form id="forgot-form" method="POST" action="forgot_password.php" novalidate>
          <input type="hidden" name="step" value="1">
          <div class="input-capsule">
            <span class="capsule-icon"><i class="fas fa-id-card-alt"></i></span>
            <input type="email" name="webmail" id="forgot-webmail" placeholder="PUP Webmail" autocomplete="email" required />
          </div>
          <button type="submit" class="btn btn-maroon btn-full">Send Code</button>
        </form>
        <p class="switch-form" style="margin-top:16px">Back to <span onclick="navigateTo('screen-login')">Log in</span></p>
      </div>
    </div>

    <!-- ═════════════ TERMS OF SERVICE MODAL ══════════════════ -->
    <div id="modal-tos" class="modal-overlay legal-modal">
      <div class="modal-content">
        <header class="legal-modal-header">
          <h3>Terms of Service</h3>
          <button class="legal-close-btn" onclick="closeModal('modal-tos')"><i class="fas fa-times"></i></button>
        </header>
        <div class="legal-modal-body">
          <p><strong>Welcome to iSkout!</strong></p>
          <p>By using our application, you agree to comply with and be bound by the university guidelines and code of conduct for digital platforms.</p>
          <p><b>1. User Accounts:</b> You must provide valid details during registration. Fake or unverified accounts will be suspended.</p>
          <p><b>2. Listing &amp; Merchant Integrity:</b> All listings must provide accurate pricing, honest location markers, and true stock indicators.</p>
          <p><b>3. Community Moderation:</b> Users agree to refrain from posting spam or behavior that violates campus digital platform ethics.</p>
          <p><b>4. Transaction Liability:</b> iSkout operates strictly as an information directory. Any transactions occur solely between the student buyer and the respective merchant.</p>
        </div>
      </div>
    </div>

    <!-- ═════════════ PRIVACY POLICY MODAL ════════════════════ -->
    <div id="modal-privacy" class="modal-overlay legal-modal">
      <div class="modal-content">
        <header class="legal-modal-header">
          <h3>Privacy Policy</h3>
          <button class="legal-close-btn" onclick="closeModal('modal-privacy')"><i class="fas fa-times"></i></button>
        </header>
        <div class="legal-modal-body">
          <p><strong>Data Privacy Acknowledgment</strong></p>
          <p>We process account configurations safely, strictly optimized for navigational assistance inside and outside the layout matrix.</p>
          <p><b>1. Information We Collect:</b> iSkout collects your name, verified student webmail, and voluntary contact links.</p>
          <p><b>2. Use of Data:</b> Used exclusively to facilitate directory queries and location mapping.</p>
          <p><b>3. Visibility Settings:</b> Your seller name and public comments are visible to all logged-in users.</p>
          <p><b>4. User Data Rights:</b> You have the right to inspect, modify, or request total erasure of your platform footprint at any time.</p>
        </div>
      </div>
    </div>

  </div><!-- /.mobile-container -->

  <script src="assets/js/script.js"></script>
  <script>
    // Handle flash error from URL param (for login failures redirected back)
    (function() {
      const params = new URLSearchParams(window.location.search);
      const err = params.get('error');
      const screen = params.get('screen');
      if (err) {
        // Show appropriate screen
        const target = (screen === 'register') ? 'screen-register' : 'screen-login';
        navigateTo(target);
        const errEl = document.getElementById(target === 'screen-register' ? 'reg-inline-error' : 'login-inline-error');
        if (errEl) {
          errEl.style.display = 'flex';
          errEl.querySelector('span').textContent = decodeURIComponent(err);
        }
      }
      if (params.get('registered') === '1') {
        // Came from registration — stay on login screen showing success
        navigateTo('screen-login');
      }
    })();
  </script>
</body>

</html>