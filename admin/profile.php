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
    body {
        background-color: #f8f9fa;
    }

    .card-profile {
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .card-profile .card-header {
        background-color: #007bff;
        color: white;
        border-radius: 10px 10px 0 0;
    }

    .profile-img {
        max-width: 150px;
        border: 2px solid #007bff;
        border-radius: 50%;
    }

    .form-section {
        padding: 1.5rem;
    }

    .form-section h5 {
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .loader-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    .loader {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        width: 40px;
        height: 40px;
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
</style>

<section class="py-5">
    <div class="container">
        <h2 class="text-primary mb-4"><i class="fas fa-user me-2"></i> My Profile</h2>

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
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="2fa_enabled" class="form-check-input" id="2fa_enabled" <?= $user['2fa_enabled'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="2fa_enabled">Enable Two-Factor Authentication</label>
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
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="email_notifications" class="form-check-input" id="email_notifications" <?= $user['email_notifications'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="email_notifications">Receive Email Notifications</label>
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

                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save me-1"></i> <span class="btn-text">Update Profile</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
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