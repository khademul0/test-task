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

$user_id = intval($_SESSION['user_id'] ?? 0);

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
            mkdir($targetDir, 0755, true);
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
        $conn->query("INSERT INTO activity_logs (user_id, action, description, created_at) VALUES ($user_id, 'Create Slide', 'Created slide: " . addslashes($title) . "', NOW())");
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

    $res = $conn->query("SELECT image, title FROM slides WHERE id = $id");
    if ($res->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Slide not found']);
        exit;
    }

    $slide = $res->fetch_assoc();
    $imageName = $slide['image'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = __DIR__ . '/../../assets/images/slides/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
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
        $conn->query("INSERT INTO activity_logs (user_id, action, description, created_at) VALUES ($user_id, 'Update Slide', 'Updated slide: " . addslashes($title) . "', NOW())");
        echo json_encode(['status' => 'success', 'message' => 'Slide updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }
    exit;
}

// Delete Slide
if (isset($_POST['delete_slide']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    $res = $conn->query("SELECT image, title FROM slides WHERE id = $id");
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
        $conn->query("INSERT INTO activity_logs (user_id, action, description, created_at) VALUES ($user_id, 'Delete Slide', 'Deleted slide: " . addslashes($slide['title'] ?? 'ID ' . $id) . "', NOW())");
        echo json_encode(['status' => 'success', 'message' => 'Slide deleted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }
    exit;
}

// Toggle Slide Status
if (isset($_POST['toggle_slide_status']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    $res = $conn->query("SELECT status, title FROM slides WHERE id = $id");
    if ($res->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Slide not found']);
        exit;
    }

    $slide = $res->fetch_assoc();
    $newStatus = $slide['status'] === 'Active' ? 'Inactive' : 'Active';

    $stmt = $conn->prepare("UPDATE slides SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $id);

    if ($stmt->execute()) {
        $conn->query("INSERT INTO activity_logs (user_id, action, description, created_at) VALUES ($user_id, 'Toggle Slide Status', 'Changed status of slide: " . addslashes($slide['title'] ?? 'ID ' . $id) . " to $newStatus', NOW())");
        echo json_encode(['status' => 'success', 'message' => "Status changed to $newStatus", 'new_status' => $newStatus]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update status']);
    }
    exit;
}

// Update Profile Photo
if (isset($_POST['action']) && $_POST['action'] === 'update_profile_photo') {
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = __DIR__ . '/../../assets/images/profiles/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $fileTmpPath = $_FILES['profile_image']['tmp_name'];
        $originalName = basename($_FILES['profile_image']['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($extension, $allowedExtensions)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid image format']);
            exit;
        }

        // Fetch current image to delete
        $stmt = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $current_image = $stmt->get_result()->fetch_assoc()['profile_image'];

        $imageName = uniqid('profile_', true) . '.' . $extension;
        $targetPath = $targetDir . $imageName;

        if (move_uploaded_file($fileTmpPath, $targetPath)) {
            if (!empty($current_image) && file_exists($targetDir . $current_image)) {
                unlink($targetDir . $current_image);
            }
            $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            $stmt->bind_param("si", $imageName, $user_id);
            if ($stmt->execute()) {
                $conn->query("INSERT INTO activity_logs (user_id, action, description, created_at) VALUES ($user_id, 'Update Profile Photo', 'Updated profile photo', NOW())");
                echo json_encode(['status' => 'success', 'message' => 'Profile photo updated successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Image upload failed']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No image uploaded or upload error']);
    }
    exit;
}

// Update Profile Info
if (isset($_POST['action']) && $_POST['action'] === 'update_profile_info') {
    $name = clean($_POST['name'] ?? '');
    $email = clean($_POST['email'] ?? '');

    if (empty($name) || empty($email)) {
        echo json_encode(['status' => 'error', 'message' => 'Name and email are required']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email address']);
        exit;
    }

    // Check if email is already used by another user
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email already in use by another account']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $email, $user_id);
    if ($stmt->execute()) {
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $conn->query("INSERT INTO activity_logs (user_id, action, description, created_at) VALUES ($user_id, 'Update Profile Info', 'Updated name to " . addslashes($name) . " and email to " . addslashes($email) . "', NOW())");
        echo json_encode(['status' => 'success', 'message' => 'Profile information updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }
    exit;
}

// Update Password
if (isset($_POST['action']) && $_POST['action'] === 'update_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        echo json_encode(['status' => 'error', 'message' => 'All password fields are required']);
        exit;
    }

    if ($new_password !== $confirm_password) {
        echo json_encode(['status' => 'error', 'message' => 'New passwords do not match']);
        exit;
    }

    if (strlen($new_password) < 6) {
        echo json_encode(['status' => 'error', 'message' => 'New password must be at least 6 characters']);
        exit;
    }

    // Verify current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!password_verify($current_password, $user['password'])) {
        echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
        exit;
    }

    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed, $user_id);
    if ($stmt->execute()) {
        $conn->query("INSERT INTO activity_logs (user_id, action, description, created_at) VALUES ($user_id, 'Update Password', 'Changed password', NOW())");
        echo json_encode(['status' => 'success', 'message' => 'Password updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
exit;
