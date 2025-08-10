<?php
require_once 'app/db.php';

session_start();

// Validate order_id
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    die("Invalid or missing Order ID.");
}

$order_id = intval($_GET['order_id']);

// Fetch order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
if (!$stmt->execute()) {
    die("Error fetching order: " . $stmt->error);
}
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Order not found.");
}

// Fetch order items
$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
if (!$stmt->execute()) {
    die("Error fetching order items: " . $stmt->error);
}
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - Netacart</title>
    <link rel="shortcut icon" href="assets/images/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <style>
        @media print {
            .no-print {
                display: none;
            }

            .invoice-container {
                margin: 0;
                box-shadow: none;
                border: none;
            }
        }

        .invoice-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .invoice-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .invoice-header img {
            height: 50px;
        }

        .invoice-details p {
            margin-bottom: 0.5rem;
        }

        .invoice-table th,
        .invoice-table td {
            vertical-align: middle;
        }

        .footer {
            background: linear-gradient(90deg, #343a40, #212529);
            color: #fff;
            padding: 2rem 0;
        }
    </style>
</head>

<body>
    <!-- Invoice Content -->
    <div class="invoice-container">
        <div class="invoice-header">
            <img src="assets/images/logo.png" alt="Netacart Logo">
            <h2>Netacart Invoice</h2>
            <p>Order ID: <?= htmlspecialchars($order_id) ?> | Date: <?= date('d M Y', strtotime($order['created_at'])) ?></p>
        </div>
        <hr>
        <div class="row invoice-details">
            <div class="col-md-6">
                <h5>Customer Details</h5>
                <p><strong>Name:</strong> <?= htmlspecialchars($order['full_name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
                <p><strong>Shipping Address:</strong> <?= nl2br(htmlspecialchars($order['address'])) ?></p>
            </div>
            <div class="col-md-6">
                <h5>Order Details</h5>
                <p><strong>Order Status:</strong> <?= htmlspecialchars($order['status']) ?></p>
                <p><strong>Payment Method:</strong> <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $order['payment_method']))) ?></p>
                <p><strong>Total Amount:</strong> $<?= number_format($order['total'], 2) ?></p>
            </div>
        </div>
        <hr>
        <h5>Order Items</h5>
        <table class="table table-bordered invoice-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $index = 1;
                foreach ($items as $item):
                ?>
                    <tr>
                        <td><?= $index++ ?></td>
                        <td><?= htmlspecialchars($item['title']) ?></td>
                        <td>$<?= number_format($item['price'], 2) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-end">Grand Total:</th>
                    <th>$<?= number_format($order['total'], 2) ?></th>
                </tr>
            </tfoot>
        </table>
        <div class="text-center no-print">
            <button class="btn btn-primary me-2" onclick="window.print()"><i class="fas fa-print me-1"></i> Print Invoice</button>
            <a href="portfolio.php" class="btn btn-secondary"><i class="fas fa-shopping-bag me-1"></i> Back to Shop</a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer no-print">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3"><img src="assets/images/logo.png" alt="Netacart Logo" style="height: 30px;" class="me-2"> Netacart</h5>
                    <p class="text-muted">Your one-stop shop for quality products. Discover the best deals and enjoy a seamless shopping experience.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php"><i class="fas fa-home me-2"></i> Home</a></li>
                        <li><a href="portfolio.php"><i class="fas fa-shopping-bag me-2"></i> Shop</a></li>
                        <li><a href="privacy.php"><i class="fas fa-shield-alt me-2"></i> Privacy Policy</a></li>
                        <li><a href="terms.php"><i class="fas fa-file-alt me-2"></i> Terms & Conditions</a></li>
                        <li><a href="contact.php"><i class="fas fa-envelope me-2"></i> Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3">Follow Us</h5>
                    <div class="social-icons">
                        <a href="#" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f me-3"></i></a>
                        <a href="#" target="_blank" rel="noopener noreferrer"><i class="fab fa-twitter me-3"></i></a>
                        <a href="#" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram me-3"></i></a>
                        <a href="#" target="_blank" rel="noopener noreferrer"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <hr class="bg-light">
            <div class="text-center">
                <p class="mb-0">&copy; <?= date('Y') ?> <strong>Netacart</strong>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>

</html>
<?php
$conn->close();
?>