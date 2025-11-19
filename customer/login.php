<?php
include '../includes/db.php';
include '../includes/functions.php';

$errors = [];
$email = '';
$login_message = '';

if (!empty($_SESSION['login_message'])) {
    $login_message = $_SESSION['login_message'];
    unset($_SESSION['login_message']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Email and password are required.';
    }

    if (!$errors) {
        $stmt = $conn->prepare("SELECT user_id, username, password_hash, role, active FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            if (!$row['active']) {
                $errors[] = 'Your account has been deactivated. Please contact support.';
            } elseif (password_verify($password, $row['password_hash'])) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$row['user_id'];
                $_SESSION['username'] = $row['username'];

                if (isset($row['role']) && $row['role'] === 'admin') {
                    $_SESSION['admin_id'] = (int)$row['user_id'];
                    $_SESSION['admin_email'] = $email;
                    redirect('../admin/dashboard.php');
                } else {
                    redirect(after_login_redirect_path());
                }
                $stmt->close();
                exit;
            }
            $stmt->close();
        } else {
            $stmt->close();
        }

        if (!$errors) {
            $adminStmt = $conn->prepare("SELECT admin_id, email, password_hash FROM admin WHERE email = ? LIMIT 1");
            $adminStmt->bind_param('s', $email);
            $adminStmt->execute();
            $adminRes = $adminStmt->get_result();
            if ($admin = $adminRes->fetch_assoc()) {
                if (password_verify($password, $admin['password_hash'])) {
                    if (session_status() === PHP_SESSION_NONE) session_start();
                    session_regenerate_id(true);
                    $_SESSION['admin_id'] = (int)$admin['admin_id'];
                    $_SESSION['admin_email'] = $admin['email'];
                    redirect('../admin/dashboard.php');
                    exit;
                }
            }
            $adminStmt->close();
        }

        if (!$errors) {
            $errors[] = 'Invalid credentials.';
        }
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="auth-container">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-5">
            <div class="col-lg-5 col-md-7">
                <div class="auth-card">
                    <div class="auth-header text-center mb-4">
                        <div class="auth-icon mb-3">
                            <i class="bi bi-box-arrow-in-right"></i>
                        </div>
                        <h1 class="auth-title">Welcome Back</h1>
                        <p class="auth-subtitle">Sign in to your ByteHub account</p>
                    </div>

                    <?php if ($login_message): ?>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <?php echo htmlspecialchars($login_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($errors): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Error:</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="auth-form">
                        <div class="mb-4">
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

                        <div class="mb-4">
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
                                       placeholder="Enter your password"
                                       autocomplete="current-password">
                                <button class="btn btn-outline-secondary border-start-0" 
                                        type="button" 
                                        onclick="togglePassword('password')">
                                    <i class="bi bi-eye" id="passwordToggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-primary-green btn-lg py-3">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                            </button>
                        </div>
                    </form>

                    <div class="auth-footer text-center">
                        <p class="mb-0">
                            Don't have an account? 
                            <a href="register.php" class="fw-semibold text-decoration-none">Create Account</a>
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
</script>

<?php include '../includes/footer.php'; ?>
