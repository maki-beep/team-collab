<?php
// =============================================================
// iSkout — actions/register.php
// Handles user registration, creates OTP, sends email, redirects.
// =============================================================

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';
require_once '../includes/mailer.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php?screen=register');
}

$webmail = trim($_POST['webmail'] ?? '');
$pass1   = $_POST['password'] ?? '';
$pass2   = $_POST['confirm_password'] ?? '';

// Basic validation
if (!$webmail || !$pass1 || !$pass2) {
    redirect('index.php?screen=register&error=' . urlencode('All fields are required.'));
}

// PUP domain validation
if (!is_valid_pup_email($webmail)) {
    redirect('index.php?screen=register&error=' . urlencode('Only valid PUP student email addresses are allowed.'));
}

if ($pass1 !== $pass2) {
    redirect('index.php?screen=register&error=' . urlencode('Passwords do not match.'));
}

if (strlen($pass1) < 8) {
    redirect('index.php?screen=register&error=' . urlencode('Password must be at least 8 characters.'));
}

// Check if user already exists
$stmt = $conn->prepare("SELECT id, is_verified FROM users WHERE webmail = ?");
$stmt->bind_param('s', $webmail);
$stmt->execute();
$res = $stmt->get_result();

if ($user = $res->fetch_assoc()) {
    if ($user['is_verified']) {
        redirect('index.php?screen=register&error=' . urlencode('This email is already registered and verified. Please log in.'));
    }
    // If not verified, we can just update the password and resend OTP
    $userId = $user['id'];
    $hash   = password_hash($pass1, PASSWORD_DEFAULT);
    $upd    = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $upd->bind_param('si', $hash, $userId);
    $upd->execute();
    $upd->close();
} else {
    // Insert new user
    $hash = password_hash($pass1, PASSWORD_DEFAULT);
    $ins  = $conn->prepare("INSERT INTO users (webmail, password_hash, is_verified) VALUES (?, ?, 0)");
    $ins->bind_param('ss', $webmail, $hash);
    if (!$ins->execute()) {
        redirect('index.php?screen=register&error=' . urlencode('Database error during registration.'));
    }
    $ins->close();
}
$stmt->close();

// Generate and store OTP
$otp = generate_otp();
$otpStmt = $conn->prepare("INSERT INTO otp_tokens (webmail, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL " . OTP_EXPIRY_MINUTES . " MINUTE))");
$otpStmt->bind_param('ss', $webmail, $otp);
$otpStmt->execute();
$otpStmt->close();

// Send OTP Email
if (sendOTP($webmail, $otp)) {
    $_SESSION['pending_webmail'] = $webmail;
    set_flash('success', 'Verification code sent to your email.');
    redirect('verify.php');
} else {
    redirect('index.php?screen=register&error=' . urlencode('Failed to send verification email. Please contact support.'));
}
