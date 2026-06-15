<?php
// =============================================================
// iSkout — includes/auth_guard.php
// Include at the top of every student-protected page.
// Checks for a valid session, and also handles remember-me cookies.
// =============================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Already authenticated via session ────────────────────────
if (!empty($_SESSION['user_id']) && !empty($_SESSION['role']) && $_SESSION['role'] === 'student') {
    // All good — continue
    return;
}

// ── Check remember-me cookie ──────────────────────────────────
if (!empty($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $stmt  = $conn->prepare(
        "SELECT rt.user_id, u.webmail, u.is_verified
         FROM remember_tokens rt
         JOIN users u ON u.id = rt.user_id
         WHERE rt.token = ? AND rt.expires_at > NOW()
         LIMIT 1"
    );
    if ($stmt) {
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if ($row['is_verified']) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['webmail'] = $row['webmail'];
                $_SESSION['role']    = 'student';
                $stmt->close();
                return; // Authenticated via cookie
            }
        }
        $stmt->close();
    }
    // Invalid/expired cookie — clear it
    setcookie('remember_token', '', time() - 3600, '/');
}

    // Not authenticated — redirect to student login
    http_response_code(403);
    header('Location: ' . (defined('APP_URL') ? APP_URL : '') . '/index.php');
    exit;


