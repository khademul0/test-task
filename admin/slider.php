<?php
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/../app/db.php';
?>

<section class="py-4 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 text-primary"><i class="fas fa-sliders-h me-2"></i> Manage Slides</h4>
            <a href="create.php" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Add Slide
            </a>
        </div>

        <div class="table-responsive">
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
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>

<script>
    $(document).ready(function() {
        $('#datatables').DataTable();

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
    });
</script>