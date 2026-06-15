<?php
// =============================================================
// iSkout Admin — actions/admin/delete_listing.php
// Deletes a listing and its image from the server.
// =============================================================

require_once '../../includes/db.php';
require_once '../../includes/admin_guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin/dashboard.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);

if ($id) {
    // Get image path to delete file
    $stmt = $conn->prepare("SELECT image_path FROM listings WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        if ($row['image_path'] && file_exists('../../' . $row['image_path'])) {
            unlink('../../' . $row['image_path']);
        }
    }
    $stmt->close();

    // Delete listing (cascades to tags/categories/reports due to foreign keys)
    $del = $conn->prepare("DELETE FROM listings WHERE id = ?");
    $del->bind_param('i', $id);
    if ($del->execute()) {
        set_flash('success', 'Listing deleted successfully.');
    } else {
        set_flash('error', 'Error deleting listing.');
    }
    $del->close();
}

header('Location: ../../admin/dashboard.php');
exit;
