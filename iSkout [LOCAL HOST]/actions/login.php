<?php
// =============================================================
// iSkout — actions/login.php
// Handles student login and "Remember Me" cookie generation.
// =============================================================

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$webmail  = trim($_POST['webmail'] ?? '');
$password = $_POST['password'] ?? '';
$remember = !empty($_POST['remember_me']);

if (!$webmail || !$password) {
    redirect('index.php?error=' . urlencode('Please enter both email and password.'));
}

// Check for admin credentials
if ($webmail === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = 999999;
    $_SESSION['webmail'] = $webmail;
    $_SESSION['role']    = 'admin';
    redirect('admin/landing.php');
}

$stmt = $conn->prepare("SELECT id, password_hash, is_verified FROM users WHERE webmail = ?");
$stmt->bind_param('s', $webmail);
$stmt->execute();
$res = $stmt->get_result();

if ($user = $res->fetch_assoc()) {
    if (password_verify($password, $user['password_hash'])) {
        
        if (!$user['is_verified']) {
            // Not verified — redirect to verification flow
            $_SESSION['pending_webmail'] = $webmail;
            
            // Resend a fresh OTP automatically
            $otp = generate_otp();
            $ins = $conn->prepare("INSERT INTO otp_tokens (webmail, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL " . OTP_EXPIRY_MINUTES . " MINUTE))");
            $ins->bind_param('ss', $webmail, $otp);
            $ins->execute();
            
            require_once '../includes/mailer.php';
            sendOTP($webmail, $otp);
            
            set_flash('error', 'Your account is not verified. We sent a new code.');
            redirect('verify.php');
        }

        // Login success
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['webmail'] = $webmail;
        $_SESSION['role']    = 'student';

        // Remember Me Logic
        if ($remember) {
            $token  = generate_remember_token();
            $expiry = date('Y-m-d H:i:s', time() + (86400 * 30)); // 30 days
            
            $ins = $conn->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
            $ins->bind_param('iss', $user['id'], $token, $expiry);
            $ins->execute();
            $ins->close();
            
            setcookie('remember_token', $token, time() + (86400 * 30), '/');
        }

        redirect('dashboard.php');
    }
}

// Invalid credentials fallback
redirect('index.php?error=' . urlencode('Invalid email or password.'));
