<?php
// Role-based access control helper
function checkUserRole($required_role = 'admin')
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    global $conn;
    $user_id = intval($_SESSION['user_id']);
    $stmt = $conn->prepare("SELECT role_play FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || $user['role_play'] !== $required_role) {
        if ($required_role === 'admin') {
            // Admin required but user is customer - redirect to portfolio
            header('Location: ../portfolio.php');
        } else {
            // Customer required but user is admin - allow access
            return true;
        }
        exit;
    }

    return true;
}

function getUserRole()
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    global $conn;
    $user_id = intval($_SESSION['user_id']);
    $stmt = $conn->prepare("SELECT role_play FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    return $user ? $user['role_play'] : null;
}
