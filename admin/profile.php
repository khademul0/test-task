<?php
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/Auth.php';

App\Auth::check();

$user_id = intval($_SESSION['user_id']);
$stmt = $conn->prepare("SELECT name, email, photo, profile_image, 2fa_enabled, theme_preference, email_notifications FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header('Location: login.php');
    exit;
}
?>

<style>
    /* Replaced entire styling with dashboard-matching glassy design system */
    :root {
        --primary-color: #164e63;
        --primary-dark: #0f3a47;
        --secondary-color: #ec4899;
        --accent-color: #f59e0b;
        --success-color: #10b981;
        --danger-color: #dc2626;
        --warning-color: #f59e0b;
        --light-bg: rgba(255, 255, 255, 0.8);
        --card-bg: rgba(255, 255, 255, 0.25);
        --glass-bg: rgba(255, 255, 255, 0.15);
        --text-primary: #164e63;
        --text-secondary: #475569;
        --shadow: 0 25px 50px rgba(22, 78, 99, 0.15);
        --shadow-lg: 0 35px 70px rgba(22, 78, 99, 0.2);
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
        background: linear-gradient(135deg, #f0f9ff 0%, #b8e1fcff 50%, #80b4e9ff 100%);
        min-height: 100vh;
    }

    .py-5 {
        padding: 3rem 0;
    }

    .container {
        max-width: 900px;
    }

    .page-title {
        color: var(--text-primary);
        font-weight: 600;
        margin-bottom: 2rem;
        font-size: 1.75rem;
    }

    .card-profile {
        backdrop-filter: blur(25px);
        background: var(--card-bg);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 20px;
        box-shadow: var(--shadow);
        overflow: hidden;
        position: relative;
    }

    .card-profile::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
    }

    .card-header {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        padding: 1.5rem 2rem;
        border: none;
        position: relative;
        overflow: hidden;
    }

    .card-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
    }

    .card-header h4 {
        margin: 0;
        font-weight: 600;
        position: relative;
        z-index: 1;
    }

    .card-body {
        padding: 0;
    }

    .profile-img {
        max-width: 150px;
        border: 3px solid var(--primary-color);
        border-radius: 50%;
        box-shadow: 0 8px 25px rgba(22, 78, 99, 0.2);
        transition: var(--transition);
    }

    .profile-img:hover {
        transform: scale(1.05);
        box-shadow: 0 12px 35px rgba(22, 78, 99, 0.3);
    }

    .form-section {
        padding: 2rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .form-section:last-child {
        border-bottom: none;
    }

    .form-section h5 {
        color: var(--text-primary);
        font-weight: 600;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid rgba(22, 78, 99, 0.1);
        font-size: 1.1rem;
    }

    .form-label {
        color: var(--text-primary);
        font-weight: 500;
        margin-bottom: 0.5rem;
        display: block;
    }

    .form-control,
    .form-select {
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 12px;
        padding: 0.75rem 1rem;
        color: var(--text-primary);
        transition: var(--transition);
        font-size: 0.95rem;
    }

    .form-control:focus,
    .form-select:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(22, 78, 99, 0.1);
        background: rgba(255, 255, 255, 0.4);
    }

    .form-control::placeholder {
        color: var(--text-secondary);
        opacity: 0.7;
    }

    .form-check {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 12px;
        transition: var(--transition);
    }

    .form-check:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .form-check-input {
        width: 18px;
        height: 18px;
        border: 2px solid var(--primary-color);
        border-radius: 4px;
        background: transparent;
        transition: var(--transition);
    }

    .form-check-input:checked {
        background: var(--primary-color);
        border-color: var(--primary-color);
    }

    .form-check-label {
        color: var(--text-primary);
        font-weight: 500;
        cursor: pointer;
        margin: 0;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 12px;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: var(--transition);
        cursor: pointer;
        backdrop-filter: blur(10px);
        font-size: 0.95rem;
        margin-right: 1rem;
        position: relative;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        box-shadow: 0 4px 15px rgba(22, 78, 99, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(22, 78, 99, 0.4);
        color: white;
        text-decoration: none;
    }

    .btn-secondary {
        background: rgba(255, 255, 255, 0.3);
        color: var(--text-secondary);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.4);
        transform: translateY(-1px);
        color: var(--text-primary);
        text-decoration: none;
    }

    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
        border-width: 0.15em;
    }

    .text-danger {
        color: var(--danger-color);
    }

    .text-primary {
        color: var(--text-primary);
    }

    .mb-3 {
        margin-bottom: 1.5rem;
    }

    .mb-4 {
        margin-bottom: 2rem;
    }

    .me-1 {
        margin-right: 0.25rem;
    }

    .me-2 {
        margin-right: 0.5rem;
    }

    .d-none {
        display: none !important;
    }

    .row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -0.75rem;
    }

    .col-md-4,
    .col-md-8 {
        padding: 0 0.75rem;
    }

    .col-md-4 {
        flex: 0 0 33.333333%;
        max-width: 33.333333%;
    }

    .col-md-8 {
        flex: 0 0 66.666667%;
        max-width: 66.666667%;
    }

    .text-center {
        text-align: center;
    }

    .img-thumbnail {
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 12px;
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.2);
        padding: 0.5rem;
    }

    .mt-2 {
        margin-top: 0.5rem;
    }

    .button-group {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        padding: 2rem;
        background: rgba(255, 255, 255, 0.1);
        border-top: 1px solid rgba(255, 255, 255, 0.2);
    }

    .loader-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    .loader {
        width: 50px;
        height: 50px;
        border: 4px solid rgba(22, 78, 99, 0.2);
        border-top: 4px solid var(--primary-color);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    @media (max-width: 768px) {

        .col-md-4,
        .col-md-8 {
            flex: 0 0 100%;
            max-width: 100%;
            margin-bottom: 1.5rem;
        }

        .row {
            flex-direction: column;
        }

        .button-group {
            flex-direction: column;
        }

        .btn {
            margin-right: 0;
            margin-bottom: 0.5rem;
        }
    }
</style>

<section class="py-5">
    <div class="container">
        <h2 class="page-title"><i class="fas fa-user me-2"></i> My Profile</h2>

        <div class="card card-profile">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-user-circle me-2"></i> Profile Settings</h4>
            </div>
            <div class="card-body">
                <form id="profileForm" enctype="multipart/form-data" action="profile_action.php" method="POST">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="form-section">
                        <h5>Personal Information</h5>
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <label class="form-label">Profile Photo</label><br>
                                <?php if (!empty($user['photo']) && file_exists(__DIR__ . '/../assets/images/profiles/' . $user['photo'])): ?>
                                    <img src="../assets/images/profiles/<?= htmlspecialchars($user['photo']) ?>" class="profile-img mb-3" alt="Profile Image">
                                <?php else: ?>
                                    <img src="../assets/images/profiles/default.png" class="profile-img mb-3" alt="Default Profile Image">
                                <?php endif; ?>
                                <div class="mb-3">
                                    <input type="file" name="photo" class="form-control" accept="image/*" onchange="previewProfileImage(event)">
                                    <div class="mt-2">
                                        <img id="preview" src="#" alt="Preview" style="max-height: 120px; display: none;" class="img-thumbnail">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h5>Security Settings</h5>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="2fa_enabled" class="form-check-input" id="2fa_enabled" <?= $user['2fa_enabled'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="2fa_enabled">Enable Two-Factor Authentication</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h5>Preferences</h5>
                        <div class="mb-3">
                            <label class="form-label">Theme Preference</label>
                            <select name="theme_preference" class="form-select">
                                <option value="light" <?= $user['theme_preference'] === 'light' ? 'selected' : '' ?>>Light</option>
                                <option value="dark" <?= $user['theme_preference'] === 'dark' ? 'selected' : '' ?>>Dark</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="email_notifications" class="form-check-input" id="email_notifications" <?= $user['email_notifications'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="email_notifications">Receive Email Notifications</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h5>Change Password</h5>
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" minlength="6">
                        </div>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save me-1"></i> <span class="btn-text">Update Profile</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="loader-overlay">
            <div class="loader"></div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>

<script>
    function previewProfileImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const preview = document.getElementById('preview');
            preview.src = reader.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    $(document).ready(function() {
        $('#profileForm').on('submit', function(e) {
            e.preventDefault();

            const form = this;
            const newPassword = form.new_password.value.trim();
            const confirmPassword = form.confirm_password.value.trim();
            const currentPassword = form.current_password.value.trim();

            if ((newPassword || confirmPassword) && !currentPassword) {
                Swal.fire('Error', 'Please enter your current password to change your password.', 'error');
                return;
            }

            if (newPassword !== confirmPassword) {
                Swal.fire('Error', 'New passwords do not match.', 'error');
                return;
            }

            const formData = new FormData(form);
            $('.loader-overlay').css('display', 'flex');
            $('#submitBtn').prop('disabled', true);
            $('#submitBtn .btn-text').text('Updating...');
            $('#submitBtn .spinner-border').removeClass('d-none');

            $.ajax({
                url: 'profile_action.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(response) {
                    $('.loader-overlay').hide();
                    $('#submitBtn').prop('disabled', false);
                    $('#submitBtn .btn-text').text('Update Profile');
                    $('#submitBtn .spinner-border').addClass('d-none');

                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = response.redirect;
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    $('.loader-overlay').hide();
                    $('#submitBtn').prop('disabled', false);
                    $('#submitBtn .btn-text').text('Update Profile');
                    $('#submitBtn .spinner-border').addClass('d-none');
                    Swal.fire('Error', 'AJAX request failed: ' + error, 'error');
                }
            });
        });
    });
</script>