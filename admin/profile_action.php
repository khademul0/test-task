<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/Auth.php';

App\Auth::check();

$user_id = intval($_SESSION['user_id']);
$response = ['status' => 'error', 'message' => 'Invalid request'];

// Fetch current user data
$stmt = $conn->prepare("SELECT name, email, photo, 2fa_enabled, theme_preference, email_notifications, password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    $response['message'] = 'User not found.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $two_fa_enabled = isset($_POST['2fa_enabled']) ? 1 : 0;
    $theme_preference = in_array($_POST['theme_preference'] ?? '', ['light', 'dark']) ? $_POST['theme_preference'] : 'light';
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if (empty($name) || strlen($name) < 2) {
        $response['message'] = 'Name must be at least 2 characters.';
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format.';
        echo json_encode($response);
        exit;
    }

    // Check if email is already taken by another user
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $response['message'] = 'Email is already in use.';
        echo json_encode($response);
        exit;
    }

    // Handle password change
    $password_update = '';
    if (!empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password)) {
            $response['message'] = 'Current password is required to change password.';
            echo json_encode($response);
            exit;
        }

        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            $response['message'] = 'Current password is incorrect.';
            echo json_encode($response);
            exit;
        }

        if ($new_password !== $confirm_password) {
            $response['message'] = 'New passwords do not match.';
            echo json_encode($response);
            exit;
        }

        if (strlen($new_password) < 6 || !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $new_password)) {
            $response['message'] = 'New password must be at least 6 characters and contain a special character.';
            echo json_encode($response);
            exit;
        }

        $password_update = password_hash($new_password, PASSWORD_DEFAULT);
    }

    // Handle profile photo upload
    $photo = $user['photo'];
    if (!empty($_FILES['photo']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['photo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $response['message'] = 'Invalid image format. Allowed formats: jpg, jpeg, png, gif.';
            echo json_encode($response);
            exit;
        }

        if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            $response['message'] = 'Image size must not exceed 2MB.';
            echo json_encode($response);
            exit;
        }

        // Delete old photo if exists and is not default
        if (!empty($photo) && $photo !== 'default.png' && file_exists(__DIR__ . '/../assets/images/profiles/' . $photo)) {
            unlink(__DIR__ . '/../assets/images/profiles/' . $photo);
        }

        // Upload new photo
        $photo = 'profile_' . uniqid() . '.' . $ext;
        $upload_path = __DIR__ . '/../assets/images/profiles/' . $photo;
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
            $response['message'] = 'Failed to upload image.';
            echo json_encode($response);
            exit;
        }
    }

    // Update user in database
    if ($password_update) {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, photo = ?, 2fa_enabled = ?, theme_preference = ?, email_notifications = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssissi", $name, $email, $photo, $two_fa_enabled, $theme_preference, $email_notifications, $password_update, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, photo = ?, 2fa_enabled = ?, theme_preference = ?, email_notifications = ? WHERE id = ?");
        $stmt->bind_param("sssissi", $name, $email, $photo, $two_fa_enabled, $theme_preference, $email_notifications, $user_id);
    }

    if ($stmt->execute()) {
        // Update session variables
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;

        // Log activity
        $description = "Updated profile: name to '$name', email to '$email'" . ($photo !== $user['photo'] ? ", updated photo" : "") . ($password_update ? ", updated password" : "");
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, created_at) VALUES (?, 'Update Profile', ?, NOW())");
        $stmt->bind_param("is", $user_id, $description);
        $stmt->execute();

        $response = [
            'status' => 'success',
            'message' => 'Profile updated successfully.',
            'redirect' => 'dashboard.php'
        ];
    } else {
        $response['message'] = 'Failed to update profile.';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
