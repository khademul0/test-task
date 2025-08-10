<?php
require_once '../../app/db.php';

header('Content-Type: application/json');

session_start();
$session_id = session_id();
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
        throw new Exception('Invalid request method or action missing.');
    }

    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS);
    if (!$action) {
        throw new Exception('Invalid action provided.');
    }

    if ($action === 'create_order') {
        // Validate input fields
        if (!isset($_POST['full_name']) || !isset($_POST['email']) || !isset($_POST['address']) || !isset($_POST['payment_method'])) {
            throw new Exception('Missing required fields.');
        }

        $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS);
        $payment_method = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_SPECIAL_CHARS);

        if (!$full_name || !$email || !$address || !$payment_method || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid input data.');
        }

        // Start transaction
        $conn->query("START TRANSACTION");
        $in_transaction = true;

        // Get cart items from DB
        $condition = $user_id ? "user_id = $user_id OR session_id = '$session_id'" : "session_id = '$session_id'";
        $cart_stmt = $conn->prepare("
            SELECT c.work_id, c.quantity, w.title, w.price
            FROM cart c
            JOIN works w ON c.work_id = w.id
            WHERE $condition AND w.status = 1
        ");
        if (!$cart_stmt->execute()) {
            throw new Exception('Failed to fetch cart items: ' . $cart_stmt->error);
        }
        $cart_items = $cart_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $cart_stmt->close();

        if (empty($cart_items)) {
            throw new Exception('Cart is empty.');
        }

        // Calculate total
        $total = 0;
        foreach ($cart_items as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        // Insert into orders table
        $stmt = $conn->prepare("INSERT INTO orders (user_id, full_name, email, address, payment_method, total) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssd", $user_id, $full_name, $email, $address, $payment_method, $total);
        if (!$stmt->execute()) {
            throw new Exception('Failed to create order: ' . $stmt->error);
        }
        $order_id = $conn->insert_id;
        $stmt->close();

        // Insert cart items into order_items table
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, work_id, title, price, quantity) VALUES (?, ?, ?, ?, ?)");
        foreach ($cart_items as $item) {
            $stmt->bind_param("iisdi", $order_id, $item['work_id'], $item['title'], $item['price'], $item['quantity']);
            if (!$stmt->execute()) {
                throw new Exception('Failed to insert order item: ' . $stmt->error);
            }
        }
        $stmt->close();

        // Clear cart after order
        $clear_stmt = $conn->prepare("DELETE FROM cart WHERE $condition");
        if (!$clear_stmt->execute()) {
            throw new Exception('Failed to clear cart: ' . $clear_stmt->error);
        }
        $clear_stmt->close();

        // Commit transaction
        $conn->query("COMMIT");
        $in_transaction = false;

        ob_clean();
        echo json_encode(['status' => 'success', 'order_id' => $order_id]);
    } else {
        throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    if (isset($in_transaction) && $in_transaction) {
        $conn->query("ROLLBACK");
    }
    error_log("Order creation failed: " . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    ob_clean();
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
