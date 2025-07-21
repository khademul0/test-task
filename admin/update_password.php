<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($token) || empty($password) || empty($confirm)) {
        die("All fields are required.");
    }

    if ($password !== $confirm) {
        die("Passwords do not match.");
    }

    $stmt = $conn->prepare("SELECT id, token_expire FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (strtotime($user['token_expire']) < time()) {
            die("Token has expired.");
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expire = NULL WHERE id = ?");
        $stmt->bind_param("si", $hashed, $user['id']);
        if ($stmt->execute()) {
            echo "Password has been updated. <a href='login.php'>Login</a>";
        } else {
            echo "Something went wrong. Try again.";
        }
    } else {
        echo "Invalid token.";
    }
} else {
    echo "Invalid request.";
}
