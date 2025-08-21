<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
require_once '../../app/db.php'; // adjust path if needed

function send_response($status, $message, $redirect = '', $extra = [])
{
    echo json_encode(array_merge([
        'status' => $status,
        'message' => $message,
        'redirect' => $redirect,
    ], $extra));
    exit;
}

function clean_input($data)
{
    return htmlspecialchars(trim($data));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response('error', 'Invalid request method');
}

$action = $_POST['action'] ?? '';
$user_id = intval($_SESSION['user_id'] ?? 0);

switch ($action) {
    case 'create_work':
        $title = clean_input($_POST['title'] ?? '');
        $description = clean_input($_POST['description'] ?? '');
        $link = clean_input($_POST['link'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $rating = floatval($_POST['rating'] ?? 0);

        if (empty($title) || empty($description)) {
            send_response('error', 'Title and Description are required');
        }

        if ($price < 0) {
            send_response('error', 'Price cannot be negative');
        }
        if ($stock < 0) {
            send_response('error', 'Stock cannot be negative');
        }
        if ($rating < 0 || $rating > 5) {
            send_response('error', 'Rating must be between 0 and 5');
        }

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            send_response('error', 'Image is required and must be uploaded without errors');
        }

        $image = $_FILES['image'];
        $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed_ext)) {
            send_response('error', 'Invalid image format. Allowed: jpg, jpeg, png, gif, webp');
        }

        $new_filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '', basename($image['name']));
        $target_dir = "../../assets/img/works/";

        if (!is_dir($target_dir) && !mkdir($target_dir, 0755, true)) {
            send_response('error', 'Failed to create image directory');
        }

        $target_path = $target_dir . $new_filename;

        if (!move_uploaded_file($image['tmp_name'], $target_path)) {
            send_response('error', 'Failed to upload image. Please check folder permissions.');
        }

        $status = isset($_POST['status']) && ($_POST['status'] === 'on' || $_POST['status'] == 1) ? 1 : 0;

        $sql = "INSERT INTO works (title, description, link, image, status, price, stock, rating, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            send_response('error', 'Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("ssssiidi", $title, $description, $link, $new_filename, $status, $price, $stock, $rating);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $conn->query("INSERT INTO activity_logs (user_id, action, description, created_at) VALUES ($user_id, 'Create Work', 'Created work: " . addslashes($title) . "', NOW())");
            send_response('success', 'Work created successfully', '/task-project/admin/works.php');
        } else {
            send_response('error', 'Database error: ' . $stmt->error);
        }
        break;

    case 'update_work':
        $id = intval($_POST['id'] ?? 0);
        $title = clean_input($_POST['title'] ?? '');
        $description = clean_input($_POST['description'] ?? '');
        $link = clean_input($_POST['link'] ?? '');
        $old_image = $_POST['old_image'] ?? '';
        $price = floatval($_POST['price'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $rating = floatval($_POST['rating'] ?? 0);

        if (empty($id) || empty($title) || empty($description)) {
            send_response('error', 'ID, Title, and Description are required');
        }

        if ($price < 0) {
            send_response('error', 'Price cannot be negative');
        }
        if ($stock < 0) {
            send_response('error', 'Stock cannot be negative');
        }
        if ($rating < 0 || $rating > 5) {
            send_response('error', 'Rating must be between 0 and 5');
        }

        $new_image_name = $old_image;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = $_FILES['image'];
            $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowed_ext)) {
                send_response('error', 'Invalid image format. Allowed: jpg, jpeg, png, gif, webp');
            }

            $new_image_name = time() . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '', basename($image['name']));
            $target_dir = "../../assets/img/works/";

            if (!is_dir($target_dir) && !mkdir($target_dir, 0755, true)) {
                send_response('error', 'Failed to create image directory');
            }

            $target_path = $target_dir . $new_image_name;

            if (!move_uploaded_file($image['tmp_name'], $target_path)) {
                send_response('error', 'Failed to upload new image.');
            }

            // Delete old image if exists
            if (!empty($old_image) && file_exists($target_dir . $old_image)) {
                @unlink($target_dir . $old_image);
            }
        }

        $status = isset($_POST['status']) && ($_POST['status'] === 'on' || $_POST['status'] == 1) ? 1 : 0;

        $sql = "UPDATE works SET title = ?, description = ?, link = ?, image = ?, status = ?, price = ?, stock = ?, rating = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            send_response('error', 'Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("ssssiidii", $title, $description, $link, $new_image_name, $status, $price, $stock, $rating, $id);
        $stmt->execute();

        if ($stmt->affected_rows >= 0) {
            $conn->query("INSERT INTO activity_logs (user_id, action, description, created_at) VALUES ($user_id, 'Update Work', 'Updated work: " . addslashes($title) . "', NOW())");
            send_response('success', 'Work updated successfully', '/task-project/admin/works.php');
        } else {
            send_response('error', 'Database error: ' . $stmt->error);
        }
        break;

    case 'delete_work':
        $id = intval($_POST['id'] ?? 0);

        if (empty($id)) {
            send_response('error', 'Invalid ID');
        }

        // Step 1: Delete related records from 'cart', 'order_items', and 'wishlist' first.
        $tables = [
            'cart' => 'work_id',
            'order_items' => 'work_id',
            'wishlist' => 'work_id'
        ];

        foreach ($tables as $table => $column) {
            $deleteSql = "DELETE FROM $table WHERE $column = ?";
            $stmt = $conn->prepare($deleteSql);
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                send_response('error', 'Error deleting from ' . $table);
            }
        }

        // Step 2: Delete the associated image if exists
        $sql = "SELECT title, image FROM works WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $work = $result->fetch_assoc();

        if ($work && !empty($work['image'])) {
            $image_path = "../../assets/img/works/" . $work['image'];
            if (file_exists($image_path)) {
                @unlink($image_path);
            }
        }

        // Step 3: Now delete the work from the 'works' table
        $deleteWorkSql = "DELETE FROM works WHERE id = ?";
        $deleteWorkStmt = $conn->prepare($deleteWorkSql);
        $deleteWorkStmt->bind_param("i", $id);
        if ($deleteWorkStmt->execute()) {
            $conn->query("INSERT INTO activity_logs (user_id, action, description, created_at) 
                      VALUES ($user_id, 'Delete Work', 'Deleted work: " . addslashes($work['title'] ?? 'ID ' . $id) . "', NOW())");
            send_response('success', 'Work deleted successfully');
        } else {
            send_response('error', 'Failed to delete work');
        }
        break;

    case 'toggle_status':
        // ✅ FIXED: no need to pass "status" from client
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            send_response('error', 'Invalid parameters');
        }

        // Get current status
        $stmt = $conn->prepare("SELECT title, status FROM works WHERE id = ?");
        if (!$stmt) {
            send_response('error', 'Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            send_response('error', 'Query failed');
        }
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            send_response('error', 'Work not found');
        }
        $row = $res->fetch_assoc();
        $current_status = (int) ($row['status'] ?? 0);

        // Flip
        $new_status = $current_status === 1 ? 0 : 1;

        // Update
        $up = $conn->prepare("UPDATE works SET status = ? WHERE id = ?");
        if (!$up) {
            send_response('error', 'Prepare failed: ' . $conn->error);
        }
        $up->bind_param("ii", $new_status, $id);
        if (!$up->execute()) {
            send_response('error', 'Failed to update status');
        }

        // Log
        $status_text = $new_status ? 'Active' : 'Inactive';
        $conn->query("INSERT INTO activity_logs (user_id, action, description, created_at) 
                      VALUES ($user_id, 'Toggle Work Status', 'Changed status of work: " . addslashes($row['title'] ?? ('ID ' . $id)) . " to $status_text', NOW())");

        send_response('success', 'Status updated successfully', '', ['new_status' => $new_status]);
        break;

    case 'update_inventory':
        $id = intval($_POST['id'] ?? 0);
        $field = clean_input($_POST['field'] ?? '');
        $value = $_POST['value'] ?? '';

        if ($id <= 0 || empty($field)) {
            send_response('error', 'Invalid parameters');
        }

        // Validate field and value
        switch ($field) {
            case 'price':
                $value = floatval($value);
                if ($value < 0) {
                    send_response('error', 'Price cannot be negative');
                }
                break;
            case 'stock':
                $value = intval($value);
                if ($value < 0) {
                    send_response('error', 'Stock cannot be negative');
                }
                break;
            case 'rating':
                $value = floatval($value);
                if ($value < 0 || $value > 5) {
                    send_response('error', 'Rating must be between 0 and 5');
                }
                break;
            default:
                send_response('error', 'Invalid field');
        }

        // Get work title for logging
        $stmt = $conn->prepare("SELECT title FROM works WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $work = $result->fetch_assoc();

        if (!$work) {
            send_response('error', 'Work not found');
        }

        // Update the field
        $sql = "UPDATE works SET $field = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($field === 'stock') {
            $stmt->bind_param("ii", $value, $id);
        } else {
            $stmt->bind_param("di", $value, $id);
        }

        if ($stmt->execute()) {
            $conn->query("INSERT INTO activity_logs (user_id, action, description, created_at) 
                          VALUES ($user_id, 'Update Inventory', 'Updated " . ucfirst($field) . " for work: " . addslashes($work['title']) . " to $value', NOW())");
            send_response('success', ucfirst($field) . ' updated successfully', '', ['new_value' => $value]);
        } else {
            send_response('error', 'Failed to update ' . $field);
        }
        break;

    case 'bulk_update_inventory':
        $updates = $_POST['updates'] ?? [];

        if (empty($updates) || !is_array($updates)) {
            send_response('error', 'No updates provided');
        }

        $success_count = 0;
        $error_count = 0;
        $errors = [];

        foreach ($updates as $update) {
            $id = intval($update['id'] ?? 0);
            $price = floatval($update['price'] ?? 0);
            $stock = intval($update['stock'] ?? 0);
            $rating = floatval($update['rating'] ?? 0);

            if ($id <= 0) {
                $error_count++;
                $errors[] = "Invalid ID: $id";
                continue;
            }

            // Validate values
            if ($price < 0 || $stock < 0 || $rating < 0 || $rating > 5) {
                $error_count++;
                $errors[] = "Invalid values for work ID: $id";
                continue;
            }

            // Update the work
            $stmt = $conn->prepare("UPDATE works SET price = ?, stock = ?, rating = ? WHERE id = ?");
            $stmt->bind_param("didi", $price, $stock, $rating, $id);

            if ($stmt->execute()) {
                $success_count++;
                // Log the update
                $conn->query("INSERT INTO activity_logs (user_id, action, description, created_at) 
                              VALUES ($user_id, 'Bulk Update Inventory', 'Updated inventory for work ID: $id', NOW())");
            } else {
                $error_count++;
                $errors[] = "Failed to update work ID: $id";
            }
        }

        $message = "Updated $success_count items successfully";
        if ($error_count > 0) {
            $message .= ", $error_count failed";
        }

        send_response('success', $message, '', [
            'success_count' => $success_count,
            'error_count' => $error_count,
            'errors' => $errors
        ]);
        break;

    default:
        send_response('error', 'Invalid action');
}
