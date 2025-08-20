<?php
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/../app/db.php';
?>

<style>
    /* Modern Dashboard Styles - Matching dashboard.php */
    :root {
        --primary-color: #4f46e5;
        --secondary-color: #f8fafc;
        --accent-color: #06b6d4;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --danger-color: #ef4444;
        --dark-color: #1e293b;
        --light-color: #ffffff;
        --sidebar-width: 280px;
        --sidebar-collapsed-width: 80px;
    }

    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    /* Sidebar Styles */
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: var(--sidebar-width);
        background: linear-gradient(180deg, var(--dark-color) 0%, #334155 100%);
        transition: all 0.3s ease;
        z-index: 1000;
        box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
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
    }

    .sidebar-logo {
        width: 40px;
        height: 40px;
        background: var(--primary-color);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        font-weight: bold;
    }

    .sidebar-title {
        color: white;
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0;
        transition: opacity 0.3s ease;
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
        border-radius: 10px;
        transition: all 0.3s ease;
        position: relative;
    }

    .nav-link:hover,
    .nav-link.active {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        transform: translateX(5px);
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

    .top-bar {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        padding: 1rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .sidebar-toggle {
        background: none;
        border: none;
        font-size: 1.2rem;
        color: var(--dark-color);
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 8px;
        transition: background 0.3s ease;
    }

    .sidebar-toggle:hover {
        background: rgba(0, 0, 0, 0.1);
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 1rem;
        position: relative;
    }

    .user-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 1.2rem;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
        border: 3px solid white;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .user-avatar:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
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
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        padding: 0.5rem 0;
        min-width: 200px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
        z-index: 1000;
        border: 1px solid #e2e8f0;
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
        transition: background 0.2s ease;
        color: var(--dark-color);
        font-size: 0.9rem;
    }

    .profile-dropdown-item:hover {
        background: #f8fafc;
    }

    .profile-dropdown-item i {
        width: 16px;
        color: #64748b;
    }

    #profilePhotoInput {
        display: none;
    }

    /* Enhanced notification styles */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 10px;
        color: white;
        font-weight: 500;
        z-index: 9999;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
    }

    .notification.show {
        opacity: 1;
        transform: translateX(0);
    }

    .notification.success {
        background: linear-gradient(135deg, var(--success-color), #34d399);
    }

    .notification.error {
        background: linear-gradient(135deg, var(--danger-color), #f87171);
    }

    .notification.info {
        background: linear-gradient(135deg, var(--accent-color), #0ea5e9);
    }

    /* Dashboard Content */
    .dashboard-content {
        padding: 2rem;
    }

    .welcome-section {
        background: linear-gradient(135deg, var(--light-color) 0%, #f1f5f9 100%);
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    .welcome-title {
        color: var(--dark-color);
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .welcome-subtitle {
        color: #64748b;
        font-size: 1.1rem;
        margin: 0;
    }

    /* Content Cards */
    .content-card {
        background: var(--light-color);
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .content-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f1f5f9;
    }

    .card-title {
        color: var(--dark-color);
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .card-title i {
        color: var(--primary-color);
    }

    /* Modern Table Styles */
    .table-container {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }

    .modern-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }

    .modern-table thead {
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        color: white;
    }

    .modern-table thead th {
        padding: 1.25rem 1rem;
        font-weight: 600;
        text-align: left;
        border: none;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .modern-table tbody tr {
        transition: all 0.3s ease;
        border-bottom: 1px solid #f1f5f9;
    }

    .modern-table tbody tr:hover {
        background: #f8fafc;
        transform: scale(1.01);
    }

    .modern-table tbody td {
        padding: 1.25rem 1rem;
        border: none;
        vertical-align: middle;
    }

    .modern-table tbody tr:last-child {
        border-bottom: none;
    }

    /* Modern Buttons */
    .btn {
        border-radius: 10px;
        font-weight: 500;
        padding: 0.75rem 1.5rem;
        transition: all 0.3s ease;
        border: none;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        text-decoration: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        color: white;
    }

    .btn-success {
        background: linear-gradient(135deg, var(--success-color), #34d399);
        color: white;
    }

    .btn-warning {
        background: linear-gradient(135deg, var(--warning-color), #fbbf24);
        color: white;
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--danger-color), #f87171);
        color: white;
    }

    .btn-info {
        background: linear-gradient(135deg, var(--accent-color), #0ea5e9);
        color: white;
    }

    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }

    /* Modern Badges */
    .badge {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.5rem 0.75rem;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .bg-success {
        background: linear-gradient(135deg, var(--success-color), #34d399) !important;
        color: white;
    }

    .bg-secondary {
        background: linear-gradient(135deg, #6b7280, #9ca3af) !important;
        color: white;
    }

    /* Image Styling */
    .slide-image {
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .slide-image:hover {
        transform: scale(1.1);
    }

    /* Time Display */
    .time-display {
        font-size: 0.85rem;
        line-height: 1.4;
        color: #64748b;
    }

    /* Action Buttons Container */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    /* Responsive */
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

        .dashboard-content {
            padding: 1rem;
        }

        .card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .action-buttons {
            flex-direction: column;
            width: 100%;
        }

        .btn-sm {
            width: 100%;
            justify-content: center;
        }
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in-up {
        animation: fadeInUp 0.6s ease forwards;
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
            <a href="slider.php" class="nav-link active">
                <i class="fas fa-sliders-h"></i>
                <span>Slides</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="#" class="nav-link">
                <i class="fas fa-chart-bar"></i>
                <span>Analytics</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="#" class="nav-link">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="#" class="nav-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
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
            <!-- Enhanced user avatar with profile photo functionality from dashboard.php -->
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
                <div style="font-size: 0.875rem; color: #64748b;">
                    Administrator
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Content -->
    <div class="dashboard-content">
        <!-- Welcome Section -->
        <div class="welcome-section fade-in-up">
            <h1 class="welcome-title">
                <i class="fas fa-sliders-h"></i>
                Manage Slides
            </h1>
            <p class="welcome-subtitle">Create, edit, and manage your website slides with ease</p>
        </div>

        <!-- Content Card -->
        <div class="content-card fade-in-up" style="animation-delay: 0.2s;">
            <div class="card-header">
                <h4 class="card-title">
                    <i class="fas fa-images"></i>
                    All Slides
                </h4>
                <a href="create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Add Slide
                </a>
            </div>

            <div class="table-container">
                <table class="modern-table" id="datatables">
                    <thead>
                        <tr>
                            <th>SL No</th>
                            <th>Title</th>
                            <th>Image</th>
                            <th>Time Limit</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM slides ORDER BY id DESC";
                        $result = $conn->query($sql);
                        $sl = 1;

                        if ($result && $result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):
                        ?>
                                <tr id="row-<?= $row['id']; ?>">
                                    <td><strong><?= $sl++; ?></strong></td>
                                    <td>
                                        <strong style="color: var(--dark-color);"><?= htmlspecialchars($row['title']); ?></strong>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['image']) && file_exists(__DIR__ . '/../assets/images/slides/' . $row['image'])): ?>
                                            <img src="../assets/images/slides/<?= $row['image']; ?>" width="60" alt="Slide Image" class="slide-image">
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="time-display">
                                            <strong>From:</strong> <?= date('d M Y, h:i A', strtotime($row['start_time'])); ?><br>
                                            <strong>To:</strong> <?= date('d M Y, h:i A', strtotime($row['end_time'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $row['status'] === 'Active' ? 'success' : 'secondary'; ?>" id="status-label-<?= $row['id']; ?>">
                                            <?= $row['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning" title="Edit Slide">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <button class="btn btn-sm btn-danger btn-delete" data-id="<?= $row['id']; ?>" title="Delete Slide">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>

                                            <button class="btn btn-sm btn-info btn-status-toggle" data-id="<?= $row['id']; ?>" title="Toggle Status">
                                                <i class="fas fa-toggle-<?= $row['status'] === 'Active' ? 'on' : 'off'; ?>"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                            endwhile;
                        else:
                            ?>
                            <tr>
                                <td colspan="6" class="text-center" style="color: #64748b; padding: 3rem;">
                                    <i class="fas fa-images" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i><br>
                                    <strong>No slides found.</strong><br>
                                    <small>Create your first slide to get started!</small>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>

<script>
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
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
            });
        }
    });

    $(document).ready(function() {
        if ($.fn.DataTable.isDataTable('#datatables')) {
            $('#datatables').DataTable().destroy();
        }

        $('#datatables').DataTable({
            "pageLength": 10,
            "responsive": true,
            "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                '<"row"<"col-sm-12"tr>>' +
                '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        });

        const urlParams = new URLSearchParams(window.location.search);
        const msg = urlParams.get('msg');
        if (msg) {
            let toastText = '';
            if (msg === 'created') toastText = 'Slide created successfully!';
            else if (msg === 'updated') toastText = 'Slide updated successfully!';
            else if (msg === 'deleted') toastText = 'Slide deleted successfully!';

            if (toastText) {
                Swal.fire({
                    toast: true,
                    icon: 'success',
                    title: toastText,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        }

        // Delete slide with confirmation
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
                    $.ajax({
                        url: 'inc/action.php',
                        method: 'POST',
                        data: {
                            delete_slide: 1,
                            id: slideId
                        },
                        dataType: 'json',
                        success: function(response) {
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
                                    timer: 3000
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'AJAX request failed', 'error');
                        }
                    });
                }
            });
        });

        // Toggle status
        $('.btn-status-toggle').click(function() {
            const slideId = $(this).data('id');
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
                            icon.removeClass('fa-toggle-off').addClass('fa-toggle-on');
                            statusLabel.removeClass('bg-secondary').addClass('bg-success').text('Active');
                        } else {
                            icon.removeClass('fa-toggle-on').addClass('fa-toggle-off');
                            statusLabel.removeClass('bg-success').addClass('bg-secondary').text('Inactive');
                        }

                        Swal.fire({
                            toast: true,
                            icon: 'success',
                            title: response.message,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'AJAX request failed', 'error');
                }
            });
        });
    });

    function toggleProfileDropdown() {
        const dropdown = document.getElementById('profileDropdown');
        dropdown.classList.toggle('show');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const avatar = document.getElementById('userAvatar');
        const dropdown = document.getElementById('profileDropdown');

        if (!avatar.contains(event.target)) {
            dropdown.classList.remove('show');
        }
    });

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

    function showNotification(message, type) {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(notification => notification.remove());

        // Create new notification
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);

        // Show notification
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);

        // Hide notification after 3 seconds
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
</script>