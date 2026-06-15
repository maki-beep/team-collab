<?php
// =============================================================
// iSkout — actions/verify_otp.php
// Validates 4-digit OTP, marks user verified, establishes session.
// =============================================================

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['pending_webmail'])) {
    redirect('index.php');
}

$webmail = $_SESSION['pending_webmail'];
$code    = ($_POST['d1'] ?? '') . ($_POST['d2'] ?? '') . ($_POST['d3'] ?? '') . ($_POST['d4'] ?? '');

if (strlen($code) !== 4) {
    set_flash('error', 'Please enter all 4 digits.');
    redirect('verify.php');
}

// Find valid OTP
$stmt = $conn->prepare("SELECT id FROM otp_tokens WHERE webmail = ? AND token = ? AND used = 0 AND expires_at > NOW() ORDER BY id DESC LIMIT 1");
$stmt->bind_param('ss', $webmail, $code);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    // Valid OTP!
    $tokenId = $row['id'];
    
    // Mark OTP used
    $upd = $conn->prepare("UPDATE otp_tokens SET used = 1 WHERE id = ?");
    $upd->bind_param('i', $tokenId);
    $upd->execute();
    
    // Mark user verified
    $updUsr = $conn->prepare("UPDATE users SET is_verified = 1 WHERE webmail = ?");
    $updUsr->bind_param('s', $webmail);
    $updUsr->execute();
    
    // Get user ID
    $usrStmt = $conn->prepare("SELECT id FROM users WHERE webmail = ?");
    $usrStmt->bind_param('s', $webmail);
    $usrStmt->execute();
    $usrRes = $usrStmt->get_result();
    $user   = $usrRes->fetch_assoc();
    
    // Set active session
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['webmail'] = $webmail;
    $_SESSION['role']    = 'student';
    unset($_SESSION['pending_webmail']);
    
    redirect('dashboard.php');
} else {
    // Invalid or expired
    set_flash('error', 'Invalid or expired code. Please try again.');
    redirect('verify.php');
}
