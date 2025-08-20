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
    <title>Netacart - Modern Shopping Experience</title>
    <link rel="shortcut icon" href="assets/images/logo.png" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;900&family=Open+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="app/globals.css">
    <style>
        /* Updated typography and modern styling */
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

        /* Modern navigation with light blue glass effect */
        .navbar-modern {
            /* Changed to light blue background with glass effect */
            background: rgba(173, 216, 230, 0.75);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(135, 206, 235, 0.3);
            box-shadow: 0 1px 20px rgba(70, 130, 180, 0.15);
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-family: var(--font-heading);
            font-weight: 900;
            font-size: 1.5rem;
            color: var(--color-primary) !important;
        }

        .nav-link {
            font-weight: 500;
            color: var(--color-foreground) !important;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--color-primary) !important;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 3px;
            background: var(--gradient-primary);
            border-radius: 2px;
        }

        /* Modern search bar */
        .search-container {
            position: relative;
            max-width: 400px;
        }

        .search-input {
            border: 2px solid var(--color-border);
            border-radius: 50px;
            padding: 12px 50px 12px 20px;
            background: var(--color-background);
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .search-input:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px var(--color-ring);
            outline: none;
        }

        .search-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--gradient-primary);
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            transform: translateY(-50%) scale(1.05);
        }

        /* Modern cart and wishlist buttons */
        .action-btn {
            background: var(--color-card);
            border: 2px solid var(--color-border);
            border-radius: 12px;
            padding: 10px 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            color: var(--color-foreground);
        }

        .action-btn:hover {
            background: var(--color-primary);
            border-color: var(--color-primary);
            color: var(--color-primary-foreground);
            transform: translateY(-2px);
        }

        .badge-modern {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--gradient-primary);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            border: 2px solid white;
        }

        /* Modern carousel with smaller, more compact styling */
        .carousel-modern {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow-soft);
            margin-bottom: 2.5rem;
            /* Made carousel smaller and more compact */
        }

        .carousel-item img {
            height: 320px;
            object-fit: cover;
        }

        .carousel-caption-modern {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.4) 100%);
            border-radius: 15px;
            padding: 2rem;
            backdrop-filter: blur(10px);
        }

        /* Modern section headers */
        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 900;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }

        .section-subtitle {
            font-size: 1.1rem;
            color: var(--color-muted-foreground);
            max-width: 600px;
            margin: 0 auto;
        }

        /* Modern filter section */
        .filter-card {
            background: var(--gradient-card);
            border: 1px solid var(--color-border);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-soft);
            margin-bottom: 3rem;
        }

        .filter-label {
            font-weight: 600;
            color: var(--color-foreground);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-control-modern,
        .form-select-modern {
            border: 2px solid var(--color-border);
            border-radius: 12px;
            padding: 12px 16px;
            background: var(--color-background);
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .form-control-modern:focus,
        .form-select-modern:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px var(--color-ring);
            outline: none;
        }

        .form-range-modern {
            height: 6px;
            background: var(--color-secondary);
            border-radius: 3px;
            outline: none;
        }

        .form-range-modern::-webkit-slider-thumb {
            appearance: none;
            width: 20px;
            height: 20px;
            background: var(--gradient-primary);
            border-radius: 50%;
            cursor: pointer;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        /* Modern product cards with colorful enhancements */
        .product-card {
            background: linear-gradient(135deg, #fef3c7 0%, #fef9e7 50%, #ffffff 100%);
            border: 1px solid rgba(251, 191, 36, 0.2);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.1);
            height: 100%;
            position: relative;
            /* Added colorful gradient background with warm yellow tones */
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 30px rgba(251, 191, 36, 0.2);
            border-color: #f59e0b;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 50%, #ffffff 100%);
            /* Enhanced hover effect with stronger yellow gradient */
        }

        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #f59e0b 0%, #84cc16 50%, #06b6d4 100%);
            /* Added colorful top border accent */
        }

        .product-image {
            height: 180px;
            object-fit: cover;
            transition: all 0.4s ease;
            border-bottom: 1px solid rgba(251, 191, 36, 0.1);
            /* Reduced height and added subtle border */
        }

        .product-card:hover .product-image {
            transform: scale(1.03);
            filter: brightness(1.05) saturate(1.1);
            /* Added brightness and saturation on hover */
        }

        .product-body {
            padding: 1rem;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            /* Reduced padding and added glass effect */
        }

        .product-title {
            font-family: var(--font-heading);
            font-weight: 700;
            font-size: 1rem;
            color: #1f2937;
            margin-bottom: 0.5rem;
            /* Slightly smaller font size for compact design */
        }

        .product-description {
            color: #6b7280;
            font-size: 0.85rem;
            line-height: 1.4;
            margin-bottom: 0.8rem;
            /* Smaller text and reduced spacing */
        }

        .price-tag {
            font-family: var(--font-heading);
            font-weight: 900;
            font-size: 1.2rem;
            background: linear-gradient(135deg, #f59e0b 0%, #84cc16 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            /* Colorful gradient for price */
        }

        .stock-badge {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            /* Smaller badge with gradient background */
        }

        .stock-badge.out-of-stock {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            /* Red gradient for out of stock */
        }

        .rating-stars {
            color: #f59e0b;
            margin-bottom: 0.8rem;
            font-size: 0.9rem;
            /* Smaller stars with reduced margin */
        }

        /* Modern buttons */
        .btn-modern {
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            font-size: 0.9rem;
        }

        .btn-primary-modern {
            background: var(--gradient-primary);
            color: white;
            border-color: var(--color-primary);
        }

        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(132, 204, 22, 0.3);
        }

        .btn-outline-modern {
            background: transparent;
            color: var(--color-primary);
            border-color: var(--color-primary);
        }

        .btn-outline-modern:hover {
            background: var(--color-primary);
            color: var(--color-primary-foreground);
            transform: translateY(-2px);
        }

        .btn-wishlist {
            background: var(--color-background);
            color: var(--color-muted-foreground);
            border: 2px solid var(--color-border);
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .btn-wishlist:hover,
        .btn-wishlist.active {
            background: #dc2626;
            color: white;
            border-color: #dc2626;
            transform: scale(1.1);
        }

        .quantity-input-modern {
            width: 70px;
            border: 2px solid var(--color-border);
            border-radius: 8px;
            padding: 8px;
            text-align: center;
            font-weight: 600;
        }

        /* Modern footer */
        .footer-modern {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-top: 1px solid var(--color-border);
            padding: 3rem 0 2rem;
            margin-top: 4rem;
        }

        .footer-title {
            font-family: var(--font-heading);
            font-weight: 700;
            color: var(--color-foreground);
            margin-bottom: 1rem;
        }

        .footer-link {
            color: var(--color-muted-foreground);
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .footer-link:hover {
            color: var(--color-primary);
            transform: translateX(5px);
        }

        .social-icon {
            width: 44px;
            height: 44px;
            background: var(--color-background);
            border: 2px solid var(--color-border);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-muted-foreground);
            text-decoration: none;
            transition: all 0.3s ease;
            margin-right: 1rem;
        }

        .social-icon:hover {
            background: var(--color-primary);
            border-color: var(--color-primary);
            color: white;
            transform: translateY(-3px);
        }

        /* Compact cart modal with enhanced colors */
        .modal-dialog {
            max-width: 600px;
            /* Made modal smaller for more compact design */
        }

        .modal-content-modern {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(59, 130, 246, 0.15);
            /* Added blue accent border and shadow */
        }

        .modal-header-modern {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(147, 197, 253, 0.1) 100%);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 20px 20px 0 0;
            padding: 1.2rem 1.5rem;
            /* Reduced padding and added blue gradient */
        }

        .modal-body {
            background: rgba(248, 250, 252, 0.8);
            backdrop-filter: blur(10px);
            padding: 1.5rem !important;
            /* Reduced padding for compact design */
        }

        .modal-footer {
            background: linear-gradient(135deg, rgba(241, 245, 249, 0.9) 0%, rgba(226, 232, 240, 0.9) 100%);
            border-top: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 0 0 20px 20px;
            backdrop-filter: blur(10px);
            padding: 1.2rem 1.5rem;
            /* Reduced padding and added blue accent border */
        }

        .cart-item-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(248, 250, 252, 0.9) 100%);
            border: 1px solid rgba(59, 130, 246, 0.15);
            border-radius: 12px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            padding: 1rem !important;
            margin-bottom: 0.8rem !important;
            /* Reduced padding and margin, added blue accent */
        }

        .cart-item-card:hover {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(147, 197, 253, 0.05) 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
            border-color: rgba(59, 130, 246, 0.3);
            /* Blue hover effect */
        }

        .wishlist-item-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(226, 232, 240, 0.6);
            border-radius: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .wishlist-item-card:hover {
            background: rgba(255, 255, 255, 0.95);
            border-color: var(--color-primary);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        /* Enhanced empty state styling */
        .empty-state {
            background: rgba(248, 250, 252, 0.8);
            backdrop-filter: blur(10px);
            border: 2px dashed rgba(203, 213, 225, 0.6);
            border-radius: 20px;
            padding: 3rem 2rem;
            text-align: center;
        }

        .total-section {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.1) 100%);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.1);
            /* Reduced padding and green gradient for total */
        }

        /* Enhanced close button styling */
        .btn-close {
            background: rgba(239, 68, 68, 0.1);
            border-radius: 50%;
            width: 32px;
            height: 32px;
            opacity: 0.8;
            transition: all 0.3s ease;
        }

        .btn-close:hover {
            background: rgba(239, 68, 68, 0.2);
            opacity: 1;
            transform: scale(1.1);
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            .section-title {
                font-size: 2rem;
            }

            .product-card {
                margin-bottom: 1.5rem;
            }

            .filter-card {
                padding: 1.5rem;
            }

            .search-container {
                max-width: 100%;
                margin-bottom: 1rem;
            }
        }

        /* Loading and animation states */
        .loading-shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }

        .fade-in {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Smaller form controls with blue accents */
        .form-control-modern,
        .form-select-modern {
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 8px;
            padding: 0.6rem 0.8rem;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(5px);
            font-size: 0.9rem;
            /* Smaller form controls with blue accents */
        }

        .form-control-modern:focus,
        .form-select-modern:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: rgba(255, 255, 255, 0.95);
            /* Blue focus state */
        }

        .filter-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.4rem;
            font-size: 0.85rem;
            /* Smaller labels for compact design */
        }

        .btn-modern {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            /* Smaller buttons for compact design */
        }

        .btn-primary-modern {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            color: white;
            /* Blue gradient for primary buttons */
        }

        .btn-primary-modern:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            /* Enhanced blue hover effect */
        }

        .btn-outline-modern {
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #3b82f6;
            background: rgba(59, 130, 246, 0.05);
            /* Blue outline buttons */
        }

        .btn-outline-modern:hover {
            background: rgba(59, 130, 246, 0.1);
            border-color: #3b82f6;
            color: #1d4ed8;
            transform: translateY(-1px);
            /* Blue hover for outline buttons */
        }
    </style>
