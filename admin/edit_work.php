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

        <!-- Added category selection dropdown with current value selected -->
        <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select">
                <option value="">Select Category (Optional)</option>
                <?php
                $categories = $conn->query("SELECT id, name FROM categories WHERE status = 1 ORDER BY name");
                while ($category = $categories->fetch_assoc()):
                ?>
                    <option value="<?= $category['id'] ?>" <?= $work['category_id'] == $category['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
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

        <!-- Added sub images management -->
        <div class="mb-3">
            <label class="form-label">Current Additional Images</label><br>
            <?php 
            $sub_images = !empty($work['sub_images']) ? json_decode($work['sub_images'], true) : [];
            if (!empty($sub_images)): 
            ?>
                <div class="d-flex gap-2 flex-wrap mb-2">
                    <?php foreach ($sub_images as $index => $sub_image): ?>
                        <?php if (file_exists("../assets/img/works/" . $sub_image)): ?>
                            <div class="position-relative">
                                <img src="../assets/img/works/<?= htmlspecialchars($sub_image) ?>" width="80" height="80" class="img-thumbnail" alt="Sub Image">
                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-sub-image" 
                                        data-image="<?= htmlspecialchars($sub_image) ?>" style="padding: 2px 6px; font-size: 0.7rem;">×</button>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <span class="text-muted">No additional images</span>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label class="form-label">Add New Additional Images</label>
            <input type="file" name="sub_images[]" class="form-control" accept="image/*" multiple>
            <small class="text-muted">You can select multiple images to add to the gallery</small>
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

        <!-- Added sizes field -->
        <div class="mb-3">
            <label class="form-label">Available Sizes</label>
            <?php $sizes = !empty($work['sizes']) ? json_decode($work['sizes'], true) : []; ?>
            <input type="text" name="sizes" class="form-control" value="<?= htmlspecialchars(implode(', ', $sizes)) ?>" placeholder="e.g., S, M, L, XL or 32, 34, 36, 38">
            <small class="text-muted">Separate sizes with commas</small>
        </div>

        <!-- Added options field -->
        <div class="mb-3">
            <label class="form-label">Color/Variant Options</label>
            <div id="optionsContainer">
                <?php 
                $options = !empty($work['options']) ? json_decode($work['options'], true) : [];
                if (!empty($options)):
                    foreach ($options as $option):
                ?>
                    <div class="option-row d-flex gap-2 mb-2">
                        <input type="text" name="option_names[]" class="form-control" value="<?= htmlspecialchars($option['name'] ?? '') ?>" placeholder="Option name">
                        <input type="color" name="option_colors[]" class="form-control form-control-color" value="<?= htmlspecialchars($option['color'] ?? '#000000') ?>" style="width: 60px;">
                        <input type="text" name="option_values[]" class="form-control" value="<?= htmlspecialchars($option['value'] ?? '') ?>" placeholder="Value">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-option">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php 
                    endforeach;
                else:
                ?>
                    <div class="option-row d-flex gap-2 mb-2">
                        <input type="text" name="option_names[]" class="form-control" placeholder="Option name (e.g., Red, Blue)">
                        <input type="color" name="option_colors[]" class="form-control form-control-color" style="width: 60px;">
                        <input type="text" name="option_values[]" class="form-control" placeholder="Value (e.g., red, blue)">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-option" style="display: none;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm" id="addOption">
                <i class="fas fa-plus me-1"></i> Add Option
            </button>
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
    $('#addOption').on('click', function() {
        const optionRow = `
            <div class="option-row d-flex gap-2 mb-2">
                <input type="text" name="option_names[]" class="form-control" placeholder="Option name (e.g., Red, Blue)">
                <input type="color" name="option_colors[]" class="form-control form-control-color" style="width: 60px;">
                <input type="text" name="option_values[]" class="form-control" placeholder="Value (e.g., red, blue)">
                <button type="button" class="btn btn-outline-danger btn-sm remove-option">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        $('#optionsContainer').append(optionRow);
        updateRemoveButtons();
    });

    $(document).on('click', '.remove-option', function() {
        $(this).closest('.option-row').remove();
        updateRemoveButtons();
    });

    function updateRemoveButtons() {
        const rows = $('.option-row');
        if (rows.length > 1) {
            $('.remove-option').show();
        } else {
            $('.remove-option').hide();
        }
    }

    // Initialize remove buttons
    updateRemoveButtons();

    // Remove sub-image functionality
    $('.remove-sub-image').on('click', function() {
        const imageName = $(this).data('image');
        const $imageContainer = $(this).closest('.position-relative');
        
        Swal.fire({
            title: 'Remove Image?',
            text: 'This will permanently delete the image.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, remove it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'inc/action_works.php',
                    method: 'POST',
                    data: {
                        action: 'remove_sub_image',
                        work_id: <?= $work['id'] ?>,
                        image_name: imageName
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $imageContainer.fadeOut(300, function() {
                                $(this).remove();
                            });
                            Swal.fire('Removed!', 'Image has been removed.', 'success');
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to remove image', 'error');
                    }
                });
            }
        });
    });

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
