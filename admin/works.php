<?php
require_once 'inc/header.php';
require_once '../app/db.php';
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
        width: var(--sidebar-width);
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
        font-size: 1.25rem;
    }

    .dashboard-content {
        padding: 2rem;
    }

    /* Enhanced table styling with glassy design */
    .table-container {
        overflow-x: auto;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
        background: transparent;
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
        transition: var(--transition);
    }

    .table tbody tr:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    /* Enhanced buttons with glassy design */
    .btn {
        padding: 0.5rem 1rem;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        transition: var(--transition);
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        backdrop-filter: blur(10px);
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        box-shadow: 0 4px 15px rgba(22, 78, 99, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(22, 78, 99, 0.4);
    }

    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }

    .btn-info {
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        color: white;
        box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3);
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--danger-color), #b91c1c);
        color: white;
        box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
    }

    .btn-success {
        background: linear-gradient(135deg, var(--success-color), #059669);
        color: white;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }

    .btn-secondary {
        background: rgba(107, 114, 128, 0.8);
        color: white;
        box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3);
    }

    .btn:hover {
        transform: translateY(-2px);
    }

    .img-thumbnail {
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(10px);
    }

    .badge {
        padding: 0.375rem 0.75rem;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .bg-secondary {
        background: rgba(107, 114, 128, 0.8) !important;
        color: white;
    }

    .text-muted {
        color: var(--text-secondary) !important;
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
            <a href="works.php" class="nav-link active">
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
        <!-- Works Content Card -->
        <div class="content-card">
            <div class="card-header">
                <h4 class="card-title">
                    <i class="fas fa-paint-brush"></i>
                    All Works
                </h4>
                <a href="create_works.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Add New
                </a>
            </div>

            <div class="table-container">
                <table class="table" id="datatables">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Thumbnail</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Link</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $works = $conn->query("SELECT * FROM works ORDER BY id DESC");
                        $i = 1;
                        while ($work = $works->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td>
                                    <?php if (!empty($work['image']) && file_exists("../assets/img/works/" . $work['image'])): ?>
                                        <img src="../assets/img/works/<?= htmlspecialchars($work['image']) ?>" width="70" height="50" class="img-thumbnail" alt="Work Image">
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($work['title']) ?></td>
                                <td><?= htmlspecialchars(substr($work['description'], 0, 50)) ?>...</td>
                                <td>
                                    <?php if (!empty($work['link'])): ?>
                                        <a href="<?= htmlspecialchars($work['link']) ?>" target="_blank" rel="noopener noreferrer">View Link</a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm toggle-status <?= $work['status'] ? 'btn-success' : 'btn-secondary' ?>" data-id="<?= $work['id'] ?>" data-status="<?= $work['status'] ?>">
                                        <?= $work['status'] ? '<i class="fas fa-toggle-on"></i>' : '<i class="fas fa-toggle-off"></i>' ?>
                                    </button>
                                </td>
                                <td><?= date('d M Y', strtotime($work['created_at'])) ?></td>
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
    </div>
</div>

<?php require_once 'inc/footer.php'; ?>

<script>
    $(document).ready(function() {
        // Initialize DataTables
        $('#datatables').DataTable();

        // Sidebar toggle functionality
        $('#sidebarToggle').click(function() {
            $('#sidebar').toggleClass('collapsed');
            $('#mainContent').toggleClass('expanded');
        });

        // Toggle status button click handler
        $('.toggle-status').click(function() {
            let button = $(this);
            let id = button.data('id');
            let currentStatus = button.data('status') === 1 || button.data('status') === '1' ? 1 : 0;

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
                        let newStatus = currentStatus === 1 ? 0 : 1;
                        button.data('status', newStatus);
                        button.toggleClass('btn-success btn-secondary');
                        button.html(newStatus === 1 ? '<i class="fas fa-toggle-on"></i>' : '<i class="fas fa-toggle-off"></i>');

                        Swal.fire({
                            toast: true,
                            icon: 'success',
                            title: response.message,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000
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

        // Delete button click handler
        $('.delete-work').click(function() {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "This work will be permanently deleted!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
            }).then(result => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'inc/action_works.php',
                        method: 'POST',
                        data: {
                            action: 'delete_work',
                            id: id
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire('Deleted!', response.message, 'success').then(() => location.reload());
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
    });

    function logout() {
        Swal.fire({
            title: 'Are you sure?',
            text: "You will be logged out!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, logout!',
        }).then(result => {
            if (result.isConfirmed) {
                window.location.href = 'logout.php';
            }
        });
    }
</script>