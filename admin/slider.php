<?php
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/../app/db.php'; // make sure this path is correct
?>

<section class="py-4 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 text-primary">
                <i class="fas fa-sliders-h me-2"></i> Manage Slides
            </h4>
            <a href="create.php" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Add Slide
            </a>
        </div>
        <hr>

        <div class="table-responsive">
            <table class="table table-bordered" id="datatables">
                <thead>
                    <tr>
                        <th>SL No</th>
                        <th>Title</th>
                        <th>Image</th>
                        <th>Time Limit</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM slides ORDER BY id DESC";
                    $result = $conn->query($sql);
                    $sl = 1;

                    if ($result && $result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                    ?>
                            <tr>
                                <td><?= $sl++; ?></td>
                                <td><?= htmlspecialchars($row['title']); ?></td>
                                <td>
                                    <?php if (!empty($row['image']) && file_exists(__DIR__ . '/../assets/images/slides/' . $row['image'])): ?>
                                        <img src="../assets/images/slides/<?= $row['image']; ?>" width="60" alt="Slide Image">
                                    <?php else: ?>
                                        <span class="text-muted">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $start = date('d M Y, h:i A', strtotime($row['start_time']));
                                    $end = date('d M Y, h:i A', strtotime($row['end_time']));
                                    echo "$start<br>to<br>$end";
                                    ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $row['status'] === 'Active' ? 'success' : 'secondary'; ?>">
                                        <?= $row['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="delete.php?id=<?= $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this slide?');" class="btn btn-sm btn-danger">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile;
                    else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No slides found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php
require_once __DIR__ . '/inc/footer.php';
?>