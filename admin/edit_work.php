<?php
require_once 'inc/header.php';
require_once '../app/db.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: works.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM works WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$work = $res->fetch_assoc();

if (!$work) {
    header('Location: works.php');
    exit;
}
?>

<div class="container py-4">
    <h4><i class="fas fa-edit me-2"></i>Edit Work</h4>
    <form action="inc/action_works.php" method="POST" enctype="multipart/form-data" id="editWorkForm">
        <input type="hidden" name="action" value="update_work">
        <input type="hidden" name="id" value="<?= $work['id'] ?>">
        <input type="hidden" name="old_image" value="<?= htmlspecialchars($work['image']) ?>">

        <div class="mb-3">
            <label class="form-label">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" value="<?= htmlspecialchars($work['title']) ?>" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description <span class="text-danger">*</span></label>
            <textarea name="description" rows="5" class="form-control" required><?= htmlspecialchars($work['description']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Link (optional)</label>
            <input type="url" name="link" class="form-control" value="<?= htmlspecialchars($work['link']) ?>" placeholder="https://example.com">
        </div>

        <div class="mb-3">
            <label class="form-label">Current Image</label><br>
            <?php if (!empty($work['image']) && file_exists("../assets/img/works/" . $work['image'])): ?>
                <img src="../assets/img/works/<?= htmlspecialchars($work['image']) ?>" width="150" class="img-thumbnail" alt="Current Work Image">
            <?php else: ?>
                <span class="text-muted">No image available</span>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label class="form-label">Change Image</label>
            <input type="file" name="image" class="form-control" accept="image/*">
        </div>

        <!-- Added inventory fields to edit form -->
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Price ($)</label>
                    <input type="number" name="price" class="form-control" value="<?= htmlspecialchars($work['price']) ?>" step="0.01" min="0" placeholder="0.00">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" class="form-control" value="<?= htmlspecialchars($work['stock']) ?>" min="0" placeholder="0">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Rating (0-5)</label>
                    <input type="number" name="rating" class="form-control" value="<?= htmlspecialchars($work['rating']) ?>" step="0.1" min="0" max="5" placeholder="0.0">
                </div>
            </div>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" name="status" class="form-check-input" <?= $work['status'] ? 'checked' : '' ?>>
            <label class="form-check-label">Active</label>
        </div>

        <button type="submit" class="btn btn-primary">Update Work</button>
        <a href="works.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php require_once 'inc/footer.php'; ?>

<script>
$(document).ready(function(){
    $('#editWorkForm').on('submit', function(e) {
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
