<?php
require_once 'app/db.php';
include 'admin/inc/header.php'; // Use your shared frontend header if different
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Our Portfolio</h2>
    </div>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php
        $stmt = $conn->prepare("SELECT * FROM works WHERE status = 'Active' ORDER BY id DESC");
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
        ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0">
                        <img src="assets/img/works/<?= htmlspecialchars($row['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['title']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                            <?php if (!empty($row['link'])): ?>
                                <a href="<?= htmlspecialchars($row['link']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Visit</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile;
        else: ?>
            <div class="col-12 text-center">
                <p class="text-muted">No works available right now. Please check back later.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'admin/inc/footer.php'; ?>