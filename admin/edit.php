<?php
require_once __DIR__ . '/../app/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: slider.php');
    exit;
}

$id = intval($_GET['id']);
$res = $conn->query("SELECT * FROM slides WHERE id = $id");

if ($res->num_rows === 0) {
    header('Location: slider.php');
    exit;
}

$slide = $res->fetch_assoc();

require_once __DIR__ . '/inc/header.php';
?>

<section class="py-4 bg-light">
  <div class="container">
    <h4 class="mb-4 text-primary"><i class="fas fa-edit me-2"></i>Edit Slide</h4>

    <form id="edit-form" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?= $slide['id']; ?>">

      <div class="mb-3">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($slide['title']); ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Subtitle</label>
        <input type="text" name="subtitle" class="form-control" value="<?= htmlspecialchars($slide['subtitle']); ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">URL</label>
        <input type="url" name="url" class="form-control" value="<?= htmlspecialchars($slide['url']); ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Start Time <span class="text-danger">*</span></label>
        <input type="datetime-local" name="start_time" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($slide['start_time'])); ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">End Time <span class="text-danger">*</span></label>
        <input type="datetime-local" name="end_time" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($slide['end_time'])); ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select" required>
          <option value="Active" <?= $slide['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
          <option value="Inactive" <?= $slide['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Current Image</label><br>
        <?php if (!empty($slide['image']) && file_exists(__DIR__ . '/../assets/images/slides/' . $slide['image'])): ?>
          <img src="../assets/images/slides/<?= $slide['image']; ?>" width="100" alt="Slide Image"><br><br>
        <?php else: ?>
          <span class="text-muted">No Image</span><br><br>
        <?php endif; ?>
        <label class="form-label">Change Image (optional)</label>
        <input type="file" name="image" class="form-control" accept="image/*">
      </div>

      <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Update Slide</button>
    </form>
  </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>

<script>
$(document).ready(function () {
  $('#edit-form').on('submit', function(e) {
    e.preventDefault();

    var formData = new FormData(this);
    formData.append('update_slide', 1);

    $(".loader-overlay").show();

    $.ajax({
      url: 'inc/action.php',
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      dataType: 'json',
      success: function(response) {
        $(".loader-overlay").hide();
        if (response.status === 'success') {
          Swal.fire({
            icon: 'success',
            title: 'Success',
            text: response.message,
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            window.location.href = 'slider.php';
          });
        } else {
          Swal.fire('Error', response.message, 'error');
        }
      },
      error: function() {
        $(".loader-overlay").hide();
        Swal.fire('Error', 'AJAX request failed', 'error');
      }
    });
  });
});
</script>
