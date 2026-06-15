<?php
// =============================================================
// iSkout — actions/resend_otp.php
// AJAX handler to resend OTP from the verify screen.
// =============================================================

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';
require_once '../includes/mailer.php';

if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['pending_webmail'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$webmail = $_SESSION['pending_webmail'];
$otp     = generate_otp();

$stmt = $conn->prepare("INSERT INTO otp_tokens (webmail, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL " . OTP_EXPIRY_MINUTES . " MINUTE))");
$stmt->bind_param('ss', $webmail, $otp);
$stmt->execute();
$stmt->close();

if (sendOTP($webmail, $otp)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Mail delivery failed']);
}
