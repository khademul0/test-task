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

        if (empty($title) || empty($description)) {
            send_response('error', 'Title and Description are required');
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

        $sql = "INSERT INTO works (title, description, link, image, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            send_response('error', 'Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("ssssi", $title, $description, $link, $new_filename, $status);
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

        if (empty($id) || empty($title) || empty($description)) {
            send_response('error', 'ID, Title, and Description are required');
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
                unlink($target_dir . $old_image);
            }
        }

        $status = isset($_POST['status']) && ($_POST['status'] === 'on' || $_POST['status'] == 1) ? 1 : 0;

        $sql = "UPDATE works SET title = ?, description = ?, link = ?, image = ?, status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            send_response('error', 'Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("ssssii", $title, $description, $link, $new_image_name, $status, $id);
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

        $sql = "SELECT title, image FROM works WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            send_response('error', 'Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $work = $res->fetch_assoc();

        if ($work && !empty($work['image'])) {
            $image_path = "../../assets/img/works/" . $work['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        $sql = "DELETE FROM works WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            send_response('error', 'Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $conn->query("INSERT INTO activity_logs (user_id, action, description, created_at) VALUES ($user_id, 'Delete Work', 'Deleted work: " . addslashes($work['title'] ?? 'ID ' . $id) . "', NOW())");
            send_response('success', 'Work deleted successfully');
        } else {
            send_response('error', 'Work not found or could not be deleted');
        }
        break;

    case 'toggle_status':
        $id = intval($_POST['id'] ?? 0);
        $current_status = intval($_POST['status'] ?? -1);

        if ($id <= 0 || ($current_status !== 0 && $current_status !== 1)) {
            send_response('error', 'Invalid parameters');
        }

        $new_status = $current_status === 1 ? 0 : 1;

        $sql = "SELECT title FROM works WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $work = $res->fetch_assoc();

        $sql = "UPDATE works SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            send_response('error', 'Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("ii", $new_status, $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $status_text = $new_status ? 'Active' : 'Inactive';
            $conn->query("INSERT INTO activity_logs (user_id, action, description, created_at) VALUES ($user_id, 'Toggle Work Status', 'Changed status of work: " . addslashes($work['title'] ?? 'ID ' . $id) . " to $status_text', NOW())");
            send_response('success', 'Status updated successfully', '', ['new_status' => $new_status]);
        } else {
            send_response('error', 'Failed to update status');
        }
        break;

    default:
        send_response('error', 'Invalid action');
}
?>