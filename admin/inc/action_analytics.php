<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
require_once '../../app/db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method or action missing.']);
    exit;
}

$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS);

try {
    switch ($action) {
        case 'get_dashboard_stats':
            // Get comprehensive dashboard statistics
            $stats = [];

            // Basic counts
            $stats['total_users'] = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
            $stats['active_users'] = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1")->fetch_assoc()['count'];
            $stats['total_works'] = $conn->query("SELECT COUNT(*) as count FROM works")->fetch_assoc()['count'];
            $stats['active_works'] = $conn->query("SELECT COUNT(*) as count FROM works WHERE status = 1")->fetch_assoc()['count'];
            $stats['total_orders'] = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
            $stats['pending_orders'] = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'Pending'")->fetch_assoc()['count'];
            $stats['total_revenue'] = $conn->query("SELECT COALESCE(SUM(total), 0) as revenue FROM orders")->fetch_assoc()['revenue'];
            $stats['total_cart_items'] = $conn->query("SELECT COUNT(*) as count FROM cart")->fetch_assoc()['count'];
            $stats['total_wishlist_items'] = $conn->query("SELECT COUNT(*) as count FROM wishlist")->fetch_assoc()['count'];
            $stats['total_contact_messages'] = $conn->query("SELECT COUNT(*) as count FROM contact_submissions")->fetch_assoc()['count'];
            $stats['pending_contacts'] = $conn->query("SELECT COUNT(*) as count FROM contact_submissions WHERE status = 'pending'")->fetch_assoc()['count'];

            echo json_encode(['status' => 'success', 'data' => $stats]);
            break;

        case 'get_sales_data':
            // Get monthly sales data for charts
            $period = $_POST['period'] ?? '12'; // months

            $sales_data = $conn->query("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as orders,
                    SUM(total) as revenue
                FROM orders 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL $period MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month ASC
            ")->fetch_all(MYSQLI_ASSOC);

            echo json_encode(['status' => 'success', 'data' => $sales_data]);
            break;

        case 'get_best_selling':
            // Get best selling products
            $limit = $_POST['limit'] ?? 10;

            $best_selling = $conn->query("
                SELECT 
                    w.id,
                    w.title,
                    w.image,
                    w.price,
                    SUM(oi.quantity) as total_sold,
                    SUM(oi.price * oi.quantity) as total_revenue
                FROM order_items oi
                JOIN works w ON oi.work_id = w.id
                GROUP BY oi.work_id
                ORDER BY total_sold DESC
                LIMIT $limit
            ")->fetch_all(MYSQLI_ASSOC);

            echo json_encode(['status' => 'success', 'data' => $best_selling]);
            break;

        case 'get_order_status_distribution':
            // Get order status distribution
            $order_status = $conn->query("
                SELECT status, COUNT(*) as count 
                FROM orders 
                GROUP BY status
            ")->fetch_all(MYSQLI_ASSOC);

            echo json_encode(['status' => 'success', 'data' => $order_status]);
            break;

        case 'get_recent_activities':
            // Get recent activity logs
            $limit = $_POST['limit'] ?? 20;

            $activities = $conn->query("
                SELECT al.*, u.name as user_name 
                FROM activity_logs al 
                LEFT JOIN users u ON al.user_id = u.id 
                ORDER BY al.created_at DESC 
                LIMIT $limit
            ")->fetch_all(MYSQLI_ASSOC);

            echo json_encode(['status' => 'success', 'data' => $activities]);
            break;

        case 'get_cart_data':
            // Get cart analytics
            $cart_data = [];

            // Cart items by user
            $cart_data['items_by_user'] = $conn->query("
                SELECT u.name, COUNT(c.id) as cart_items
                FROM cart c
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.user_id IS NOT NULL
                GROUP BY c.user_id
                ORDER BY cart_items DESC
                LIMIT 10
            ")->fetch_all(MYSQLI_ASSOC);

            // Most added to cart products
            $cart_data['popular_cart_items'] = $conn->query("
                SELECT w.title, w.image, COUNT(c.id) as times_added
                FROM cart c
                JOIN works w ON c.work_id = w.id
                GROUP BY c.work_id
                ORDER BY times_added DESC
                LIMIT 10
            ")->fetch_all(MYSQLI_ASSOC);

            echo json_encode(['status' => 'success', 'data' => $cart_data]);
            break;

        case 'get_wishlist_data':
            // Get wishlist analytics
            $wishlist_data = [];

            // Wishlist items by user
            $wishlist_data['items_by_user'] = $conn->query("
                SELECT u.name, COUNT(w.id) as wishlist_items
                FROM wishlist w
                JOIN users u ON w.user_id = u.id
                GROUP BY w.user_id
                ORDER BY wishlist_items DESC
                LIMIT 10
            ")->fetch_all(MYSQLI_ASSOC);

            // Most wishlisted products
            $wishlist_data['popular_wishlist_items'] = $conn->query("
                SELECT wk.title, wk.image, COUNT(w.id) as times_wishlisted
                FROM wishlist w
                JOIN works wk ON w.work_id = wk.id
                GROUP BY w.work_id
                ORDER BY times_wishlisted DESC
                LIMIT 10
            ")->fetch_all(MYSQLI_ASSOC);

            echo json_encode(['status' => 'success', 'data' => $wishlist_data]);
            break;

        case 'get_contact_messages':
            // Get contact messages with pagination
            $page = $_POST['page'] ?? 1;
            $limit = $_POST['limit'] ?? 20;
            $offset = ($page - 1) * $limit;

            $messages = $conn->query("
                SELECT cs.*, u.name as user_name
                FROM contact_submissions cs
                LEFT JOIN users u ON cs.user_id = u.id
                ORDER BY cs.created_at DESC
                LIMIT $limit OFFSET $offset
            ")->fetch_all(MYSQLI_ASSOC);

            $total = $conn->query("SELECT COUNT(*) as count FROM contact_submissions")->fetch_assoc()['count'];

            echo json_encode([
                'status' => 'success',
                'data' => $messages,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($total / $limit),
                    'total_items' => $total
                ]
            ]);
            break;

        case 'get_user_analytics':
            // Get user registration analytics
            $user_analytics = [];

            // User registrations by month
            $user_analytics['registrations_by_month'] = $conn->query("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as registrations
                FROM users 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month ASC
            ")->fetch_all(MYSQLI_ASSOC);

            // Active vs inactive users
            $user_analytics['user_status'] = $conn->query("
                SELECT 
                    CASE WHEN is_active = 1 THEN 'Active' ELSE 'Inactive' END as status,
                    COUNT(*) as count
                FROM users
                GROUP BY is_active
            ")->fetch_all(MYSQLI_ASSOC);

            echo json_encode(['status' => 'success', 'data' => $user_analytics]);
            break;

        case 'get_revenue_analytics':
            // Get detailed revenue analytics
            $revenue_analytics = [];

            // Daily revenue for last 30 days
            $revenue_analytics['daily_revenue'] = $conn->query("
                SELECT 
                    DATE(created_at) as date,
                    SUM(total) as revenue,
                    COUNT(*) as orders
                FROM orders 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC
            ")->fetch_all(MYSQLI_ASSOC);

            // Revenue by payment method
            $revenue_analytics['revenue_by_payment'] = $conn->query("
                SELECT 
                    payment_method,
                    SUM(total) as revenue,
                    COUNT(*) as orders
                FROM orders
                GROUP BY payment_method
                ORDER BY revenue DESC
            ")->fetch_all(MYSQLI_ASSOC);

            echo json_encode(['status' => 'success', 'data' => $revenue_analytics]);
            break;

        case 'export_analytics':
            // Export analytics data (basic implementation)
            $export_type = $_POST['export_type'] ?? 'orders';
            $format = $_POST['format'] ?? 'json';

            $data = [];
            switch ($export_type) {
                case 'orders':
                    $data = $conn->query("
                        SELECT o.*, GROUP_CONCAT(oi.title) as items
                        FROM orders o
                        LEFT JOIN order_items oi ON o.id = oi.order_id
                        GROUP BY o.id
                        ORDER BY o.created_at DESC
                    ")->fetch_all(MYSQLI_ASSOC);
                    break;

                case 'users':
                    $data = $conn->query("
                        SELECT id, name, email, created_at, is_active
                        FROM users
                        ORDER BY created_at DESC
                    ")->fetch_all(MYSQLI_ASSOC);
                    break;

                case 'works':
                    $data = $conn->query("
                        SELECT id, title, price, stock, rating, status, created_at
                        FROM works
                        ORDER BY created_at DESC
                    ")->fetch_all(MYSQLI_ASSOC);
                    break;
            }

            echo json_encode(['status' => 'success', 'data' => $data, 'export_type' => $export_type]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action specified.']);
            break;
    }
} catch (Exception $e) {
    error_log('Analytics action error: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred.',
        'debug' => $e->getMessage()
    ]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
