<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

// Helper function to send JSON response
function send_response($status, $message)
{
    echo json_encode(['status' => $status, 'message' => $message]);
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
                    // Set email and password cookies for 7 days
                    setcookie('remember_email', $user['email'], time() + (86400 * 7), '/');
                    setcookie('remember_pass', $password, time() + (86400 * 7), '/');
                } else {
                    // Clear cookies if unchecked
                    setcookie('remember_email', '', time() - 3600, '/');
                    setcookie('remember_pass', '', time() - 3600, '/');
                }

                // Set cookie (expires in 1 hour)
                setcookie('user_email', $user['email'], time() + 3600, '/');

                send_response('success', 'Login successful. Redirecting...');
            } else {
                send_response('error', 'Incorrect password.');
            }
        } else {
            send_response('error', 'No user found with this email.');
        }

        // REGISTER
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

        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            send_response('error', 'Email already registered.');
        }

        // Insert
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $name, $email, $hashed);
        if ($stmt->execute()) {
            send_response('success', 'Registration successful. You can now log in.');
        } else {
            send_response('error', 'Registration failed.');
        }

        // RESET PASSWORD
    } elseif ($action === 'reset_password') {
        $email = clean_input($_POST['email'] ?? '');

        if (empty($email)) send_response('error', 'Email is required.');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) send_response('error', 'Invalid email.');

        // Check if user exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) send_response('error', 'No account found with that email.');

        // Generate token and expire time
        $token = bin2hex(random_bytes(32));
        $expire = date("Y-m-d H:i:s", strtotime("+30 minutes"));

        // Save token in DB
        $stmt = $conn->prepare("UPDATE users SET reset_token = ?, token_expire = ? WHERE email = ?");
        $stmt->bind_param('sss', $token, $expire, $email);
        $stmt->execute();

        // Send reset email
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
    send_response('error', 'Invalid request.');
}
