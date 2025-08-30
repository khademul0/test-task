<?php
session_start();
require_once 'app/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: admin/login.php');
    exit;
}

$user_id = intval($_SESSION['user_id']);

// Get user information
$stmt = $conn->prepare("SELECT name, email, photo FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get order history with items
$orders_stmt = $conn->prepare("
    SELECT o.*, 
           GROUP_CONCAT(oi.title SEPARATOR ', ') as items,
           COUNT(oi.id) as item_count
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.created_at DESC
");
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$orders = $orders_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get contact message history
$contacts_stmt = $conn->prepare("
    SELECT * FROM contact_submissions 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$contacts_stmt->bind_param("i", $user_id);
$contacts_stmt->execute();
$contacts = $contacts_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get recent activity
$activity_stmt = $conn->prepare("
    SELECT * FROM activity_logs 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 10
");
$activity_stmt->bind_param("i", $user_id);
$activity_stmt->execute();
$activities = $activity_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - NetaCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;900&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        /* Applied portfolio's glassy design system with enhanced colors and effects */
        :root {
            --font-heading: 'Montserrat', sans-serif;
            --font-body: 'Open Sans', sans-serif;
            --shadow-soft: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 8px 30px rgba(0, 0, 0, 0.12);
            --gradient-primary: linear-gradient(135deg, #84cc16 0%, #65a30d 100%);
            --gradient-card: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
        }

        body {
            font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #ecfeff 0%, #cffafe 25%, #a5f3fc 50%, #67e8f9 75%, #22d3ee 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: var(--font-heading);
            font-weight: 700;
        }

        /* Enhanced navbar with light blue glass effect from portfolio */
        .navbar-modern {
            background: rgba(173, 216, 230, 0.75);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(135, 206, 235, 0.3);
            box-shadow: 0 1px 20px rgba(70, 130, 180, 0.15);
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-family: var(--font-heading);
            font-weight: 900;
            font-size: 1.5rem;
            color: var(--color-primary) !important;
        }

        .nav-link {
            font-weight: 500;
            color: var(--color-foreground) !important;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--color-primary) !important;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 3px;
            background: var(--gradient-primary);
            border-radius: 2px;
        }

        /* Enhanced user avatar with colorful glassy styling */
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 15px rgba(22, 78, 99, 0.2);
        }

        .user-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(22, 78, 99, 0.3);
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .action-btn {
            background: var(--glass-bg);
            border: 2px solid var(--color-border);
            border-radius: 12px;
            padding: 10px 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            color: var(--color-foreground);
            text-decoration: none;
        }

        .action-btn:hover {
            background: var(--color-primary);
            border-color: var(--color-primary);
            color: white;
            transform: translateY(-2px);
            text-decoration: none;
        }

        /* Enhanced glassy dashboard container with prominent light blue glass effect */
        .dashboard-container {
            backdrop-filter: blur(25px);
            background: rgba(173, 216, 230, 0.85);
            border: 1px solid rgba(135, 206, 235, 0.4);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(70, 130, 180, 0.25);
            margin: 2rem auto;
            max-width: 1200px;
            overflow: hidden;
        }

        /* Colorful gradient header with enhanced glass effects */
        .dashboard-header {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 50%, #0e7490 100%);
            color: white;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .dashboard-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #f59e0b 0%, #84cc16 50%, #06b6d4 100%);
        }

        .profile-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Enhanced profile avatar with warm gradient */
        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.3), rgba(132, 204, 22, 0.3));
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 600;
            border: 3px solid rgba(255, 255, 255, 0.4);
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        /* Enhanced navigation with glass effects and colorful accents */
        .dashboard-nav {
            background: linear-gradient(135deg, rgba(248, 250, 252, 0.9) 0%, rgba(241, 245, 249, 0.9) 100%);
            backdrop-filter: blur(15px);
            padding: 1rem 2rem;
            border-bottom: 1px solid rgba(59, 130, 246, 0.2);
        }

        .nav-tabs {
            border: none;
        }

        .nav-tabs .nav-link {
            border: none;
            color: var(--color-foreground);
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            margin-right: 0.5rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(59, 130, 246, 0.1);
        }

        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(6, 182, 212, 0.3);
        }

        .nav-tabs .nav-link:hover {
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.1) 0%, rgba(8, 145, 178, 0.1) 100%);
            color: var(--color-primary);
            transform: translateY(-1px);
        }

        .dashboard-content {
            padding: 2rem;
            background: rgba(248, 250, 252, 0.5);
            backdrop-filter: blur(10px);
        }

        /* Colorful stat cards with warm gradient backgrounds */
        .stat-card {
            background: linear-gradient(135deg, #fef3c7 0%, #fef9e7 50%, #ffffff 100%);
            border: 1px solid rgba(251, 191, 36, 0.2);
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.1);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #f59e0b 0%, #84cc16 50%, #06b6d4 100%);
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 30px rgba(251, 191, 36, 0.2);
            border-color: #f59e0b;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 50%, #ffffff 100%);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #f59e0b 0%, #84cc16 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        /* Enhanced order cards with glass effects and colorful accents */
        .order-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(59, 130, 246, 0.15);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .order-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #06b6d4 0%, #0891b2 50%, #0e7490 100%);
        }

        .order-card:hover {
            box-shadow: 0 8px 30px rgba(59, 130, 246, 0.2);
            transform: translateY(-4px);
            border-color: #3b82f6;
        }

        /* Enhanced status badges with gradient backgrounds */
        .status-badge {
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            backdrop-filter: blur(10px);
        }

        .status-pending {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
            border: 1px solid rgba(251, 191, 36, 0.3);
        }

        .status-processing {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .status-shipped {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .status-delivered {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #166534;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .status-cancelled {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        /* Enhanced contact cards with glass effects */
        .contact-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(147, 197, 253, 0.2);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .contact-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(147, 197, 253, 0.15);
        }

        /* Enhanced buttons with gradient effects */
        .btn-primary {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            border: none;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(6, 182, 212, 0.2);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(6, 182, 212, 0.3);
        }

        /* Enhanced profile upload section with colorful glass design */
        .profile-upload-section {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(248, 250, 252, 0.9) 100%);
            backdrop-filter: blur(20px);
            border: 2px dashed rgba(59, 130, 246, 0.3);
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .upload-area {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .upload-area:hover {
            border-color: #3b82f6;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(147, 197, 253, 0.05) 100%);
            transform: translateY(-2px);
        }

        /* Enhanced timeline with colorful accents */
        .timeline {
            position: relative;
            padding-left: 2rem;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 0.75rem;
            top: 0;
            bottom: 0;
            width: 3px;
            background: linear-gradient(180deg, #06b6d4 0%, #0891b2 50%, #0e7490 100%);
            border-radius: 2px;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 1.5rem;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            padding: 1rem;
            border-radius: 12px;
            border: 1px solid rgba(59, 130, 246, 0.1);
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -1.85rem;
            top: 1rem;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            border: 3px solid white;
            box-shadow: 0 0 0 2px #06b6d4;
        }

        /* Enhanced form controls with glass effects */
        .form-control {
            border: 2px solid rgba(59, 130, 246, 0.2);
            border-radius: 12px;
            padding: 0.75rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
        }

        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: rgba(255, 255, 255, 0.95);
        }

        /* Enhanced notification system with glass effects */
        .notification {
            backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            border-radius: 12px !important;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
        }

        .badge-modern {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--gradient-primary);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            border: 2px solid white;
        }
    </style>
</head>

<body>
    <!-- Added portfolio-style navbar with user dashboard connection -->
    <nav class="navbar navbar-expand-lg navbar-modern sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="portfolio.php">
                <img src="assets/images/logo.png" alt="Netacart Logo" height="40" class="me-2">
                Netacart
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <i class="bi bi-list fs-4"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                    <li class="nav-item">
                        <a class="nav-link" href="portfolio.php">
                            <i class="bi bi-shop me-1"></i> Shop
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">
                            <i class="bi bi-envelope me-1"></i> Contact
                        </a>
                    </li>
                </ul>

                <div class="d-flex gap-2 align-items-center">
                    <div class="user-avatar">
                        <?php if ($user['photo'] && file_exists("assets/images/profiles/" . $user['photo'])): ?>
                            <img src="assets/images/profiles/<?= htmlspecialchars($user['photo']) ?>" alt="Profile Photo">
                        <?php else: ?>
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="dashboard-container">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="profile-section">
                        <div class="profile-avatar">
                            <?php if ($user['photo'] && file_exists("assets/images/profiles/" . $user['photo'])): ?>
                                <img src="assets/images/profiles/<?= htmlspecialchars($user['photo']) ?>" alt="Profile Photo">
                            <?php else: ?>
                                <?= strtoupper(substr($user['name'], 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h2 class="mb-1">Welcome, <?= htmlspecialchars($user['name']) ?>!</h2>
                            <p class="mb-0 opacity-75"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                    </div>
                    <a href="portfolio.php" class="btn btn-light">
                        <i class="bi bi-arrow-left me-2"></i>Back to Shop
                    </a>
                </div>
            </div>

            <!-- Dashboard Navigation -->
            <div class="dashboard-nav">
                <ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                            <i class="bi bi-grid me-2"></i>Overview
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab">
                            <i class="bi bi-bag me-2">
                                <span id="orderCount" class="badge-modern"><?= count($orders) ?></span></i>Orders
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="contacts-tab" data-bs-toggle="tab" data-bs-target="#contacts" type="button" role="tab">
                            <i class="bi bi-envelope me-2"><span class="badge-modern"><?= count(array_filter($orders, fn($o) => $o['status'] === 'Pending')) ?></span></i></i>Messages
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                            <i class="bi bi-person me-2"></i>Profile
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <div class="tab-content" id="dashboardTabContent">
                    <!-- Overview Tab -->
                    <div class="tab-pane fade show active" id="overview" role="tabpanel">

                        <div class="row">
                            <div class="col-md-8">
                                <h4 class="mb-3">Recent Orders</h4>
                                <?php if (empty($orders)): ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-bag-x display-1 text-muted"></i>
                                        <p class="text-muted mt-3">No orders found</p>
                                        <a href="portfolio.php" class="btn btn-primary">Start Shopping</a>
                                    </div>
                                <?php else: ?>
                                    <?php foreach (array_slice($orders, 0, 3) as $order): ?>
                                        <div class="order-card">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">Order #<?= $order['id'] ?></h6>
                                                    <p class="text-muted mb-2"><?= htmlspecialchars($order['items']) ?></p>
                                                    <small class="text-muted"><?= date('M j, Y', strtotime($order['created_at'])) ?></small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="status-badge status-<?= strtolower($order['status']) ?>">
                                                        <?= $order['status'] ?>
                                                    </span>
                                                    <div class="mt-2">
                                                        <strong>$<?= number_format($order['total'], 2) ?></strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <h4 class="mb-3">Recent Activity</h4>
                                <div class="timeline">
                                    <?php foreach ($activities as $activity): ?>
                                        <div class="timeline-item">
                                            <div class="small text-muted"><?= date('M j, Y g:i A', strtotime($activity['created_at'])) ?></div>
                                            <div><?= htmlspecialchars($activity['action']) ?></div>
                                            <?php if ($activity['description']): ?>
                                                <small class="text-muted"><?= htmlspecialchars($activity['description']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Orders Tab -->
                    <div class="tab-pane fade" id="orders" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4>Order History</h4>
                            <div class="d-flex gap-2">
                                <select class="form-select" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Processing">Processing</option>
                                    <option value="Shipped">Shipped</option>
                                    <option value="Delivered">Delivered</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>

                        <?php if (empty($orders)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-bag-x display-1 text-muted"></i>
                                <p class="text-muted mt-3">No orders found</p>
                                <a href="portfolio.php" class="btn btn-primary">Start Shopping</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <div class="order-card" data-status="<?= $order['status'] ?>">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h5 class="mb-1">Order #<?= $order['id'] ?></h5>
                                                    <p class="text-muted mb-0">Placed on <?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?></p>
                                                </div>
                                                <span class="status-badge status-<?= strtolower($order['status']) ?>">
                                                    <?= $order['status'] ?>
                                                </span>
                                            </div>
                                            <div class="mb-3">
                                                <strong>Items:</strong> <?= htmlspecialchars($order['items']) ?>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <small class="text-muted">Delivery Address:</small>
                                                    <p class="mb-0"><?= htmlspecialchars($order['address']) ?></p>
                                                </div>
                                                <div class="col-sm-6">
                                                    <small class="text-muted">Payment Method:</small>
                                                    <p class="mb-0"><?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <div class="mb-3">
                                                <h4 class="text-primary">$<?= number_format($order['total'], 2) ?></h4>
                                                <small class="text-muted"><?= $order['item_count'] ?> item(s)</small>
                                            </div>
                                            <div class="d-flex flex-column gap-2">
                                                <button class="btn btn-outline-primary btn-sm" onclick="generateInvoice(<?= $order['id'] ?>)">
                                                    <i class="bi bi-file-earmark-pdf me-1"></i>View Invoice
                                                </button>
                                                <?php // Added cancel button for orders that can be cancelled 
                                                ?>
                                                <?php if (in_array($order['status'], ['Pending', 'Processing'])): ?>
                                                    <button class="btn btn-outline-danger btn-sm" onclick="cancelOrder(<?= $order['id'] ?>)" id="cancel-btn-<?= $order['id'] ?>">
                                                        <i class="bi bi-x-circle me-1"></i>Cancel Order
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Messages Tab -->
                    <div class="tab-pane fade" id="contacts" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4>Message History</h4>
                            <a href="contact.php" class="btn btn-primary">
                                <i class="bi bi-plus me-1"></i>Send New Message
                            </a>
                        </div>

                        <?php if (empty($contacts)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-envelope-x display-1 text-muted"></i>
                                <p class="text-muted mt-3">No messages found</p>
                                <a href="contact.php" class="btn btn-primary">Send Your First Message</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($contacts as $contact): ?>
                                <div class="contact-card">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($contact['subject']) ?></h6>
                                            <small class="text-muted">Sent on <?= date('F j, Y \a\t g:i A', strtotime($contact['created_at'])) ?></small>
                                        </div>
                                        <span class="status-badge status-<?= $contact['status'] === 'pending' ? 'pending' : 'delivered' ?>">
                                            <?= ucfirst($contact['status']) ?>
                                        </span>
                                    </div>
                                    <p class="mb-0"><?= nl2br(htmlspecialchars($contact['message'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Profile Tab -->
                    <div class="tab-pane fade" id="profile" role="tabpanel">
                        <h4 class="mb-4">Profile Settings</h4>

                        <!-- Enhanced profile picture upload with proper functionality -->
                        <div class="profile-upload-section">
                            <div class="upload-area" onclick="document.getElementById('profilePhotoInput').click()">
                                <div class="profile-avatar mx-auto mb-3" style="width: 120px; height: 120px; font-size: 3rem;">
                                    <?php if ($user['photo'] && file_exists("assets/images/profiles/" . $user['photo'])): ?>
                                        <img src="assets/images/profiles/<?= htmlspecialchars($user['photo']) ?>" alt="Profile Photo">
                                    <?php else: ?>
                                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                    <?php endif; ?>
                                </div>
                                <h6>Click to change profile picture</h6>
                                <p class="text-muted mb-0">JPG, PNG or GIF (max 2MB)</p>
                            </div>
                            <input type="file" id="profilePhotoInput" accept="image/*" style="display: none;" onchange="uploadProfilePhoto(this)">
                        </div>

                        <!-- Profile Form -->
                        <form id="profileForm" onsubmit="updateProfile(event)">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <h6 class="mb-3">Change Password (Optional)</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i>Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Status filter functionality
        document.getElementById('statusFilter').addEventListener('change', function() {
            const filterValue = this.value;
            const orderCards = document.querySelectorAll('.order-card');

            orderCards.forEach(card => {
                if (filterValue === '' || card.dataset.status === filterValue) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        function cancelOrder(orderId) {
            // Show confirmation dialog
            if (!confirm('Are you sure you want to cancel this order? This action cannot be undone.')) {
                return;
            }

            const cancelBtn = document.getElementById(`cancel-btn-${orderId}`);
            const originalText = cancelBtn.innerHTML;

            // Disable button and show loading state
            cancelBtn.disabled = true;
            cancelBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Cancelling...';

            // Create form data
            const formData = new FormData();
            formData.append('order_id', orderId);

            // Send cancellation request
            fetch('cancel_order.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        showNotification('Order cancelled successfully!', 'success');

                        // Update the order card UI
                        const orderCard = cancelBtn.closest('.order-card');
                        const statusBadge = orderCard.querySelector('.status-badge');

                        // Update status badge
                        statusBadge.className = 'status-badge status-cancelled';
                        statusBadge.textContent = 'Cancelled';

                        // Update data attribute for filtering
                        orderCard.setAttribute('data-status', 'Cancelled');

                        // Remove cancel button
                        cancelBtn.remove();

                    } else {
                        throw new Error(data.message || 'Failed to cancel order');
                    }
                })
                .catch(error => {
                    console.error('Cancellation error:', error);
                    showNotification(error.message || 'Failed to cancel order. Please try again.', 'error');

                    // Restore button state
                    cancelBtn.disabled = false;
                    cancelBtn.innerHTML = originalText;
                });
        }

        function uploadProfilePhoto(input) {
            if (input.files && input.files[0]) {
                const formData = new FormData();
                formData.append('photo', input.files[0]);
                formData.append('action', 'update_profile');
                formData.append('name', document.getElementById('name').value);
                formData.append('email', document.getElementById('email').value);

                showNotification('Uploading photo...', 'info');

                fetch('admin/profile_action.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showNotification('Profile photo updated successfully!', 'success');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            showNotification(data.message || 'Failed to upload photo', 'error');
                        }
                    })
                    .catch(error => {
                        showNotification('Upload failed: ' + error, 'error');
                    });
            }
        }

        // Profile form update
        function updateProfile(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            formData.append('action', 'update_profile');

            showNotification('Updating profile...', 'info');

            fetch('admin/profile_action.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showNotification('Profile updated successfully!', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showNotification(data.message || 'Failed to update profile', 'error');
                    }
                })
                .catch(error => {
                    showNotification('Update failed: ' + error, 'error');
                });
        }

        // Generate invoice
        function generateInvoice(orderId) {
            window.open(`invoice.php?order_id=${orderId}`, '_blank');
        }

        function showNotification(message, type = 'info') {
            const existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach(n => n.remove());

            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} position-fixed notification`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px;';

            const icon = type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle';
            notification.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="bi bi-${icon} me-2"></i>
                    <span>${message}</span>
                    <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
                </div>
            `;

            document.body.appendChild(notification);

            // Auto-remove after 5 seconds for error messages, 3 seconds for others
            const timeout = type === 'error' ? 5000 : 3000;
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, timeout);
        }
    </script>
</body>

</html>