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
                                    <span class="text-muted">No Image</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= date('d M Y, h:i A', strtotime($row['start_time'])); ?><br>to<br><?= date('d M Y, h:i A', strtotime($row['end_time'])); ?>
                            </td>
                            <td><span class="badge bg-<?= $row['status'] === 'Active' ? 'success' : 'secondary'; ?>"><?= $row['status']; ?></span></td>
                            <td>
                                <a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                <button class="btn btn-sm btn-danger btn-delete" data-id="<?= $row['id']; ?>">Delete</button>
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

    // Delete slide with SweetAlert confirmation and AJAX
    $('.btn-delete').click(function() {
        var slideId = $(this).data('id');

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
                // AJAX request to delete slide
                $.ajax({
                    url: 'inc/action.php',
                    method: 'POST',
                    data: { delete_slide: 1, id: slideId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#row-' + slideId).fadeOut(500, function() {
                                $(this).remove();
                            });
                            Swal.fire('Deleted!', response.message, 'success');
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
</script>
