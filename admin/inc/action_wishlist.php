<?php
session_start();
require_once '../../app/db.php';

header('Content-Type: application/json');

// Enable debug mode (set to false in production)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please log in to manage your wishlist.']);
    exit;
}

$user_id = intval($_SESSION['user_id']);
if ($user_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid user session.']);
    exit;
}

// Validate request method and action
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method or action missing.']);
    exit;
}

$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);

try {
    // Database connection check
    if (!$conn) {
        throw new Exception('Database connection failed.');
    }

    if ($action === 'add') {
        if (!isset($_POST['work_id']) || !is_numeric($_POST['work_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid work ID.']);
            exit;
        }
        $work_id = intval($_POST['work_id']);
        
        // Validate work exists and is active
        $check_stmt = $conn->prepare("SELECT id FROM works WHERE id = ? AND status = 1");
        $check_stmt->bind_param("i", $work_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows === 0) {
            error_log("Add wishlist: Work ID $work_id does not exist or is not active.");
            echo json_encode(['status' => 'error', 'message' => 'Product does not exist or is not active.']);
            $check_stmt->close();
            exit;
        }
        $check_stmt->close();

        // Check if already in wishlist
        $check_wishlist = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND work_id = ?");
        $check_wishlist->bind_param("ii", $user_id, $work_id);
        $check_wishlist->execute();
        $wishlist_result = $check_wishlist->get_result();
        
        if ($wishlist_result->num_rows > 0) {
            error_log("Add wishlist: Work ID $work_id already in wishlist for user $user_id.");
            echo json_encode(['status' => 'info', 'message' => 'Already in wishlist.']);
            $check_wishlist->close();
            exit;
        }
        $check_wishlist->close();

        // Add to wishlist
        $stmt = $conn->prepare("INSERT INTO wishlist (user_id, work_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $work_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to add to wishlist: ' . $stmt->error);
        }
        error_log("Add wishlist: Successfully added work_id $work_id for user $user_id.");
        $stmt->close();

        echo json_encode([
            'status' => 'success',
            'message' => 'Added to wishlist.'
        ]);
    } elseif ($action === 'remove') {
        if (!isset($_POST['work_id']) || !is_numeric($_POST['work_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid work ID.']);
            exit;
        }
        $work_id = intval($_POST['work_id']);

        $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND work_id = ?");
        $stmt->bind_param("ii", $user_id, $work_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to remove from wishlist: ' . $stmt->error);
        }
        $affected_rows = $stmt->affected_rows;
        error_log("Remove wishlist: Removed work_id $work_id for user $user_id. Affected rows: $affected_rows");
        $stmt->close();

        echo json_encode([
            'status' => 'success',
            'message' => 'Removed from wishlist.'
        ]);
    } elseif ($action === 'fetch') {
        $stmt = $conn->prepare("
            SELECT w.id, w.work_id, wk.title, wk.price, wk.image 
            FROM wishlist w 
            JOIN works wk ON w.work_id = wk.id 
            WHERE w.user_id = ? AND wk.status = 1
        ");
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to fetch wishlist: ' . $stmt->error);
        }
        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = [
                'id' => $row['id'],
                'work_id' => (int)$row['work_id'],
                'title' => $row['title'],
                'price' => (float)$row['price'],
                'image' => $row['image']
            ];
        }
        error_log("Fetch wishlist: Retrieved " . count($items) . " items for user $user_id.");
        $stmt->close();

        echo json_encode([
            'status' => 'success',
            'items' => $items
        ]);
    } elseif ($action === 'clear') {
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to clear wishlist: ' . $stmt->error);
        }
        $affected_rows = $stmt->affected_rows;
        error_log("Clear wishlist: Cleared $affected_rows items for user $user_id.");
        $stmt->close();

        echo json_encode([
            'status' => 'success',
            'message' => 'Wishlist cleared.'
        ]);
    } elseif ($action === 'delete') {
        // New action for admin to delete specific wishlist item
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
            exit;
        }
        if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid wishlist item ID.']);
            exit;
        }
        $wishlist_id = intval($_POST['id']);

        $stmt = $conn->prepare("DELETE FROM wishlist WHERE id = ?");
        $stmt->bind_param("i", $wishlist_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to delete wishlist item: ' . $stmt->error);
        }
        $affected_rows = $stmt->affected_rows;
        $stmt->close();

        if ($affected_rows > 0) {
            error_log("Wishlist item deleted: ID=$wishlist_id by admin user {$_SESSION['user_id']}");
            echo json_encode([
                'status' => 'success',
                'message' => 'Wishlist item deleted successfully.'
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Wishlist item not found.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
    }
} catch (Exception $e) {
    error_log('Wishlist error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} finally {
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>