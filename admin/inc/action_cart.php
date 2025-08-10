<?php
session_start();
require_once __DIR__ . '/../../app/db.php';

header('Content-Type: application/json');

// Suppress errors in output
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

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
    error_log("Action: $action, User ID: " . ($user_id ?? 'null') . ", Session ID: $session_id");

    if (!in_array($action, ['add', 'update', 'remove', 'clear', 'fetch', 'delete'])) {
        throw new Exception('Invalid action.');
    }

    // Admin delete action
    if ($action === 'delete') {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            throw new Exception('Unauthorized access.');
        }
        if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
            throw new Exception('Invalid cart item ID.');
        }
        $cart_id = intval($_POST['id']);

        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ?");
        $stmt->bind_param("i", $cart_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to delete cart item: ' . $stmt->error);
        }
        $affected_rows = $stmt->affected_rows;
        $stmt->close();

        if ($affected_rows > 0) {
            error_log("Cart item deleted: ID=$cart_id by admin user {$_SESSION['user_id']}");
            ob_clean();
            echo json_encode(['status' => 'success', 'message' => 'Cart item deleted successfully.']);
        } else {
            throw new Exception('Cart item not found.');
        }
        exit;
    }

    // Common function to get cart identifier condition
    function getCartCondition($user_id, $session_id) {
        if ($user_id) {
            return "user_id = $user_id OR session_id = '$session_id'";
        }
        return "session_id = '$session_id'";
    }

    $condition = getCartCondition($user_id, $session_id);

    if ($action === 'add' || $action === 'update') {
        if (!isset($_POST['work_id']) || !is_numeric($_POST['work_id']) || !isset($_POST['quantity']) || !is_numeric($_POST['quantity'])) {
            throw new Exception('Invalid work ID or quantity.');
        }
        $work_id = intval($_POST['work_id']);
        $quantity = intval($_POST['quantity']);

        if ($quantity <= 0) {
            throw new Exception('Quantity must be greater than 0.');
        }

        // Check stock
        $stock_stmt = $conn->prepare("SELECT stock FROM works WHERE id = ? AND status = 1");
        $stock_stmt->bind_param("i", $work_id);
        if (!$stock_stmt->execute()) {
            throw new Exception('Failed to check stock: ' . $stock_stmt->error);
        }
        $stock_result = $stock_stmt->get_result()->fetch_assoc();
        $stock_stmt->close();

        if (!$stock_result || $stock_result['stock'] < $quantity) {
            throw new Exception('Insufficient stock.');
        }

        // Check if item exists in cart
        $check_stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE work_id = ? AND ($condition)");
        $check_stmt->bind_param("i", $work_id);
        if (!$check_stmt->execute()) {
            throw new Exception('Failed to check cart: ' . $check_stmt->error);
        }
        $check_result = $check_stmt->get_result()->fetch_assoc();
        $check_stmt->close();

        if ($check_result) {
            // Update existing item
            $new_quantity = $action === 'add' ? $check_result['quantity'] + $quantity : $quantity;
            if ($new_quantity > $stock_result['stock']) {
                throw new Exception('Cannot exceed stock.');
            }
            $update_stmt = $conn->prepare("UPDATE cart SET quantity = ?, user_id = ? WHERE id = ?");
            $update_stmt->bind_param("iii", $new_quantity, $user_id, $check_result['id']);
            if (!$update_stmt->execute()) {
                throw new Exception('Failed to update cart: ' . $update_stmt->error);
            }
            $update_stmt->close();
            ob_clean();
            echo json_encode(['status' => 'success', 'message' => 'Cart updated.']);
        } else {
            // Add new item
            $insert_stmt = $conn->prepare("INSERT INTO cart (user_id, session_id, work_id, quantity) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("isii", $user_id, $session_id, $work_id, $quantity);
            if (!$insert_stmt->execute()) {
                throw new Exception('Failed to add to cart: ' . $insert_stmt->error);
            }
            $insert_stmt->close();
            ob_clean();
            echo json_encode(['status' => 'success', 'message' => 'Added to cart.']);
        }
    } elseif ($action === 'remove') {
        if (!isset($_POST['work_id']) || !is_numeric($_POST['work_id'])) {
            throw new Exception('Invalid work ID.');
        }
        $work_id = intval($_POST['work_id']);

        $delete_stmt = $conn->prepare("DELETE FROM cart WHERE work_id = ? AND ($condition)");
        $delete_stmt->bind_param("i", $work_id);
        if (!$delete_stmt->execute()) {
            throw new Exception('Failed to remove from cart: ' . $delete_stmt->error);
        }
        $delete_stmt->close();
        ob_clean();
        echo json_encode(['status' => 'success', 'message' => 'Removed from cart.']);
    } elseif ($action === 'clear') {
        $clear_stmt = $conn->prepare("DELETE FROM cart WHERE $condition");
        if (!$clear_stmt->execute()) {
            throw new Exception('Failed to clear cart: ' . $clear_stmt->error);
        }
        $clear_stmt->close();
        ob_clean();
        echo json_encode(['status' => 'success', 'message' => 'Cart cleared.']);
    } elseif ($action === 'fetch') {
        $fetch_stmt = $conn->prepare("
            SELECT c.id, c.work_id, c.quantity, w.title, w.price, w.image, w.stock
            FROM cart c
            JOIN works w ON c.work_id = w.id
            WHERE $condition AND w.status = 1
        ");
        if (!$fetch_stmt->execute()) {
            throw new Exception('Failed to fetch cart: ' . $fetch_stmt->error);
        }
        $result = $fetch_stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        $fetch_stmt->close();
        ob_clean();
        echo json_encode(['status' => 'success', 'items' => $items]);
    }
} catch (Exception $e) {
    error_log('Cart action error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
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
?>