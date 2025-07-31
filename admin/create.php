<?php
require_once __DIR__ . '/inc/header.php';
?>

<section class="py-4 bg-light">
  <div class="container">
    <h4 class="text-primary mb-4"><i class="fas fa-plus-circle me-2"></i>Add New Slide</h4>

    <form id="create-form" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Subtitle</label>
        <input type="text" name="subtitle" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label">URL</label>
        <input type="url" name="url" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label">Start Time <span class="text-danger">*</span></label>
        <input type="datetime-local" name="start_time" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">End Time <span class="text-danger">*</span></label>
        <input type="datetime-local" name="end_time" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select" required>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Image <span class="text-danger">*</span></label>
        <input type="file" name="image" class="form-control" accept="image/*" required>
      </div>
      <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Save Slide</button>
    </form>
  </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>

<script>
$(document).ready(function () {
  $('#create-form').on('submit', function(e) {
    e.preventDefault();

    var formData = new FormData(this);
    formData.append('create_slide', 1);

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
