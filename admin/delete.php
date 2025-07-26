<?php
require_once(__DIR__ . '/../app/db.php'); // ✅ Include your DB connection here

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Optional: Check if slide exists
    $check = $conn->query("SELECT * FROM slides WHERE id = $id");
    if ($check->num_rows > 0) {
        $conn->query("DELETE FROM slides WHERE id = $id");
        header("Location: slider.php?msg=Slide+Deleted");
        exit;
    } else {
        header("Location: slider.php?error=Slide+Not+Found");
        exit;
    }
} else {
    header("Location: slider.php?error=Invalid+Request");
    exit;
}
