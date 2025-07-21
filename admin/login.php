<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0" />
    <title>Admin Login Panel</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../assets/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/css/admin/login.css" />

    <style>
        .logo-container {
            margin-top: 50px;
            margin-bottom: 30px;
        }

        .logo-img {
            width: 130px;
            height: 70px;
            object-fit: cover;
            border-radius: 5%;
            border: 2px solid #007bff;
        }

        .logo-container h1 {
            font-size: 1.4rem;
            color: rgb(255, 255, 255);
            margin-top: 10px;
            margin-bottom: -120px;
        }
    </style>
</head>

<body>


    <!---Admin login form start--->
    <div class="container">
        <div class="row justify-content-center min-vh-100 align-items-center" id="login-form-box">
            <div class="col-lg-10">
                <div class="card-group shadow">
                    <div class="card p-4">
                        <h2 class="text-center text-primary font-weight-bold">Login to your account</h2>
                        <hr class="my-3" />
                        <form action="action.php" method="post" class="px-3 py-4" id="login-form">
                            <input type="hidden" name="action" value="login_user" />

                            <!-- Message container -->
                            <div id="login-msg" class="alert d-none"></div>

                            <!-- Email Field -->
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i data-feather="mail"></i></span>
                                </div>
                                <input type="email" name="email" class="form-control" placeholder="Enter your email" required
                                    value="<?= isset($_COOKIE['remember_email']) ? htmlspecialchars($_COOKIE['remember_email']) : '' ?>" />
                            </div>

                            <!-- Password Field -->
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i data-feather="key"></i></span>
                                </div>
                                <input type="password" name="password" class="form-control" placeholder="Enter your password" required
                                    value="<?= isset($_COOKIE['remember_pass']) ? htmlspecialchars($_COOKIE['remember_pass']) : '' ?>" />
                            </div>


                            <!-- Checkbox and Forgot Password -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="rememberMe" name="remember" />
                                    <label class="custom-control-label" for="rememberMe">Remember me</label>
                                </div>
                                <div>
                                    <a href="javascript:void(0);" class="text-primary btn-forgot-password">Forgot password?</a>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg w-100">Login</button>
                            </div>
                        </form>
                    </div>

                    <div class="card p-4 justify-content-center" style="background: #363c43;">
                        <h2 class="text-center text-white font-weight-bold">Welcome Back!</h2>
                        <hr class="my-3 bg-light" />
                        <p class="text-center text-light lead">
                            Please log in using your email address and password to access your account.
                        </p>
                        <button type="submit" class="btn btn-light" id="showsignup">Signup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!---Admin login form end--->

    <!---Admin register form start--->
    <div class="row justify-content-center min-vh-100 align-items-center" id="register-form-box" style="display:none">
        <div class="col-lg-10">
            <div class="card-group shadow">
                <div class="card p-4">
                    <h2 class="text-center text-primary font-weight-bold">Create new account</h2>
                    <hr class="my-3" />
                    <form action="#" method="post" class="px-3 py-4" id="register-form" novalidate>
                        <!-- Message container -->
                        <div id="register-msg" class="alert d-none"></div>

                        <div class="input-group mb-3 has-validation">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i data-feather="mail"></i></span>
                            </div>
                            <input type="email" id="reg_email" class="form-control" placeholder="Enter your email" required />
                            <div class="invalid-feedback">Please enter a valid email.</div>
                        </div>

                        <div class="input-group mb-3 has-validation">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i data-feather="user"></i></span>
                            </div>
                            <input type="text" id="reg_name" class="form-control" placeholder="Enter your name" required />
                            <div class="invalid-feedback">Name must be at least 2 characters.</div>
                        </div>

                        <div class="input-group mb-3 has-validation">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i data-feather="key"></i></span>
                            </div>
                            <input type="password" id="reg_password" class="form-control" placeholder="Enter your password" required />
                            <div class="invalid-feedback">Password must be 6+ characters with special char.</div>
                        </div>

                        <div class="input-group mb-3 has-validation">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i data-feather="key"></i></span>
                            </div>
                            <input type="password" id="reg_cpassword" class="form-control" placeholder="Re-write your password" required />
                            <div class="invalid-feedback">Passwords do not match.</div>
                        </div>

                        <div class="text-center">
                            <button id="registeruser" type="submit" class="btn btn-primary btn-lg w-100">
                                <span
                                    class="spinner-border spinner-border-sm me-2 d-none"
                                    id="register-loading"
                                    role="status"
                                    aria-hidden="true"></span>
                                <span id="register-text">Register</span>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card p-4 justify-content-center" style="background: #363c43;">
                    <h2 class="text-center text-white font-weight-bold">Welcome!!</h2>
                    <hr class="my-3 bg-light" />
                    <p class="text-center text-light lead">
                        Let’s get you started — just fill in your details to join us. Already have an account?
                    </p>
                    <button type="button" class="btn btn-light btn-show-login">Sign-in</button>
                </div>
            </div>
        </div>
    </div>
    <!---Admin register form end--->

    <!-- Admin forgot password form -->
    <div class="row justify-content-center min-vh-100 align-items-center" id="forgotten-form-box" style="display:none">
        <div class="col-lg-10">
            <div class="card-group shadow">
                <div class="card p-4">
                    <h2 class="text-center text-primary font-weight-bold">Forgot Password?</h2>
                    <hr class="my-3" />
                    <form action="#" method="post" class="px-3 py-4" id="forgotten-form">
                        <input type="hidden" name="action" value="reset_password" />

                        <!-- Message container -->
                        <div id="forgot-msg" class="alert d-none"></div>

                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i data-feather="mail"></i></span>
                            </div>
                            <input type="email" name="email" class="form-control" placeholder="Enter your email" required />
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg w-100">Reset password</button>
                        </div>
                    </form>
                </div>

                <div class="card p-4 justify-content-center" style="background: #363c43;">
                    <h2 class="text-center text-white font-weight-bold">Lost password?</h2>
                    <hr class="my-3 bg-light" />
                    <p class="text-center text-light lead">No worries. Let's get a new one quickly!</p>
                    <button type="button" class="btn btn-light" id="signup-from-forgot">Signup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../assets/js/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/popper.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>

    <!-- Feather icons -->
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>

    <script src="../assets/js/admin/login.js"></script>

    <script>
        // Replace feather icons once DOM is ready
        $(document).ready(function() {
            feather.replace();
        });
    </script>
</body>

</html>