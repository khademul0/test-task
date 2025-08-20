<?php
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/../app/db.php';
?>

<style>
    /* === Shared Modern Dashboard Styles (same look as slider.php) === */
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

    /* Sidebar */
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: var(--sidebar-width);
        background: linear-gradient(180deg, var(--dark-color) 0%, #334155 100%);
        transition: all .3s ease;
        z-index: 1000;
        box-shadow: 4px 0 20px rgba(0, 0, 0, .1)
    }

    .sidebar.collapsed {
        width: var(--sidebar-collapsed-width)
    }

    .sidebar-header {
        padding: 1.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, .1);
        display: flex;
        align-items: center;
        gap: 1rem
    }

    .sidebar-logo {
        width: 40px;
        height: 40px;
        background: var(--primary-color);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.2rem;
        font-weight: bold
    }

    .sidebar-title {
        color: #fff;
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0;
        transition: opacity .3s ease
    }

    .sidebar.collapsed .sidebar-title {
        opacity: 0
    }

    .sidebar-nav {
        padding: 1rem 0
    }

    .nav-item {
        margin: .25rem 1rem
    }

    .nav-link {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: .75rem 1rem;
        color: rgba(255, 255, 255, .8);
        text-decoration: none;
        border-radius: 10px;
        transition: all .3s ease;
        position: relative
    }

    .nav-link:hover,
    .nav-link.active {
        background: rgba(255, 255, 255, .1);
        color: #fff;
        transform: translateX(5px)
    }

    .nav-link i {
        width: 20px;
        text-align: center;
        font-size: 1.1rem
    }

    .sidebar.collapsed .nav-link span {
        opacity: 0
    }

    /* Main */
    .main-content {
        margin-left: var(--sidebar-width);
        transition: margin-left .3s ease;
        min-height: 100vh
    }

    .main-content.expanded {
        margin-left: var(--sidebar-collapsed-width)
    }

    .top-bar {
        background: rgba(255, 255, 255, .95);
        backdrop-filter: blur(10px);
        padding: 1rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 20px rgba(0, 0, 0, .1);
        position: sticky;
        top: 0;
        z-index: 100
    }

    .sidebar-toggle {
        background: none;
        border: none;
        font-size: 1.2rem;
        color: var(--dark-color);
        cursor: pointer;
        padding: .5rem;
        border-radius: 8px;
        transition: background .3s ease
    }

    .sidebar-toggle:hover {
        background: rgba(0, 0, 0, .1)
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 1rem;
        position: relative
    }

    .user-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 600;
        font-size: 1.2rem;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        transition: all .3s ease;
        border: 3px solid #fff;
        box-shadow: 0 4px 15px rgba(0, 0, 0, .1)
    }

    .user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%
    }

    .user-avatar:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(0, 0, 0, .15)
    }

    .profile-upload-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, .7);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity .3s ease;
        color: #fff;
        font-size: .9rem
    }

    .user-avatar:hover .profile-upload-overlay {
        opacity: 1
    }

    .profile-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, .15);
        padding: .5rem 0;
        min-width: 200px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all .3s ease;
        z-index: 1000;
        border: 1px solid #e2e8f0
    }

    .profile-dropdown.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0)
    }

    .profile-dropdown-item {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .75rem 1rem;
        cursor: pointer;
        transition: background .2s ease;
        color: var(--dark-color);
        font-size: .9rem
    }

    .profile-dropdown-item:hover {
        background: #f8fafc
    }

    .profile-dropdown-item i {
        width: 16px;
        color: #64748b
    }

    #profilePhotoInput {
        display: none
    }

    /* Content */
    .dashboard-content {
        padding: 2rem
    }

    .welcome-section {
        background: linear-gradient(135deg, var(--light-color) 0%, #f1f5f9 100%);
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, .1)
    }

    .welcome-title {
        color: var(--dark-color);
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: .5rem;
        display: flex;
        align-items: center;
        gap: 1rem
    }

    .welcome-subtitle {
        color: #64748b;
        font-size: 1.1rem;
        margin: 0
    }

    .content-card {
        background: #fff;
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, .1);
        transition: all .3s ease
    }

    .content-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 60px rgba(0, 0, 0, .15)
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f1f5f9
    }

    .card-title {
        color: var(--dark-color);
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: .75rem
    }

    .card-title i {
        color: var(--primary-color)
    }

    .table-container {
        background: #fff;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, .1)
    }

    .modern-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0
    }

    .modern-table thead {
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        color: #fff
    }

    .modern-table thead th {
        padding: 1.25rem 1rem;
        font-weight: 600;
        text-align: left;
        border: none;
        font-size: .9rem;
        text-transform: uppercase;
        letter-spacing: .5px
    }

    .modern-table tbody tr {
        transition: all .3s ease;
        border-bottom: 1px solid #f1f5f9
    }

    .modern-table tbody tr:hover {
        background: #f8fafc;
        transform: scale(1.01)
    }

    .modern-table tbody td {
        padding: 1.25rem 1rem;
        border: none;
        vertical-align: middle
    }

    .modern-table tbody tr:last-child {
        border-bottom: none
    }

    .btn {
        border-radius: 10px;
        font-weight: 500;
        padding: .75rem 1.5rem;
        transition: all .3s ease;
        border: none;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        cursor: pointer
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, .2);
        text-decoration: none
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        color: #fff
    }

    .btn-warning {
        background: linear-gradient(135deg, var(--warning-color), #fbbf24);
        color: #fff
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--danger-color), #f87171);
        color: #fff
    }

    .btn-info {
        background: linear-gradient(135deg, var(--accent-color), #0ea5e9);
        color: #fff
    }

    .btn-success {
        background: linear-gradient(135deg, var(--success-color), #34d399);
        color: #fff
    }

    .btn-secondary {
        background: linear-gradient(135deg, #6b7280, #9ca3af);
        color: #fff
    }

    .btn-sm {
        padding: .5rem 1rem;
        font-size: .875rem
    }

    .badge {
        border-radius: 8px;
        font-weight: 500;
        padding: .5rem .75rem;
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .5px
    }

    .bg-success {
        background: linear-gradient(135deg, var(--success-color), #34d399) !important;
        color: #fff
    }

    .bg-secondary {
        background: linear-gradient(135deg, #6b7280, #9ca3af) !important;
        color: #fff
    }

    .thumb {
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .1);
        transition: transform .3s ease
    }

    .thumb:hover {
        transform: scale(1.08)
    }

    .action-buttons {
        display: flex;
        gap: .5rem;
        align-items: center
    }

    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 10px;
        color: #fff;
        font-weight: 500;
        z-index: 9999;
        opacity: 0;
        transform: translateX(100%);
        transition: all .3s ease
    }

    .notification.show {
        opacity: 1;
        transform: translateX(0)
    }

    .notification.success {
        background: linear-gradient(135deg, var(--success-color), #34d399)
    }

    .notification.error {
        background: linear-gradient(135deg, var(--danger-color), #f87171)
    }

    .notification.info {
        background: linear-gradient(135deg, var(--accent-color), #0ea5e9)
    }

    @media (max-width:768px) {
        .sidebar {
            transform: translateX(-100%)
        }

        .sidebar.show {
            transform: translateX(0)
        }

        .main-content {
            margin-left: 0
        }

        .dashboard-content {
            padding: 1rem
        }

        .card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem
        }

        .action-buttons {
            flex-direction: column;
            width: 100%
        }

        .btn-sm {
            width: 100%;
            justify-content: center
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px)
        }

        to {
            opacity: 1;
            transform: translateY(0)
        }
    }

    .fade-in-up {
        animation: fadeInUp .6s ease forwards
    }
</style>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo"><i class="fas fa-cube"></i></div>
        <h3 class="sidebar-title">Dashboard</h3>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-item">
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="works.php" class="nav-link active">
                <i class="fas fa-briefcase"></i><span>Works</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="slider.php" class="nav-link">
                <i class="fas fa-sliders-h"></i><span>Slides</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="#" class="nav-link">
                <i class="fas fa-chart-bar"></i><span>Analytics</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="#" class="nav-link">
                <i class="fas fa-cog"></i><span>Settings</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i><span>Logout</span>
            </a>
        </div>
    </nav>
</div>

<!-- Main -->
<div class="main-content" id="mainContent">
    <!-- Top Bar -->
    <div class="top-bar">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>

        <div class="user-info">
            <div class="user-avatar" id="userAvatar" onclick="toggleProfileDropdown()">
                <?php
                $user_id = intval($_SESSION['user_id'] ?? 0);
                $stmt = $conn->prepare("SELECT photo FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $user_data = $stmt->get_result()->fetch_assoc();
                $profile_photo = $user_data['photo'] ?? null;

                if ($profile_photo && file_exists("../assets/images/profiles/" . $profile_photo)): ?>
                    <img src="../assets/images/profiles/<?= htmlspecialchars($profile_photo) ?>" alt="Profile Photo">
                    <div class="profile-upload-overlay"><i class="fas fa-camera"></i></div>
                <?php else: ?>
                    <?= strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)) ?>
                    <div class="profile-upload-overlay"><i class="fas fa-camera"></i></div>
                <?php endif; ?>

                <div class="profile-dropdown" id="profileDropdown">
                    <div class="profile-dropdown-item" onclick="document.getElementById('profilePhotoInput').click()">
                        <i class="fas fa-camera"></i><span>Change Photo</span>
                    </div>
                    <div class="profile-dropdown-item" onclick="removeProfilePhoto()">
                        <i class="fas fa-trash"></i><span>Remove Photo</span>
                    </div>
                    <hr style="margin:.5rem 0;border:none;border-top:1px solid #e2e8f0;">
                    <div class="profile-dropdown-item" onclick="window.location.href='profile.php'">
                        <i class="fas fa-user"></i><span>Profile Settings</span>
                    </div>
                    <div class="profile-dropdown-item" onclick="logout()">
                        <i class="fas fa-sign-out-alt"></i><span>Logout</span>
                    </div>
                </div>
            </div>
            <input type="file" id="profilePhotoInput" accept="image/*" onchange="uploadProfilePhoto(this)">
            <div>
                <div style="font-weight:600;color:var(--dark-color);"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></div>
                <div style="font-size:.875rem;color:#64748b;">Administrator</div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="dashboard-content">
        <div class="welcome-section fade-in-up">
            <h1 class="welcome-title"><i class="fas fa-briefcase"></i> Manage Works</h1>
            <p class="welcome-subtitle">Create, edit, and manage your portfolio works</p>
        </div>

        <div class="content-card fade-in-up" style="animation-delay:.2s">
            <div class="card-header">
                <h4 class="card-title"><i class="fas fa-images"></i> All Works</h4>
                <a href="create_works.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Work
                </a>
            </div>

            <div class="table-container">
                <table class="modern-table" id="datatables">
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
                        $res = $conn->query("SELECT * FROM works ORDER BY id DESC");
                        $i = 1;
                        if ($res && $res->num_rows > 0):
                            while ($work = $res->fetch_assoc()):
                                $imgFile = $work['image'] ?? '';
                                $imgPath = __DIR__ . '/../assets/img/works/' . $imgFile;
                                $hasImg  = !empty($imgFile) && file_exists($imgPath);
                                $active  = intval($work['status']) === 1;
                                $desc    = trim($work['description'] ?? '');
                                $descCut = mb_substr($desc, 0, 80) . (mb_strlen($desc) > 80 ? '...' : '');
                        ?>
                                <tr id="row-<?= $work['id'] ?>">
                                    <td><strong><?= $i++ ?></strong></td>
                                    <td>
                                        <?php if ($hasImg): ?>
                                            <img src="../assets/img/works/<?= htmlspecialchars($imgFile) ?>" width="70" height="50" class="thumb" alt="Work Image">
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong style="color:var(--dark-color)"><?= htmlspecialchars($work['title']) ?></strong></td>
                                    <td style="color:#475569"><?= htmlspecialchars($descCut) ?></td>
                                    <td>
                                        <?php if (!empty($work['link'])): ?>
                                            <a href="<?= htmlspecialchars($work['link']) ?>" target="_blank" rel="noopener noreferrer">View</a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $active ? 'bg-success' : 'bg-secondary' ?>" id="status-label-<?= $work['id'] ?>">
                                            <?= $active ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td><?= !empty($work['created_at']) ? date('d M Y, h:i A', strtotime($work['created_at'])) : '-' ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit_work.php?id=<?= $work['id'] ?>" class="btn btn-sm btn-warning" title="Edit Work">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-danger delete-work" data-id="<?= $work['id'] ?>" title="Delete Work">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                            <button class="btn btn-sm btn-info btn-status-toggle" data-id="<?= $work['id'] ?>" title="Toggle Status">
                                                <i class="fas fa-toggle-<?= $active ? 'on' : 'off' ?>"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                            endwhile;
                        else:
                            ?>
                            <tr>
                                <td colspan="8" class="text-center" style="color:#64748b;padding:3rem">
                                    <i class="fas fa-briefcase" style="font-size:3rem;margin-bottom:1rem;opacity:.3"></i><br>
                                    <strong>No works found.</strong><br>
                                    <small>Add your first work to get started!</small>
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

            // mobile
            if (window.innerWidth <= 768) sidebar.classList.toggle('show');
        });
    });

    $(document).ready(function() {
        if ($.fn.DataTable.isDataTable('#datatables')) {
            $('#datatables').DataTable().destroy();
        }
        $('#datatables').DataTable({
            pageLength: 10,
            responsive: true,
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                '<"row"<"col-sm-12"tr>>' +
                '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
        });

        // URL toast (created/updated/deleted)
        const urlParams = new URLSearchParams(window.location.search);
        const msg = urlParams.get('msg');
        if (msg) {
            let toastText = '';
            if (msg === 'created') toastText = 'Work created successfully!';
            else if (msg === 'updated') toastText = 'Work updated successfully!';
            else if (msg === 'deleted') toastText = 'Work deleted successfully!';
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

        // Delete work
        $('.delete-work').click(function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: 'This work will be permanently deleted!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((res) => {
                if (res.isConfirmed) {
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
                                $('#row-' + id).fadeOut(500, function() {
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

        // Toggle status (server toggles using only id)
        $('.btn-status-toggle').click(function() {
            const id = $(this).data('id');
            $.ajax({
                url: 'inc/action_works.php',
                method: 'POST',
                data: {
                    action: 'toggle_status',
                    id: id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        const icon = $('#row-' + id + ' .btn-status-toggle i');
                        const label = $('#status-label-' + id);

                        if (response.new_status === 1 || response.new_status === '1') {
                            icon.removeClass('fa-toggle-off').addClass('fa-toggle-on');
                            label.removeClass('bg-secondary').addClass('bg-success').text('Active');
                        } else {
                            icon.removeClass('fa-toggle-on').addClass('fa-toggle-off');
                            label.removeClass('bg-success').addClass('bg-secondary').text('Inactive');
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

    // Avatar dropdown + profile photo actions (same as slider.php)
    function toggleProfileDropdown() {
        document.getElementById('profileDropdown').classList.toggle('show');
    }
    document.addEventListener('click', function(e) {
        const avatar = document.getElementById('userAvatar');
        const dropdown = document.getElementById('profileDropdown');
        if (avatar && dropdown && !avatar.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });

    function showNotification(message, type) {
        document.querySelectorAll('.notification').forEach(n => n.remove());
        const n = document.createElement('div');
        n.className = `notification ${type}`;
        n.textContent = message;
        document.body.appendChild(n);
        setTimeout(() => n.classList.add('show'), 100);
        setTimeout(() => {
            n.classList.remove('show');
            setTimeout(() => n.remove(), 300);
        }, 3000);
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
                success: function(res) {
                    if (res.status === 'success') {
                        showNotification('Profile photo updated!', 'success');
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        showNotification(res.message || 'Failed to upload', 'error');
                    }
                },
                error: function(_, __, err) {
                    showNotification('Upload failed: ' + err, 'error');
                }
            });
        }
    }

    function removeProfilePhoto() {
        if (confirm('Remove your profile photo?')) {
            $.ajax({
                url: 'profile_action.php',
                type: 'POST',
                data: {
                    action: 'remove_profile_photo'
                },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        showNotification('Profile photo removed!', 'success');
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        showNotification(res.message || 'Failed to remove', 'error');
                    }
                },
                error: function(_, __, err) {
                    showNotification('Remove failed: ' + err, 'error');
                }
            });
        }
    }

    function logout() {
        if (confirm('Are you sure you want to logout?')) window.location.href = 'logout.php';
    }
</script>