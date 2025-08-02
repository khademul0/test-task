<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

// Helper function to send JSON response
function send_response($status, $message, $redirect = '')
{
    echo json_encode(['status' => $status, 'message' => $message, 'redirect' => $redirect]);
    exit;
}

// Sanitize input
function clean_input($data)
{
    return htmlspecialchars(trim($data));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // LOGIN
    if ($action === 'login_user') {
        $email = clean_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            send_response('error', 'Email and password are required.');
        }

        $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                if (isset($_POST['remember'])) {
                    setcookie('remember_email', $user['email'], time() + (86400 * 7), '/');
                    setcookie('remember_pass', $password, time() + (86400 * 7), '/');
                } else {
                    setcookie('remember_email', '', time() - 3600, '/');
                    setcookie('remember_pass', '', time() - 3600, '/');
                }

                setcookie('user_email', $user['email'], time() + 3600, '/');

                send_response('success', 'Login successful. Redirecting...', 'dashboard.php');
            } else {
                send_response('error', 'Incorrect password.');
            }
        } else {
            send_response('error', 'No user found with this email.');
        }
    } elseif ($action === 'register_user') {
        $email = clean_input($_POST['email'] ?? '');
        $name = clean_input($_POST['name'] ?? '');
        $password = $_POST['password'] ?? '';
        $cpassword = $_POST['cpassword'] ?? '';

        if (empty($email) || empty($name) || empty($password) || empty($cpassword)) {
            send_response('error', 'All fields are required.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            send_response('error', 'Invalid email address.');
        }

        if ($password !== $cpassword) {
            send_response('error', 'Passwords do not match.');
        }

        if (strlen($password) < 6) {
            send_response('error', 'Password must be at least 6 characters.');
        }

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            send_response('error', 'Email already registered.');
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $name, $email, $hashed);
        if ($stmt->execute()) {
            send_response('success', 'Registration successful. You can now log in.');
        } else {
            send_response('error', 'Registration failed.');
        }
    } elseif ($action === 'reset_password') {
        $email = clean_input($_POST['email'] ?? '');

        if (empty($email)) send_response('error', 'Email is required.');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) send_response('error', 'Invalid email.');

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) send_response('error', 'No account found with that email.');

        $token = bin2hex(random_bytes(32));
        $expire = date("Y-m-d H:i:s", strtotime("+30 minutes"));

        $stmt = $conn->prepare("UPDATE users SET reset_token = ?, token_expire = ? WHERE email = ?");
        $stmt->bind_param('sss', $token, $expire, $email);
        $stmt->execute();

        $resetLink = "http://localhost/task-project/reset.php?token=$token";
        $subject = "Password Reset Request";
        $message = "Hi,\n\nClick the link below to reset your password:\n\n$resetLink\n\nThis link will expire in 30 minutes.";
        $headers = "From: noreply@yourdomain.com";

        if (mail($email, $subject, $message, $headers)) {
            send_response('success', 'A reset link has been sent to your email.');
        } else {
            send_response('error', 'Failed to send reset email.');
        }
    } else {
        send_response('error', 'Invalid action.');
    }
} else {
    // Update Profile Photo
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile_photo') {
        $user_id = intval($_SESSION['user_id']);
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
                send_response('error', 'Invalid image format');
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
                    $conn->query("INSERT INTO activity_logs (user_id, action, description) VALUES ($user_id, 'Update Profile Photo', 'Updated profile photo')");
                    send_response('success', 'Profile photo updated successfully');
                } else {
                    send_response('error', 'Database error: ' . $stmt->error);
                }
            } else {
                send_response('error', 'Image upload failed');
            }
        } else {
            send_response('error', 'No image uploaded or upload error');
        }
    }

    // Update Profile Info
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile_info') {
        $user_id = intval($_SESSION['user_id']);
        $name = clean_input($_POST['name'] ?? '');
        $email = clean_input($_POST['email'] ?? '');

        if (empty($name) || empty($email)) {
            send_response('error', 'Name and email are required');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            send_response('error', 'Invalid email address');
        }

        // Check if email is already used by another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            send_response('error', 'Email already in use by another account');
        }

        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $email, $user_id);
        if ($stmt->execute()) {
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $conn->query("INSERT INTO activity_logs (user_id, action, description) VALUES ($user_id, 'Update Profile Info', 'Updated name to $name and email to $email')");
            send_response('success', 'Profile information updated successfully');
        } else {
            send_response('error', 'Database error: ' . $stmt->error);
        }
    }

    // Update Password
    if (isset($_POST['action']) && $_POST['action'] === 'update_password') {
        $user_id = intval($_SESSION['user_id']);
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            send_response('error', 'All password fields are required');
        }

        if ($new_password !== $confirm_password) {
            send_response('error', 'New passwords do not match');
        }

        if (strlen($new_password) < 6) {
            send_response('error', 'New password must be at least 6 characters');
        }

        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!password_verify($current_password, $user['password'])) {
            send_response('error', 'Current password is incorrect');
        }

        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $user_id);
        if ($stmt->execute()) {
            $conn->query("INSERT INTO activity_logs (user_id, action, description) VALUES ($user_id, 'Update Password', 'Changed password')");
            send_response('success', 'Password updated successfully');
        } else {
            send_response('error', 'Database error: ' . $stmt->error);
        }
    }
    send_response('error', 'Invalid request.');
}
