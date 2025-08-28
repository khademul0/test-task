<?php
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/role_check.php';

// Check if user is admin - redirect customers to portfolio
checkUserRole('admin');

$site_url = "http://localhost/task-project/";

// Fetch statistics
$total_works = $conn->query("SELECT COUNT(*) as count FROM works")->fetch_assoc()['count'];
$active_works = $conn->query("SELECT COUNT(*) as count FROM works WHERE status = 1")->fetch_assoc()['count'];
$total_slides = $conn->query("SELECT COUNT(*) as count FROM slides")->fetch_assoc()['count'];
$active_slides = $conn->query("SELECT COUNT(*) as count FROM slides WHERE status = 'Active'")->fetch_assoc()['count'];

// Fetch recent works
$recent_works = $conn->query("SELECT * FROM works ORDER BY created_at DESC LIMIT 5");

// Fetch recent slides
$recent_slides = $conn->query("SELECT * FROM slides ORDER BY id DESC LIMIT 5");

// Fetch recent activity logs
$recent_logs = $conn->query("SELECT al.*, u.name FROM activity_logs al JOIN users u ON al.user_id = u.id WHERE al.user_id = " . intval($_SESSION['user_id']) . " ORDER BY al.created_at DESC LIMIT 5");
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

    /* Reduced stats grid card sizes to make them cuter */
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

    /* Reduced stat icon size for cuter appearance */
    .stat-icon {
        width: 50px;
        height: 50px;
        margin: 0 auto 1rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        color: white;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        box-shadow: 0 8px 25px rgba(22, 78, 99, 0.2);
    }

    .stat-card.success .stat-icon {
        background: linear-gradient(135deg, var(--success-color), #34d399);
    }

    .stat-card.info .stat-icon {
        background: linear-gradient(135deg, var(--accent-color), #fbbf24);
    }

    .stat-card.warning .stat-icon {
        background: linear-gradient(135deg, var(--warning-color), #f97316);
    }

    .stat-title {
        font-size: 0.85rem;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    /* Reduced stat value font size for cuter cards */
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-primary);
        font-family: 'Work Sans', sans-serif;
    }

    /* Content cards with glassy design */
    .content-card {
        backdrop-filter: blur(25px);
        background: rgba(255, 255, 255, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 20px;
        margin-bottom: 2rem;
        overflow: hidden;
        box-shadow: var(--shadow);
        position: relative;
    }

    .content-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
    }

    .card-header {
        padding: 1.5rem 2rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(255, 255, 255, 0.1);
    }

    .card-title {
        font-family: 'Work Sans', sans-serif;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .table-container {
        overflow-x: auto;
    }

    /* Enhanced table styling with glassy design */
    .table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }

    .table th {
        background: rgba(255, 255, 255, 0.1);
        color: var(--text-primary);
        font-weight: 600;
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        font-size: 0.9rem;
    }

    .table td {
        padding: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        color: var(--text-secondary);
        vertical-align: middle;
    }

    .table tbody tr {
        transition: all 0.3s ease;
    }

    .table tbody tr:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    /* Enhanced buttons with glassy styling */
    .btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 10px;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        backdrop-filter: blur(10px);
        font-size: 0.875rem;
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
        background: linear-gradient(135deg, var(--success-color), #34d399);
        color: white;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
    }

    .btn-secondary {
        background: rgba(255, 255, 255, 0.2);
        color: var(--text-secondary);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-1px);
    }

    .btn-info {
        background: linear-gradient(135deg, var(--accent-color), #fbbf24);
        color: white;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
    }

    .btn-info:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
    }

    .btn-warning {
        background: linear-gradient(135deg, var(--warning-color), #f97316);
        color: white;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
    }

    .btn-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--danger-color), #f87171);
        color: white;
        box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
    }

    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(220, 38, 38, 0.4);
    }

    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.8rem;
    }

    .badge {
        padding: 0.375rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        backdrop-filter: blur(10px);
    }

    .bg-success {
        background: linear-gradient(135deg, var(--success-color), #34d399);
        color: white;
    }

    .bg-secondary {
        background: rgba(255, 255, 255, 0.2);
        color: var(--text-secondary);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .img-thumbnail {
        border: 2px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    /* Enhanced notification styles */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 16px;
        color: white;
        font-weight: 500;
        z-index: 10000;
        transform: translateX(400px);
        transition: transform 0.3s ease;
        backdrop-filter: blur(25px);
        border: 1px solid rgba(255, 255, 255, 0.25);
        box-shadow: var(--shadow-lg);
    }

    .notification.show {
        transform: translateX(0);
    }

    .notification.success {
        background: rgba(16, 185, 129, 0.9);
    }

    .notification.error {
        background: rgba(220, 38, 38, 0.9);
    }

    .notification.info {
        background: rgba(22, 78, 99, 0.9);
    }

    /* Enhanced loader overlay */
    .loader-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .loader {
        width: 50px;
        height: 50px;
        border: 4px solid rgba(22, 78, 99, 0.2);
        border-top: 4px solid var(--primary-color);
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

    /* Enhanced animations */
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
            <a href="#" class="nav-link active">
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
        <!-- Welcome Section -->
        <div class="welcome-section fade-in-up">
            <h1 class="welcome-title">
                <i class="fas fa-wave-square"></i>
                Welcome back, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?>!
            </h1>
            <p class="welcome-subtitle">Here's what's happening with your projects today.</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card primary fade-in-up" onclick="window.location.href='works.php'" style="animation-delay: 0.1s;">
                <div class="stat-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="stat-title">Total Works</div>
                <div class="stat-value"><?= $total_works ?></div>
            </div>

            <div class="stat-card success fade-in-up" onclick="window.location.href='works.php'" style="animation-delay: 0.2s;">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-title">Active Works</div>
                <div class="stat-value"><?= $active_works ?></div>
            </div>

            <div class="stat-card info fade-in-up" onclick="window.location.href='slider.php'" style="animation-delay: 0.3s;">
                <div class="stat-icon">
                    <i class="fas fa-sliders-h"></i>
                </div>
                <div class="stat-title">Total Slides</div>
                <div class="stat-value"><?= $total_slides ?></div>
            </div>

            <div class="stat-card warning fade-in-up" onclick="window.location.href='slider.php'" style="animation-delay: 0.4s;">
                <div class="stat-icon">
                    <i class="fas fa-toggle-on"></i>
                </div>
                <div class="stat-title">Active Slides</div>
                <div class="stat-value"><?= $active_slides ?></div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="content-card fade-in-up" style="animation-delay: 0.5s;">
            <div class="card-header">
                <h4 class="card-title">
                    <i class="fas fa-history"></i>
                    Recent Activity
                </h4>
            </div>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        while ($log = $recent_logs->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($log['name']) ?></td>
                                <td><?= htmlspecialchars($log['action']) ?></td>
                                <td><?= htmlspecialchars($log['description'] ?? '-') ?></td>
                                <td><?= date('d M Y, h:i A', strtotime($log['created_at'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Works -->
        <div class="content-card fade-in-up" style="animation-delay: 0.6s;">
            <div class="card-header">
                <h4 class="card-title">
                    <i class="fas fa-paint-brush"></i>
                    Recent Works
                </h4>
                <a href="works.php" class="btn btn-primary">
                    <i class="fas fa-eye me-1"></i> View All
                </a>
            </div>
            <div class="table-container">
                <table class="table" id="worksTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Thumbnail</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        while ($work = $recent_works->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td>
                                    <?php if (!empty($work['image']) && file_exists("../assets/img/works/" . $work['image'])): ?>
                                        <img src="../assets/img/works/<?= htmlspecialchars($work['image']) ?>" width="70" height="50" class="img-thumbnail" alt="Work Image" style="border-radius: 10px;">
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($work['title']) ?></td>
                                <td><?= htmlspecialchars(substr($work['description'], 0, 50)) ?>...</td>
                                <td>
                                    <button class="btn btn-sm toggle-status <?= $work['status'] ? 'btn-success' : 'btn-secondary' ?>" data-id="<?= $work['id'] ?>" data-status="<?= $work['status'] ?>">
                                        <?= $work['status'] ? '<i class="fas fa-toggle-on"></i>' : '<i class="fas fa-toggle-off"></i>' ?>
                                    </button>
                                </td>
                                <td>
                                    <a href="edit_work.php?id=<?= $work['id'] ?>" class="btn btn-sm btn-info" title="Edit Work"><i class="fas fa-edit"></i></a>
                                    <button class="btn btn-sm btn-danger delete-work" data-id="<?= $work['id'] ?>" title="Delete Work"><i class="fas fa-trash-alt"></i></button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Slides -->
        <div class="content-card fade-in-up" style="animation-delay: 0.7s;">
            <div class="card-header">
                <h4 class="card-title">
                    <i class="fas fa-sliders-h"></i>
                    Recent Slides
                </h4>
                <a href="slider.php" class="btn btn-primary">
                    <i class="fas fa-eye me-1"></i> View All
                </a>
            </div>
            <div class="table-container">
                <table class="table" id="slidesTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Image</th>
                            <th>Time Limit</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sl = 1;
                        while ($row = $recent_slides->fetch_assoc()):
                        ?>
                            <tr id="row-<?= $row['id'] ?>">
                                <td><?= $sl++ ?></td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td>
                                    <?php if (!empty($row['image']) && file_exists(__DIR__ . '/../assets/images/slides/' . $row['image'])): ?>
                                        <img src="../assets/images/slides/<?= $row['image'] ?>" width="60" alt="Slide Image" style="border-radius: 8px;">
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small>
                                        <?= date('d M Y, h:i A', strtotime($row['start_time'])) ?><br>
                                        <strong>to</strong><br>
                                        <?= date('d M Y, h:i A', strtotime($row['end_time'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $row['status'] === 'Active' ? 'success' : 'secondary' ?>" id="status-label-<?= $row['id'] ?>">
                                        <?= $row['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning" title="Edit Slide"><i class="fas fa-edit"></i></a>
                                    <button class="btn btn-sm btn-danger btn-delete" data-id="<?= $row['id'] ?>" title="Delete Slide"><i class="fas fa-trash-alt"></i></button>
                                    <button class="btn btn-sm btn-info btn-status-toggle" data-id="<?= $row['id'] ?>" title="Toggle Status">
                                        <i class="fas fa-toggle-<?= $row['status'] === 'Active' ? 'on' : 'off' ?>"></i>
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
    });

    function toggleProfileDropdown() {
        const dropdown = document.getElementById('profileDropdown');
        dropdown.classList.toggle('show');
    }

    function uploadProfilePhoto(input) {
        if (input.files && input.files[0]) {
            const formData = new FormData();
            formData.append('photo', input.files[0]);
            formData.append('action', 'upload_profile_photo');

            // Show loading
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
                        // Reload the page to show new photo
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
        // Remove existing notifications
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

    function logout() {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = 'logout.php';
        }
    }

    // Toggle work status
    $('.toggle-status').click(function() {
        let button = $(this);
        let id = button.data('id');
        let currentStatus = button.data('status') === 1 ? 1 : 0;

        // Add loading state
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: 'inc/action_works.php',
            method: 'POST',
            data: {
                action: 'toggle_status',
                id: id,
                status: currentStatus
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let newStatus = response.new_status;
                    button.data('status', newStatus);
                    button.toggleClass('btn-success btn-secondary');
                    button.html(newStatus === 1 ? '<i class="fas fa-toggle-on"></i>' : '<i class="fas fa-toggle-off"></i>');

                    Swal.fire({
                        toast: true,
                        icon: 'success',
                        title: response.message,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        background: '#10b981',
                        color: '#fff'
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
                button.prop('disabled', false);
            },
            error: function() {
                Swal.fire('Error', 'AJAX request failed', 'error');
                button.prop('disabled', false);
            }
        });
    });

    // Delete work with enhanced confirmation
    $('.delete-work').click(function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This work will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            background: '#fff',
            customClass: {
                popup: 'rounded-lg'
            }
        }).then(result => {
            if (result.isConfirmed) {
                $('#loaderOverlay').show();

                $.ajax({
                    url: 'inc/action_works.php',
                    method: 'POST',
                    data: {
                        action: 'delete_work',
                        id: id
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('#loaderOverlay').hide();
                        if (response.status === 'success') {
                            Swal.fire({
                                title: 'Deleted!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonColor: '#10b981'
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        $('#loaderOverlay').hide();
                        Swal.fire('Error', 'AJAX request failed', 'error');
                    }
                });
            }
        });
    });

    // Delete slide with enhanced UI
    $('.btn-delete').click(function() {
        const slideId = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This will delete the slide permanently!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#loaderOverlay').show();

                $.ajax({
                    url: 'inc/action.php',
                    method: 'POST',
                    data: {
                        delete_slide: 1,
                        id: slideId
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('#loaderOverlay').hide();
                        if (response.status === 'success') {
                            $('#row-' + slideId).fadeOut(500, function() {
                                $(this).remove();
                            });
                            Swal.fire({
                                toast: true,
                                icon: 'success',
                                title: response.message,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                background: '#10b981',
                                color: '#fff'
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        $('#loaderOverlay').hide();
                        Swal.fire('Error', 'AJAX request failed', 'error');
                    }
                });
            }
        });
    });

    // Toggle slide status with loading state
    $('.btn-status-toggle').click(function() {
        const slideId = $(this).data('id');
        const button = $(this);

        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: 'inc/action.php',
            method: 'POST',
            data: {
                toggle_slide_status: 1,
                id: slideId
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    const icon = $('#row-' + slideId + ' .btn-status-toggle i');
                    const statusLabel = $('#status-label-' + slideId);

                    if (response.new_status === 'Active') {
                        button.html('<i class="fas fa-toggle-on"></i>');
                        statusLabel.removeClass('bg-secondary').addClass('bg-success').text('Active');
                    } else {
                        button.html('<i class="fas fa-toggle-off"></i>');
                        statusLabel.removeClass('bg-success').addClass('bg-secondary').text('Inactive');
                    }

                    Swal.fire({
                        toast: true,
                        icon: 'success',
                        title: response.message,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        background: '#10b981',
                        color: '#fff'
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
                button.prop('disabled', false);
            },
            error: function() {
                Swal.fire('Error', 'AJAX request failed', 'error');
                button.prop('disabled', false);
            }
        });
    });

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