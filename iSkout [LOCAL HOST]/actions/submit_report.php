<?php
// =============================================================
// iSkout — actions/submit_report.php
// Handles inserting a user report for a listing into the DB.
// =============================================================

require_once '../includes/auth_guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php');
}

$listing_id = (int)($_POST['listing_id'] ?? 0);
$reason     = trim($_POST['reason'] ?? '');
$details    = trim($_POST['details'] ?? '');
$user_id    = $_SESSION['user_id'];

if (!$listing_id || !$reason) {
    set_flash('error', 'Invalid report submission.');
    redirect("listing.php?id=$listing_id");
}

$stmt = $conn->prepare("INSERT INTO reports (listing_id, reported_by_user_id, reason, details) VALUES (?, ?, ?, ?)");
$stmt->bind_param('iiss', $listing_id, $user_id, $reason, $details);

if ($stmt->execute()) {
    set_flash('success', 'Thank you for your report. Our team will review it shortly.');
} else {
    set_flash('error', 'Failed to submit report. Please try again later.');
}

$stmt->close();
redirect("listing.php?id=$listing_id");
