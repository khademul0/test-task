<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0" />
    <title>Netacart - Login</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../assets/fontawesome/css/fontawesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            /* Professional glassy color palette */
            --primary-color: #164e63;
            --primary-dark: #0f3a47;
            --secondary-color: #ec4899;
            --accent-color: #f59e0b;
            --success-color: #10b981;
            --danger-color: #dc2626;
            --warning-color: #f59e0b;
            --light-bg: rgba(255, 255, 255, 0.8);
            --dark-bg: rgba(15, 23, 42, 0.9);
            --card-bg: rgba(255, 255, 255, 0.2);
            --glass-bg: rgba(255, 255, 255, 0.15);
            --glass-border: rgba(255, 255, 255, 0.25);
            --text-primary: #164e63;
            --text-secondary: #475569;
            --border-color: rgba(0, 0, 0, 0.1);
            --shadow: 0 25px 50px rgba(22, 78, 99, 0.15);
            --shadow-lg: 0 35px 70px rgba(22, 78, 99, 0.2);
            --glow: 0 0 30px rgba(22, 78, 99, 0.3);
            --glass-gradient: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            /* Professional glassy background with subtle gradient */
            font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #ecfeff 0%, #cffafe 25%, #a5f3fc 50%, #67e8f9 75%, #22d3ee 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        .auth-container {
            /* Reduced container max-width for smaller, cuter form */
            width: 100%;
            max-width: 420px;
            margin: 0 auto;
            padding: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .auth-card {
            /* Made border darker and more visible */
            backdrop-filter: blur(25px);
            background: rgba(255, 255, 255, 0.25);
            border: 2px solid rgba(22, 78, 99, 0.4);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(22, 78, 99, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.4);
            overflow: hidden;
            position: relative;
            min-height: 420px;
        }

        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
        }

        .auth-grid {
            /* Changed to single column layout to hide welcome section */
            display: grid;
            grid-template-columns: 1fr;
            min-height: 420px;
        }

        .auth-form-section {
            /* Centered the form section and added max-width for better proportions */
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            max-width: 100%;
            margin: 0 auto;
        }

        .logo-container {
            /* Reduced margin for more compact layout */
            margin-bottom: 1rem;
        }

        .logo-img {
            /* Enhanced logo with darker shadow and 3D glowing effect */
            width: 140px;
            height: auto;
            filter:
                drop-shadow(0 8px 16px rgba(0, 0, 0, 0.4)) drop-shadow(0 4px 8px rgba(22, 78, 99, 0.6)) drop-shadow(0 0 20px rgba(22, 78, 99, 0.8));
            transition: all 0.3s ease;
            transform-style: preserve-3d;
            position: relative;
        }

        .logo-img:hover {
            transform: scale(1.05) translateZ(10px);
            filter:
                drop-shadow(0 12px 24px rgba(0, 0, 0, 0.5)) drop-shadow(0 6px 12px rgba(22, 78, 99, 0.7)) drop-shadow(0 0 30px rgba(22, 78, 99, 1)) drop-shadow(0 0 40px rgba(67, 233, 249, 0.6));
        }

        .auth-title {
            /* Reduced font size for compact design */
            font-family: 'Work Sans', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
            text-shadow: 0 2px 4px rgba(22, 78, 99, 0.08);
        }

        .auth-subtitle {
            color: var(--text-secondary);
            font-size: 0.875rem;
            margin-bottom: 1.25rem;
            opacity: 0.8;
        }

        .form-group {
            position: relative;
            /* Reduced margin for more compact form */
            margin-bottom: 1rem;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            width: 20px;
            height: 20px;
            z-index: 2;
            opacity: 0.7;
            transition: all 0.3s ease;
        }

        .form-control {
            /* Enhanced with lighter glassy appearance and darker form input borders */
            width: 100%;
            padding: 0.75rem 0.75rem 0.75rem 2.5rem;
            border: 1.5px solid rgba(22, 78, 99, 0.3);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(15px);
            color: var(--text-primary);
            font-size: 0.9rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05), 0 0 0 1px rgba(22, 78, 99, 0.1);
        }

        .form-control:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.4);
            border-color: rgba(22, 78, 99, 0.6);
            box-shadow: 0 0 15px rgba(22, 78, 99, 0.2), inset 0 1px 2px rgba(0, 0, 0, 0.05), 0 0 0 2px rgba(22, 78, 99, 0.15);
            transform: translateY(-1px);
        }

        .form-control:focus+.input-icon {
            color: var(--primary-color);
            opacity: 1;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            /* Reduced margin for compact layout */
            margin-bottom: 1.25rem;
            font-size: 0.8rem;
        }

        .custom-checkbox {
            display: flex;
            align-items: center;
            cursor: pointer;
            color: var(--text-secondary);
        }

        .custom-checkbox input {
            margin-right: 0.5rem;
            accent-color: var(--primary-color);
        }

        .forgot-link {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .forgot-link:hover {
            color: var(--primary-color);
            text-shadow: 0 0 8px rgba(22, 78, 99, 0.3);
        }

        .btn-primary {
            /* Reduced padding and enhanced light glassy styling */
            width: 100%;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, rgba(22, 78, 99, 0.9) 0%, rgba(15, 58, 71, 0.95) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-family: 'Work Sans', sans-serif;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 6px 20px rgba(22, 78, 99, 0.25);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(22, 78, 99, 0.4);
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-secondary {
            /* Enhanced secondary button for welcome section */
            padding: 0.75rem 2rem;
            background: transparent;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            font-family: 'Work Sans', sans-serif;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-1px);
        }

        .auth-welcome-section {
            /* Hide welcome section completely */
            display: none !important;
        }

        .create-account-toggle {
            text-align: center;
            /* Reduced margins for compact layout and Made top border darker and more visible */
            margin-top: 1.25rem;
            padding-top: 1.25rem;
            border-top: 1px solid rgba(22, 78, 99, 0.25);
        }

        .toggle-text {
            color: var(--text-secondary);
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }

        .btn-toggle {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.1) 0%, rgba(22, 78, 99, 0.1) 100%);
            /* Made toggle button border darker and more prominent */
            border: 2px solid rgba(22, 78, 99, 0.4);
            color: var(--secondary-color);
            /* Reduced padding for smaller, cuter toggle button */
            padding: 0.625rem 1.5rem;
            border-radius: 20px;
            font-family: 'Work Sans', sans-serif;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .btn-toggle::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(236, 72, 153, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-toggle:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(236, 72, 153, 0.3);
            /* Enhanced hover state with darker border */
            border-color: rgba(22, 78, 99, 0.6);
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.2) 0%, rgba(22, 78, 99, 0.2) 100%);
        }

        .btn-toggle:hover::before {
            left: 100%;
        }

        .back-to-login {
            text-align: center;
            margin-top: 1.5rem;
        }

        .back-link {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-link:hover {
            color: var(--primary-color);
            transform: translateX(-3px);
        }

        .alert {
            /* Enhanced alert styling */
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            backdrop-filter: blur(10px);
            border: 1px solid transparent;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border-color: rgba(16, 185, 129, 0.2);
        }

        .alert-danger {
            background: rgba(220, 38, 38, 0.1);
            color: #dc2626;
            border-color: rgba(220, 38, 38, 0.2);
        }

        .form-transition {
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .spinner-border {
            width: 1rem;
            height: 1rem;
            margin-right: 0.5rem;
        }

        /* Enhanced animations */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            33% {
                transform: translateY(-10px) rotate(1deg);
            }

            66% {
                transform: translateY(5px) rotate(-1deg);
            }
        }

        /* Updated responsive breakpoints for smaller form */
        @media (max-width: 768px) {
            .auth-container {
                max-width: 100%;
                padding: 1rem;
            }

            .auth-form-section {
                padding: 1.5rem 1rem;
            }

            .logo-img {
                width: 120px;
            }

            .auth-title {
                font-size: 1.25rem;
            }
        }

        .d-none {
            display: none !important;
        }

        .text-center {
            text-align: center;
        }

        .is-invalid {
            border-color: var(--danger-color) !important;
            box-shadow: 0 0 15px rgba(220, 38, 38, 0.2) !important;
        }

        .invalid-feedback {
            color: var(--danger-color);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
</head>

<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-grid">
                <!-- Login Form -->
                <div id="login-form-box" class="form-transition">
                    <div class="auth-form-section">
                        <div class="logo-container text-center">
                            <img src="../assets/images/logo.png" alt="Netacart Logo" class="logo-img">
                        </div>

                        <h2 class="auth-title text-center">Welcome Back</h2>
                        <p class="auth-subtitle text-center">Sign in to your account to continue</p>

                        <form action="action.php" method="post" id="login-form">
                            <input type="hidden" name="action" value="login_user" />

                            <div id="login-msg" class="alert d-none"></div>

                            <div class="form-group">
                                <i data-feather="mail" class="input-icon"></i>
                                <input type="email" name="email" class="form-control" placeholder="Enter your email" required
                                    value="<?= isset($_COOKIE['remember_email']) ? htmlspecialchars($_COOKIE['remember_email']) : '' ?>" />
                            </div>

                            <div class="form-group">
                                <i data-feather="lock" class="input-icon"></i>
                                <input type="password" name="password" class="form-control" placeholder="Enter your password" required
                                    value="<?= isset($_COOKIE['remember_pass']) ? htmlspecialchars($_COOKIE['remember_pass']) : '' ?>" />
                            </div>

                            <div class="form-options">
                                <label class="custom-checkbox">
                                    <input type="checkbox" id="rememberMe" name="remember" />
                                    <span>Remember me</span>
                                </label>
                                <a href="javascript:void(0);" class="forgot-link btn-forgot-password">Forgot password?</a>
                            </div>

                            <button type="submit" class="btn-primary">
                                <span class="spinner-border d-none" id="login-loading"></span>
                                <span id="login-text">Sign In</span>
                            </button>
                        </form>

                        <!-- Added creative account creation toggle -->
                        <div class="create-account-toggle">
                            <p class="toggle-text">Don't have an account?</p>
                            <button type="button" class="btn-toggle" id="showsignup">
                                <i data-feather="user-plus" style="width: 16px; height: 16px; margin-right: 8px;"></i>
                                Create New Account
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Register Form -->
                <div id="register-form-box" class="form-transition d-none">
                    <div class="auth-form-section">
                        <div class="logo-container text-center">
                            <img src="../assets/images/logo.png" alt="Netacart Logo" class="logo-img">
                        </div>

                        <h2 class="auth-title text-center">Create Account</h2>
                        <p class="auth-subtitle text-center">Join us today and get started</p>

                        <form action="#" method="post" id="register-form" novalidate>
                            <div id="register-msg" class="alert d-none"></div>

                            <div class="form-group">
                                <i data-feather="mail" class="input-icon"></i>
                                <input type="email" id="reg_email" class="form-control" placeholder="Enter your email" required />
                                <div class="invalid-feedback">Please enter a valid email.</div>
                            </div>

                            <div class="form-group">
                                <i data-feather="user" class="input-icon"></i>
                                <input type="text" id="reg_name" class="form-control" placeholder="Enter your full name" required />
                                <div class="invalid-feedback">Name must be at least 2 characters.</div>
                            </div>

                            <div class="form-group">
                                <i data-feather="lock" class="input-icon"></i>
                                <input type="password" id="reg_password" class="form-control" placeholder="Create password" required />
                                <div class="invalid-feedback">Password must be 6+ characters with special character.</div>
                            </div>

                            <div class="form-group">
                                <i data-feather="lock" class="input-icon"></i>
                                <input type="password" id="reg_cpassword" class="form-control" placeholder="Confirm password" required />
                                <div class="invalid-feedback">Passwords do not match.</div>
                            </div>

                            <button id="registeruser" type="submit" class="btn-primary">
                                <span class="spinner-border d-none" id="register-loading"></span>
                                <span id="register-text">Create Account</span>
                            </button>
                        </form>

                        <!-- Added back to login link -->
                        <div class="back-to-login">
                            <a href="javascript:void(0);" class="back-link btn-show-login">
                                <i data-feather="arrow-left" style="width: 16px; height: 16px;"></i>
                                Back to Login
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Forgot Password Form -->
                <div id="forgotten-form-box" class="form-transition d-none">
                    <div class="auth-form-section">
                        <div class="logo-container text-center">
                            <img src="../assets/images/logo.png" alt="Netacart Logo" class="logo-img">
                        </div>

                        <h2 class="auth-title text-center">Reset Password</h2>
                        <p class="auth-subtitle text-center">Enter your email to receive reset instructions</p>

                        <form action="#" method="post" id="forgotten-form">
                            <input type="hidden" name="action" value="reset_password" />

                            <div id="forgot-msg" class="alert d-none"></div>

                            <div class="form-group">
                                <i data-feather="mail" class="input-icon"></i>
                                <input type="email" name="email" class="form-control" placeholder="Enter your email" required />
                            </div>

                            <button type="submit" class="btn-primary">
                                <span class="spinner-border d-none" id="forgot-loading"></span>
                                <span id="forgot-text">Send Reset Link</span>
                            </button>
                        </form>

                        <!-- Added back to login link -->
                        <div class="back-to-login">
                            <a href="javascript:void(0);" class="back-link btn-show-login">
                                <i data-feather="arrow-left" style="width: 16px; height: 16px;"></i>
                                Back to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../assets/js/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/popper.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>

    <script>
        $(document).ready(function() {
            feather.replace();

            function showForm(formToShow) {
                $('.form-transition').addClass('d-none');
                $(formToShow).removeClass('d-none');
                // Re-initialize feather icons for new form
                setTimeout(() => feather.replace(), 100);
            }

            $('#showsignup').click(() => showForm('#register-form-box'));
            $('.btn-show-login').click(() => showForm('#login-form-box'));
            $('.btn-forgot-password').click(() => showForm('#forgotten-form-box'));

            // LOGIN FORM SUBMIT
            $('#login-form').submit(function(e) {
                e.preventDefault();
                var form = $(this);
                var btn = form.find('button[type="submit"]');
                var msgDiv = $('#login-msg');
                var loading = $('#login-loading');
                var btnText = $('#login-text');

                btn.prop('disabled', true);
                loading.removeClass('d-none');
                btnText.text('Signing in...');
                msgDiv.removeClass('alert-success alert-danger').addClass('d-none').text('');

                $.ajax({
                    url: 'action.php',
                    type: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            msgDiv
                                .removeClass('d-none alert-danger')
                                .addClass('alert alert-success')
                                .text(res.message);
                            setTimeout(function() {
                                window.location.href = res.redirect || 'dashboard.php';
                            }, 1500);
                        } else {
                            msgDiv
                                .removeClass('d-none alert-success')
                                .addClass('alert alert-danger')
                                .text(res.message);
                            btn.prop('disabled', false);
                            loading.addClass('d-none');
                            btnText.text('Sign In');
                        }
                    },
                    error: function() {
                        msgDiv
                            .removeClass('d-none alert-success')
                            .addClass('alert alert-danger')
                            .text('Something went wrong. Please try again.');
                        btn.prop('disabled', false);
                        loading.addClass('d-none');
                        btnText.text('Sign In');
                    },
                });
            });

            // REGISTER FORM SUBMIT
            $('#register-form').submit(function(e) {
                e.preventDefault();
                var email = $('#reg_email');
                var name = $('#reg_name');
                var password = $('#reg_password');
                var cpassword = $('#reg_cpassword');
                var msgDiv = $('#register-msg');
                var btn = $('#registeruser');
                var loading = $('#register-loading');
                var btnText = $('#register-text');

                // Basic client-side validation
                var emailVal = email.val().trim();
                var nameVal = name.val().trim();
                var passVal = password.val();
                var cpassVal = cpassword.val();

                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                var specialCharPattern = /[!@#$%^&*(),.?":{}|<>]/;

                // Reset validation states
                $('.form-control').removeClass('is-invalid');

                if (!emailPattern.test(emailVal)) {
                    email.addClass('is-invalid');
                    msgDiv
                        .removeClass('d-none alert-success')
                        .addClass('alert alert-danger')
                        .text('Please enter a valid email.');
                    return;
                }
                if (nameVal.length < 2) {
                    name.addClass('is-invalid');
                    msgDiv
                        .removeClass('d-none alert-success')
                        .addClass('alert alert-danger')
                        .text('Name must be at least 2 characters.');
                    return;
                }
                if (passVal.length < 6 || !specialCharPattern.test(passVal)) {
                    password.addClass('is-invalid');
                    msgDiv
                        .removeClass('d-none alert-success')
                        .addClass('alert alert-danger')
                        .text('Password must be 6+ characters with special character.');
                    return;
                }
                if (passVal !== cpassVal) {
                    cpassword.addClass('is-invalid');
                    msgDiv
                        .removeClass('d-none alert-success')
                        .addClass('alert alert-danger')
                        .text('Passwords do not match.');
                    return;
                }

                msgDiv.addClass('d-none').text('');
                btn.prop('disabled', true);
                loading.removeClass('d-none');
                btnText.text('Creating Account...');

                $.ajax({
                    url: 'action.php',
                    type: 'POST',
                    data: {
                        action: 'register_user',
                        email: emailVal,
                        name: nameVal,
                        password: passVal,
                        cpassword: cpassVal,
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            msgDiv
                                .removeClass('d-none alert-danger')
                                .addClass('alert alert-success')
                                .text(res.message);
                            $('#register-form')[0].reset();
                            $('.form-control').removeClass('is-invalid');

                            setTimeout(function() {
                                showForm('#login-form-box');
                                $('#login-msg')
                                    .removeClass('d-none alert-danger')
                                    .addClass('alert alert-success')
                                    .text('Account created successfully! Please sign in.');
                            }, 1500);
                        } else {
                            msgDiv
                                .removeClass('d-none alert-success')
                                .addClass('alert alert-danger')
                                .text(res.message);
                        }
                        btn.prop('disabled', false);
                        loading.addClass('d-none');
                        btnText.text('Create Account');
                    },
                    error: function() {
                        msgDiv
                            .removeClass('d-none alert-success')
                            .addClass('alert alert-danger')
                            .text('Something went wrong. Please try again.');
                        btn.prop('disabled', false);
                        loading.addClass('d-none');
                        btnText.text('Create Account');
                    },
                });
            });

            // FORGOT PASSWORD FORM SUBMIT
            $('#forgotten-form').submit(function(e) {
                e.preventDefault();
                var form = $(this);
                var btn = form.find('button[type="submit"]');
                var msgDiv = $('#forgot-msg');
                var loading = $('#forgot-loading');
                var btnText = $('#forgot-text');
                var email = form.find('input[name="email"]').val().trim();

                if (email === '') {
                    msgDiv
                        .removeClass('d-none alert-success')
                        .addClass('alert alert-danger')
                        .text('Please enter your email.');
                    return;
                }

                btn.prop('disabled', true);
                loading.removeClass('d-none');
                btnText.text('Sending...');

                $.ajax({
                    url: 'action.php',
                    type: 'POST',
                    data: {
                        action: 'reset_password',
                        email: email,
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            msgDiv
                                .removeClass('d-none alert-danger')
                                .addClass('alert alert-success')
                                .text(res.message);
                            form[0].reset();
                        } else {
                            msgDiv
                                .removeClass('d-none alert-success')
                                .addClass('alert alert-danger')
                                .text(res.message);
                        }
                        btn.prop('disabled', false);
                        loading.addClass('d-none');
                        btnText.text('Send Reset Link');
                    },
                    error: function() {
                        msgDiv
                            .removeClass('d-none alert-success')
                            .addClass('alert alert-danger')
                            .text('Something went wrong. Please try again.');
                        btn.prop('disabled', false);
                        loading.addClass('d-none');
                        btnText.text('Send Reset Link');
                    },
                });
            });
        });
    </script>
</body>

</html>