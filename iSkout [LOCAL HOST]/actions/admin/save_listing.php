<?php
// =============================================================
// iSkout Admin — actions/admin/save_listing.php
// Handles inserting or updating a listing, handling file upload,
// and updating categories/tags.
// =============================================================

require_once '../../includes/db.php';
require_once '../../includes/admin_guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin/dashboard.php');
    exit;
}

$id          = (int)($_POST['id'] ?? 0);
$name        = trim($_POST['name'] ?? '');
$location    = trim($_POST['location'] ?? '');
$description = trim($_POST['description'] ?? '');
$zone        = trim($_POST['zone'] ?? 'outside');
$open_time   = trim($_POST['open_time'] ?? '08:00');
$close_time  = trim($_POST['close_time'] ?? '17:00');
$categories  = $_POST['categories'] ?? []; // Array
$tags_str    = trim($_POST['tags'] ?? '');

if (!$name || !$location) {
    set_flash('error', 'Name and Location are required.');
    header("Location: ../../admin/" . ($id ? "edit_listing.php?id=$id" : "add_listing.php"));
    exit;
}

// ── Handle Image Upload ──────────────────────────────────────────
$image_path = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $tmp_name = $_FILES['image']['tmp_name'];
    $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed  = ['jpg','jpeg','png','webp'];
    
    if (in_array($file_ext, $allowed) && $_FILES['image']['size'] <= 2 * 1024 * 1024) {
        $filename   = uniqid('listing_') . '.' . $file_ext;
        $target_dir = '../../uploads/listings/';
        $target_file = $target_dir . $filename;
        
        if (move_uploaded_file($tmp_name, $target_file)) {
            $image_path = 'uploads/listings/' . $filename;
        }
    }
}

// ── Insert or Update Listing ──────────────────────────────────────
if ($id > 0) {
    // UPDATE
    if ($image_path) {
        $stmt = $conn->prepare("UPDATE listings SET name=?, location=?, description=?, zone=?, open_time=?, close_time=?, image_path=? WHERE id=?");
        $stmt->bind_param('sssssssi', $name, $location, $description, $zone, $open_time, $close_time, $image_path, $id);
    } else {
        $stmt = $conn->prepare("UPDATE listings SET name=?, location=?, description=?, zone=?, open_time=?, close_time=? WHERE id=?");
        $stmt->bind_param('ssssssi', $name, $location, $description, $zone, $open_time, $close_time, $id);
    }
    $stmt->execute();
    $stmt->close();
} else {
    // INSERT
    $stmt = $conn->prepare("INSERT INTO listings (name, location, description, zone, open_time, close_time, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssssss', $name, $location, $description, $zone, $open_time, $close_time, $image_path);
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();
}

// ── Sync Categories ───────────────────────────────────────────────
$conn->query("DELETE FROM listing_categories WHERE listing_id = $id");
if (!empty($categories)) {
    $ins = $conn->prepare("INSERT INTO listing_categories (listing_id, category) VALUES (?, ?)");
    foreach ($categories as $cat) {
        $ins->bind_param('is', $id, $cat);
        $ins->execute();
    }
    $ins->close();
}

// ── Sync Tags ─────────────────────────────────────────────────────
$conn->query("DELETE FROM listing_tags WHERE listing_id = $id");
if ($tags_str) {
    $tagsArray = array_map('trim', explode(',', $tags_str));
    $ins = $conn->prepare("INSERT INTO listing_tags (listing_id, tag) VALUES (?, ?)");
    foreach ($tagsArray as $tag) {
        if ($tag) {
            $ins->bind_param('is', $id, $tag);
            $ins->execute();
        }
    }
    $ins->close();
}

set_flash('success', 'Listing saved successfully.');
header('Location: ../../admin/dashboard.php');
exit;
