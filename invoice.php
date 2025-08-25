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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;900&family=Open+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Applied modern design system from portfolio.php */
        :root {
            --font-heading: 'Montserrat', sans-serif;
            --font-body: 'Open Sans', sans-serif;
            --shadow-soft: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 8px 30px rgba(0, 0, 0, 0.12);
            --gradient-primary: linear-gradient(135deg, #84cc16 0%, #65a30d 100%);
            --gradient-card: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
        }

        body {
            font-family: var(--font-body);
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            min-height: 100vh;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: var(--font-heading);
            font-weight: 700;
        }

        /* Enhanced print styles for professional single-page printing */
        @media print {
            .no-print {
                display: none !important;
            }

            .invoice-container {
                margin: 0 !important;
                padding: 1.5rem !important;
                box-shadow: none !important;
                border: none !important;
                background: white !important;
                backdrop-filter: none !important;
                max-width: 100% !important;
                page-break-inside: avoid;
            }

            body {
                background: white !important;
                font-size: 12px !important;
                line-height: 1.4 !important;
            }

            .invoice-header {
                margin-bottom: 1.5rem !important;
                padding-bottom: 1rem !important;
            }

            .invoice-header h2 {
                font-size: 1.8rem !important;
                color: #84cc16 !important;
                -webkit-print-color-adjust: exact;
            }

            .detail-card {
                background: white !important;
                border: 1px solid #e5e7eb !important;
                padding: 1rem !important;
                margin-bottom: 1rem !important;
                backdrop-filter: none !important;
                box-shadow: none !important;
            }

            .invoice-table {
                background: white !important;
                font-size: 11px !important;
            }

            .invoice-table thead {
                background: #84cc16 !important;
                -webkit-print-color-adjust: exact;
            }

            .invoice-table thead th {
                color: white !important;
                -webkit-print-color-adjust: exact;
                padding: 0.5rem !important;
            }

            .invoice-table tbody td {
                padding: 0.4rem !important;
                border-bottom: 1px solid #e5e7eb !important;
            }

            .invoice-table tfoot th {
                background: #f3f4f6 !important;
                -webkit-print-color-adjust: exact;
                padding: 0.5rem !important;
            }

            .invoice-details {
                margin-bottom: 1.5rem !important;
            }
        }

        /* Modern invoice container - optimized for single page */
        .invoice-container {
            max-width: 900px;
            margin: 1rem auto;
            padding: 2rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 250, 252, 0.95) 100%);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(226, 232, 240, 0.3);
            border-radius: 20px;
            box-shadow: var(--shadow-soft);
        }

        .invoice-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid rgba(132, 204, 22, 0.2);
        }

        .invoice-header img {
            height: 50px;
            margin-bottom: 0.5rem;
        }

        .invoice-header h2 {
            font-size: 2rem;
            font-weight: 900;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .invoice-header p {
            color: #6b7280;
            font-size: 1rem;
            font-weight: 500;
        }

        /* Compact invoice details cards */
        .invoice-details {
            margin-bottom: 2rem;
        }

        .detail-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.8) 0%, rgba(248, 250, 252, 0.8) 100%);
            border: 1px solid rgba(226, 232, 240, 0.4);
            border-radius: 12px;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .detail-card h5 {
            color: #374151;
            font-weight: 700;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid rgba(132, 204, 22, 0.2);
            font-size: 1.1rem;
        }

        .detail-card p {
            margin-bottom: 0.6rem;
            color: #6b7280;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .detail-card strong {
            color: #374151;
        }

        /* Compact table styling */
        .invoice-table {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(248, 250, 252, 0.9) 100%);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(226, 232, 240, 0.3);
            font-size: 0.9rem;
        }

        .invoice-table thead {
            background: var(--gradient-primary);
        }

        .invoice-table thead th {
            color: white;
            font-weight: 700;
            padding: 0.8rem;
            border: none;
            font-size: 0.9rem;
        }

        .invoice-table tbody td {
            padding: 0.7rem;
            border-bottom: 1px solid rgba(226, 232, 240, 0.3);
            color: #374151;
            font-weight: 500;
        }

        .invoice-table tbody tr:hover {
            background: rgba(132, 204, 22, 0.05);
        }

        .invoice-table tfoot th {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.1) 100%);
            color: #374151;
            font-weight: 700;
            padding: 0.8rem;
            border: none;
            font-size: 1rem;
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            .invoice-container {
                margin: 1rem;
                padding: 2rem;
            }

            .invoice-header h2 {
                font-size: 2rem;
            }

            .detail-card {
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <!-- Updated invoice content with modern styling -->
    <div class="invoice-container">
        <div class="invoice-header">
            <img src="assets/images/logo.png" alt="Netacart Logo">
            <h2>Netacart Invoice</h2>
            <p>Order ID: <strong><?= htmlspecialchars($order_id) ?></strong> | Date: <strong><?= date('d M Y', strtotime($order['created_at'])) ?></strong></p>
        </div>

        <div class="row invoice-details">
            <div class="col-md-6 mb-3">
                <div class="detail-card">
                    <h5><i class="bi bi-person me-2"></i>Customer Details</h5>
                    <p><strong>Name:</strong> <?= htmlspecialchars($order['full_name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
                    <p><strong>Shipping Address:</strong><br><?= nl2br(htmlspecialchars($order['address'])) ?></p>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="detail-card">
                    <h5><i class="bi bi-bag me-2"></i>Order Details</h5>
                    <p><strong>Order Status:</strong> <span class="badge bg-success"><?= htmlspecialchars($order['status']) ?></span></p>
                    <p><strong>Payment Method:</strong> <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $order['payment_method']))) ?></p>
                    <p><strong>Total Amount:</strong> <span style="color: #84cc16; font-weight: 700; font-size: 1.2rem;">$<?= number_format($order['total'], 2) ?></span></p>
                </div>
            </div>
        </div>

        <h5 class="mb-3"><i class="bi bi-list-ul me-2"></i>Order Items</h5>
        <table class="table invoice-table">
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

        <div class="text-center mt-4 no-print">
            <button class="btn btn-primary me-3" onclick="window.print()"><i class="bi bi-printer me-1"></i> Print Invoice</button>
            <a href="portfolio.php" class="btn btn-secondary"><i class="bi bi-shop me-1"></i> Back to Shop</a>
        </div>
    </div>

    <!-- Updated footer with modern styling -->
    <footer class="footer-modern no-print">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="footer-title"><img src="assets/images/logo.png" alt="Netacart Logo" style="height: 30px;" class="me-2"> Netacart</h5>
                    <p class="text-muted">Your one-stop shop for quality products. Discover the best deals and enjoy a seamless shopping experience.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="footer-title">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php"><i class="bi bi-house me-2"></i> Home</a></li>
                        <li><a href="portfolio.php"><i class="bi bi-shop me-2"></i> Shop</a></li>
                        <li><a href="privacy.php"><i class="bi bi-shield-check me-2"></i> Privacy Policy</a></li>
                        <li><a href="terms.php"><i class="bi bi-file-text me-2"></i> Terms & Conditions</a></li>
                        <li><a href="contact.php"><i class="bi bi-envelope me-2"></i> Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="footer-title">Follow Us</h5>
                    <div class="social-icons">
                        <a href="#" target="_blank" rel="noopener noreferrer"><i class="bi bi-facebook"></i></a>
                        <a href="#" target="_blank" rel="noopener noreferrer"><i class="bi bi-twitter"></i></a>
                        <a href="#" target="_blank" rel="noopener noreferrer"><i class="bi bi-instagram"></i></a>
                        <a href="#" target="_blank" rel="noopener noreferrer"><i class="bi bi-linkedin"></i></a>
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