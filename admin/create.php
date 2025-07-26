<?php
require_once __DIR__ . '/inc/header.php';
?>

<section class="py-4 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 text-primary">
                <i class="fas fa-sliders-h me-2"></i> Manage Slides
            </h4>
            <a href="slider.php" class="btn btn-sm btn-primary">
                <i class="fas fa-arrow-left me-1"></i> Back to Slider
            </a>
        </div>
        <hr>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-plus me-2"></i>Create New Slide
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST" enctype="multipart/form-data" id="create-form">
                                <!-- Title -->
                                <div class="mb-3">
                                    <label for="title" class="form-label">Slide Title</label>
                                    <input type="text" name="title" id="title" class="form-control">
                                </div>

                                <!-- Subtitle -->
                                <div class="mb-3">
                                    <label for="subtitle" class="form-label">Subtitle</label>
                                    <input type="text" name="subtitle" id="subtitle" class="form-control">
                                </div>

                                <!-- URL -->
                                <div class="mb-3">
                                    <label for="url" class="form-label">URL</label>
                                    <input type="url" name="url" id="url" class="form-control" placeholder="https://example.com">
                                </div>

                                <!-- Start Time -->
                                <div class="mb-3">
                                    <label for="start_time" class="form-label">Start Time</label>
                                    <input type="datetime-local" name="start_time" id="start_time" class="form-control">
                                </div>

                                <!-- End Time -->
                                <div class="mb-3">
                                    <label for="end_time" class="form-label">End Time</label>
                                    <input type="datetime-local" name="end_time" id="end_time" class="form-control">
                                </div>

                                <!-- Image Upload -->
                                <div class="mb-3">
                                    <label for="image" class="form-label">Slide Image</label>
                                    <input type="file" name="image" id="image" class="form-control" accept="image/*">
                                </div>

                                <!-- Status Radio Inline -->
                                <div class="mb-4">
                                    <label class="form-label d-block">Status</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="status" id="status_active" value="Active" checked>
                                        <label class="form-check-label" for="status_active">Active</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="status" id="status_inactive" value="Inactive">
                                        <label class="form-check-label" for="status_inactive">Inactive</label>
                                    </div>
                                </div>

                                <!-- Submit Button Centered -->
                                <div class="text-center">
                                    <button type="submit" class="btn btn-success px-5">
                                        <i class="fas fa-save me-1"></i> Submit
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>



    </div>
</section>

<?php
require_once __DIR__ . '/inc/footer.php';
?>