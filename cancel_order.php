<?php
session_start();
require_once 'app/db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please login.']);
    exit;
}

// Validate request method and required data
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['order_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method or missing order ID.']);
    exit;
}

try {
    $user_id = intval($_SESSION['user_id']);
    $order_id = intval($_POST['order_id']);
    
    // Validate order ID
    if ($order_id <= 0) {
        throw new Exception('Invalid order ID provided.');
    }

    // Start transaction for data consistency
    $conn->query("START TRANSACTION");
    $in_transaction = true;

    // Check if order exists and belongs to the user
    $check_stmt = $conn->prepare("SELECT id, status, user_id FROM orders WHERE id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $order_id, $user_id);
    
    if (!$check_stmt->execute()) {
        throw new Exception('Failed to verify order: ' . $check_stmt->error);
    }
    
    $order_result = $check_stmt->get_result();
    $order = $order_result->fetch_assoc();
    $check_stmt->close();

    if (!$order) {
        throw new Exception('Order not found or you do not have permission to cancel this order.');
    }

    // Check if order can be cancelled (only Pending and Processing orders can be cancelled)
    if (!in_array($order['status'], ['Pending', 'Processing'])) {
        throw new Exception('Order cannot be cancelled. Only pending and processing orders can be cancelled.');
    }

    // Update order status to Cancelled
    $update_stmt = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ? AND user_id = ?");
    $update_stmt->bind_param("ii", $order_id, $user_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception('Failed to cancel order: ' . $update_stmt->error);
    }
    
    $affected_rows = $update_stmt->affected_rows;
    $update_stmt->close();

    if ($affected_rows === 0) {
        throw new Exception('No changes made. Order may have already been cancelled or does not exist.');
    }

    // Log the cancellation activity
    $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description) VALUES (?, 'Order Cancelled', ?)");
    $log_description = "Cancelled order #$order_id";
    $log_stmt->bind_param("is", $user_id, $log_description);
    
    if (!$log_stmt->execute()) {
        // Log error but don't fail the transaction
        error_log("Failed to log order cancellation: " . $log_stmt->error);
    }
    $log_stmt->close();

    // Commit transaction
    $conn->query("COMMIT");
    $in_transaction = false;

    // Success response
    echo json_encode([
        'status' => 'success',
        'message' => 'Order cancelled successfully.',
        'order_id' => $order_id
    ]);

} catch (Exception $e) {
    // Rollback transaction if it was started
    if (isset($in_transaction) && $in_transaction) {
        $conn->query("ROLLBACK");
    }
    
    // Log the error
    error_log("Order cancellation failed: " . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    
    // Return error response
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    // Close database connection
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
