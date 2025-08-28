<?php
session_start();
require_once '../../app/db.php';

header('Content-Type: application/json');

// Enable debug mode (set to false in production)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please login.']);
    exit;
}

// If role is not set in session, fetch it from database
if (!isset($_SESSION['role_play'])) {
    $user_id = intval($_SESSION['user_id']);
    $stmt = $conn->prepare("SELECT role_play FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['role_play'] = $user['role_play'];
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not found.']);
        exit;
    }
    $stmt->close();
}

// Check if user is admin
if ($_SESSION['role_play'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Admin privileges required.']);
    exit;
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

    switch ($action) {
        case 'update_order_status':
            updateOrderStatus($conn);
            break;

        case 'get_order_details':
            getOrderDetails($conn);
            break;

        case 'delete_order':
            deleteOrder($conn);
            break;

        case 'toggle_contact_status':
            toggleContactStatus($conn);
            break;

        case 'delete_contact':
            deleteContact($conn);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
            break;
    }
} catch (Exception $e) {
    error_log('Controls action error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} finally {
    if (isset($conn) && $conn) {
        $conn->close();
    }
}

function updateOrderStatus($conn)
{
    if (!isset($_POST['order_id']) || !is_numeric($_POST['order_id']) || !isset($_POST['status'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid order ID or status.']);
        return;
    }

    $order_id = intval($_POST['order_id']);
    $status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);

    // Validate status
    $valid_statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
    if (!in_array($status, $valid_statuses)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid status value.']);
        return;
    }

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);

    if (!$stmt->execute()) {
        throw new Exception('Failed to update order status: ' . $stmt->error);
    }

    $affected_rows = $stmt->affected_rows;
    $stmt->close();

    if ($affected_rows > 0) {
        error_log("Order status updated: ID=$order_id to $status by admin user {$_SESSION['user_id']}");
        echo json_encode([
            'status' => 'success',
            'message' => 'Order status updated successfully.',
            'new_status' => $status
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Order not found or no changes made.']);
    }
}

function getOrderDetails($conn)
{
    if (!isset($_POST['order_id']) || !is_numeric($_POST['order_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid order ID.']);
        return;
    }

    $order_id = intval($_POST['order_id']);

    // Get order details
    $order_stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $order_stmt->bind_param("i", $order_id);

    if (!$order_stmt->execute()) {
        throw new Exception('Failed to fetch order details: ' . $order_stmt->error);
    }

    $order_result = $order_stmt->get_result();
    $order = $order_result->fetch_assoc();
    $order_stmt->close();

    if (!$order) {
        echo json_encode(['status' => 'error', 'message' => 'Order not found.']);
        return;
    }

    // Get order items
    $items_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $items_stmt->bind_param("i", $order_id);

    if (!$items_stmt->execute()) {
        throw new Exception('Failed to fetch order items: ' . $items_stmt->error);
    }

    $items_result = $items_stmt->get_result();
    $items = [];
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }
    $items_stmt->close();

    echo json_encode([
        'status' => 'success',
        'data' => [
            'order' => $order,
            'items' => $items
        ]
    ]);
}

function deleteOrder($conn)
{
    if (!isset($_POST['order_id']) || !is_numeric($_POST['order_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid order ID.']);
        return;
    }

    $order_id = intval($_POST['order_id']);

    // Start transaction
    $conn->query("START TRANSACTION");
    $in_transaction = true;

    try {
        // Delete order items first (due to foreign key constraint)
        $items_stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
        $items_stmt->bind_param("i", $order_id);

        if (!$items_stmt->execute()) {
            throw new Exception('Failed to delete order items: ' . $items_stmt->error);
        }
        $items_stmt->close();

        // Delete the order
        $order_stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $order_stmt->bind_param("i", $order_id);

        if (!$order_stmt->execute()) {
            throw new Exception('Failed to delete order: ' . $order_stmt->error);
        }

        $affected_rows = $order_stmt->affected_rows;
        $order_stmt->close();

        if ($affected_rows > 0) {
            // Commit transaction
            $conn->query("COMMIT");
            $in_transaction = false;

            error_log("Order deleted: ID=$order_id by admin user {$_SESSION['user_id']}");
            echo json_encode([
                'status' => 'success',
                'message' => 'Order deleted successfully.'
            ]);
        } else {
            throw new Exception('Order not found.');
        }
    } catch (Exception $e) {
        if ($in_transaction) {
            $conn->query("ROLLBACK");
        }
        throw $e;
    }
}

function toggleContactStatus($conn)
{
    if (!isset($_POST['id']) || !is_numeric($_POST['id']) || !isset($_POST['status'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid contact submission ID or status.']);
        return;
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
}

function deleteContact($conn)
{
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid contact submission ID.']);
        return;
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
}
