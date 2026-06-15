<?php
// =============================================================
// iSkout — actions/admin/admin_login.php
// Validates admin credentials against config constants.
// =============================================================

require_once '../../includes/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin/index.php');
    exit;
}

$user = trim($_POST['username'] ?? '');
$pass = $_POST['password'] ?? '';

if ($user === ADMIN_USERNAME && password_verify($pass, ADMIN_PASSWORD_HASH)) {
    session_regenerate_id(true);
    // Use an arbitrary high ID for admin to separate from student IDs
    $_SESSION['user_id'] = 999999;
    $_SESSION['webmail'] = $user;
    $_SESSION['role']    = 'admin';
    header('Location: ' . APP_URL . '/admin/landing.php');
    exit;
}

header('Location: ../../admin/index.php?error=' . urlencode('Invalid admin credentials.'));
exit;
