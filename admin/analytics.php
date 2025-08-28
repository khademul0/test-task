<?php
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/role_check.php';

// Check if user is admin - redirect customers to portfolio
checkUserRole('admin');


// Fetch analytics data
try {
    // Basic statistics
    $total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
    $total_works = $conn->query("SELECT COUNT(*) as count FROM works")->fetch_assoc()['count'];
    $active_works = $conn->query("SELECT COUNT(*) as count FROM works WHERE status = 1")->fetch_assoc()['count'];
    $total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
    $total_revenue = $conn->query("SELECT COALESCE(SUM(total), 0) as revenue FROM orders")->fetch_assoc()['revenue'];
    $pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'Pending'")->fetch_assoc()['count'];
    $total_cart_items = $conn->query("SELECT COUNT(*) as count FROM cart")->fetch_assoc()['count'];
    $total_wishlist_items = $conn->query("SELECT COUNT(*) as count FROM wishlist")->fetch_assoc()['count'];
    $total_contact_messages = $conn->query("SELECT COUNT(*) as count FROM contact_submissions")->fetch_assoc()['count'];
    $pending_contacts = $conn->query("SELECT COUNT(*) as count FROM contact_submissions WHERE status = 'pending'")->fetch_assoc()['count'];

    // Recent activity logs
    $recent_activities = $conn->query("
        SELECT al.*, u.name as user_name 
        FROM activity_logs al 
        LEFT JOIN users u ON al.user_id = u.id 
        ORDER BY al.created_at DESC 
        LIMIT 10
    ")->fetch_all(MYSQLI_ASSOC);

    // Monthly sales data for chart
    $monthly_sales = $conn->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as orders,
            SUM(total) as revenue
        FROM orders 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
    ")->fetch_all(MYSQLI_ASSOC);

    // Best selling products
    $best_selling = $conn->query("
        SELECT 
            w.title,
            w.image,
            w.price,
            SUM(oi.quantity) as total_sold,
            SUM(oi.price * oi.quantity) as total_revenue
        FROM order_items oi
        JOIN works w ON oi.work_id = w.id
        GROUP BY oi.work_id
        ORDER BY total_sold DESC
        LIMIT 5
    ")->fetch_all(MYSQLI_ASSOC);

    // Order status distribution
    $order_status = $conn->query("
        SELECT status, COUNT(*) as count 
        FROM orders 
        GROUP BY status
    ")->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    error_log("Analytics data fetch error: " . $e->getMessage());
    $total_users = $total_works = $active_works = $total_orders = $total_revenue = 0;
    $pending_orders = $total_cart_items = $total_wishlist_items = $total_contact_messages = $pending_contacts = 0;
    $recent_activities = $monthly_sales = $best_selling = $order_status = [];
}
?>

<style>
    /* Enhanced color scheme to match dashboard glassy design */
    :root {
        --primary-color: #164e63;
        --primary-dark: #0f3a47;
        --secondary-color: #ec4899;
        --accent-color: #f59e0b;
        --success-color: #10b981;
        --danger-color: #dc2626;
        --warning-color: #f59e0b;
        --info-color: #3b82f6;
        --light-bg: rgba(255, 255, 255, 0.8);
        --dark-bg: rgba(15, 23, 42, 0.9);
        --card-bg: rgba(255, 255, 255, 0.2);
        --glass-bg: rgba(255, 255, 255, 0.15);
        --glass-border: rgba(255, 255, 255, 0.25);
        --text-primary: #164e63;
        --text-secondary: #475569;
        --border-color: rgba(0, 0, 0, 0.1);
        --shadow: 0 25px 50px rgba(22, 78, 99, 0.15);
        --shadow-lg: 0 35px 70px rgba(22, 78, 99, 0.2);
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --sidebar-width: 280px;
        --sidebar-collapsed-width: 80px;
    }

    body {
        font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        background: linear-gradient(135deg, #ecfeff 0%, #cffafe 25%, #a5f3fc 50%, #67e8f9 75%, #22d3ee 100%);
        min-height: 100vh;
        position: relative;
        overflow-x: hidden;
    }

    /* Enhanced sidebar with darker glassy design */
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;

        backdrop-filter: blur(30px);
        background: rgba(15, 23, 42, 0.95);
        border-right: 2px solid rgba(255, 255, 255, 0.15);
        transition: var(--transition);
        z-index: 1000;
        box-shadow: 4px 0 30px rgba(0, 0, 0, 0.2);
    }

    .sidebar.collapsed {
        width: var(--sidebar-collapsed-width);
    }

    .sidebar-header {
        padding: 1.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        gap: 1rem;
        background: rgba(255, 255, 255, 0.05);
    }

    .sidebar-logo {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        font-weight: bold;
        box-shadow: 0 4px 15px rgba(236, 72, 153, 0.3);
    }

    .sidebar-title {
        color: white;
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0;
        transition: opacity 0.3s ease;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .sidebar.collapsed .sidebar-title {
        opacity: 0;
    }

    .sidebar-nav {
        padding: 1rem 0;
    }

    .nav-item {
        margin: 0.25rem 1rem;
    }

    .nav-link {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem 1rem;
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        border-radius: 12px;
        transition: var(--transition);
        position: relative;
        backdrop-filter: blur(10px);
        cursor: pointer;
    }

    .nav-link:hover,
    .nav-link.active {
        background: rgba(255, 255, 255, 0.15);
        color: white;
        transform: translateX(5px);
        box-shadow: 0 4px 15px rgba(255, 255, 255, 0.1);
    }

    .nav-link i {
        width: 20px;
        text-align: center;
        font-size: 1.1rem;
    }

    .sidebar.collapsed .nav-link span {
        opacity: 0;
    }

    .main-content {
        margin-left: 210px;
        transition: margin-left 0.3s ease;
        min-height: 100vh;
    }

    .main-content.expanded {
        margin-left: var(--sidebar-collapsed-width);
    }

    /* Enhanced top bar with dark blue glassy design */
    .top-bar {
        backdrop-filter: blur(30px);
        background: rgba(30, 58, 138, 0.9);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 1rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 8px 32px rgba(30, 58, 138, 0.3);
        position: sticky;
        top: 0;
        z-index: 100;
        border-radius: 0 0 20px 20px;
        margin: 0 1rem;
    }

    .sidebar-toggle {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        font-size: 1.2rem;
        color: white;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 10px;
        transition: var(--transition);
        backdrop-filter: blur(10px);
    }

    .sidebar-toggle:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(255, 255, 255, 0.2);
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 1rem;
        position: relative;
    }

    /* Enhanced user avatar with glassy styling */
    .user-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 1.2rem;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        transition: var(--transition);
        border: 3px solid rgba(255, 255, 255, 0.5);
        box-shadow: 0 8px 25px rgba(22, 78, 99, 0.2);
    }

    .user-avatar:hover {
        transform: scale(1.05);
        box-shadow: 0 12px 35px rgba(22, 78, 99, 0.3);
    }

    .user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .profile-upload-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        color: white;
        font-size: 0.9rem;
    }

    .user-avatar:hover .profile-upload-overlay {
        opacity: 1;
    }

    /* Enhanced profile dropdown with glassy design */
    .profile-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        backdrop-filter: blur(25px);
        background: rgba(255, 255, 255, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 16px;
        box-shadow: var(--shadow-lg);
        padding: 0.5rem 0;
        min-width: 200px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: var(--transition);
        z-index: 1000;
    }

    .profile-dropdown.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .profile-dropdown-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        cursor: pointer;
        transition: var(--transition);
        color: var(--text-primary);
        font-size: 0.9rem;
        font-weight: 500;
    }

    .profile-dropdown-item:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateX(5px);
    }

    .profile-dropdown-item i {
        width: 16px;
        color: var(--text-secondary);
    }

    #profilePhotoInput {
        display: none;
    }

    /* Dashboard Content */
    .dashboard-content {
        padding: 2rem;
    }

    .page-title {
        color: #0d0c0c;
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        text-shadow: 0 4px 8px rgba(5, 5, 5, 0.3);
    }

    .page-subtitle {
        color: rgba(58, 6, 6, 0.8);
        font-size: 1.1rem;
        margin-bottom: 2rem;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        backdrop-filter: blur(25px);
        background: rgba(255, 255, 255, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 20px;
        padding: 1.5rem;
        text-align: center;
        transition: var(--transition);
        cursor: pointer;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
        background: rgba(255, 255, 255, 0.3);
    }

    .stat-card.primary {
        border-left: 4px solid var(--primary-color);
    }

    .stat-card.success {
        border-left: 4px solid var(--success-color);
    }

    .stat-card.info {
        border-left: 4px solid var(--info-color);
    }

    .stat-card.warning {
        border-left: 4px solid var(--warning-color);
    }

    .stat-card.danger {
        border-left: 4px solid var(--danger-color);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.5rem;
        color: white;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }

    .stat-icon.primary {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    }

    .stat-icon.success {
        background: linear-gradient(135deg, var(--success-color), #059669);
    }

    .stat-icon.info {
        background: linear-gradient(135deg, var(--info-color), #2563eb);
    }

    .stat-icon.warning {
        background: linear-gradient(135deg, var(--warning-color), #d97706);
    }

    .stat-icon.danger {
        background: linear-gradient(135deg, var(--danger-color), #b91c1c);
    }

    .stat-title {
        color: var(--text-primary);
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        color: var(--text-primary);
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
    }

    /* Content Cards */
    .content-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
        margin-top: 2.5rem;
        margin-bottom: 2rem;
    }

    .content-card {
        backdrop-filter: blur(25px);
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: var(--shadow);
        transition: var(--transition);
    }

    .content-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .card-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .card-title {
        color: var(--text-primary);
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .card-title i {
        color: var(--secondary-color);
    }

    /* Chart Container */
    .chart-container {
        position: relative;
        height: 300px;
        margin: 1rem 0;
    }

    /* Tables */
    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }

    .data-table th,
    .data-table td {
        padding: 0.75rem;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .data-table th {
        background: rgba(255, 255, 255, 0.1);
        color: var(--text-primary);
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .data-table td {
        color: var(--text-secondary);
    }

    .data-table tr:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    /* Product Images */
    .product-image {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        object-fit: cover;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    /* Status Badges */
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-badge.pending {
        background: rgba(245, 158, 11, 0.2);
        color: var(--warning-color);
        border: 1px solid var(--warning-color);
    }

    .status-badge.resolved {
        background: rgba(16, 185, 129, 0.2);
        color: var(--success-color);
        border: 1px solid var(--success-color);
    }

    /* Activity List */
    .activity-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem;
        border-radius: 10px;
        margin-bottom: 0.5rem;
        transition: var(--transition);
    }

    .activity-item:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    .activity-icon {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        color: white;
        flex-shrink: 0;
    }

    .activity-content {
        flex: 1;
    }

    .activity-description {
        color: var(--text-primary);
        font-weight: 500;
        margin-bottom: 0.25rem;
    }

    .activity-time {
        color: var(--text-secondary);
        font-size: 0.8rem;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.show {
            transform: translateX(0);
        }

        .main-content {
            margin-left: 0;
        }

        .top-bar {
            margin: 0;
            border-radius: 0;
        }

        .dashboard-content {
            padding: 1rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .page-title {
            font-size: 2rem;
        }
    }

    .d-none {
        display: none !important;
    }

    .text-center {
        text-align: center;
    }

    .me-1 {
        margin-right: 0.25rem;
    }
</style>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="fas fa-cube"></i>
        </div>
        <h3 class="sidebar-title">Dashboard</h3>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-item">
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="works.php" class="nav-link">
                <i class="fas fa-briefcase"></i>
                <span>Works</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="slider.php" class="nav-link">
                <i class="fas fa-sliders-h"></i>
                <span>Slides</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="controls.php" class="nav-link">
                <i class="fas fa-cogs"></i>
                <span>Controls</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="#" class="nav-link active">
                <i class="fas fa-chart-bar"></i>
                <span>Analytics</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="settings.php" class="nav-link">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </div>
        <div class="nav-item">
            <div class="nav-link" onclick="logout()">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </div>
        </div>
    </nav>
</div>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <!-- Top Bar -->
    <div class="top-bar">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>

        <div class="user-info">
            <!-- Enhanced user avatar with profile photo functionality from profile.php -->
            <div class="user-avatar" id="userAvatar" onclick="toggleProfileDropdown()">
                <?php
                // Get user profile photo from database
                $user_id = intval($_SESSION['user_id']);
                $stmt = $conn->prepare("SELECT photo FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $user_data = $stmt->get_result()->fetch_assoc();
                $profile_photo = $user_data['photo'] ?? null;

                if ($profile_photo && file_exists("../assets/images/profiles/" . $profile_photo)):
                ?>
                    <img src="../assets/images/profiles/<?= htmlspecialchars($profile_photo) ?>" alt="Profile Photo">
                    <div class="profile-upload-overlay">
                        <i class="fas fa-camera"></i>
                    </div>
                <?php else: ?>
                    <?= strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)) ?>
                    <div class="profile-upload-overlay">
                        <i class="fas fa-camera"></i>
                    </div>
                <?php endif; ?>

                <!-- Profile Dropdown -->
                <div class="profile-dropdown" id="profileDropdown">
                    <div class="profile-dropdown-item" onclick="document.getElementById('profilePhotoInput').click()">
                        <i class="fas fa-camera"></i>
                        <span>Change Photo</span>
                    </div>
                    <div class="profile-dropdown-item" onclick="removeProfilePhoto()">
                        <i class="fas fa-trash"></i>
                        <span>Remove Photo</span>
                    </div>
                    <hr style="margin: 0.5rem 0; border: none; border-top: 1px solid #e2e8f0;">
                    <div class="profile-dropdown-item" onclick="window.location.href='profile.php'">
                        <i class="fas fa-user"></i>
                        <span>Profile Settings</span>
                    </div>
                    <div class="profile-dropdown-item" onclick="logout()">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </div>
                </div>
            </div>

            <!-- Hidden file input for profile photo upload -->
            <input type="file" id="profilePhotoInput" accept="image/*" onchange="uploadProfilePhoto(this)">

            <div>
                <div style="font-weight: 600; color: var(--dark-color);">
                    <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?>
                </div>
                <div style="font-size: 0.85rem; color: #64748b;">Administrator</div>
            </div>
        </div>
    </div>

    <!-- Dashboard Content -->
    <div class="dashboard-content">
        <div class="welcome-section">
            <h1 class="page-title">
                <i class="fas fa-chart-line me-1"></i>
                Analytics Dashboard
            </h1>
            <p class="page-subtitle">Comprehensive insights into your business performance and user engagement.</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-icon primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-title">Total Users</div>
                <div class="stat-value"><?= number_format($total_users) ?></div>
            </div>

            <div class="stat-card success">
                <div class="stat-icon success">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="stat-title">Total Works</div>
                <div class="stat-value"><?= number_format($total_works) ?></div>
            </div>

            <div class="stat-card info">
                <div class="stat-icon info">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-title">Total Orders</div>
                <div class="stat-value"><?= number_format($total_orders) ?></div>
            </div>

            <div class="stat-card warning">
                <div class="stat-icon warning">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-title">Total Revenue</div>
                <div class="stat-value">$<?= number_format($total_revenue, 2) ?></div>
            </div>

            <div class="stat-card danger">
                <div class="stat-icon danger">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-title">Pending Orders</div>
                <div class="stat-value"><?= number_format($pending_orders) ?></div>
            </div>

            <div class="stat-card primary">
                <div class="stat-icon primary">
                    <i class="fas fa-shopping-basket"></i>
                </div>
                <div class="stat-title">Cart Items</div>
                <div class="stat-value"><?= number_format($total_cart_items) ?></div>
            </div>

            <div class="stat-card success">
                <div class="stat-icon success">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-title">Wishlist Items</div>
                <div class="stat-value"><?= number_format($total_wishlist_items) ?></div>
            </div>

            <div class="stat-card info">
                <div class="stat-icon info">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="stat-title">Contact Messages</div>
                <div class="stat-value"><?= number_format($total_contact_messages) ?></div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Sales Chart -->
            <div class="content-card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        Monthly Sales Overview
                    </h4>
                </div>
                <div class="chart-container">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="content-card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-history"></i>
                        Recent Activity
                    </h4>
                </div>
                <div class="activity-list">
                    <?php foreach ($recent_activities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-description">
                                    <strong><?= htmlspecialchars($activity['user_name'] ?? 'System') ?></strong>
                                    <?= htmlspecialchars($activity['description']) ?>
                                </div>
                                <div class="activity-time">
                                    <?= date('M j, Y g:i A', strtotime($activity['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Best Selling Products -->
        <div class="content-card">
            <div class="card-header">
                <h4 class="card-title">
                    <i class="fas fa-trophy"></i>
                    Best Selling Products
                </h4>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Image</th>
                        <th>Price</th>
                        <th>Units Sold</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($best_selling as $product): ?>
                        <tr>
                            <td><?= htmlspecialchars($product['title']) ?></td>
                            <td>
                                <img src="../assets/img/works/<?= htmlspecialchars($product['image']) ?>"
                                    alt="<?= htmlspecialchars($product['title']) ?>"
                                    class="product-image">
                            </td>
                            <td>$<?= number_format($product['price'], 2) ?></td>
                            <td><?= number_format($product['total_sold']) ?></td>
                            <td>$<?= number_format($product['total_revenue'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Order Status Distribution -->
        <div class="content-grid">
            <div class="content-card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-pie-chart"></i>
                        Order Status Distribution
                    </h4>
                </div>
                <div class="chart-container">
                    <canvas id="orderStatusChart"></canvas>
                </div>
            </div>

            <!-- Contact Messages -->
            <div class="content-card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-envelope-open"></i>
                        Contact Messages
                    </h4>
                </div>
                <div style="text-align: center; padding: 2rem;">
                    <div style="font-size: 3rem; color: var(--info-color); margin-bottom: 1rem;">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div style="font-size: 2rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">
                        <?= number_format($total_contact_messages) ?>
                    </div>
                    <div style="color: var(--text-secondary); margin-bottom: 1rem;">Total Messages</div>
                    <div style="font-size: 1.2rem; color: var(--warning-color);">
                        <?= number_format($pending_contacts) ?> Pending
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Sidebar Toggle
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
    });

    // Mobile Sidebar Toggle
    if (window.innerWidth <= 768) {
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        });
    }

    // Logout Function
    function logout() {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = '../auth/logout.php';
        }
    }

    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($monthly_sales, 'month')) ?>,
            datasets: [{
                label: 'Revenue ($)',
                data: <?= json_encode(array_column($monthly_sales, 'revenue')) ?>,
                borderColor: '#ec4899',
                backgroundColor: 'rgba(236, 72, 153, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }, {
                label: 'Orders',
                data: <?= json_encode(array_column($monthly_sales, 'orders')) ?>,
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: '#164e63'
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#475569'
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    ticks: {
                        color: '#475569'
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    ticks: {
                        color: '#475569'
                    },
                    grid: {
                        drawOnChartArea: false,
                    }
                }
            }
        }
    });

    // Order Status Chart
    const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
    const orderStatusChart = new Chart(orderStatusCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($order_status, 'status')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($order_status, 'count')) ?>,
                backgroundColor: [
                    '#f59e0b',
                    '#3b82f6',
                    '#10b981',
                    '#ec4899',
                    '#dc2626'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#164e63',
                        padding: 20
                    }
                }
            }
        }
    });
</script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>