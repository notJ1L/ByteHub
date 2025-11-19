<?php
include '../includes/db.php';
include '../includes/functions.php';

$errors = [];
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';
    $photoName = null;

    if ($username === '' || $email === '' || $password === '' || $confirm === '') {
        $errors[] = 'All fields are required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!empty($_FILES['photo']['name'])) {
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $errors[] = "Invalid photo format. Allowed: JPG, PNG, GIF, WEBP.";
        }
    }

    if (!$errors) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Email already registered.';
        }
        $stmt->close();
    }

    if (!$errors) {
        if (!empty($_FILES['photo']['name'])) {
            $photoName = time() . '_' . $_FILES['photo']['name'];
            $uploadPath = "../uploads/users/" . $photoName;
            move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath);
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            INSERT INTO users (username, email, password_hash, photo, active, role) 
            VALUES (?, ?, ?, ?, 1, 'user')
        ");

        $stmt->bind_param('ssss', $username, $email, $hash, $photoName);

        if ($stmt->execute()) {
            redirect('login.php');
        } else {
            $errors[] = 'Registration failed.';
        }

        $stmt->close();
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="auth-container">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-5">
            <div class="col-lg-6 col-md-8">
                <div class="auth-card">
                    <div class="auth-header text-center mb-4">
                        <div class="auth-icon mb-3">
                            <i class="bi bi-person-plus-fill"></i>
                        </div>
                        <h1 class="auth-title">Create Account</h1>
                        <p class="auth-subtitle">Join ByteHub and start shopping today</p>
                    </div>

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

                    <form method="post" enctype="multipart/form-data" class="auth-form">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="username" class="form-label fw-semibold">
                                    <i class="bi bi-person me-2"></i>Username
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-person text-muted"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control border-start-0 ps-0" 
                                           id="username" 
                                           name="username" 
                                           value="<?php echo htmlspecialchars($username); ?>" 
                                           placeholder="Choose a username"
                                           autocomplete="username">
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="email" class="form-label fw-semibold">
                                    <i class="bi bi-envelope me-2"></i>Email Address
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-envelope text-muted"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control border-start-0 ps-0" 
                                           id="email" 
                                           name="email" 
                                           value="<?php echo htmlspecialchars($email); ?>" 
                                           placeholder="your.email@example.com"
                                           autocomplete="email">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="password" class="form-label fw-semibold">
                                    <i class="bi bi-lock me-2"></i>Password
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-lock text-muted"></i>
                                    </span>
                                    <input type="password" 
                                           class="form-control border-start-0 ps-0" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Create a password"
                                           autocomplete="new-password">
                                    <button class="btn btn-outline-secondary border-start-0" 
                                            type="button" 
                                            onclick="togglePassword('password')">
                                        <i class="bi bi-eye" id="passwordToggleIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="confirm" class="form-label fw-semibold">
                                    <i class="bi bi-lock-fill me-2"></i>Confirm Password
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-lock-fill text-muted"></i>
                                    </span>
                                    <input type="password" 
                                           class="form-control border-start-0 ps-0" 
                                           id="confirm" 
                                           name="confirm" 
                                           placeholder="Confirm your password"
                                           autocomplete="new-password">
                                    <button class="btn btn-outline-secondary border-start-0" 
                                            type="button" 
                                            onclick="togglePassword('confirm')">
                                        <i class="bi bi-eye" id="confirmToggleIcon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="photo" class="form-label fw-semibold">
                                <i class="bi bi-camera me-2"></i>Profile Photo <small class="text-muted">(Optional)</small>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-image text-muted"></i>
                                </span>
                                <input type="file" 
                                       class="form-control border-start-0 ps-0" 
                                       id="photo" 
                                       name="photo" 
                                       accept="image/*"
                                       onchange="previewPhoto(this)">
                            </div>
                            <div class="form-text">JPG, PNG, GIF or WEBP. Max 5MB.</div>
                            
                            <div id="photoPreview" class="mt-3 text-center" style="display: none;">
                                <img id="previewImg" src="" alt="Preview" class="img-thumbnail rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                            </div>
                        </div>

                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-primary-green btn-lg py-3">
                                <i class="bi bi-person-plus me-2"></i>Create Account
                            </button>
                        </div>
                    </form>

                    <div class="auth-footer text-center">
                        <p class="mb-0">
                            Already have an account? 
                            <a href="login.php" class="fw-semibold text-decoration-none">Sign In</a>
                        </p>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="index.php" class="text-muted text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i>Back to Store
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
.auth-container {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: calc(100vh - 200px);
    padding: 2rem 0;
}

.auth-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    padding: 3rem;
}

.auth-header {
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e9ecef;
}

.auth-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    box-shadow: 0 8px 20px rgba(0, 77, 38, 0.3);
}

.auth-icon i {
    font-size: 2.5rem;
    color: white;
}

.auth-title {
    font-size: 2rem;
    font-weight: 700;
    color: #212529;
    margin: 1rem 0 0.5rem;
}

.auth-subtitle {
    color: #6c757d;
    font-size: 1rem;
    margin: 0;
}

.auth-form {
    margin-top: 2rem;
}

.form-label {
    color: #495057;
    margin-bottom: 0.75rem;
    font-size: 0.95rem;
}

.input-group-text {
    border-right: none !important;
}

.form-control {
    border-left: none !important;
    padding-left: 0.75rem;
    height: 50px;
    font-size: 1rem;
}

.form-control:focus {
    border-color: var(--primary-green);
    box-shadow: 0 0 0 0.2rem rgba(0, 77, 38, 0.25);
}

.input-group:focus-within .input-group-text {
    border-color: var(--primary-green);
}

.btn-primary-green {
    background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
    border: none;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 77, 38, 0.3);
}

.btn-primary-green:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 77, 38, 0.4);
    color: white;
}

.auth-footer {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e9ecef;
}

.auth-footer a {
    color: var(--primary-green);
    transition: color 0.3s ease;
}

.auth-footer a:hover {
    color: var(--secondary-green);
}

.alert {
    border-radius: 12px;
    border: none;
}

@media (max-width: 768px) {
    .auth-card {
        padding: 2rem 1.5rem;
    }
    
    .auth-title {
        font-size: 1.75rem;
    }
}
</style>

<script>
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

function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('photoPreview');
            const previewImg = document.getElementById('previewImg');
            
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include '../includes/footer.php'; ?>
