<?php
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/../app/db.php';

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
    /* Custom Dashboard Styles */
    body {
        background-color: #f8f9fa;
    }

    .card-stats {
        transition: transform 0.2s;
        border-radius: 10px;
        overflow: hidden;
        cursor: pointer;
    }

    .card-stats:hover {
        transform: translateY(-5px);
    }

    .card-stats .card-body {
        padding: 1.5rem;
    }

    .card-stats .card-icon {
        font-size: 2rem;
        color: #fff;
    }

    .card-stats .card-title {
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
    }

    .card-stats .card-value {
        font-size: 2rem;
        font-weight: bold;
    }

    .table-responsive {
        border-radius: 10px;
        overflow: hidden;
    }

    .table th,
    .table td {
        vertical-align: middle;
    }

    .badge {
        font-size: 0.9rem;
        padding: 0.5em 0.75em;
    }

    .loader-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    .loader {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        width: 40px;
        height: 40px;
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

    /* Activity Log Styles */
    .activity-log {
        background: #fff;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
</style>

<section class="py-5">
    <div class="container">
        <!-- Welcome Message -->
        <div class="mb-4">
            <h2 class="text-primary">
                <i class="fas fa-tachometer-alt me-2"></i>
                Welcome, <?= isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin'; ?>!
            </h2>
            <p class="text-muted">Here's an overview of your dashboard</p>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card card-stats shadow-sm" onclick="window.location.href='works.php'">
                    <div class="card-body bg-primary text-white">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-briefcase card-icon me-3"></i>
                            <div>
                                <h5 class="card-title">Total Works</h5>
                                <div class="card-value"><?= $total_works ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stats shadow-sm" onclick="window.location.href='works.php'">
                    <div class="card-body bg-success text-white">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle card-icon me-3"></i>
                            <div>
                                <h5 class="card-title">Active Works</h5>
                                <div class="card-value"><?= $active_works ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stats shadow-sm" onclick="window.location.href='slider.php'">
                    <div class="card-body bg-info text-white">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-sliders-h card-icon me-3"></i>
                            <div>
                                <h5 class="card-title">Total Slides</h5>
                                <div class="card-value"><?= $total_slides ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stats shadow-sm" onclick="window.location.href='slider.php'">
                    <div class="card-body bg-warning text-white">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-toggle-on card-icon me-3"></i>
                            <div>
                                <h5 class="card-title">Active Slides</h5>
                                <div class="card-value"><?= $active_slides ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Log -->
        <div class="mb-5 activity-log">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="text-primary"><i class="fas fa-history me-2"></i>Recent Activity</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
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
        <div class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="text-primary"><i class="fas fa-paint-brush me-2"></i>Recent Works</h4>
                <a href="works.php" class="btn btn-sm btn-primary">
                    <i class="fas fa-eye me-1"></i> View All
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="worksTable">
                    <thead class="table-dark">
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
                            // Log actions (example: this should be integrated into action_works.php and action.php)
                            if (false) { // Placeholder for logging logic, to be added in action files
                                $conn->query("INSERT INTO activity_logs (user_id, action, description, created_at) VALUES (" . intval($_SESSION['user_id']) . ", 'View Work', 'Viewed work: " . htmlspecialchars($work['title']) . "', NOW())");
                            }
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
        <div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="text-primary"><i class="fas fa-sliders-h me-2"></i>Recent Slides</h4>
                <a href="slider.php" class="btn btn-sm btn-primary">
                    <i class="fas fa-eye me-1"></i> View All
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="slidesTable">
                    <thead class="table-dark">
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
                                        <img src="../assets/images/slides/<?= $row['image'] ?>" width="60" alt="Slide Image">
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= date('d M Y, h:i A', strtotime($row['start_time'])) ?><br>to<br><?= date('d M Y, h:i A', strtotime($row['end_time'])) ?>
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
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>

<script>
    // Initialize DataTables
    $(document).ready(function() {
        $('#worksTable').DataTable({
            "pageLength": 5,
            "lengthChange": false,
            "searching": false,
            "ordering": false
        });

        $('#slidesTable').DataTable({
            "pageLength": 5,
            "lengthChange": false,
            "searching": false,
            "ordering": false
        });

        // Toggle work status
        $('.toggle-status').click(function() {
            let button = $(this);
            let id = button.data('id');
            let currentStatus = button.data('status') === 1 ? 1 : 0;

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

        // Delete work
        $('.delete-work').click(function() {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "This work will be permanently deleted!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!'
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

        // Delete slide
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

        // Toggle slide status
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
</script>