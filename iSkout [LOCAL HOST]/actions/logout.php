<?php
// =============================================================
// iSkout — actions/logout.php
// Clears session and remember-me cookies, redirects to login.
// =============================================================

if (session_status() === PHP_SESSION_NONE) session_start();

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Clear the remember me cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to login page
header('Location: ../index.php');
exit;
