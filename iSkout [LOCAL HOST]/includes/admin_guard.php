<?php
// =============================================================
// iSkout — includes/admin_guard.php
// Include at the top of every admin-protected page.
// Returns HTTP 403 and redirects non-admins to admin login.
// =============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/helpers.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    header('Location: ' . (defined('APP_URL') ? APP_URL : '') . '/admin/index.php');
    exit;
}
