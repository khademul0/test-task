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
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin/style.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .navbar {
            background: linear-gradient(90deg, #007bff, #0056b3);
        }

        .navbar-nav .nav-link {
            color: #fff !important;
            font-weight: 500;
        }

        .navbar-nav .nav-link:hover {
            color: #f8f9fa !important;
        }

        .navbar-brand img {
            height: 40px;
        }

        .search-bar {
            max-width: 300px;
        }

        .contact-section {
            background: #fff;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .contact-form .form-label {
            font-weight: 500;
        }

        .contact-form .form-control {
            border-radius: 5px;
        }

        .contact-form .invalid-feedback {
            display: none;
            color: #dc3545;
        }

        .contact-form .is-invalid~.invalid-feedback {
            display: block;
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
    </style>
</head>

<body>
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
                        <a class="nav-link" href="portfolio.php"><i class="fas fa-shopping-bag me-1"></i> Shop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="contact.php"><i class="fas fa-envelope me-1"></i> Contact</a>
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

    <!-- Contact Section -->
    <section class="py-5">
        <div class="container">
            <div class="contact-section">
                <h2 class="text-primary mb-4"><i class="fas fa-envelope me-2"></i> Contact Us</h2>
                <p class="text-muted mb-4">Have questions or need assistance? Fill out the form below, and we'll get back to you soon!</p>
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
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> Send Message</button>
                </form>
            </div>
        </div>
    </section>

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
        <div class="loader-overlay"><span class="loader"></span></div>
    </footer>
    <!-- Footer End -->

    <!-- Scripts -->
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/sweetalert2.all.min.js"></script>
    <script>
        $(document).ready(function() {
            const isLoggedIn = <?= json_encode($is_logged_in) ?>;
            const userId = <?= json_encode($user_id) ?>;

            // Contact form submission
            $('#contactForm').on('submit', function(e) {
                e.preventDefault();
                let form = $(this);
                let isValid = true;

                // Validate form fields
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