<?php
require_once __DIR__ . '/../app/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: slider.php?msg=invalid");
    exit;
}

$id = intval($_GET['id']);

// Fetch image path to delete
$result = $conn->query("SELECT image FROM slides WHERE id = $id");
if ($result->num_rows === 0) {
    header("Location: slider.php?msg=notfound");
    exit;
}

$row = $result->fetch_assoc();
$imagePath = __DIR__ . '/../assets/images/slides/' . $row['image'];

if (!empty($row['image']) && file_exists($imagePath)) {
    unlink($imagePath);
}

// Delete DB record
$conn->query("DELETE FROM slides WHERE id = $id");

// Redirect with message for toast
header("Location: slider.php?msg=deleted");
exit;
