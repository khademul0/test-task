<?php require_once __DIR__ . '/inc/header.php'; ?>

<section class="py-4 bg-light">
  <div class="container">
    <h4 class="text-primary mb-4"><i class="fas fa-plus-circle me-2"></i>Add New Slide</h4>

    <form id="create-form" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" required maxlength="100">
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
        <input type="file" name="image" class="form-control" accept="image/*" required onchange="previewImage(event)">
        <div class="mt-2">
          <img id="preview" src="#" alt="Image Preview" style="max-height: 120px; display: none;" class="img-thumbnail">
        </div>
      </div>

      <button type="submit" id="submitBtn" class="btn btn-success">
        <i class="fas fa-save me-1"></i> <span class="btn-text">Save Slide</span>
        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
      </button>
    </form>
  </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>

<script>
function previewImage(event) {
  const reader = new FileReader();
  reader.onload = function () {
    const preview = document.getElementById('preview');
    preview.src = reader.result;
    preview.style.display = 'block';
  };
  reader.readAsDataURL(event.target.files[0]);
}

$(document).ready(function () {
  $('#create-form').on('submit', function(e) {
    e.preventDefault();

    const form = this;
    const subtitle = form.subtitle.value.trim();
    const url = form.url.value.trim();

    // Info toast if optional fields are empty
    if (!subtitle || !url) {
      Swal.fire({
        toast: true,
        icon: 'info',
        title: 'Note: Subtitle or URL is empty.',
        position: 'top-end',
        timer: 2500,
        showConfirmButton: false
      });
    }

    const formData = new FormData(form);
    formData.append('create_slide', 1);

    // UI changes
    $('#submitBtn').prop('disabled', true);
    $('#submitBtn .btn-text').text('Saving...');
    $('#submitBtn .spinner-border').removeClass('d-none');

    $.ajax({
      url: 'inc/action.php',
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      dataType: 'json',
      success: function(response) {
        $('#submitBtn').prop('disabled', false);
        $('#submitBtn .btn-text').text('Save Slide');
        $('#submitBtn .spinner-border').addClass('d-none');

        if (response.status === 'success') {
          Swal.fire({
            icon: 'success',
            title: 'Success',
            text: response.message,
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            window.location.href = 'slider.php?msg=created';
          });
        } else {
          Swal.fire('Error', response.message, 'error');
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
      },
      error: function() {
        $('#submitBtn').prop('disabled', false);
        $('#submitBtn .btn-text').text('Save Slide');
        $('#submitBtn .spinner-border').addClass('d-none');

        Swal.fire('Error', 'AJAX request failed', 'error');
      }
    });
  });
});
</script>
