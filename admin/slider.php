<?php
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/../app/db.php';
?>

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
    }

    /* Updated body background and font to match dashboard exactly */
    body {
        background: linear-gradient(135deg, #ecfeff 0%, #cffafe 25%, #a5f3fc 50%, #67e8f9 75%, #22d3ee 100%);
        min-height: 100vh;
        font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        margin: 0;
        padding: 0;
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

    /* Enhanced nav links to match dashboard styling exactly */
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

    /* Enhanced profile dropdown to match dashboard styling exactly */
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

    /* Hidden file input */
    #profilePhotoInput {
        display: none;
    }

    /* Content styling to match dashboard cards exactly */
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
        font-family: 'Work Sans', sans-serif;
    }

    /* Enhanced buttons to match dashboard styling exactly */
    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        border: none;
        border-radius: 10px;
        padding: 0.5rem 1rem;
        color: white;
        text-decoration: none;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 15px rgba(22, 78, 99, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(22, 78, 99, 0.4);
        color: white;
    }

    /* Table container to match dashboard styling exactly */
    .table-container {
        backdrop-filter: blur(25px);
        background: rgba(255, 255, 255, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 20px;
        padding: 0;
        box-shadow: var(--shadow);
        overflow: hidden;
        position: relative;
    }

    .table-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
    }

    .table {
        margin-bottom: 0;
        width: 100%;
        border-collapse: collapse;
    }

    .table th {
        background: rgba(255, 255, 255, 0.1);
        border: none;
        color: var(--text-primary);
        font-weight: 600;
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        font-size: 0.9rem;
    }

    .table td {
        border: none;
        padding: 1rem;
        vertical-align: middle;
        color: var(--text-secondary);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .table tbody tr {
        transition: all 0.3s ease;
    }

    .table tbody tr:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    /* Enhanced button styling to match dashboard exactly */
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

    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.8rem;
    }

    .btn-warning {
        background: linear-gradient(135deg, var(--warning-color), #f97316);
        border: none;
        color: white;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
    }

    .btn-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--danger-color), #f87171);
        border: none;
        color: white;
        box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
    }

    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(220, 38, 38, 0.4);
    }

    .btn-info {
        background: linear-gradient(135deg, var(--accent-color), #fbbf24);
        border: none;
        color: white;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
    }

    .btn-info:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
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

    /* Added responsive design to match dashboard */
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
            <a href="analytics.php" class="nav-link">
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
                <div style="font-weight: 600;">
                    <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?>
                </div>
                <div style="font-size: 0.85rem; color: #64748b;">Administrator</div>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="content-section">
        <div class="page-header">
            <h4 class="page-title">
                <i class="fas fa-sliders-h"></i> Manage Slides
            </h4>
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Slide
            </a>
        </div>

        <div class="table-container">
            <table class="table table-bordered" id="datatables">
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
                                <td><?= $sl++; ?></td>
                                <td><?= htmlspecialchars($row['title']); ?></td>
                                <td>
                                    <?php if (!empty($row['image']) && file_exists(__DIR__ . '/../assets/images/slides/' . $row['image'])): ?>
                                        <img src="../assets/images/slides/<?= $row['image']; ?>" width="60" alt="Slide Image">
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= date('d M Y, h:i A', strtotime($row['start_time'])); ?><br>to<br><?= date('d M Y, h:i A', strtotime($row['end_time'])); ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $row['status'] === 'Active' ? 'success' : 'secondary'; ?>" id="status-label-<?= $row['id']; ?>">
                                        <?= $row['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning" title="Edit Slide">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger btn-delete" data-id="<?= $row['id']; ?>" title="Delete Slide">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>

                                    <button class="btn btn-sm btn-info btn-status-toggle" data-id="<?= $row['id']; ?>" title="Toggle Status">
                                        <i class="fas fa-toggle-<?= $row['status'] === 'Active' ? 'on' : 'off'; ?>"></i>
                                    </button>

                                </td>
                            </tr>
                        <?php
                        endwhile;
                    else:
                        ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No slides found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>

<script>
    $(document).ready(function() {
        $('#datatables').DataTable();

        $('#sidebarToggle').click(function() {
            $('#sidebar').toggleClass('collapsed');
            $('#mainContent').toggleClass('expanded');
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
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
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

        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userAvatar = document.getElementById('userAvatar');
            const dropdown = document.getElementById('profileDropdown');

            if (!userAvatar.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });

        // Make functions global
        window.toggleProfileDropdown = toggleProfileDropdown;
        window.uploadProfilePhoto = uploadProfilePhoto;
        window.removeProfilePhoto = removeProfilePhoto;
    });

    function uploadProfilePhoto(input) {
        if (input.files && input.files[0]) {
            const formData = new FormData();
            formData.append('photo', input.files[0]);
            formData.append('action', 'upload_profile_photo');

            // Show loading
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
                        // Reload the page to show new photo
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

    // Make logout function global
    window.logout = logout;
</script>