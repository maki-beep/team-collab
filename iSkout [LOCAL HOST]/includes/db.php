<?php
// =============================================================
// iSkout — includes/db.php
// Creates the global mysqli connection $conn.
// Include this file on every page that needs database access.
// =============================================================

require_once __DIR__ . '/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    // In production you may want to log this instead of dying loudly
    die('Database connection failed: ' . htmlspecialchars($conn->connect_error));
}

$conn->set_charset(DB_CHARSET);
