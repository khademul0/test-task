<?php
// File: admin/Auth.php

namespace App;

session_start();

class Auth
{
    public static function check()
    {
        if (!isset($_SESSION['user_email'])) {
            header('Location: login.php');
            exit;
        }
    }

    public static function logout()
    {
        session_unset();
        session_destroy();
        setcookie('user_email', '', time() - 3600, '/');
        header('Location: login.php');
        exit;
    }
}


// If session already exists, user is authenticated
if (isset($_SESSION['user_id'])) {
    return;
}

// If session doesn't exist, check for "Remember Me" cookies
if (isset($_COOKIE['remember_email']) && isset($_COOKIE['remember_pass'])) {
    require_once 'db.php';

    $email = $_COOKIE['remember_email'];
    $password = $_COOKIE['remember_pass'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            return;
        }
    }

    // If cookie login fails, clear the cookies
    setcookie('remember_email', '', time() - 3600, '/');
    setcookie('remember_pass', '', time() - 3600, '/');
}

// Redirect to login if neither session nor valid cookies exist
header("Location: login.php");

exit;
