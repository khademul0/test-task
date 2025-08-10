<?php
session_start();
require_once 'app/db.php';

// Enable debug mode (set to false in production)
$debug_mode = true;

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? intval($_SESSION['user_id']) : null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Netacart - Shop</title>
    <link rel="shortcut icon" href="assets/images/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .portfolio-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
        }

        .portfolio-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .card-img-top {
            object-fit: cover;
            height: 200px;
            background-color: #f8f9fa;
        }

        .rating-stars {
            color: #f1c40f;
        }

        .stock-badge {
            font-size: 0.85rem;
        }

        .price-tag {
            font-weight: bold;
            color: #28a745;
        }

        .no-works {
            background: #fff;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .carousel-caption {
            background: rgba(0, 0, 0, 0.6);
            border-radius: 5px;
        }

        .filter-section {
            background: #fff;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .cart-badge,
        .wishlist-badge {
            position: relative;
            top: -10px;
            right: -10px;
        }

        .navbar-brand img {
            height: 40px;
        }

        .navbar {
            background: linear-gradient(90deg, #007bff, #0056b3);
        }

        .navbar-nav .nav-link {
            color: #fff;
            font-weight: 500;
        }

        .navbar-nav .nav-link:hover {
            color: #f8f9fa;
        }

        .search-bar {
            max-width: 300px;
        }

        .footer {
            background: linear-gradient(90deg, #343a40, #212529);
            color: #fff;
        }

        .footer a {
            color: #adb5bd;
            text-decoration: none;
        }

        .footer a:hover {
            color: #fff;
        }

        .footer .social-icons a {
            font-size: 1.5rem;
            margin: 0 10px;
        }

        .checkout-form .form-label {
            font-weight: 500;
        }

        .checkout-form .form-control {
            border-radius: 5px;
        }

        .checkout-form .is-invalid~.invalid-feedback {
            display: block;
        }

        .add-to-wishlist {
            transition: color 0.3s;
        }

        .add-to-wishlist.active {
            color: #dc3545;
        }

        .quantity-input {
            width: 80px;
        }
    </style>
</head>

<body class="bg-light">
    <!-- Header Start -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/images/logo.png" alt="Netacart Logo" class="me-2"> Netacart
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
                aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="portfolio.php"><i class="fas fa-shopping-bag me-1"></i> Shop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php"><i class="fas fa-envelope me-1"></i> Contact</a>
                    </li>
                </ul>
                <form class="d-flex search-bar my-2 my-lg-0">
                    <input class="form-control me-2" type="search" placeholder="Search products..." aria-label="Search">
                    <button class="btn btn-outline-light" type="submit"><i class="fas fa-search"></i></button>
                </form>
                <ul class="navbar-nav ms-3">
                    <li class="nav-item">
                        <button class="btn btn-light btn-sm position-relative" data-bs-toggle="modal" data-bs-target="#cartModal">
                            <i class="fas fa-shopping-cart"></i> Cart
                            <span id="cartCount" class="badge bg-danger cart-badge">0</span>
                        </button>
                    </li>
                    <li class="nav-item ms-2">
                        <button class="btn btn-light btn-sm position-relative" data-bs-toggle="modal" data-bs-target="#wishlistModal" id="wishlistBtn" <?= !$is_logged_in ? 'disabled' : '' ?>>
                            <i class="fas fa-heart"></i> Wishlist
                            <span id="wishlistCount" class="badge bg-primary wishlist-badge">0</span>
                        </button>
                    </li>

                </ul>
            </div>
        </div>
    </nav>
    <!-- Header End -->

    <!-- Announcement Slider -->
    <section class="mb-4">
        <div id="announcementCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php
                $stmt = $conn->prepare("SELECT title, subtitle, image, url FROM slides WHERE status = ? ORDER BY created_at DESC");
                $status = 'Active';
                $stmt->bind_param("s", $status);
                if (!$stmt->execute()) {
                    if ($debug_mode) {
                        error_log("Slider query failed: " . $stmt->error);
                    }
                }
                $result = $stmt->get_result();
                $first = true;

                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                        $image_path = 'assets/images/slides/' . htmlspecialchars($row['image']) . '?v=' . time();
                        $server_path = __DIR__ . '/assets/images/slides/' . $row['image'];
                        $image_exists = !empty($row['image']) && file_exists($server_path);

                        if ($debug_mode && !$image_exists) {
                            error_log("Slider image not found: $server_path");
                        }
                ?>
                        <div class="carousel-item <?= $first ? 'active' : '' ?>">
                            <?php if ($image_exists): ?>
                                <a href="<?= !empty($row['url']) ? htmlspecialchars($row['url']) : '#' ?>" <?= !empty($row['url']) ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
                                    <img src="<?= $image_path ?>" class="d-block w-100" style="height: 300px; object-fit: cover;" alt="<?= htmlspecialchars($row['title']) ?>">
                                </a>
                            <?php else: ?>
                                <div class="d-block w-100 bg-secondary" style="height: 300px; display: flex; align-items: center; justify-content: center;">
                                    <span class="text-white"><i class="fas fa-image fa-3x"></i> Image Missing: <?= htmlspecialchars($row['image']) ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="carousel-caption d-none d-md-block">
                                <h5><?= htmlspecialchars($row['title']) ?></h5>
                                <p><?= htmlspecialchars(substr($row['subtitle'], 0, 100)) . (strlen($row['subtitle']) > 100 ? '...' : '') ?></p>
                            </div>
                        </div>
                    <?php
                        $first = false;
                    endwhile;
                    $stmt->close();
                else:
                    if ($debug_mode) {
                        error_log("No active slides found");
                    }
                    ?>
                    <div class="carousel-item active">
                        <div class="d-block w-100 bg-secondary" style="height: 300px; display: flex; align-items: center; justify-content: center;">
                            <span class="text-white">No active announcements available.</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#announcementCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#announcementCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </section>

    <!-- Portfolio/E-commerce Section -->
    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-primary mb-0"><i class="fas fa-shopping-bag me-2"></i> Shop Our Products</h2>
            </div>

            <!-- Filter and Sort Controls -->
            <div class="filter-section mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="priceRange" class="form-label">Price Range ($0 - $500)</label>
                        <input type="range" class="form-range" id="priceRange" min="0" max="500" value="500">
                    </div>
                    <div class="col-md-4">
                        <label for="ratingFilter" class="form-label">Minimum Rating</label>
                        <select class="form-select" id="ratingFilter">
                            <option value="0">All Ratings</option>
                            <option value="1">1+ Stars</option>
                            <option value="2">2+ Stars</option>
                            <option value="3">3+ Stars</option>
                            <option value="4">4+ Stars</option>
                            <option value="5">5 Stars</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="sortOption" class="form-label">Sort By</label>
                        <select class="form-select" id="sortOption">
                            <option value="default">Default</option>
                            <option value="price-asc">Price: Low to High</option>
                            <option value="price-desc">Price: High to Low</option>
                            <option value="rating-desc">Rating: High to Low</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4" id="productGrid">
                <?php
                $stmt = $conn->prepare("SELECT id, title, description, image, link, price, stock, rating, created_at FROM works WHERE status = ? ORDER BY id DESC");
                $status = 1;
                $stmt->bind_param("i", $status);
                $stmt->execute();
                $result = $stmt->get_result();

                // Fetch wishlist items for logged-in users
                $wishlist_items = [];
                if ($is_logged_in) {
                    $wishlist_stmt = $conn->prepare("SELECT work_id FROM wishlist WHERE user_id = ?");
                    $wishlist_stmt->bind_param("i", $user_id);
                    $wishlist_stmt->execute();
                    $wishlist_result = $wishlist_stmt->get_result();
                    while ($wishlist_row = $wishlist_result->fetch_assoc()) {
                        $wishlist_items[] = (int)$wishlist_row['work_id'];
                    }
                    $wishlist_stmt->close();
                    if ($debug_mode) {
                        error_log("Initial wishlist items for user $user_id: " . json_encode($wishlist_items));
                    }
                }

                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                        $image_path = 'assets/img/works/' . htmlspecialchars($row['image']);
                        $server_path = __DIR__ . '/assets/img/works/' . $row['image'];
                        $image_exists = !empty($row['image']) && file_exists($server_path);

                        if ($debug_mode && !$image_exists) {
                            error_log("Works image not found: $server_path");
                        }
                        $is_wishlisted = $is_logged_in && in_array((int)$row['id'], $wishlist_items);
                ?>
                        <div class="col product-item" data-price="<?= $row['price'] ?>" data-rating="<?= $row['rating'] ?>">
                            <div class="card portfolio-card h-100 border-0 shadow-sm">
                                <?php if ($image_exists): ?>
                                    <img src="<?= $image_path ?>" class="card-img-top" alt="<?= htmlspecialchars($row['title']) ?>">
                                <?php else: ?>
                                    <div class="card-img-top d-flex align-items-center justify-content-center">
                                        <span class="text-muted"><i class="fas fa-image fa-3x"></i> Image Missing: <?= htmlspecialchars($row['image']) ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                                    <p class="card-text text-muted"><?= htmlspecialchars(substr($row['description'], 0, 100)) . (strlen($row['description']) > 100 ? '...' : '') ?></p>
                                    <div class="mb-2">
                                        <span class="price-tag">$<?= number_format($row['price'], 2) ?></span>
                                    </div>
                                    <div class="mb-2">
                                        <span class="badge stock-badge <?= $row['stock'] > 0 ? 'bg-success' : 'bg-secondary' ?>">
                                            Stock: <?= $row['stock'] ?>
                                        </span>
                                    </div>
                                    <div class="mb-3">
                                        <span class="rating-stars">
                                            <?php
                                            $rating = round($row['rating']);
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                            }
                                            ?>
                                            (<?= number_format($row['rating'], 1) ?>)
                                        </span>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 align-items-center">
                                        <?php if (!empty($row['link'])): ?>
                                            <a href="<?= htmlspecialchars($row['link']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-link me-1"></i> Visit
                                            </a>
                                        <?php endif; ?>
                                        <input type="number" class="form-control form-control-sm quantity-input" data-id="<?= $row['id'] ?>" min="1" max="<?= $row['stock'] ?>" value="1">
                                        <button class="btn btn-sm btn-primary add-to-cart" data-id="<?= $row['id'] ?>" data-title="<?= htmlspecialchars($row['title']) ?>" data-price="<?= $row['price'] ?>" data-stock="<?= $row['stock'] ?>">
                                            <i class="fas fa-cart-plus me-1"></i> Add to Cart
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger add-to-wishlist <?= $is_wishlisted ? 'active' : '' ?>" data-id="<?= $row['id'] ?>" <?= !$is_logged_in ? 'disabled' : '' ?>>
                                            <i class="fas fa-heart"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-footer text-muted small">
                                    Added on <?= isset($row['created_at']) && $row['created_at'] ? date('d M Y', strtotime($row['created_at'])) : 'Unknown' ?>
                                </div>
                            </div>
                        </div>
                    <?php
                    endwhile;
                    $stmt->close();
                else:
                    ?>
                    <div class="col-12 text-center">
                        <div class="no-works">
                            <p class="text-muted mb-0"><i class="fas fa-exclamation-circle me-2"></i> No products available right now. Please check back later.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Cart Modal -->
    <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cartModalLabel">Shopping Cart</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="cartItems" class="mb-4"></div>
                    <p class="text-end fw-bold">Total: $<span id="cartTotal">0.00</span></p>
                    <hr>
                    <h5>Checkout</h5>
                    <form id="checkoutForm" class="checkout-form">
                        <div class="mb-3">
                            <label for="fullName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="fullName" required>
                            <div class="invalid-feedback">Please enter your full name.</div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" required>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Shipping Address</label>
                            <textarea class="form-control" id="address" rows="3" required></textarea>
                            <div class="invalid-feedback">Please enter your shipping address.</div>
                        </div>
                        <div class="mb-3">
                            <label for="paymentMethod" class="form-label">Payment Method</label>
                            <select class="form-select" id="paymentMethod" required>
                                <option value="" disabled selected>Select a payment method</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="paypal">PayPal</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                            <div class="invalid-feedback">Please select a payment method.</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="clearCart">Clear Cart</button>
                    <button type="button" class="btn btn-primary" id="checkoutBtn">Complete Checkout</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Wishlist Modal -->
    <div class="modal fade" id="wishlistModal" tabindex="-1" aria-labelledby="wishlistModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="wishlistModalLabel">My Wishlist</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="wishlistItems" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="clearWishlist">Clear Wishlist</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Start -->
    <footer class="footer py-5 mt-5">
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
                        <a href="#" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" target="_blank" rel="noopener noreferrer"><i class="fab fa-twitter"></i></a>
                        <a href="#" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a>
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
    <!-- Footer End -->

    <!-- Scripts -->
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="assets/js/sweetalert2.all.min.js"></script>
    <script>
        $(document).ready(function() {
            const isLoggedIn = <?= json_encode($is_logged_in) ?>;
            const userId = <?= json_encode($user_id) ?>;
            let wishlistItems = <?= json_encode($wishlist_items) ?>.map(id => parseInt(id));

            // Update cart count
            function updateCartCount() {
                $.ajax({
                    url: 'admin/inc/action_cart.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'fetch'
                    },
                    beforeSend: function() {
                        console.log('Fetching cart');
                    },
                    success: function(response) {
                        console.log('Cart fetch response:', response);
                        if (response.status === 'success') {
                            $('#cartCount').text(response.items.length);
                            updateCartModal(response.items);
                        } else {
                            $('#cartCount').text(0);
                            updateCartModal([]);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to fetch cart.'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Cart fetch error:', xhr.responseText, status, error);
                        $('#cartCount').text(0);
                        updateCartModal([]);
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: 'Failed to fetch cart: ' + (xhr.responseText || 'Unknown error.')
                        });
                    }
                });
            }

            // Update cart modal
            function updateCartModal(items) {
                let cartItems = $('#cartItems');
                let total = 0;
                cartItems.empty();
                if (items.length === 0) {
                    cartItems.append('<p class="text-muted">Your cart is empty.</p>');
                } else {
                    items.forEach(item => {
                        let itemTotal = item.price * item.quantity;
                        total += itemTotal;
                        cartItems.append(`
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>${item.title} ($${parseFloat(item.price).toFixed(2)} x ${item.quantity})</span>
                                <div>
                                    <input type="number" class="form-control form-control-sm quantity-input-cart" data-id="${item.work_id}" value="${item.quantity}" min="1" max="${item.stock}" style="width: 80px; display: inline-block;">
                                    <button class="btn btn-sm btn-danger remove-from-cart" data-id="${item.work_id}">Remove</button>
                                </div>
                            </div>
                        `);
                    });
                }
                $('#cartTotal').text(total.toFixed(2));
            }

            // Update wishlist modal
            function updateWishlistModal() {
                if (!isLoggedIn) {
                    $('#wishlistItems').html('<p class="text-muted">Please log in to view your wishlist.</p>');
                    $('#wishlistCount').text(0);
                    return;
                }

                $.ajax({
                    url: 'admin/inc/action_wishlist.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'fetch',
                        user_id: userId
                    },
                    beforeSend: function() {
                        console.log('Fetching wishlist for user:', userId);
                    },
                    success: function(response) {
                        console.log('Wishlist fetch response:', response);
                        $('#wishlistItems').empty();
                        if (response.status === 'success' && response.items && response.items.length > 0) {
                            response.items.forEach(item => {
                                let imagePath = item.image ? `assets/img/works/${item.image}` : 'assets/images/placeholder.png';
                                $('#wishlistItems').append(`
                                    <div class="col">
                                        <div class="card h-100">
                                            <img src="${imagePath}" class="card-img-top" style="height: 150px; object-fit: cover;" alt="${item.title}">
                                            <div class="card-body">
                                                <h6 class="card-title">${item.title}</h6>
                                                <p class="card-text text-muted">$${parseFloat(item.price).toFixed(2)}</p>
                                                <button class="btn btn-sm btn-danger remove-from-wishlist" data-id="${item.work_id}">Remove</button>
                                                <button class="btn btn-sm btn-primary add-to-cart-from-wishlist" data-id="${item.work_id}" data-title="${item.title}" data-price="${item.price}" data-stock="${item.stock}">Add to Cart</button>
                                            </div>
                                        </div>
                                    </div>
                                `);
                            });
                            wishlistItems = response.items.map(item => parseInt(item.work_id));
                            $('#wishlistCount').text(wishlistItems.length);
                            updateWishlistButtons();
                        } else {
                            $('#wishlistItems').html('<p class="text-muted">Your wishlist is empty.</p>');
                            $('#wishlistCount').text(0);
                            wishlistItems = [];
                            updateWishlistButtons();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Wishlist fetch error:', xhr.responseText, status, error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load wishlist.'
                        });
                    }
                });
            }

            // Update wishlist button states
            function updateWishlistButtons() {
                console.log('Updating wishlist buttons with items:', wishlistItems);
                $('.add-to-wishlist').each(function() {
                    let workId = parseInt($(this).data('id'));
                    $(this).toggleClass('active', wishlistItems.includes(workId));
                });
            }

            // Add to cart
            $('.add-to-cart').on('click', function() {
                let id = parseInt($(this).data('id'));
                let title = $(this).data('title');
                let price = parseFloat($(this).data('price'));
                let stock = parseInt($(this).data('stock'));
                let quantityInput = $(this).siblings('.quantity-input');
                let quantity = parseInt(quantityInput.val());

                if (isNaN(quantity) || quantity <= 0 || quantity > stock) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Quantity',
                        text: `Please select a quantity between 1 and ${stock}.`
                    });
                    quantityInput.val(1);
                    return;
                }

                $.ajax({
                    url: 'admin/inc/action_cart.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'add',
                        work_id: id,
                        quantity: quantity,
                        user_id: userId
                    },
                    beforeSend: function() {
                        console.log(`Adding to cart: work_id=${id}, quantity=${quantity}, user_id=${userId || 'null'}`);
                    },
                    success: function(response) {
                        console.log('Add to cart response:', response);
                        if (response.status === 'success') {
                            updateCartCount();
                            Swal.fire({
                                icon: 'success',
                                title: 'Added to Cart',
                                text: `${title} (x${quantity}) has been added to your cart.`,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            quantityInput.val(1); // Reset quantity input
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to add to cart.'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Add to cart error:', xhr.responseText, status, error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: 'Unable to add to cart: ' + (xhr.responseText || 'Unknown error.')
                        });
                    }
                });
            });

            // Update cart quantity
            $(document).on('change', '.quantity-input-cart', function() {
                let workId = parseInt($(this).data('id'));
                let quantity = parseInt($(this).val());
                let max = parseInt($(this).attr('max'));

                if (isNaN(quantity) || quantity <= 0 || quantity > max) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Quantity',
                        text: `Please select a quantity between 1 and ${max}.`
                    });
                    $(this).val(1);
                    return;
                }

                $.ajax({
                    url: 'admin/inc/action_cart.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'update',
                        work_id: workId,
                        quantity: quantity,
                        user_id: userId
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            updateCartCount();
                            Swal.fire({
                                icon: 'success',
                                title: 'Cart Updated',
                                text: `Quantity updated to ${quantity}.`,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to update cart.'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Update cart error:', xhr.responseText, status, error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: 'Unable to update cart: ' + (xhr.responseText || 'Unknown error.')
                        });
                    }
                });
            });

            // Remove from cart
            $(document).on('click', '.remove-from-cart', function() {
                let workId = parseInt($(this).data('id'));
                $.ajax({
                    url: 'admin/inc/action_cart.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'remove',
                        work_id: workId,
                        user_id: userId
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            updateCartCount();
                            Swal.fire({
                                icon: 'success',
                                title: 'Removed',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to remove from cart.'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Remove from cart error:', xhr.responseText, status, error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: 'Unable to remove from cart: ' + (xhr.responseText || 'Unknown error.')
                        });
                    }
                });
            });

            // Clear cart
            $('#clearCart').on('click', function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Clear Cart?',
                    text: 'This will remove all items from your cart.',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, clear it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'admin/inc/action_cart.php',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                action: 'clear',
                                user_id: userId
                            },
                            success: function(response) {
                                if (response.status === 'success') {
                                    updateCartCount();
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Cart Cleared',
                                        text: response.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message || 'Failed to clear cart.'
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Clear cart error:', xhr.responseText, status, error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Server Error',
                                    text: 'Unable to clear cart: ' + (xhr.responseText || 'Unknown error.')
                                });
                            }
                        });
                    }
                });
            });

            // Checkout
            $('#checkoutBtn').on('click', function() {
                let form = $('#checkoutForm');
                let isValid = true;

                // Validate form fields
                form.find('input[required], select[required], textarea[required]').each(function() {
                    let input = $(this);
                    if (!input.val()) {
                        input.addClass('is-invalid');
                        isValid = false;
                    } else {
                        input.removeClass('is-invalid');
                    }
                });

                let email = $('#email').val();
                if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    $('#email').addClass('is-invalid');
                    isValid = false;
                }

                if (!isValid) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Incomplete Form',
                        text: 'Please fill out all required fields correctly.'
                    });
                    return;
                }

                // Check cart before proceeding
                $.ajax({
                    url: 'admin/inc/action_cart.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'fetch',
                        user_id: userId
                    },
                    success: function(response) {
                        if (response.status === 'success' && response.items.length === 0) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Empty Cart',
                                text: 'Please add items to your cart before checking out.'
                            });
                            return;
                        }

                        let formData = {
                            action: 'create_order',
                            full_name: $('#fullName').val(),
                            email: $('#email').val(),
                            address: $('#address').val(),
                            payment_method: $('#paymentMethod').val(),
                            user_id: userId
                        };

                        $.ajax({
                            url: 'admin/inc/action_orders.php',
                            type: 'POST',
                            dataType: 'json',
                            data: formData,
                            success: function(response) {
                                console.log('Checkout response:', response);
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Order Placed!',
                                        text: 'Your order has been placed successfully. Redirecting to invoice...',
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        form[0].reset();
                                        $('#cartModal').modal('hide');
                                        window.location.href = `invoice.php?order_id=${response.order_id}`;
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Order Failed',
                                        text: response.message || 'Failed to place order. Please try again.'
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Checkout error:', xhr.responseText, status, error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Server Error',
                                    text: 'Unable to place order: ' + (xhr.responseText || 'Unknown error.')
                                });
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Cart fetch error:', xhr.responseText, status, error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: 'Unable to fetch cart: ' + (xhr.responseText || 'Unknown error.')
                        });
                    }
                });
            });

            // Wishlist toggle
            $('.add-to-wishlist').on('click', function() {
                if (!isLoggedIn) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Login Required',
                        text: 'Please log in to manage your wishlist.',
                        confirmButtonText: 'Go to Login'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'login.php';
                        }
                    });
                    return;
                }

                let $button = $(this);
                let workId = parseInt($button.data('id'));
                let action = $button.hasClass('active') ? 'remove' : 'add';

                $.ajax({
                    url: 'admin/inc/action_wishlist.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: action,
                        work_id: workId,
                        user_id: userId
                    },
                    beforeSend: function() {
                        console.log(`Sending ${action} request for work_id: ${workId}, user_id: ${userId}`);
                    },
                    success: function(response) {
                        console.log('Wishlist toggle response:', response);
                        if (response.status === 'success' || response.status === 'info') {
                            if (action === 'add') {
                                $button.addClass('active');
                                if (!wishlistItems.includes(workId)) {
                                    wishlistItems.push(workId);
                                }
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Added to Wishlist',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            } else {
                                $button.removeClass('active');
                                wishlistItems = wishlistItems.filter(id => id !== workId);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Removed from Wishlist',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            }
                            $('#wishlistCount').text(wishlistItems.length);
                            updateWishlistModal();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to update wishlist.'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Wishlist toggle error:', xhr.responseText, status, error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: 'Unable to process request: ' + (xhr.responseText || 'Unknown error.')
                        });
                    }
                });
            });

            // Remove from wishlist
            $(document).on('click', '.remove-from-wishlist', function() {
                let workId = parseInt($(this).data('id'));
                $.ajax({
                    url: 'admin/inc/action_wishlist.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'remove',
                        work_id: workId,
                        user_id: userId
                    },
                    beforeSend: function() {
                        console.log('Removing work_id:', workId, 'for user_id:', userId);
                    },
                    success: function(response) {
                        console.log('Remove from wishlist response:', response);
                        if (response.status === 'success') {
                            wishlistItems = wishlistItems.filter(id => id !== workId);
                            updateWishlistModal();
                            updateWishlistButtons();
                            Swal.fire({
                                icon: 'success',
                                title: 'Removed',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to remove from wishlist.'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Remove from wishlist error:', xhr.responseText, status, error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: 'Unable to process request: ' + (xhr.responseText || 'Unknown error.')
                        });
                    }
                });
            });

            // Add to cart from wishlist
            $(document).on('click', '.add-to-cart-from-wishlist', function() {
                let id = parseInt($(this).data('id'));
                let title = $(this).data('title');
                let price = parseFloat($(this).data('price'));
                let stock = parseInt($(this).data('stock'));
                let quantity = 1;

                if (stock <= 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Out of Stock',
                        text: `${title} is currently out of stock.`
                    });
                    return;
                }

                $.ajax({
                    url: 'admin/inc/action_cart.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'add',
                        work_id: id,
                        quantity: quantity,
                        user_id: userId
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            updateCartCount();
                            Swal.fire({
                                icon: 'success',
                                title: 'Added to Cart',
                                text: `${title} has been added to your cart.`,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to add to cart.'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Add to cart from wishlist error:', xhr.responseText, status, error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: 'Unable to add to cart: ' + (xhr.responseText || 'Unknown error.')
                        });
                    }
                });
            });

            // Clear wishlist
            $('#clearWishlist').on('click', function() {
                if (!isLoggedIn) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Login Required',
                        text: 'Please log in to manage your wishlist.'
                    });
                    return;
                }
                Swal.fire({
                    icon: 'warning',
                    title: 'Clear Wishlist?',
                    text: 'This will remove all items from your wishlist.',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, clear it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'admin/inc/action_wishlist.php',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                action: 'clear',
                                user_id: userId
                            },
                            beforeSend: function() {
                                console.log('Clearing wishlist for user_id:', userId);
                            },
                            success: function(response) {
                                console.log('Clear wishlist response:', response);
                                if (response.status === 'success') {
                                    wishlistItems = [];
                                    updateWishlistModal();
                                    updateWishlistButtons();
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Wishlist Cleared',
                                        text: 'Your wishlist has been cleared.',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message || 'Failed to clear wishlist.'
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Clear wishlist error:', xhr.responseText, status, error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Server Error',
                                    text: 'Unable to clear wishlist: ' + (xhr.responseText || 'Unknown error.')
                                });
                            }
                        });
                    }
                });
            });

            // Filter and sort
            function filterAndSortProducts() {
                let maxPrice = parseInt($('#priceRange').val());
                let minRating = parseInt($('#ratingFilter').val());
                let sortOption = $('#sortOption').val();

                let products = $('.product-item').get();
                products.forEach(product => {
                    let price = parseFloat($(product).data('price'));
                    let rating = parseFloat($(product).data('rating'));
                    if (price <= maxPrice && rating >= minRating) {
                        $(product).show();
                    } else {
                        $(product).hide();
                    }
                });

                if (sortOption !== 'default') {
                    products.sort(function(a, b) {
                        let aPrice = parseFloat($(a).data('price'));
                        let bPrice = parseFloat($(b).data('price'));
                        let aRating = parseFloat($(a).data('rating'));
                        let bRating = parseFloat($(b).data('rating'));

                        if (sortOption === 'price-asc') return aPrice - bPrice;
                        if (sortOption === 'price-desc') return bPrice - aPrice;
                        if (sortOption === 'rating-desc') return bRating - aRating;
                        return 0;
                    });

                    $('#productGrid').empty().append(products);
                }
            }

            $('#priceRange, #ratingFilter, #sortOption').on('change', filterAndSortProducts);

            // Initialize modals
            updateCartCount();
            updateWishlistModal();

            // Card hover effect
            $('.portfolio-card').on('mouseenter', function() {
                $(this).css('cursor', 'pointer');
            });
        });
    </script>
</body>

</html>