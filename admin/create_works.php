<?php require_once 'inc/header.php'; ?>

<style>
    /* Added dashboard-matching glassy design system */
    :root {
        --primary-color: #164e63;
        --primary-dark: #0f3a47;
        --secondary-color: #ec4899;
        --accent-color: #f59e0b;
        --success-color: #10b981;
        --danger-color: #dc2626;
        --light-bg: rgba(255, 255, 255, 0.8);
        --card-bg: rgba(255, 255, 255, 0.25);
        --glass-bg: rgba(255, 255, 255, 0.15);
        --text-primary: #164e63;
        --text-secondary: #475569;
        --shadow: 0 25px 50px rgba(22, 78, 99, 0.15);
        --shadow-lg: 0 35px 70px rgba(22, 78, 99, 0.2);
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #f8fafc 100%);
        min-height: 100vh;
    }

    .container {
        max-width: 800px;
    }

    .page-header {
        backdrop-filter: blur(25px);
        background: var(--card-bg);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow);
        text-align: center;
    }

    .page-header h4 {
        color: var(--text-primary);
        font-weight: 600;
        margin: 0;
        font-size: 1.5rem;
    }

    .form-container {
        backdrop-filter: blur(25px);
        background: var(--card-bg);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 20px;
        padding: 2.5rem;
        box-shadow: var(--shadow);
        position: relative;
        overflow: hidden;
    }

    .form-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
    }

    .form-label {
        color: var(--text-primary);
        font-weight: 500;
        margin-bottom: 0.5rem;
        display: block;
    }

    .form-control,
    .form-select {
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 12px;
        padding: 0.75rem 1rem;
        color: var(--text-primary);
        transition: var(--transition);
        font-size: 0.95rem;
    }

    .form-control:focus,
    .form-select:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(22, 78, 99, 0.1);
        background: rgba(255, 255, 255, 0.4);
    }

    .form-control::placeholder {
        color: var(--text-secondary);
        opacity: 0.7;
    }

    .form-check {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 12px;
        transition: var(--transition);
    }

    .form-check:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .form-check-input {
        width: 18px;
        height: 18px;
        border: 2px solid var(--primary-color);
        border-radius: 4px;
        background: transparent;
        transition: var(--transition);
    }

    .form-check-input:checked {
        background: var(--primary-color);
        border-color: var(--primary-color);
    }

    .form-check-label {
        color: var(--text-primary);
        font-weight: 500;
        cursor: pointer;
        margin: 0;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 12px;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: var(--transition);
        cursor: pointer;
        backdrop-filter: blur(10px);
        font-size: 0.95rem;
        margin-right: 1rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        box-shadow: 0 4px 15px rgba(22, 78, 99, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(22, 78, 99, 0.4);
        color: white;
        text-decoration: none;
    }

    .btn-secondary {
        background: rgba(255, 255, 255, 0.3);
        color: var(--text-secondary);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.4);
        transform: translateY(-1px);
        color: var(--text-primary);
        text-decoration: none;
    }

    .text-danger {
        color: var(--danger-color);
    }

    .mb-3 {
        margin-bottom: 1.5rem;
    }

    .button-group {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
    }
</style>

<div class="container py-5">
    <div class="page-header">
        <h4><i class="fas fa-plus-circle me-2"></i>Add New Work</h4>
    </div>

    <div class="form-container">
        <form action="inc/action_works.php" method="POST" enctype="multipart/form-data" id="createWorkForm">
            <input type="hidden" name="action" value="create_work">

            <div class="mb-3">
                <label class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <!-- Added category selection dropdown -->
            <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">Select Category (Optional)</option>
                    <?php
                    require_once '../app/db.php';
                    $categories = $conn->query("SELECT id, name FROM categories WHERE status = 1 ORDER BY name");
                    while ($category = $categories->fetch_assoc()):
                    ?>
                        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endwhile; ?>
                </select>
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

            <!-- Added sub images upload field -->
            <div class="mb-3">
                <label class="form-label">Additional Images (Optional)</label>
                <input type="file" name="sub_images[]" class="form-control" accept="image/*" multiple>
                <small class="text-muted">You can select multiple images for product gallery</small>
            </div>

            <!-- Added sizes field -->
            <div class="mb-3">
                <label class="form-label">Available Sizes (Optional)</label>
                <input type="text" name="sizes" class="form-control" placeholder="e.g., S, M, L, XL or 32, 34, 36, 38">
                <small class="text-muted">Separate sizes with commas</small>
            </div>

            <!-- Added color/options field -->
            <div class="mb-3">
                <label class="form-label">Color/Variant Options (Optional)</label>
                <div id="optionsContainer">
                    <div class="option-row d-flex gap-2 mb-2">
                        <input type="text" name="option_names[]" class="form-control" placeholder="Option name (e.g., Red, Blue)">
                        <input type="color" name="option_colors[]" class="form-control form-control-color" style="width: 60px;">
                        <input type="text" name="option_values[]" class="form-control" placeholder="Value (e.g., red, blue)">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-option" style="display: none;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" id="addOption">
                    <i class="fas fa-plus me-1"></i> Add Option
                </button>
                <small class="text-muted d-block mt-1">Add color or variant options for your product</small>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Price ($)</label>
                        <input type="number" name="price" class="form-control" step="0.01" min="0" placeholder="0.00" value="0.00">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Stock</label>
                        <input type="number" name="stock" class="form-control" min="0" placeholder="0" value="0">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Rating (0-5)</label>
                        <input type="number" name="rating" class="form-control" step="0.1" min="0" max="5" placeholder="0.0" value="0.0">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input type="checkbox" name="status" class="form-check-input" checked>
                    <label class="form-check-label">Active</label>
                </div>
            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save Work
                </button>
                <a href="works.php" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once 'inc/footer.php'; ?>

<script>
    $(document).ready(function() {
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
                    if (response.status === 'success') {
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
