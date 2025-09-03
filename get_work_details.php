<?php
session_start();
require_once 'app/db.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid work ID']);
    exit;
}

$work_id = intval($_GET['id']);

try {
    $stmt = $conn->prepare("
        SELECT w.*, c.name as category_name 
        FROM works w 
        LEFT JOIN categories c ON w.category_id = c.id 
        WHERE w.id = ? AND w.status = 1
    ");
    $stmt->bind_param("i", $work_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Work not found']);
        exit;
    }
    
    $work = $result->fetch_assoc();
    
    // Parse JSON fields
    $work['sub_images'] = !empty($work['sub_images']) ? json_decode($work['sub_images'], true) : [];
    $work['sizes'] = !empty($work['sizes']) ? json_decode($work['sizes'], true) : [];
    $work['options'] = !empty($work['options']) ? json_decode($work['options'], true) : [];
    
    // Ensure numeric fields are properly typed
    $work['price'] = floatval($work['price']);
    $work['stock'] = intval($work['stock']);
    $work['rating'] = floatval($work['rating']);
    
    echo json_encode([
        'status' => 'success',
        'data' => $work
    ]);
    
} catch (Exception $e) {
    error_log('Get work details error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
