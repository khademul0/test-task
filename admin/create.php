<?php require_once __DIR__ . '/inc/header.php'; ?>

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

  .py-4 {
    padding: 3rem 0;
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

  .image-preview-container {
    margin-top: 1rem;
    text-align: center;
  }

  .img-thumbnail {
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 12px;
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.2);
    padding: 0.5rem;
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
    position: relative;
  }

  .btn-success {
    background: linear-gradient(135deg, var(--success-color), #34d399);
    color: white;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
  }

  .btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
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

  .spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 0.15em;
  }

  .text-danger {
    color: var(--danger-color);
  }

  .text-primary {
    color: var(--text-primary);
  }

  .mb-3 {
    margin-bottom: 1.5rem;
  }

  .d-none {
    display: none !important;
  }

  .button-group {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
  }
</style>

<section class="py-4">
  <div class="container">
    <div class="page-header">
      <h4 class="text-primary mb-0"><i class="fas fa-plus-circle me-2"></i>Add New Slide</h4>
    </div>

    <div class="form-container">
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
          <div class="image-preview-container">
            <img id="preview" src="#" alt="Image Preview" style="max-height: 120px; display: none;" class="img-thumbnail">
          </div>
        </div>

        <div class="button-group">
          <button type="submit" id="submitBtn" class="btn btn-success">
            <i class="fas fa-save me-1"></i> <span class="btn-text">Save Slide</span>
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
          </button>
          <a href="slider.php" class="btn btn-secondary">
            <i class="fas fa-times me-1"></i> Cancel
          </a>
        </div>
      </form>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>

<script>
  function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
      const preview = document.getElementById('preview');
      preview.src = reader.result;
      preview.style.display = 'block';
    };
    reader.readAsDataURL(event.target.files[0]);
  }

  $(document).ready(function() {
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
            window.scrollTo({
              top: 0,
              behavior: 'smooth'
            });
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