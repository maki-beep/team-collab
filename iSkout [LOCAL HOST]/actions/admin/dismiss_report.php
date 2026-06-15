<?php
// =============================================================
// iSkout Admin — actions/admin/dismiss_report.php
// Marks a report as 'reviewed' to remove it from the pending queue.
// =============================================================

require_once '../../includes/db.php';
require_once '../../includes/admin_guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin/reports.php');
    exit;
}

$report_id = (int)($_POST['report_id'] ?? 0);

if ($report_id) {
    $stmt = $conn->prepare("UPDATE reports SET status = 'reviewed' WHERE id = ?");
    $stmt->bind_param('i', $report_id);
    $stmt->execute();
    $stmt->close();
    set_flash('success', 'Report dismissed.');
}

header('Location: ../../admin/reports.php');
exit;
