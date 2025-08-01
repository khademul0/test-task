<?php require_once 'inc/header.php'; ?>

<div class="container py-4">
    <h4><i class="fas fa-plus-circle me-2"></i>Add New Work</h4>
    <form action="inc/action_works.php" method="POST" enctype="multipart/form-data" id="createWorkForm">
        <input type="hidden" name="action" value="create_work">

        <div class="mb-3">
            <label class="form-label">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description <span class="text-danger">*</span></label>
            <textarea name="description" rows="5" class="form-control" required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Link (optional)</label>
            <input type="url" name="link" class="form-control" placeholder="https://example.com">
        </div>

        <div class="mb-3">
            <label class="form-label">Image <span class="text-danger">*</span></label>
            <input type="file" name="image" class="form-control" accept="image/*" required>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" name="status" class="form-check-input" checked>
            <label class="form-check-label">Active</label>
        </div>

        <button type="submit" class="btn btn-primary">Save Work</button>
        <a href="works.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php require_once 'inc/footer.php'; ?>

<script>
$(document).ready(function(){
    $('#createWorkForm').on('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: 'inc/action_works.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = response.redirect;
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
