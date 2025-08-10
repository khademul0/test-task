<?php
session_start();
require_once '../../app/db.php';

header('Content-Type: application/json');

// Enable debug mode (set to false in production)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Check if user is logged in and is admin for certain actions
if (isset($_POST['action']) && in_array($_POST['action'], ['toggle_status', 'delete'])) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
        exit;
    }
}

// Validate request method and action
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method or action.']);
    exit;
}

try {
    // Database connection check
    if (!$conn) {
        throw new Exception('Database connection failed.');
    }

    $action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);

    if ($action === 'submit_contact') {
        // Validate input
        $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $subject = filter_var($_POST['subject'] ?? '', FILTER_SANITIZE_STRING);
        $message = filter_var($_POST['message'] ?? '', FILTER_SANITIZE_STRING);
        $user_id = isset($_POST['user_id']) && is_numeric($_POST['user_id']) ? intval($_POST['user_id']) : null;

        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email address.']);
            exit;
        }

        // Insert into contact_submissions table
        $stmt = $conn->prepare("INSERT INTO contact_submissions (user_id, name, email, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $name, $email, $subject, $message);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to save contact submission: ' . $stmt->error);
        }

        error_log("Contact form submitted: Name=$name, Email=$email, UserID=" . ($user_id ?? 'Guest'));
        $stmt->close();

        echo json_encode([
            'status' => 'success',
            'message' => 'Your message has been sent successfully.'
        ]);
    } elseif ($action === 'toggle_status') {
        if (!isset($_POST['id']) || !is_numeric($_POST['id']) || !isset($_POST['status'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid contact submission ID or status.']);
            exit;
        }
        $contact_id = intval($_POST['id']);
        $current_status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);
        $new_status = $current_status === 'pending' ? 'resolved' : 'pending';

        $stmt = $conn->prepare("UPDATE contact_submissions SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $contact_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to update contact submission status: ' . $stmt->error);
        }
        $affected_rows = $stmt->affected_rows;
        $stmt->close();

        if ($affected_rows > 0) {
            error_log("Contact submission status toggled: ID=$contact_id to $new_status by admin user {$_SESSION['user_id']}");
            echo json_encode([
                'status' => 'success',
                'message' => 'Contact submission status updated successfully.',
                'new_status' => $new_status
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Contact submission not found.']);
        }
    } elseif ($action === 'delete') {
        if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid contact submission ID.']);
            exit;
        }
        $contact_id = intval($_POST['id']);

        $stmt = $conn->prepare("DELETE FROM contact_submissions WHERE id = ?");
        $stmt->bind_param("i", $contact_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to delete contact submission: ' . $stmt->error);
        }
        $affected_rows = $stmt->affected_rows;
        $stmt->close();

        if ($affected_rows > 0) {
            error_log("Contact submission deleted: ID=$contact_id by admin user {$_SESSION['user_id']}");
            echo json_encode([
                'status' => 'success',
                'message' => 'Contact submission deleted successfully.'
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Contact submission not found.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
    }
} catch (Exception $e) {
    error_log('Contact action error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} finally {
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>