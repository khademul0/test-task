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
    <title>Netacart - Contact Us</title>
    <link rel="shortcut icon" href="assets/images/logo.png" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;900&family=Open+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="app/globals.css">
    <style>
        :root {
            --font-heading: 'Montserrat', sans-serif;
            --font-body: 'Open Sans', sans-serif;
            --shadow-soft: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 8px 30px rgba(0, 0, 0, 0.12);
            --gradient-primary: linear-gradient(135deg, #84cc16 0%, #65a30d 100%);
            --gradient-card: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
            --color-primary: #84cc16;
            --color-foreground: #374151;
            --color-muted-foreground: #6b7280;
            --color-background: #ffffff;
            --color-border: #e2e8f0;
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

        .navbar-modern {
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

        .navbar-nav .nav-link {
            font-weight: 500;
            color: #374151 !important;
            transition: all 0.3s ease;
            position: relative;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: #84cc16 !important;
        }

        .navbar-nav .nav-link.active::after {
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

        .navbar-brand img {
            height: 40px;
        }

        .search-container {
            position: relative;
            max-width: 300px;
        }

        .search-input {
            border: 2px solid rgba(226, 232, 240, 0.6);
            border-radius: 50px;
            padding: 12px 50px 12px 20px;
            background: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .search-input:focus {
            border-color: #84cc16;
            box-shadow: 0 0 0 3px rgba(132, 204, 22, 0.1);
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

        .action-btn {
            background: rgba(255, 255, 255, 0.8);
            border: 2px solid rgba(226, 232, 240, 0.6);
            border-radius: 12px;
            padding: 10px 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            color: #374151;
        }

        .action-btn:hover {
            background: #84cc16;
            border-color: #84cc16;
            color: white;
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

        .contact-section {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(248, 250, 252, 0.9) 100%);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(226, 232, 240, 0.3);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: var(--shadow-soft);
            margin: 2rem 0;
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

        .contact-form .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .contact-form .form-control {
            border: 2px solid rgba(226, 232, 240, 0.6);
            border-radius: 12px;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .contact-form .form-control:focus {
            border-color: #84cc16;
            box-shadow: 0 0 0 3px rgba(132, 204, 22, 0.1);
            outline: none;
            background: rgba(255, 255, 255, 0.95);
        }

        .contact-form .invalid-feedback {
            display: none;
            color: #dc3545;
        }

        .contact-form .is-invalid~.invalid-feedback {
            display: block;
        }

        .btn-primary {
            background: var(--gradient-primary);
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #65a30d 0%, #4d7c0f 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(132, 204, 22, 0.3);
        }

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
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--color-muted-foreground);
            text-decoration: none;
            transition: all 0.3s ease;
            margin-right: 1rem;
            font-size: 1.2rem;
        }

        .social-icon:hover {
            background: var(--color-primary);
            border-color: var(--color-primary);
            color: white;
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        @media (max-width: 768px) {
            .contact-section {
                padding: 2rem;
                margin: 1rem 0;
            }

            .section-title {
                font-size: 2rem;
            }

            .search-container {
                max-width: 100%;
                margin-bottom: 1rem;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-modern sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="assets/images/logo.png" alt="Netacart Logo" class="me-2"> Netacart
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
                aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="bi bi-house me-1"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="portfolio.php"><i class="bi bi-shop me-1"></i> Shop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="contact.php"><i class="bi bi-envelope me-1"></i> Contact</a>
                    </li>
                </ul>

                <div class="search-container me-3">
                    <input class="form-control search-input" type="search" placeholder="Search products..." aria-label="Search">
                    <button class="search-btn" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>

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

    <section class="py-5">
        <div class="container">
            <div class="contact-section">
                <div class="text-center mb-4">
                    <h2 class="section-title"><i class="bi bi-envelope me-2"></i> Contact Us</h2>
                    <p class="text-muted mb-4">Have questions or need assistance? Fill out the form below, and we'll get back to you soon!</p>
                </div>
                <form id="contactForm" class="contact-form">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback">Please enter your full name.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                        <div class="invalid-feedback">Please enter a subject.</div>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        <div class="invalid-feedback">Please enter your message.</div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i> Send Message</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

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

    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="assets/js/sweetalert2.all.min.js"></script>
    <script>
        $(document).ready(function() {
            const isLoggedIn = <?= json_encode($is_logged_in) ?>;
            const userId = <?= json_encode($user_id) ?>;

            $('#contactForm').on('submit', function(e) {
                e.preventDefault();
                let form = $(this);
                let isValid = true;

                form.find('input[required], textarea[required]').each(function() {
                    let input = $(this);
                    if (!input.val().trim()) {
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

                let formData = {
                    action: 'submit_contact',
                    name: $('#name').val().trim(),
                    email: $('#email').val().trim(),
                    subject: $('#subject').val().trim(),
                    message: $('#message').val().trim(),
                    user_id: userId
                };

                $.ajax({
                    url: 'admin/inc/action_contact.php',
                    type: 'POST',
                    dataType: 'json',
                    data: formData,
                    beforeSend: function() {
                        console.log('Submitting contact form:', formData);
                    },
                    success: function(response) {
                        console.log('Contact form response:', response);
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Message Sent!',
                                text: 'Your message has been submitted. We will get back to you soon.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                form[0].reset();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Submission Failed',
                                text: response.message || 'Failed to send message. Please try again.'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Contact form error:', xhr.responseText, status, error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: 'Unable to send message. Check console for details.'
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>