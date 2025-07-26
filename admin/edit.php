<?php
require_once(__DIR__ . '/../app/db.php'); // Include DB connection

if (!isset($_GET['id'])) {
    header("Location: slider.php?error=Invalid+Request");
    exit;
}

$id = intval($_GET['id']);

// Fetch slide data for the form
$result = $conn->query("SELECT * FROM slides WHERE id = $id");
if ($result->num_rows === 0) {
    header("Location: slider.php?error=Slide+Not+Found");
    exit;
}

$slide = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize inputs
    $title = $_POST['title'] ?? '';
    $subtitle = $_POST['subtitle'] ?? '';
    $url = $_POST['url'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $status = $_POST['status'] ?? 'Inactive';

    // Validate required fields
    if (empty($title) || empty($subtitle) || empty($status)) {
        $error = "Please fill in all required fields.";
    } else {
        // Handle image upload (optional)
        $imageName = $slide['image']; // keep existing image by default
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $targetDir = __DIR__ . '/../assets/images/slides/';

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $fileTmpPath = $_FILES['image']['tmp_name'];
            $originalName = basename($_FILES['image']['name']);
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($extension, $allowedExtensions)) {
                $imageName = uniqid('slide_', true) . '.' . $extension;
                $targetPath = $targetDir . $imageName;

                if (!move_uploaded_file($fileTmpPath, $targetPath)) {
                    $error = "Failed to upload image.";
                } else {
                    // Optionally delete old image file here
                    if (!empty($slide['image']) && file_exists($targetDir . $slide['image'])) {
                        unlink($targetDir . $slide['image']);
                    }
                }
            } else {
                $error = "Invalid image format.";
            }
        }

        if (!isset($error)) {
            // Update slide in DB
            $stmt = $conn->prepare("UPDATE slides SET title=?, subtitle=?, url=?, start_time=?, end_time=?, status=?, image=? WHERE id=?");
            $stmt->bind_param("sssssssi", $title, $subtitle, $url, $start_time, $end_time, $status, $imageName, $id);
            if ($stmt->execute()) {
                header("Location: slider.php?msg=Slide+Updated+Successfully");
                exit;
            } else {
                $error = "Database update failed: " . $conn->error;
            }
        }
    }
}
?>

<?php require_once __DIR__ . '/inc/header.php'; ?>

<section class="py-4 bg-light">
    <div class="container">
        <h4 class="mb-4 text-primary"><i class="fas fa-edit me-2"></i>Edit Slide</h4>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" id="edit-form">
            <div class="mb-3">
                <label for="title" class="form-label">Slide Title</label>
                <input type="text" name="title" id="title" class="form-control" value="<?= htmlspecialchars($slide['title']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="subtitle" class="form-label">Subtitle</label>
                <input type="text" name="subtitle" id="subtitle" class="form-control" value="<?= htmlspecialchars($slide['subtitle']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="url" class="form-label">URL</label>
                <input type="url" name="url" id="url" class="form-control" value="<?= htmlspecialchars($slide['url']); ?>">
            </div>

            <div class="mb-3">
                <label for="start_time" class="form-label">Start Time</label>
                <input type="datetime-local" name="start_time" id="start_time" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($slide['start_time'])); ?>">
            </div>

            <div class="mb-3">
                <label for="end_time" class="form-label">End Time</label>
                <input type="datetime-local" name="end_time" id="end_time" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($slide['end_time'])); ?>">
            </div>

            <div class="mb-3">
                <label for="image" class="form-label">Slide Image (leave blank to keep current)</label><br>
                <?php if (!empty($slide['image'])): ?>
                    <img src="../assets/images/slides/<?= htmlspecialchars($slide['image']); ?>" width="100" alt="Current Slide Image"><br><br>
                <?php endif; ?>
                <input type="file" name="image" id="image" class="form-control" accept="image/*">
            </div>

            <div class="mb-4">
                <label class="form-label d-block">Status</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="status_active" value="Active" <?= $slide['status'] === 'Active' ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="status_active">Active</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="status_inactive" value="Inactive" <?= $slide['status'] === 'Inactive' ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="status_inactive">Inactive</label>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-success px-5"><i class="fas fa-save me-1"></i> Update</button>
            </div>
        </form>
    </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>