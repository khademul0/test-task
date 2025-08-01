<?php
require_once __DIR__ . '/app/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Portfolio</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
</head>
<body>

<section class="py-5 bg-light">
    <div class="container">
        <h2 class="mb-4 text-center">My Portfolio Works</h2>
        <div class="row g-4">
            <?php
            $sql = "SELECT * FROM works WHERE status = 'Active' ORDER BY id DESC";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
            ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <?php if (!empty($row['image']) && file_exists(__DIR__ . '/assets/images/works/' . $row['image'])): ?>
                            <img src="assets/images/works/<?= htmlspecialchars($row['image']); ?>" class="card-img-top" alt="<?= htmlspecialchars($row['title']); ?>" />
                        <?php else: ?>
                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height:200px;">
                                No Image
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['title']); ?></h5>
                            <?php if (!empty($row['url'])): ?>
                                <a href="<?= htmlspecialchars($row['url']); ?>" target="_blank" class="btn btn-primary btn-sm">View More</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php
                endwhile;
            else:
            ?>
                <p class="text-center text-muted">No works to display.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
