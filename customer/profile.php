<?php
include '../includes/db.php';
include '../includes/functions.php';
include '../includes/header.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = "";

// Fetch current user info
$sql = "SELECT username, email, photo FROM users WHERE user_id = $user_id LIMIT 1";
$user = $conn->query($sql)->fetch_assoc();

$username = $user['username'];
$email    = $user['email'];
$photo    = $user['photo'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if ($username === '' || $email === '') {
        $errors[] = "Username and email cannot be empty.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Handle password change only if provided
    $updatePassword = false;
    if ($password !== '' || $confirm !== '') {
        if ($password !== $confirm) {
            $errors[] = "Passwords do not match.";
        } else {
            $updatePassword = true;
            $hashed = password_hash($password, PASSWORD_DEFAULT);
        }
    }

    // Handle photo upload
    $newPhotoName = $photo; // Keep old photo if none uploaded
    if (!empty($_FILES['photo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];

        if (!in_array($ext, $allowed)) {
            $errors[] = "Invalid photo type. Allowed: JPG, JPEG, PNG, GIF, WEBP.";
        } else {
            $newPhotoName = time() . "_" . $_FILES['photo']['name'];
            move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/users/" . $newPhotoName);
        }
    }

    if (!$errors) {

        if ($updatePassword) {
            $stmt = $conn->prepare("
                UPDATE users SET username=?, email=?, photo=?, password_hash=? 
                WHERE user_id=?
            ");
            $stmt->bind_param('ssssi', $username, $email, $newPhotoName, $hashed, $user_id);
        } else {
            $stmt = $conn->prepare("
                UPDATE users SET username=?, email=?, photo=? 
                WHERE user_id=?
            ");
            $stmt->bind_param('sssi', $username, $email, $newPhotoName, $user_id);
        }

        if ($stmt->execute()) {
            $success = "Profile updated successfully!";
            $photo = $newPhotoName; // Update photo preview immediately
        } else {
            $errors[] = "Failed to update profile.";
        }
        $stmt->close();
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Page Header -->
            <div class="mb-4">
                <h1 class="display-5 fw-bold text-dark mb-2">My Profile</h1>
                <p class="text-muted">Manage your account settings and preferences</p>
            </div>

            <!-- Success/Error Messages -->
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($errors): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <!-- Profile Photo Section -->
                    <div class="col-md-4 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body text-center">
                                <h5 class="card-title mb-4">Profile Photo</h5>
                                
                                <!-- Current Photo Display -->
                                <div class="mb-4">
                                    <div class="profile-photo-container position-relative d-inline-block">
                                        <?php if ($photo): ?>
                                            <img src="../uploads/users/<?php echo htmlspecialchars($photo); ?>" 
                                                 alt="Profile Photo" 
                                                 class="profile-photo rounded-circle shadow"
                                                 id="currentPhoto">
                                        <?php else: ?>
                                            <div class="profile-photo-placeholder rounded-circle shadow d-flex align-items-center justify-content-center bg-light">
                                                <i class="bi bi-person-fill" style="font-size: 4rem; color: #6c757d;"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="photo-overlay rounded-circle">
                                            <i class="bi bi-camera-fill"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- File Upload -->
                                <div class="mb-3">
                                    <label for="photo" class="form-label fw-semibold">Upload New Photo</label>
                                    <input type="file" 
                                           class="form-control" 
                                           id="photo" 
                                           name="photo" 
                                           accept="image/*"
                                           onchange="previewPhoto(this)">
                                    <div class="form-text">JPG, PNG, GIF or WEBP. Max 5MB.</div>
                                </div>

                                <!-- Photo Preview (hidden initially) -->
                                <div id="photoPreview" class="mt-3" style="display: none;">
                                    <img id="previewImg" src="" alt="Preview" class="img-thumbnail rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Information Section -->
                    <div class="col-md-8">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-person-gear me-2"></i>Account Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Username -->
                                <div class="mb-4">
                                    <label for="username" class="form-label fw-semibold">
                                        <i class="bi bi-person me-2"></i>Username
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg" 
                                           id="username" 
                                           name="username" 
                                           value="<?php echo htmlspecialchars($username); ?>" 
                                           required
                                           placeholder="Enter your username">
                                </div>

                                <!-- Email -->
                                <div class="mb-4">
                                    <label for="email" class="form-label fw-semibold">
                                        <i class="bi bi-envelope me-2"></i>Email Address
                                    </label>
                                    <input type="email" 
                                           class="form-control form-control-lg" 
                                           id="email" 
                                           name="email" 
                                           value="<?php echo htmlspecialchars($email); ?>" 
                                           required
                                           placeholder="your.email@example.com">
                                </div>

                                <hr class="my-4">

                                <!-- Password Section -->
                                <div class="mb-3">
                                    <h6 class="fw-semibold mb-3">
                                        <i class="bi bi-lock me-2"></i>Change Password
                                        <small class="text-muted fw-normal">(Leave blank to keep current password)</small>
                                    </h6>
                                </div>

                                <!-- New Password -->
                                <div class="mb-3">
                                    <label for="password" class="form-label fw-semibold">New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-key"></i></span>
                                        <input type="password" 
                                               class="form-control" 
                                               id="password" 
                                               name="password"
                                               placeholder="Enter new password"
                                               autocomplete="new-password">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                            <i class="bi bi-eye" id="passwordToggleIcon"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Confirm Password -->
                                <div class="mb-4">
                                    <label for="confirm" class="form-label fw-semibold">Confirm New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                                        <input type="password" 
                                               class="form-control" 
                                               id="confirm" 
                                               name="confirm"
                                               placeholder="Confirm new password"
                                               autocomplete="new-password">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm')">
                                            <i class="bi bi-eye" id="confirmToggleIcon"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                    <button type="submit" class="btn btn-primary-green btn-lg px-5">
                                        <i class="bi bi-check-circle me-2"></i>Update Profile
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
.profile-photo-container {
    position: relative;
    cursor: pointer;
}

.profile-photo {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border: 4px solid #fff;
    transition: all 0.3s ease;
}

.profile-photo-placeholder {
    width: 150px;
    height: 150px;
    border: 4px solid #fff;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.photo-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 150px;
    height: 150px;
    background: rgba(0, 77, 38, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    color: white;
    font-size: 2rem;
}

.profile-photo-container:hover .photo-overlay {
    opacity: 1;
}

.card {
    border: none;
    border-radius: 12px;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    padding: 1.25rem 1.5rem;
}

.form-control {
    color: #212529 !important;
    background-color: #fff !important;
}

.form-control::placeholder {
    color: #6c757d !important;
}

.form-control:focus {
    border-color: var(--primary-green);
    box-shadow: 0 0 0 0.2rem rgba(0, 77, 38, 0.25);
    color: #212529 !important;
    background-color: #fff !important;
}

.form-select {
    color: #212529 !important;
    background-color: #fff !important;
}

.form-select:focus {
    color: #212529 !important;
    background-color: #fff !important;
}

.btn-primary-green {
    background-color: var(--primary-green);
    border-color: var(--primary-green);
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary-green:hover {
    background-color: var(--secondary-green);
    border-color: var(--secondary-green);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 77, 38, 0.3);
}

.alert {
    border-radius: 10px;
    border: none;
}
</style>

<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('photoPreview');
            const previewImg = document.getElementById('previewImg');
            const currentPhoto = document.getElementById('currentPhoto');
            
            previewImg.src = e.target.result;
            preview.style.display = 'block';
            
            if (currentPhoto) {
                currentPhoto.src = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + 'ToggleIcon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}
</script>

<?php include '../includes/footer.php'; ?>
