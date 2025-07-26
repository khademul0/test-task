<?php
// Start session and include the database connection
session_start();
require_once(__DIR__ . '/../../app/db.php');

header('Content-Type: application/json');

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Get POST data
$title = $_POST['title'] ?? '';
$subtitle = $_POST['subtitle'] ?? '';
$url = $_POST['url'] ?? '';
$start_time = $_POST['start_time'] ?? '';
$end_time = $_POST['end_time'] ?? '';
$status = $_POST['status'] ?? 'Inactive';

// Validate required fields
if (empty($title) || empty($subtitle) || empty($status)) {
    echo json_encode(['status' => 'error', 'message' => 'Required fields are missing']);
    exit;
}

// Handle image upload
$imageName = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $targetDir = __DIR__ . '/../../assets/images/slides/';

    // Create directory if it doesn't exist
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileTmpPath = $_FILES['image']['tmp_name'];
    $originalName = basename($_FILES['image']['name']);
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($extension, $allowedExtensions)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid image format']);
        exit;
    }

    $imageName = uniqid('slide_', true) . '.' . $extension;
    $targetPath = $targetDir . $imageName;

    if (!move_uploaded_file($fileTmpPath, $targetPath)) {
        echo json_encode(['status' => 'error', 'message' => 'Image upload failed']);
        exit;
    }
}

// Insert into `slides` table
try {
    $stmt = $conn->prepare("INSERT INTO slides (title, subtitle, url, start_time, end_time, status, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $title, $subtitle, $url, $start_time, $end_time, $status, $imageName);
    $stmt->execute();

    echo json_encode(['status' => 'success', 'message' => 'Slide created successfully']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
}
