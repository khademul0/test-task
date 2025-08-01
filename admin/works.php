<?php 
require_once 'inc/header.php';
require_once '../app/db.php'; 
?>

<div class="container py-4">
    <div class="d-flex justify-content-between mb-3">
        <h4><i class="fas fa-paint-brush me-2"></i>All Works</h4>
        <a href="create_works.php" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add New</a>
    </div>

    <table class="table table-bordered table-striped" id="datatables">
        <thead class="table-dark">
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
                        <?php if(!empty($work['image']) && file_exists("../assets/img/works/" . $work['image'])): ?>
                            <img src="../assets/img/works/<?= htmlspecialchars($work['image']) ?>" width="70" height="50" class="img-thumbnail" alt="Work Image">
                        <?php else: ?>
                            <span class="badge bg-secondary">No Image</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($work['title']) ?></td>
                    <td><?= htmlspecialchars(substr($work['description'], 0, 50)) ?>...</td>
                    <td>
                        <?php if(!empty($work['link'])): ?>
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

<?php require_once 'inc/footer.php'; ?>

<script>
$(document).ready(function() {
    // Initialize DataTables
    $('#datatables').DataTable();

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
</script>