</head>

<body>
    <!-- Modern navigation header -->
    <nav class="navbar navbar-expand-lg navbar-modern sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="assets/images/logo.png" alt="Netacart Logo" height="40" class="me-2">
                Netacart
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <i class="bi bi-list fs-4"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-house me-1"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="portfolio.php">
                            <i class="bi bi-shop me-1"></i> Shop
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">
                            <i class="bi bi-envelope me-1"></i> Contact
                        </a>
                    </li>
                </ul>

                <!-- Modern search bar -->
                <div class="search-container me-3">
                    <input class="form-control search-input" type="search" placeholder="Search products..." aria-label="Search">
                    <button class="search-btn" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>

                <!-- Modern action buttons -->
                <div class="d-flex gap-2">
                    <button class="action-btn" data-bs-toggle="modal" data-bs-target="#cartModal">
                        <i class="bi bi-cart3"></i> Cart
                        <span id="cartCount" class="badge-modern">0</span>
                    </button>
                    <button class="action-btn" data-bs-toggle="modal" data-bs-target="#wishlistModal" id="wishlistBtn" <?= !$is_logged_in ? 'disabled' : '' ?>>
                        <i class="bi bi-heart"></i> Wishlist
                        <span id="wishlistCount" class="badge-modern">0</span>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Modern announcement carousel -->
    <section class="container my-4">
        <div id="announcementCarousel" class="carousel slide carousel-modern" data-bs-ride="carousel">
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
                                    <img src="<?= $image_path ?>" class="d-block w-100" alt="<?= htmlspecialchars($row['title']) ?>">
                                </a>
                            <?php else: ?>
                                <div class="d-block w-100 bg-secondary d-flex align-items-center justify-content-center" style="height: 400px;">
                                    <div class="text-center text-white">
                                        <i class="bi bi-image fs-1 mb-3"></i>
                                        <p>Image Missing: <?= htmlspecialchars($row['image']) ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="carousel-caption carousel-caption-modern">
                                <h3 class="fw-bold"><?= htmlspecialchars($row['title']) ?></h3>
                                <p class="mb-0"><?= htmlspecialchars(substr($row['subtitle'], 0, 100)) . (strlen($row['subtitle']) > 100 ? '...' : '') ?></p>
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
                        <div class="d-block w-100 bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
                            <div class="text-center text-muted">
                                <i class="bi bi-megaphone fs-1 mb-3"></i>
                                <h4>No Active Announcements</h4>
                                <p>Check back later for exciting updates!</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#announcementCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#announcementCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </section>

    <!-- Modern shop section -->
    <section class="container py-5">
        <!-- Modern section header -->
        <div class="section-header fade-in">
            <h1 class="section-title">Discover Amazing Products</h1>
            <p class="section-subtitle">Explore our curated collection of high-quality products, carefully selected to bring you the best shopping experience.</p>
        </div>

        <!-- Modern filter section -->
        <div class="filter-card fade-in">
            <div class="row g-4">
                <div class="col-md-4">
                    <label class="filter-label">Price Range ($0 - $500)</label>
                    <input type="range" class="form-range form-range-modern w-100" id="priceRange" min="0" max="500" value="500">
                    <div class="d-flex justify-content-between mt-2">
                        <small class="text-muted">$0</small>
                        <small class="text-muted" id="priceValue">$500</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="filter-label">Minimum Rating</label>
                    <select class="form-select form-select-modern" id="ratingFilter">
                        <option value="0">All Ratings</option>
                        <option value="1">1+ Stars</option>
                        <option value="2">2+ Stars</option>
                        <option value="3">3+ Stars</option>
                        <option value="4">4+ Stars</option>
                        <option value="5">5 Stars Only</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="filter-label">Sort By</label>
                    <select class="form-select form-select-modern" id="sortOption">
                        <option value="default">Featured</option>
                        <option value="price-asc">Price: Low to High</option>
                        <option value="price-desc">Price: High to Low</option>
                        <option value="rating-desc">Highest Rated</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Modern product grid -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-4" id="productGrid">
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
                    <div class="col product-item fade-in" data-price="<?= $row['price'] ?>" data-rating="<?= $row['rating'] ?>">
                        <div class="card product-card border-0">
                            <div class="position-relative overflow-hidden">
                                <?php if ($image_exists): ?>
                                    <img src="<?= $image_path ?>" class="card-img-top product-image" alt="<?= htmlspecialchars($row['title']) ?>">
                                <?php else: ?>
                                    <div class="product-image d-flex align-items-center justify-content-center bg-light">
                                        <div class="text-center text-muted">
                                            <i class="bi bi-image fs-1 mb-2"></i>
                                            <small>Image Missing</small>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Modern wishlist button positioned on image -->
                                <button class="btn-wishlist position-absolute top-0 end-0 m-3 add-to-wishlist <?= $is_wishlisted ? 'active' : '' ?>"
                                    data-id="<?= $row['id'] ?>" <?= !$is_logged_in ? 'disabled' : '' ?>>
                                    <i class="bi bi-heart-fill"></i>
                                </button>
                            </div>

                            <div class="product-body">
                                <h5 class="product-title"><?= htmlspecialchars($row['title']) ?></h5>
                                <p class="product-description"><?= htmlspecialchars(substr($row['description'], 0, 80)) . (strlen($row['description']) > 80 ? '...' : '') ?></p>

                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="price-tag">$<?= number_format($row['price'], 2) ?></span>
                                    <span class="stock-badge <?= $row['stock'] > 0 ? '' : 'out-of-stock' ?>">
                                        <?= $row['stock'] > 0 ? $row['stock'] . ' in stock' : 'Out of stock' ?>
                                    </span>
                                </div>

                                <div class="rating-stars mb-3">
                                    <?php
                                    $rating = round($row['rating']);
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= $rating ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
                                    }
                                    ?>
                                    <small class="text-muted ms-2">(<?= number_format($row['rating'], 1) ?>)</small>
                                </div>

                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <input type="number" class="form-control quantity-input-modern quantity-input"
                                        data-id="<?= $row['id'] ?>" min="1" max="<?= $row['stock'] ?>" value="1"
                                        style="width: 60px; font-size: 0.9rem;">
                                    <!-- Added smaller, more prominent cart button -->
                                    <button class="btn btn-primary-modern btn-sm add-to-cart"
                                        data-id="<?= $row['id'] ?>" data-title="<?= htmlspecialchars($row['title']) ?>"
                                        data-price="<?= $row['price'] ?>" data-stock="<?= $row['stock'] ?>"
                                        style="background: linear-gradient(135deg, #84cc16 0%, #65a30d 100%); 
                                                   border: none; border-radius: 10px; font-weight: 600; 
                                                   padding: 8px 16px; box-shadow: 0 2px 8px rgba(132, 204, 22, 0.3);">
                                        <i class="bi bi-cart-plus me-1"></i> Add
                                    </button>
                                </div>

                                <?php if (!empty($row['link'])): ?>
                                    <a href="<?= htmlspecialchars($row['link']) ?>" target="_blank" rel="noopener noreferrer"
                                        class="btn btn-outline-modern btn-modern w-100">
                                        <i class="bi bi-link-45deg me-1"></i> View Details
                                    </a>
                                <?php endif; ?>
                            </div>

                            <div class="card-footer bg-transparent border-0 px-3 pb-3">
                                <small class="text-muted">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    Added <?= isset($row['created_at']) && $row['created_at'] ? date('M j, Y', strtotime($row['created_at'])) : 'Recently' ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php
                endwhile;
                $stmt->close();
            else:
                ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-shop fs-1 text-muted"></i>
                        </div>
                        <h3 class="text-muted mb-3">No Products Available</h3>
                        <p class="text-muted">We're working hard to bring you amazing products. Check back soon!</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>


    <!-- Modern cart modal -->
    <!-- Enhanced cart modal with glass morphism design -->
    <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title modal-title-modern" id="cartModalLabel">
                        <i class="bi bi-cart3 me-2 text-primary"></i>Shopping Cart
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="cartItems" class="mb-4"></div>
                    <div class="total-section d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0 text-foreground">Total:</h5>
                        <h4 class="mb-0 text-primary fw-bold">$<span id="cartTotal">0.00</span></h4>
                    </div>

                    <hr style="border-color: rgba(226, 232, 240, 0.5);">
                    <h5 class="mb-3 text-foreground">
                        <i class="bi bi-credit-card me-2 text-primary"></i>Checkout Information
                    </h5>
                    <form id="checkoutForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="fullName" class="filter-label">Full Name</label>
                                <input type="text" class="form-control form-control-modern" id="fullName" required>
                                <div class="invalid-feedback">Please enter your full name.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="filter-label">Email Address</label>
                                <input type="email" class="form-control form-control-modern" id="email" required>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>
                            <div class="col-12">
                                <label for="address" class="filter-label">Shipping Address</label>
                                <textarea class="form-control form-control-modern" id="address" rows="3" required></textarea>
                                <div class="invalid-feedback">Please enter your shipping address.</div>
                            </div>
                            <div class="col-12">
                                <label for="paymentMethod" class="filter-label">Payment Method</label>
                                <select class="form-select form-select-modern" id="paymentMethod" required>
                                    <option value="" disabled selected>Select a payment method</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="paypal">PayPal</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                                <div class="invalid-feedback">Please select a payment method.</div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer p-4">
                    <button type="button" class="btn btn-outline-modern btn-modern" id="clearCart">
                        <i class="bi bi-trash me-1"></i> Clear Cart
                    </button>
                    <button type="button" class="btn btn-primary-modern btn-modern" id="checkoutBtn">
                        <i class="bi bi-check-circle me-1"></i> Complete Checkout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern wishlist modal -->
    <!-- Enhanced wishlist modal with glass morphism design -->
    <div class="modal fade" id="wishlistModal" tabindex="-1" aria-labelledby="wishlistModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title modal-title-modern" id="wishlistModalLabel">
                        <i class="bi bi-heart me-2 text-danger"></i>My Wishlist
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="wishlistItems" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4"></div>
                </div>
                <div class="modal-footer p-4">
                    <button type="button" class="btn btn-outline-modern btn-modern" id="clearWishlist">
                        <i class="bi bi-trash me-1"></i> Clear Wishlist
                    </button>
                    <button type="button" class="btn btn-outline-modern btn-modern" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern footer -->
    <footer class="footer-modern">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="d-flex align-items-center mb-3">
                        <img src="assets/images/logo.png" alt="Netacart Logo" height="40" class="me-2">
                        <h5 class="footer-title mb-0">Netacart</h5>
                    </div>
                    <p class="text-muted mb-4">Your trusted partner for quality products and exceptional shopping experiences. Discover, shop, and enjoy with confidence.</p>
                    <div class="d-flex">
                        <a href="#" class="social-icon" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="#" class="social-icon" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-twitter"></i>
                        </a>
                        <a href="#" class="social-icon" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="#" class="social-icon" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-linkedin"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="footer-title">Quick Links</h6>
                    <div class="d-flex flex-column">
                        <a href="index.php" class="footer-link">
                            <i class="bi bi-house me-2"></i> Home
                        </a>
                        <a href="portfolio.php" class="footer-link">
                            <i class="bi bi-shop me-2"></i> Shop
                        </a>
                        <a href="contact.php" class="footer-link">
                            <i class="bi bi-envelope me-2"></i> Contact
                        </a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="footer-title">Support</h6>
                    <div class="d-flex flex-column">
                        <a href="privacy.php" class="footer-link">
                            <i class="bi bi-shield-check me-2"></i> Privacy Policy
                        </a>
                        <a href="terms.php" class="footer-link">
                            <i class="bi bi-file-text me-2"></i> Terms & Conditions
                        </a>
                        <a href="#" class="footer-link">
                            <i class="bi bi-question-circle me-2"></i> FAQ
                        </a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <h6 class="footer-title">Stay Updated</h6>
                    <p class="text-muted mb-3">Subscribe to our newsletter for the latest products and exclusive offers.</p>
                    <div class="d-flex">
                        <input type="email" class="form-control form-control-modern me-2" placeholder="Enter your email">
                        <button class="btn btn-primary-modern btn-modern">
                            <i class="bi bi-send"></i>
                        </button>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p class="text-muted mb-0">
                    &copy; <?= date('Y') ?> <strong>Netacart</strong>. All rights reserved. Made with
                    <i class="bi bi-heart-fill text-danger"></i> for amazing shopping experiences.
                </p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="assets/js/sweetalert2.all.min.js"></script>
    <script>
        $(document).ready(function() {
            const isLoggedIn = <?= json_encode($is_logged_in) ?>;
            const userId = <?= json_encode($user_id) ?>;
            let wishlistItems = <?= json_encode($wishlist_items) ?>.map(id => parseInt(id));

            $('#priceRange').on('input', function() {
                $('#priceValue').text('$' + $(this).val());
            });

            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animationDelay = Math.random() * 0.3 + 's';
                        entry.target.classList.add('animate-fade-in-up');
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.product-item').forEach(item => {
                observer.observe(item);
            });

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
                    cartItems.append(`
                        <div class="empty-state">
                            <i class="bi bi-cart-x fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted mb-2">Your cart is empty</h5>
                            <p class="text-muted mb-0">Add some products to get started!</p>
                        </div>
                    `);
                } else {
                    items.forEach(item => {
                        let itemTotal = item.price * item.quantity;
                        total += itemTotal;
                        cartItems.append(`
                            <div class="cart-item-card d-flex align-items-center justify-content-between p-3 mb-3">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold text-foreground">${item.title}</h6>
                                    <small class="text-muted">$${parseFloat(item.price).toFixed(2)} each</small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="number" class="form-control form-control-sm quantity-input-cart" 
                                           data-id="${item.work_id}" value="${item.quantity}" min="1" max="${item.stock}" 
                                           style="width: 70px; border-radius: 8px;">
                                    <button class="btn btn-sm btn-outline-danger remove-from-cart" data-id="${item.work_id}" 
                                            style="border-radius: 8px;">
                                        <i class="bi bi-trash"></i>
                                    </button>
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
                    $('#wishlistItems').html(`
                        <div class="col-12">
                            <div class="empty-state">
                                <i class="bi bi-heart fs-1 text-muted mb-3"></i>
                                <h5 class="text-muted mb-2">Please log in</h5>
                                <p class="text-muted mb-0">Sign in to view your wishlist</p>
                            </div>
                        </div>
                    `);
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
                                        <div class="wishlist-item-card card product-card h-100">
                                            <img src="${imagePath}" class="card-img-top" style="height: 150px; object-fit: cover; border-radius: 16px 16px 0 0;" alt="${item.title}">
                                            <div class="card-body p-3">
                                                <h6 class="card-title fw-bold text-foreground">${item.title}</h6>
                                                <p class="text-primary fw-bold mb-3">$${parseFloat(item.price).toFixed(2)}</p>
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-sm btn-outline-danger remove-from-wishlist" data-id="${item.work_id}" 
                                                            style="border-radius: 8px;">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-primary-modern add-to-cart-from-wishlist flex-grow-1" 
                                                            data-id="${item.work_id}" data-title="${item.title}" 
                                                            data-price="${item.price}" data-stock="${item.stock}"
                                                            style="border-radius: 8px;">
                                                        <i class="bi bi-cart-plus"></i> Add to Cart
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `);
                            });
                            wishlistItems = response.items.map(item => parseInt(item.work_id));
                            $('#wishlistCount').text(wishlistItems.length);
                            updateWishlistButtons();
                        } else {
                            $('#wishlistItems').html(`
                                <div class="col-12">
                                    <div class="empty-state">
                                        <i class="bi bi-heart fs-1 text-muted mb-3"></i>
                                        <h5 class="text-muted mb-2">Your wishlist is empty</h5>
                                        <p class="text-muted mb-0">Save your favorite products here!</p>
                                    </div>
                                </div>
                            `);
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
                let quantityInput = $(this).closest('.product-body').find('.quantity-input');
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

            $('.product-card').on('mouseenter', function() {
                $(this).addClass('card-hover-effect');
            }).on('mouseleave', function() {
                $(this).removeClass('card-hover-effect');
            });
        });
    </script>
</body>

</html>