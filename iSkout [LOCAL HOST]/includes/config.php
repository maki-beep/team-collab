<?php
// =============================================================
// iSkout — includes/config.php
// Store DB credentials, SMTP settings, and Admin constants here.
// DO NOT commit this file with real credentials to version control.
// Add includes/config.php to .gitignore and share config.sample.php.
// =============================================================

// ── Database ─────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'iskout_db');
define('DB_USER', 'root');
define('DB_PASS', '');          // XAMPP default: empty string
define('DB_CHARSET', 'utf8mb4');

// ── Application ───────────────────────────────────────────────
// Set this to the base URL of your installation.
// XAMPP example:  http://localhost/ISkout
// InfinityFree:   https://yoursubdomain.infinityfreeapp.com
define('APP_URL', 'http://localhost/ISkout');

// ── PUP Email Domain ─────────────────────────────────────────
define('PUP_EMAIL_DOMAIN', '@iskolar.pup.edu.ph');

// Optional: comma-separated list of allowed PUP email domains.
// Use wildcards if needed, for example '@*.pup.edu.ph'.
// Example values:
//   '@iskolar.pup.edu.ph,@pup.edu.ph,@students.pup.edu.ph'
//   '@*.pup.edu.ph'
// If empty, the single `PUP_EMAIL_DOMAIN` constant is used for validation.
define('PUP_EMAIL_DOMAINS', '@*.pup.edu.ph');

// ── OTP Settings ─────────────────────────────────────────────
define('OTP_EXPIRY_MINUTES', 10);

// ── SMTP (PHPMailer) ─────────────────────────────────────────
// Recommended: Brevo (formerly Sendinblue) — free 300 emails/day
// Port 587 works on both XAMPP localhost and InfinityFree.
// Alternative: Gmail SMTP with an App Password.
define('SMTP_HOST',       'smtp.gmail.com');  // Gmail SMTP host
define('SMTP_PORT',       465);  // use SSL/TLS over port 465 for Gmail
define('SMTP_USER',       'noreply.iskout@gmail.com');  // your Gmail address
define('SMTP_PASS',       'kpufzkaehrrlhkkj');    // App Password from Google
define('SMTP_FROM_EMAIL', 'noreply.iskout@gmail.com');
define('SMTP_FROM_NAME',  'iSkout');

// ── Admin Credentials ─────────────────────────────────────────
// The hash below is for the password: admin@iskout2025
// To change the password, generate a new hash with:
//   echo password_hash('your-new-password', PASSWORD_DEFAULT);
// Then paste the result below.
define('ADMIN_USERNAME',      'admin@iskout.pup');
define('ADMIN_PASSWORD_HASH', '$2y$10$RIVR7O8HMkrKeckZA0YZme0hUJafJvRAI1pfSHKGt1cToHAxRSLEO');
// ↑ hash for: admin@iskout2025
