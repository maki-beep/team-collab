<?php
// =============================================================
// iSkout — actions/create_listing.php
// Handles student-created listing submissions from dashboard.php.
// =============================================================

require_once '../includes/auth_guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_URL . '/dashboard.php');
    exit;
}

$name        = trim($_POST['name'] ?? '');
$price       = trim($_POST['price'] ?? '');
$category    = trim($_POST['category'] ?? 'food');
$description = trim($_POST['description'] ?? '');
$location    = trim($_POST['location'] ?? '');
$zone        = trim($_POST['zone'] ?? 'outside');
$stock       = trim($_POST['stock'] ?? 'Available');
$duration    = trim($_POST['duration'] ?? 'Until supplies last');
$contact     = trim($_POST['contact'] ?? '');

if ($name === '' || $price === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Please provide a name and a price for the listing.']);
    exit;
}

$allowedCategories = ['food', 'study', 'print', 'rentals', 'supplies', 'repair'];
if (!in_array($category, $allowedCategories, true)) {
    $category = 'food';
}

$zone = strtolower($zone) === 'inside' ? 'inside' : 'outside';
$open_time = '08:00:00';
$close_time = '20:00:00';

$image_path = null;
if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $tmp_name = $_FILES['image']['tmp_name'];
    $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($file_ext, $allowed_ext, true) && $_FILES['image']['size'] <= 2 * 1024 * 1024) {
        $uploadDir = __DIR__ . '/../uploads/listings/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid('listing_', true) . '.' . $file_ext;
        $target = $uploadDir . $filename;
        if (move_uploaded_file($tmp_name, $target)) {
            $image_path = 'uploads/listings/' . $filename;
        }
    }
}

$descriptionForDb = $description;
$locationForDb = $location ?: 'Campus area';

$stmt = $conn->prepare(
    'INSERT INTO listings (name, location, description, zone, open_time, close_time, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)' 
);
$stmt->bind_param('sssssss', $name, $locationForDb, $descriptionForDb, $zone, $open_time, $close_time, $image_path);
$stmt->execute();
$listingId = $conn->insert_id;
$stmt->close();

if ($listingId > 0) {
    $stmtCat = $conn->prepare('INSERT INTO listing_categories (listing_id, category) VALUES (?, ?)');
    $stmtCat->bind_param('is', $listingId, $category);
    $stmtCat->execute();
    $stmtCat->close();
}

header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'listing_id' => $listingId]);
exit;
