<?php
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/role_check.php';

// Check if user is admin - redirect customers to portfolio
checkUserRole('admin');

$site_url = "http://localhost/task-project/";

// Fetch statistics
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'Pending'")->fetch_assoc()['count'];
$total_contacts = $conn->query("SELECT COUNT(*) as count FROM contact_submissions")->fetch_assoc()['count'];
$pending_contacts = $conn->query("SELECT COUNT(*) as count FROM contact_submissions WHERE status = 'pending'")->fetch_assoc()['count'];

// Fetch recent orders with customer details
$recent_orders = $conn->query("
    SELECT o.*, 
           (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count
    FROM orders o 
    ORDER BY o.created_at DESC 
    LIMIT 10
");

// Fetch recent contact submissions
$recent_contacts = $conn->query("
    SELECT cs.*, u.name as user_name 
    FROM contact_submissions cs 
    LEFT JOIN users u ON cs.user_id = u.id 
    ORDER BY cs.created_at DESC 
    LIMIT 10
");
?>

<style>
    /* Updated color scheme to match login page glassy design */
    :root {
        /* Professional glassy color palette matching login.php */
        --primary-color: #164e63;
        --primary-dark: #0f3a47;
        --secondary-color: #ec4899;
        --accent-color: #f59e0b;
        --success-color: #10b981;
        --danger-color: #dc2626;
        --warning-color: #f59e0b;
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
        --glow: 0 0 30px rgba(22, 78, 99, 0.3);
        --glass-gradient: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
        --sidebar-width: 280px;
        --sidebar-collapsed-width: 80px;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* Updated body background to match login page gradient */
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

    /* Enhanced nav links with glassy effect */
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

    /* Main Content */
    .main-content {
        margin-left: var(--sidebar-width);
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
        color: white;
    }

    .user-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        border: 2px solid rgba(255, 255, 255, 0.3);
        transition: var(--transition);
    }

    .user-avatar:hover {
        transform: scale(1.1);
        box-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
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
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        border-radius: 50%;
    }

    .user-avatar:hover .profile-upload-overlay {
        opacity: 1;
    }

    .profile-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        min-width: 200px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
        z-index: 1000;
        margin-top: 10px;
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

    /* Dashboard content with glassy cards */
    .dashboard-content {
        padding: 2rem;
    }

    .welcome-section {
        margin-bottom: 2rem;
        text-align: center;
    }

    .welcome-title {
        font-family: 'Work Sans', sans-serif;
        font-weight: 700;
        font-size: 2.5rem;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 4px rgba(22, 78, 99, 0.1);
    }

    .welcome-subtitle {
        color: var(--text-secondary);
        font-size: 1.1rem;
        opacity: 0.8;
    }

    /* Stats grid card sizes */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.2rem;
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
        border-left: 4px solid var(--accent-color);
    }

    .stat-card.warning {
        border-left: 4px solid var(--warning-color);
    }

    .stat-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        color: var(--primary-color);
        opacity: 0.8;
    }

    .stat-title {
        font-size: 0.9rem;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-primary);
        text-shadow: 0 2px 4px rgba(22, 78, 99, 0.1);
    }

    /* Content cards with enhanced glassy effect */
    .content-card {
        backdrop-filter: blur(25px);
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 20px;
        margin-bottom: 2rem;
        overflow: hidden;
        box-shadow: var(--shadow);
        transition: var(--transition);
    }

    .content-card:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-2px);
    }

    .card-header {
        padding: 1.5rem 2rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        display: flex;
        justify-content: between;
        align-items: center;
        background: rgba(255, 255, 255, 0.1);
    }

    .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .card-title i {
        color: var(--primary-color);
    }

    /* Enhanced table styling */
    .table-container {
        overflow-x: auto;
        padding: 0;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        background: transparent;
    }

    .table th {
        background: rgba(255, 255, 255, 0.1);
        color: var(--text-primary);
        font-weight: 600;
        padding: 1rem;
        text-align: left;
        border-bottom: 2px solid rgba(255, 255, 255, 0.15);
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table td {
        padding: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        color: var(--text-secondary);
        vertical-align: middle;
    }

    .table tbody tr {
        transition: var(--transition);
    }

    .table tbody tr:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: scale(1.01);
    }

    /* Enhanced buttons */
    .btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: var(--transition);
        backdrop-filter: blur(10px);
        font-size: 0.9rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        box-shadow: 0 4px 15px rgba(22, 78, 99, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(22, 78, 99, 0.4);
    }

    .btn-success {
        background: linear-gradient(135deg, var(--success-color), #059669);
        color: white;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
    }

    .btn-warning {
        background: linear-gradient(135deg, var(--warning-color), #d97706);
        color: white;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
    }

    .btn-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--danger-color), #b91c1c);
        color: white;
        box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
    }

    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(220, 38, 38, 0.4);
    }

    .btn-info {
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        color: white;
        box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3);
    }

    .btn-info:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(14, 165, 233, 0.4);
    }

    .btn-secondary {
        background: rgba(255, 255, 255, 0.2);
        color: var(--text-secondary);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
    }

    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.8rem;
    }

    /* Badge styling */
    .badge {
        padding: 0.375rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .bg-success {
        background: linear-gradient(135deg, var(--success-color), #059669);
        color: white;
    }

    .bg-warning {
        background: linear-gradient(135deg, var(--warning-color), #d97706);
        color: white;
    }

    .bg-danger {
        background: linear-gradient(135deg, var(--danger-color), #b91c1c);
        color: white;
    }

    .bg-info {
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        color: white;
    }

    .bg-secondary {
        background: rgba(255, 255, 255, 0.2);
        color: var(--text-secondary);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    /* Notification styling */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 15px;
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        z-index: 10000;
        transform: translateX(400px);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 500;
    }

    .notification.show {
        transform: translateX(0);
    }

    .notification.success {
        background: rgba(16, 185, 129, 0.9);
        color: white;
    }

    .notification.error {
        background: rgba(220, 38, 38, 0.9);
        color: white;
    }

    .notification.info {
        background: rgba(14, 165, 233, 0.9);
        color: white;
    }

    /* Loader styling */
    .loader-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 10000;
        backdrop-filter: blur(5px);
    }

    .loader {
        width: 50px;
        height: 50px;
        border: 4px solid rgba(255, 255, 255, 0.3);
        border-top: 4px solid white;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* Animation classes */
    .fade-in-up {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.6s ease;
    }

    .fade-in-up.visible {
        opacity: 1;
        transform: translateY(0);
    }

    /* Responsive design */
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

        .welcome-title {
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

    /* Order details modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px);
    }

    .modal-content {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(30px);
        margin: 5% auto;
        padding: 0;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 20px;
        width: 90%;
        max-width: 800px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
        overflow: hidden;
    }

    .modal-header {
        background: rgba(22, 78, 99, 0.9);
        color: white;
        padding: 1.5rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-body {
        padding: 2rem;
        max-height: 70vh;
        overflow-y: auto;
    }

    .close {
        color: white;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        transition: var(--transition);
    }

    .close:hover {
        transform: scale(1.1);
    }

    .order-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .detail-group {
        background: rgba(255, 255, 255, 0.3);
        padding: 1rem;
        border-radius: 15px;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .detail-label {
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .detail-value {
        color: var(--text-secondary);
        font-size: 1rem;
    }
</style>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="fas fa-cogs"></i>
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
            <a href="controls.php" class="nav-link active">
                <i class="fas fa-cogs"></i>
                <span>Controls</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="analytics.php" class="nav-link">
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
            <!-- Enhanced user avatar with profile photo functionality -->
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
                <div style="font-weight: 600; color: white;">
                    <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?>
                </div>
                <div style="font-size: 0.85rem; color: rgba(255, 255, 255, 0.8);">Administrator</div>
            </div>
        </div>
    </div>

    <!-- Dashboard Content -->
    <div class="dashboard-content">
        <!-- Welcome Section -->
        <div class="welcome-section fade-in-up">
            <h1 class="welcome-title">
                <i class="fas fa-cogs"></i>
                Control Center
            </h1>
            <p class="welcome-subtitle">Manage orders, messages, and system controls from here.</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card primary fade-in-up" style="animation-delay: 0.1s;">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-title">Total Orders</div>
                <div class="stat-value"><?= $total_orders ?></div>
            </div>

            <div class="stat-card warning fade-in-up" style="animation-delay: 0.2s;">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-title">Pending Orders</div>
                <div class="stat-value"><?= $pending_orders ?></div>
            </div>

            <div class="stat-card info fade-in-up" style="animation-delay: 0.3s;">
                <div class="stat-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="stat-title">Total Messages</div>
                <div class="stat-value"><?= $total_contacts ?></div>
            </div>

            <div class="stat-card success fade-in-up" style="animation-delay: 0.4s;">
                <div class="stat-icon">
                    <i class="fas fa-envelope-open"></i>
                </div>
                <div class="stat-title">Pending Messages</div>
                <div class="stat-value"><?= $pending_contacts ?></div>
            </div>
        </div>

        <!-- Orders Management -->
        <div class="content-card fade-in-up" style="animation-delay: 0.5s;">
            <div class="card-header">
                <h4 class="card-title">
                    <i class="fas fa-shopping-cart"></i>
                    Orders Management
                </h4>
            </div>
            <div class="table-container">
                <table class="table" id="ordersTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        while ($order = $recent_orders->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($order['full_name']) ?></td>
                                <td><?= htmlspecialchars($order['email']) ?></td>
                                <td><?= $order['item_count'] ?> items</td>
                                <td>$<?= number_format($order['total'], 2) ?></td>
                                <td>
                                    <select class="badge bg-<?=
                                                            $order['status'] === 'Pending' ? 'warning' : ($order['status'] === 'Processing' ? 'info' : ($order['status'] === 'Shipped' ? 'primary' : ($order['status'] === 'Delivered' ? 'success' : 'danger')))
                                                            ?> order-status-select"
                                        data-order-id="<?= $order['id'] ?>"
                                        style="border: none; background: transparent; color: inherit; cursor: pointer;">
                                        <option value="Pending" <?= $order['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="Processing" <?= $order['status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                                        <option value="Shipped" <?= $order['status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                        <option value="Delivered" <?= $order['status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                        <option value="Cancelled" <?= $order['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </td>
                                <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info view-order" data-order-id="<?= $order['id'] ?>" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-order" data-order-id="<?= $order['id'] ?>" title="Delete Order">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Contact Messages Management -->
        <div class="content-card fade-in-up" style="animation-delay: 0.6s;">
            <div class="card-header">
                <h4 class="card-title">
                    <i class="fas fa-envelope"></i>
                    Contact Messages
                </h4>
            </div>
            <div class="table-container">
                <table class="table" id="contactsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        while ($contact = $recent_contacts->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($contact['name']) ?></td>
                                <td><?= htmlspecialchars($contact['email']) ?></td>
                                <td><?= htmlspecialchars($contact['subject']) ?></td>
                                <td><?= htmlspecialchars(substr($contact['message'], 0, 50)) ?>...</td>
                                <td>
                                    <button class="btn btn-sm toggle-contact-status <?= $contact['status'] === 'pending' ? 'btn-warning' : 'btn-success' ?>"
                                        data-contact-id="<?= $contact['id'] ?>"
                                        data-status="<?= $contact['status'] ?>"
                                        title="Toggle Status">
                                        <?= $contact['status'] === 'pending' ? '<i class="fas fa-clock"></i> Pending' : '<i class="fas fa-check"></i> Resolved' ?>
                                    </button>
                                </td>
                                <td><?= date('d M Y', strtotime($contact['created_at'])) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info view-message"
                                        data-contact-id="<?= $contact['id'] ?>"
                                        data-name="<?= htmlspecialchars($contact['name']) ?>"
                                        data-email="<?= htmlspecialchars($contact['email']) ?>"
                                        data-subject="<?= htmlspecialchars($contact['subject']) ?>"
                                        data-message="<?= htmlspecialchars($contact['message']) ?>"
                                        data-date="<?= date('d M Y, h:i A', strtotime($contact['created_at'])) ?>"
                                        title="View Message">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-contact" data-contact-id="<?= $contact['id'] ?>" title="Delete Message">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div id="orderModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h4>Order Details</h4>
            <span class="close" onclick="closeOrderModal()">&times;</span>
        </div>
        <div class="modal-body" id="orderModalBody">
            <!-- Order details will be loaded here -->
        </div>
    </div>
</div>

<!-- Message Details Modal -->
<div id="messageModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h4>Message Details</h4>
            <span class="close" onclick="closeMessageModal()">&times;</span>
        </div>
        <div class="modal-body" id="messageModalBody">
            <!-- Message details will be loaded here -->
        </div>
    </div>
</div>

<!-- Loader Overlay -->
<div class="loader-overlay" id="loaderOverlay">
    <div class="loader"></div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>

<script>
    // Sidebar Toggle Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggle = document.getElementById('sidebarToggle');

        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });

        // Mobile sidebar toggle
        if (window.innerWidth <= 768) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }

        document.addEventListener('click', function(event) {
            const profileDropdown = document.getElementById('profileDropdown');
            const userAvatar = document.getElementById('userAvatar');

            if (!userAvatar.contains(event.target)) {
                profileDropdown.classList.remove('show');
            }
        });

        // Initialize event listeners
        initializeEventListeners();
    });

    function initializeEventListeners() {
        // Order status change
        document.querySelectorAll('.order-status-select').forEach(select => {
            select.addEventListener('change', function() {
                updateOrderStatus(this.dataset.orderId, this.value);
            });
        });

        // Contact status toggle
        document.querySelectorAll('.toggle-contact-status').forEach(button => {
            button.addEventListener('click', function() {
                toggleContactStatus(this.dataset.contactId, this.dataset.status);
            });
        });

        // View order details
        document.querySelectorAll('.view-order').forEach(button => {
            button.addEventListener('click', function() {
                viewOrderDetails(this.dataset.orderId);
            });
        });

        // View message details
        document.querySelectorAll('.view-message').forEach(button => {
            button.addEventListener('click', function() {
                viewMessageDetails(this.dataset);
            });
        });

        // Delete order
        document.querySelectorAll('.delete-order').forEach(button => {
            button.addEventListener('click', function() {
                deleteOrder(this.dataset.orderId);
            });
        });

        // Delete contact
        document.querySelectorAll('.delete-contact').forEach(button => {
            button.addEventListener('click', function() {
                deleteContact(this.dataset.contactId);
            });
        });
    }

    function updateOrderStatus(orderId, status) {
        showLoader();

        $.ajax({
            url: 'inc/action_controls.php',
            type: 'POST',
            data: {
                action: 'update_order_status',
                order_id: orderId,
                status: status
            },
            dataType: 'json',
            success: function(response) {
                hideLoader();
                if (response.status === 'success') {
                    showNotification('Order status updated successfully!', 'success');
                } else {
                    showNotification(response.message || 'Failed to update order status', 'error');
                }
            },
            error: function(xhr, status, error) {
                hideLoader();
                showNotification('Update failed: ' + error, 'error');
            }
        });
    }

    function toggleContactStatus(contactId, currentStatus) {
        showLoader();

        $.ajax({
            url: 'inc/action_controls.php',
            type: 'POST',
            data: {
                action: 'toggle_contact_status',
                id: contactId,
                status: currentStatus
            },
            dataType: 'json',
            success: function(response) {
                hideLoader();
                if (response.status === 'success') {
                    showNotification('Contact status updated successfully!', 'success');
                    // Reload page to reflect changes
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showNotification(response.message || 'Failed to update contact status', 'error');
                }
            },
            error: function(xhr, status, error) {
                hideLoader();
                showNotification('Update failed: ' + error, 'error');
            }
        });
    }

    function viewOrderDetails(orderId) {
        showLoader();

        $.ajax({
            url: 'inc/action_controls.php',
            type: 'POST',
            data: {
                action: 'get_order_details',
                order_id: orderId
            },
            dataType: 'json',
            success: function(response) {
                hideLoader();
                if (response.status === 'success') {
                    displayOrderDetails(response.data);
                } else {
                    showNotification(response.message || 'Failed to load order details', 'error');
                }
            },
            error: function(xhr, status, error) {
                hideLoader();
                showNotification('Load failed: ' + error, 'error');
            }
        });
    }

    function displayOrderDetails(data) {
        const modalBody = document.getElementById('orderModalBody');

        let itemsHtml = '';
        data.items.forEach(item => {
            itemsHtml += `
                <tr>
                    <td>${item.title}</td>
                    <td>$${parseFloat(item.price).toFixed(2)}</td>
                    <td>${item.quantity}</td>
                    <td>$${(parseFloat(item.price) * parseInt(item.quantity)).toFixed(2)}</td>
                </tr>
            `;
        });

        modalBody.innerHTML = `
            <div class="order-details">
                <div class="detail-group">
                    <div class="detail-label">Order ID</div>
                    <div class="detail-value">#${data.order.id}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Customer Name</div>
                    <div class="detail-value">${data.order.full_name}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Email</div>
                    <div class="detail-value">${data.order.email}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Status</div>
                    <div class="detail-value">
                        <span class="badge bg-${
                            data.order.status === 'Pending' ? 'warning' : 
                            (data.order.status === 'Processing' ? 'info' : 
                            (data.order.status === 'Shipped' ? 'primary' : 
                            (data.order.status === 'Delivered' ? 'success' : 'danger')))
                        }">${data.order.status}</span>
                    </div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Payment Method</div>
                    <div class="detail-value">${data.order.payment_method}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Order Date</div>
                    <div class="detail-value">${new Date(data.order.created_at).toLocaleDateString()}</div>
                </div>
            </div>
            
            <div class="detail-group" style="grid-column: 1 / -1;">
                <div class="detail-label">Shipping Address</div>
                <div class="detail-value">${data.order.address}</div>
            </div>

            <h5 style="margin: 2rem 0 1rem 0; color: var(--text-primary);">
                <i class="fas fa-shopping-bag"></i> Order Items
            </h5>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHtml}
                        <tr style="font-weight: bold; border-top: 2px solid var(--primary-color);">
                            <td colspan="3">Total</td>
                            <td>$${parseFloat(data.order.total).toFixed(2)}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `;

        document.getElementById('orderModal').style.display = 'block';
    }

    function viewMessageDetails(data) {
        const modalBody = document.getElementById('messageModalBody');

        modalBody.innerHTML = `
            <div class="order-details">
                <div class="detail-group">
                    <div class="detail-label">Name</div>
                    <div class="detail-value">${data.name}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Email</div>
                    <div class="detail-value">${data.email}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Date</div>
                    <div class="detail-value">${data.date}</div>
                </div>
            </div>
            
            <div class="detail-group" style="margin-bottom: 1rem;">
                <div class="detail-label">Subject</div>
                <div class="detail-value">${data.subject}</div>
            </div>
            
            <div class="detail-group">
                <div class="detail-label">Message</div>
                <div class="detail-value" style="white-space: pre-wrap; line-height: 1.6;">${data.message}</div>
            </div>
        `;

        document.getElementById('messageModal').style.display = 'block';
    }

    function deleteOrder(orderId) {
        if (confirm('Are you sure you want to delete this order? This action cannot be undone.')) {
            showLoader();

            $.ajax({
                url: 'inc/action_controls.php',
                type: 'POST',
                data: {
                    action: 'delete_order',
                    order_id: orderId
                },
                dataType: 'json',
                success: function(response) {
                    hideLoader();
                    if (response.status === 'success') {
                        showNotification('Order deleted successfully!', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showNotification(response.message || 'Failed to delete order', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    showNotification('Delete failed: ' + error, 'error');
                }
            });
        }
    }

    function deleteContact(contactId) {
        if (confirm('Are you sure you want to delete this message? This action cannot be undone.')) {
            showLoader();

            $.ajax({
                url: 'inc/action_controls.php',
                type: 'POST',
                data: {
                    action: 'delete_contact',
                    id: contactId
                },
                dataType: 'json',
                success: function(response) {
                    hideLoader();
                    if (response.status === 'success') {
                        showNotification('Message deleted successfully!', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showNotification(response.message || 'Failed to delete message', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    showNotification('Delete failed: ' + error, 'error');
                }
            });
        }
    }

    function closeOrderModal() {
        document.getElementById('orderModal').style.display = 'none';
    }

    function closeMessageModal() {
        document.getElementById('messageModal').style.display = 'none';
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        const orderModal = document.getElementById('orderModal');
        const messageModal = document.getElementById('messageModal');

        if (event.target === orderModal) {
            orderModal.style.display = 'none';
        }
        if (event.target === messageModal) {
            messageModal.style.display = 'none';
        }
    }

    function toggleProfileDropdown() {
        const dropdown = document.getElementById('profileDropdown');
        dropdown.classList.toggle('show');
    }

    function uploadProfilePhoto(input) {
        if (input.files && input.files[0]) {
            const formData = new FormData();
            formData.append('photo', input.files[0]);
            formData.append('action', 'upload_profile_photo');

            showNotification('Uploading photo...', 'info');

            $.ajax({
                url: 'profile_action.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        showNotification('Profile photo updated successfully!', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showNotification(response.message || 'Failed to upload photo', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    showNotification('Upload failed: ' + error, 'error');
                }
            });
        }
    }

    function removeProfilePhoto() {
        if (confirm('Are you sure you want to remove your profile photo?')) {
            $.ajax({
                url: 'profile_action.php',
                type: 'POST',
                data: {
                    action: 'remove_profile_photo'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        showNotification('Profile photo removed successfully!', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showNotification(response.message || 'Failed to remove photo', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    showNotification('Remove failed: ' + error, 'error');
                }
            });
        }
    }

    function showNotification(message, type = 'info') {
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(n => n.remove());

        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'}-circle"></i> ${message}`;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.add('show');
        }, 100);

        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }

    function showLoader() {
        document.getElementById('loaderOverlay').style.display = 'flex';
    }

    function hideLoader() {
        document.getElementById('loaderOverlay').style.display = 'none';
    }

    function logout() {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = 'logout.php';
        }
    }

    // Add smooth scrolling and fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe all fade-in elements
    document.querySelectorAll('.fade-in-up').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'all 0.6s ease';
        observer.observe(el);
    });
</script>
</body>

</html>