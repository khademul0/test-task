$(document).ready(function () {
    // Show register form
    $('#showsignup').click(function () {
        $('#login-form-box').hide();
        $('#register-form-box').show();
        $('#forgotten-form-box').hide();
        feather.replace();
    });

    // Show login form from register
    $('.btn-show-login').click(function () {
        $('#login-form-box').show();
        $('#register-form-box').hide();
        $('#forgotten-form-box').hide();
        feather.replace();
    });

    // Show forgot password form
    $('.btn-forgot-password').click(function () {
        $('#login-form-box').hide();
        $('#register-form-box').hide();
        $('#forgotten-form-box').show();
        feather.replace();
    });

    // Show signup from forgot password form
    $('#signup-from-forgot').click(function () {
        $('#login-form-box').hide();
        $('#register-form-box').show();
        $('#forgotten-form-box').hide();
        feather.replace();
    });

    // LOGIN FORM SUBMIT
    $('#login-form').submit(function (e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        var msgDiv = $('#login-msg');

        btn.prop('disabled', true).text('Logging in...');
        msgDiv.removeClass('alert-success alert-danger').addClass('d-none').text('');

        $.ajax({
            url: 'action.php',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    msgDiv
                        .removeClass('d-none alert-danger')
                        .addClass('alert alert-success')
                        .text(res.message);
                    setTimeout(function () {
                        window.location.href = 'index.php'; // change this if needed
                    }, 1500);
                } else {
                    msgDiv
                        .removeClass('d-none alert-success')
                        .addClass('alert alert-danger')
                        .text(res.message);
                    btn.prop('disabled', false).text('Login');
                }
            },
            error: function () {
                msgDiv
                    .removeClass('d-none alert-success')
                    .addClass('alert alert-danger')
                    .text('Something went wrong. Please try again.');
                btn.prop('disabled', false).text('Login');
            },
        });
    });

    // REGISTER FORM SUBMIT
    $('#register-form').submit(function (e) {
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

        if (!emailPattern.test(emailVal)) {
            email.addClass('is-invalid');
            msgDiv
                .removeClass('d-none alert-success')
                .addClass('alert alert-danger')
                .text('Please enter a valid email.');
            return;
        } else {
            email.removeClass('is-invalid');
        }
        if (nameVal.length < 2) {
            name.addClass('is-invalid');
            msgDiv
                .removeClass('d-none alert-success')
                .addClass('alert alert-danger')
                .text('Name must be at least 2 characters.');
            return;
        } else {
            name.removeClass('is-invalid');
        }
        if (passVal.length < 6 || !specialCharPattern.test(passVal)) {
            password.addClass('is-invalid');
            msgDiv
                .removeClass('d-none alert-success')
                .addClass('alert alert-danger')
                .text('Password must be 6+ characters with special character.');
            return;
        } else {
            password.removeClass('is-invalid');
        }
        if (passVal !== cpassVal) {
            cpassword.addClass('is-invalid');
            msgDiv
                .removeClass('d-none alert-success')
                .addClass('alert alert-danger')
                .text('Passwords do not match.');
            return;
        } else {
            cpassword.removeClass('is-invalid');
        }

        msgDiv.addClass('d-none').text('');
        btn.prop('disabled', true);
        loading.removeClass('d-none');
        btnText.text('Registering...');

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
            success: function (res) {
                if (res.status === 'success') {
                    msgDiv
                        .removeClass('d-none alert-danger')
                        .addClass('alert alert-success')
                        .text(res.message);
                    // Reset form
                    $('#register-form')[0].reset();
                } else {
                    msgDiv
                        .removeClass('d-none alert-success')
                        .addClass('alert alert-danger')
                        .text(res.message);
                }
                btn.prop('disabled', false);
                loading.addClass('d-none');
                btnText.text('Register');
            },
            error: function () {
                msgDiv
                    .removeClass('d-none alert-success')
                    .addClass('alert alert-danger')
                    .text('Something went wrong. Please try again.');
                btn.prop('disabled', false);
                loading.addClass('d-none');
                btnText.text('Register');
            },
        });
    });

    // FORGOT PASSWORD FORM SUBMIT
    $('#forgotten-form').submit(function (e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        var msgDiv = $('#forgot-msg');
        var email = form.find('input[name="email"]').val().trim();

        if (email === '') {
            msgDiv
                .removeClass('d-none alert-success')
                .addClass('alert alert-danger')
                .text('Please enter your email.');
            return;
        }

        btn.prop('disabled', true).text('Sending...');

        $.ajax({
            url: 'action.php',
            type: 'POST',
            data: {
                action: 'reset_password',
                email: email,
            },
            dataType: 'json',
            success: function (res) {
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
                btn.prop('disabled', false).text('Reset password');
            },
            error: function () {
                msgDiv
                    .removeClass('d-none alert-success')
                    .addClass('alert alert-danger')
                    .text('Something went wrong. Please try again.');
                btn.prop('disabled', false).text('Reset password');
            },
        });
    });
});
