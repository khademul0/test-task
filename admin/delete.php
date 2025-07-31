<?php
require_once __DIR__ . '/../app/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header("Location: slider.php?error=Invalid+Request");
  exit;
}

$id = intval($_GET['id']);

// Fetch slide to delete image file
$result = $conn->query("SELECT image FROM slides WHERE id = $id");
if ($result->num_rows === 0) {
  header("Location: slider.php?error=Slide+Not+Found");
  exit;
}

$row = $result->fetch_assoc();

$targetDir = __DIR__ . '/../assets/images/slides/';
if (!empty($row['image']) && file_exists($targetDir . $row['image'])) {
  unlink($targetDir . $row['image']);
}

// Delete DB record
$conn->query("DELETE FROM slides WHERE id = $id");

header("Location: slider.php?msg=Slide+deleted+successfully");
exit;
