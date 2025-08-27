<?php
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/role_check.php';

// Check if user is admin - redirect customers to portfolio
checkUserRole('admin');

// Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_role') {
    $target_user_id = intval($_POST['user_id']);
    $new_role = $_POST['role_play'];

    if (in_array($new_role, ['admin', 'customer'])) {
        $stmt = $conn->prepare("UPDATE users SET role_play = ? WHERE id = ?");
        $stmt->bind_param("si", $new_role, $target_user_id);

        if ($stmt->execute()) {
            $user_id = intval($_SESSION['user_id']);
            $conn->query("INSERT INTO activity_logs (user_id, action, description) VALUES ($user_id, 'Update User Role', 'Changed user ID $target_user_id role to $new_role')");
            $success_message = "User role updated successfully!";
        } else {
            $error_message = "Failed to update user role.";
        }
    }
}

// Fetch all users
$users_query = $conn->query("SELECT id, name, email, role_play, created_at, is_active FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Role Management - Settings</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/fontawesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;500;600;700&family=Open+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Professional glassy color palette matching dashboard */
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
            /* Added proper font family definitions */
            --font-primary: 'Work Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --font-secondary: 'Open Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #ecfeff 0%, #cffafe 25%, #a5f3fc 50%, #67e8f9 75%, #22d3ee 100%);
            min-height: 100vh;
            /* Updated font family to use CSS variable */
            font-family: var(--font-secondary);
            margin: 0;
            padding: 0;
            position: relative;
            overflow-x: hidden;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 210px;
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
            /* Added proper font family for sidebar navigation */
            font-family: var(--font-primary);
            font-weight: 500;
            font-size: 0.95rem;
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

        .nav-link span {
            /* Ensured sidebar text uses primary font */
            font-family: var(--font-primary);
            font-weight: 500;
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
            position: relative;
        }

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
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: var(--transition);
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

        .content-section {
            padding: 2rem;
            margin: 1rem;
            backdrop-filter: blur(25px);
            background: rgba(255, 255, 255, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 20px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        .content-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            color: var(--text-primary);
            font-size: 1.75rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-family: var(--font-primary);
        }

        .table-container {
            backdrop-filter: blur(25px);
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow);
            margin-top: 1rem;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            background: transparent;
        }

        .users-table th {
            background: rgba(22, 78, 99, 0.1);
            color: var(--text-primary);
            font-weight: 600;
            padding: 1.25rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 0.9rem;
            text-align: left;
            font-family: var(--font-primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .users-table td {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-secondary);
            vertical-align: middle;
            font-family: var(--font-secondary);
        }

        .users-table tbody tr {
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
        }

        .users-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-1px);
        }

        .users-table tbody tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.08);
        }

        .users-table tbody tr:nth-child(even):hover {
            background: rgba(255, 255, 255, 0.18);
        }

        /* Added comprehensive DataTables styling to match glassy theme */
        .dataTables_wrapper {
            font-family: var(--font-secondary);
        }

        .dataTables_length,
        .dataTables_filter,
        .dataTables_info,
        .dataTables_paginate {
            margin: 1rem 0;
        }

        .dataTables_length label,
        .dataTables_filter label,
        .dataTables_info {
            color: var(--text-primary);
            font-weight: 500;
            font-family: var(--font-primary);
        }

        .dataTables_length select,
        .dataTables_filter input {
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            background: rgba(255, 255, 255, 0.2);
            color: var(--text-primary);
            backdrop-filter: blur(10px);
            transition: var(--transition);
            margin-left: 0.5rem;
        }

        .dataTables_length select:focus,
        .dataTables_filter input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(22, 78, 99, 0.1);
            background: rgba(255, 255, 255, 0.3);
        }

        .dataTables_paginate {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }

        .dataTables_paginate .paginate_button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1rem;
            margin: 0 0.25rem;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 12px;
            color: var(--text-primary) !important;
            text-decoration: none !important;
            font-weight: 500;
            font-size: 0.875rem;
            transition: var(--transition);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(22, 78, 99, 0.1);
            min-width: 44px;
            font-family: var(--font-primary);
        }

        .dataTables_paginate .paginate_button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(22, 78, 99, 0.2);
            color: var(--primary-color) !important;
        }

        .dataTables_paginate .paginate_button.current {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white !important;
            box-shadow: 0 8px 25px rgba(22, 78, 99, 0.3);
            transform: translateY(-1px);
        }

        .dataTables_paginate .paginate_button.current:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(22, 78, 99, 0.4);
        }

        .dataTables_paginate .paginate_button.disabled {
            background: rgba(255, 255, 255, 0.1) !important;
            color: var(--text-secondary) !important;
            cursor: not-allowed;
            opacity: 0.5;
        }

        .dataTables_paginate .paginate_button.disabled:hover {
            background: rgba(255, 255, 255, 0.1) !important;
            transform: none;
            box-shadow: 0 4px 15px rgba(22, 78, 99, 0.1);
        }

        .dataTables_paginate .paginate_button.previous,
        .dataTables_paginate .paginate_button.next {
            font-weight: 600;
            padding: 0.75rem 1.25rem;
        }

        .dataTables_paginate .paginate_button.previous:before {
            content: '← ';
            margin-right: 0.25rem;
        }

        .dataTables_paginate .paginate_button.next:after {
            content: ' →';
            margin-left: 0.25rem;
        }

        .dataTables_wrapper .dataTables_processing {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 16px;
            color: var(--text-primary);
            font-weight: 500;
            box-shadow: var(--shadow);
        }

        .dataTables_empty {
            color: var(--text-secondary);
            font-style: italic;
            text-align: center;
            padding: 2rem;
            font-family: var(--font-secondary);
        }

        /* Added comprehensive styling for Actions section elements */
        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 500;
            font-family: var(--font-primary);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            transition: var(--transition);
        }

        .role-badge.role-admin {
            background: linear-gradient(135deg, rgba(220, 38, 38, 0.2), rgba(185, 28, 28, 0.2));
            color: #dc2626;
            border-color: rgba(220, 38, 38, 0.3);
        }

        .role-badge.role-customer {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.2));
            color: #10b981;
            border-color: rgba(16, 185, 129, 0.3);
        }

        .status-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 0.5rem;
            box-shadow: 0 0 10px currentColor;
        }

        .status-indicator.status-active {
            background: #10b981;
            color: #10b981;
        }

        .status-indicator.status-inactive {
            background: #6b7280;
            color: #6b7280;
        }

        .role-select {
            padding: 0.5rem 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            color: var(--text-primary);
            font-size: 0.875rem;
            font-weight: 500;
            font-family: var(--font-primary);
            transition: var(--transition);
            cursor: pointer;
            min-width: 100px;
        }

        .role-select:focus {
            outline: none;
            border-color: var(--primary-color);
            background: rgba(255, 255, 255, 0.3);
            box-shadow: 0 0 0 3px rgba(22, 78, 99, 0.1);
        }

        .role-select:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.4);
        }

        .btn-update {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 500;
            font-family: var(--font-primary);
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(22, 78, 99, 0.2);
            backdrop-filter: blur(10px);
        }

        .btn-update:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(22, 78, 99, 0.3);
        }

        .btn-update:active {
            transform: translateY(0);
            box-shadow: 0 4px 15px rgba(22, 78, 99, 0.2);
        }

        .btn-update i {
            font-size: 0.8rem;
        }

        /* Enhanced Actions column layout */
        .actions-form {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .current-user-text {
            color: var(--text-secondary);
            font-style: italic;
            font-size: 0.875rem;
            font-family: var(--font-secondary);
            padding: 0.5rem 0;
        }

        /* Enhanced responsive design for Actions section */
        @media (max-width: 768px) {
            .actions-form {
                flex-direction: column;
                gap: 0.5rem;
                align-items: stretch;
            }

            .role-select {
                min-width: auto;
                width: 100%;
            }

            .btn-update {
                width: 100%;
                justify-content: center;
            }

            .role-badge {
                font-size: 0.8rem;
                padding: 0.4rem 0.8rem;
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

            .content-section {
                padding: 1rem;
                margin: 0.5rem;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .settings-container {
                margin: 1rem auto;
                padding: 0 0.5rem;
            }

            .settings-header {
                padding: 1.5rem;
            }

            .settings-title {
                font-size: 1.5rem;
            }

            .users-table th,
            .users-table td {
                padding: 0.75rem 0.5rem;
                font-size: 0.875rem;
            }

            .table-container {
                margin: 0 -1rem;
            }
        }
    </style>
</head>

<body>
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
                <a href="analytics.php" class="nav-link">
                    <i class="fas fa-chart-bar"></i>
                    <span>Analytics</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="settings.php" class="nav-link active">
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

    <div class="main-content" id="mainContent">
        <div class="top-bar">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>

            <div class="user-info">
                <div class="user-avatar" id="userAvatar" onclick="toggleProfileDropdown()">
                    <?php
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

                <input type="file" id="profilePhotoInput" accept="image/*" onchange="uploadProfilePhoto(this)">

                <div>
                    <div style="font-weight: 600;">
                        <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?>
                    </div>
                    <div style="font-size: 0.85rem; color: #64748b;">Administrator</div>
                </div>
            </div>
        </div>

        <div class="content-section">
            <div class="page-header">
                <h4 class="page-title">
                    <i class="fas fa-users-cog"></i> User Role Management
                </h4>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success fade-in-up">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger fade-in-up">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <div class="table-container">
                <!-- Updated table classes to use custom styling -->
                <table class="users-table" id="datatables">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Current Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users_query->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?= $user['id'] ?></strong></td>
                                <td>
                                    <strong><?= htmlspecialchars($user['name']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="role-badge role-<?= $user['role_play'] ?>">
                                        <i class="fas fa-<?= $user['role_play'] === 'admin' ? 'user-shield' : 'user' ?>"></i>
                                        <?= ucfirst($user['role_play']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-indicator status-<?= $user['is_active'] ? 'active' : 'inactive' ?>"></span>
                                    <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                </td>
                                <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <!-- Updated form structure and classes for better styling -->
                                        <form method="POST" class="actions-form">
                                            <input type="hidden" name="action" value="update_role">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <select name="role_play" class="role-select">
                                                <option value="admin" <?= $user['role_play'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                                <option value="customer" <?= $user['role_play'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                                            </select>
                                            <button type="submit" class="btn-update">
                                                <i class="fas fa-save"></i>
                                                Update
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <!-- Added proper class for current user text -->
                                        <span class="current-user-text">Current User</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#datatables').DataTable({
                "pageLength": 10,
                "lengthMenu": [
                    [5, 10, 25, 50, -1],
                    [5, 10, 25, 50, "All"]
                ],
                "language": {
                    "lengthMenu": "Show _MENU_ entries per page",
                    "zeroRecords": "No users found matching your search",
                    "info": "Showing _START_ to _END_ of _TOTAL_ users",
                    "infoEmpty": "No users available",
                    "infoFiltered": "(filtered from _MAX_ total users)",
                    "search": "Search users:",
                    "paginate": {
                        "first": "First",
                        "last": "Last",
                        "next": "Next",
                        "previous": "Previous"
                    }
                },
                "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                "responsive": true,
                "order": [
                    [0, "desc"]
                ],
                "columnDefs": [{
                    "orderable": false,
                    "targets": [6]
                }]
            });

            $('#sidebarToggle').click(function() {
                $('#sidebar').toggleClass('collapsed');
                $('#mainContent').toggleClass('expanded');
            });

            function toggleProfileDropdown() {
                const dropdown = document.getElementById('profileDropdown');
                dropdown.classList.toggle('show');
            }

            document.addEventListener('click', function(event) {
                const userAvatar = document.getElementById('userAvatar');
                const dropdown = document.getElementById('profileDropdown');

                if (!userAvatar.contains(event.target)) {
                    dropdown.classList.remove('show');
                }
            });

            window.toggleProfileDropdown = toggleProfileDropdown;
            window.uploadProfilePhoto = uploadProfilePhoto;
            window.removeProfilePhoto = removeProfilePhoto;
        });

        function uploadProfilePhoto(input) {
            if (input.files && input.files[0]) {
                const formData = new FormData();
                formData.append('photo', input.files[0]);
                formData.append('action', 'upload_profile_photo');

                Swal.fire({
                    toast: true,
                    icon: 'info',
                    title: 'Uploading photo...',
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });

                $.ajax({
                    url: 'profile_action.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                toast: true,
                                icon: 'success',
                                title: 'Profile photo updated successfully!',
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            Swal.fire('Error', response.message || 'Failed to upload photo', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire('Error', 'Upload failed: ' + error, 'error');
                    }
                });
            }
        }

        function removeProfilePhoto() {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will remove your profile photo!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'profile_action.php',
                        type: 'POST',
                        data: {
                            action: 'remove_profile_photo'
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    toast: true,
                                    icon: 'success',
                                    title: 'Profile photo removed successfully!',
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1500);
                            } else {
                                Swal.fire('Error', response.message || 'Failed to remove photo', 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire('Error', 'Remove failed: ' + error, 'error');
                        }
                    });
                }
            });
        }

        function logout() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You will be logged out!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, logout!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout.php';
                }
            });
        }

        window.logout = logout;
    </script>
</body>

</html>