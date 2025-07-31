<?php
session_start();
require_once __DIR__ . '/../../app/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

function clean($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

// Create Slide
if (isset($_POST['create_slide'])) {
    $title = clean($_POST['title'] ?? '');
    $subtitle = clean($_POST['subtitle'] ?? '');
    $url = clean($_POST['url'] ?? '');
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $status = clean($_POST['status'] ?? 'Inactive');

    if (empty($title) || empty($start_time) || empty($end_time) || empty($status)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields']);
        exit;
    }

    $imageName = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = __DIR__ . '/../../assets/images/slides/';
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
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Image is required']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO slides (title, subtitle, url, start_time, end_time, status, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $title, $subtitle, $url, $start_time, $end_time, $status, $imageName);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Slide created successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }
    exit;
}

// Update Slide
if (isset($_POST['update_slide']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $title = clean($_POST['title'] ?? '');
    $subtitle = clean($_POST['subtitle'] ?? '');
    $url = clean($_POST['url'] ?? '');
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $status = clean($_POST['status'] ?? 'Inactive');

    if (empty($title) || empty($start_time) || empty($end_time) || empty($status)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields']);
        exit;
    }

    $res = $conn->query("SELECT image FROM slides WHERE id = $id");
    if ($res->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Slide not found']);
        exit;
    }
    $slide = $res->fetch_assoc();
    $imageName = $slide['image'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = __DIR__ . '/../../assets/images/slides/';
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

        $newImageName = uniqid('slide_', true) . '.' . $extension;
        $targetPath = $targetDir . $newImageName;

        if (move_uploaded_file($fileTmpPath, $targetPath)) {
            if (!empty($imageName) && file_exists($targetDir . $imageName)) {
                unlink($targetDir . $imageName);
            }
            $imageName = $newImageName;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Image upload failed']);
            exit;
        }
    }

    $stmt = $conn->prepare("UPDATE slides SET title=?, subtitle=?, url=?, start_time=?, end_time=?, status=?, image=? WHERE id=?");
    $stmt->bind_param("sssssssi", $title, $subtitle, $url, $start_time, $end_time, $status, $imageName, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Slide updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }
    exit;
}

// Delete Slide
if (isset($_POST['delete_slide']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $res = $conn->query("SELECT image FROM slides WHERE id = $id");
    if ($res->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Slide not found']);
        exit;
    }
    $slide = $res->fetch_assoc();

    $stmt = $conn->prepare("DELETE FROM slides WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $targetDir = __DIR__ . '/../../assets/images/slides/';
        if (!empty($slide['image']) && file_exists($targetDir . $slide['image'])) {
            unlink($targetDir . $slide['image']);
        }
        echo json_encode(['status' => 'success', 'message' => 'Slide deleted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
exit;
