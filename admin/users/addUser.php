<?php
ob_start();
session_start();
include '../../includes/db.php';
include '../../includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    ob_end_clean();
    redirect('../index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $role     = $_POST['role'];
    $active   = $_POST['active'];

    // Username Validation
    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters long.";
    } elseif (strlen($username) > 50) {
        $errors[] = "Username must not exceed 50 characters.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores.";
    }

    // Email Validation
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } elseif (strlen($email) > 255) {
        $errors[] = "Email must not exceed 255 characters.";
    }

    // Password Validation
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    } elseif (strlen($password) > 128) {
        $errors[] = "Password must not exceed 128 characters.";
    }

    // Check for duplicate username
    if (empty($errors)) {
        $checkUsernameStmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $checkUsernameStmt->bind_param("s", $username);
        $checkUsernameStmt->execute();
        $usernameResult = $checkUsernameStmt->get_result();
        if ($usernameResult->num_rows > 0) {
            $errors[] = "Username already exists. Please choose a different username.";
        }
        $checkUsernameStmt->close();
    }

    // Check for duplicate email
    if (empty($errors)) {
        $checkStmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "Email already exists. Please use a different email address.";
        }
        $checkStmt->close();
    }

    if (empty($errors)) {
        $photoName = null;

        $uploadDir = "../../uploads/users/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB

        if (!empty($_FILES['photo']['name'])) {
            if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                switch ($_FILES['photo']['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $errors[] = 'Photo file size exceeds the maximum allowed size (5MB).';
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $errors[] = 'Photo was only partially uploaded.';
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        break;
                    default:
                        $errors[] = 'An error occurred while uploading the photo.';
                }
            } elseif ($_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $fileType = mime_content_type($_FILES['photo']['tmp_name']);
                if (!in_array($fileType, $allowedTypes)) {
                    $errors[] = 'Photo must be a valid image file (JPEG, PNG, GIF, or WebP).';
                } elseif ($_FILES['photo']['size'] > $maxFileSize) {
                    $errors[] = 'Photo size must be less than 5MB.';
                } else {
                    $fileExtension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                    if (!in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        $errors[] = 'Photo file extension is not allowed.';
                    } else {
                        $photoName = uniqid('user_', true) . '_' . time() . '.' . $fileExtension;
                        $target = $uploadDir . $photoName;
                        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
                            $errors[] = 'Failed to upload photo.';
                        }
                    }
                }
            }
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            INSERT INTO users (username, email, password_hash, photo, role, active)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssssi", $username, $email, $hash, $photoName, $role, $active);

        if ($stmt->execute()) {
            $stmt->close();
            ob_end_clean();
            redirect('users.php');
        } else {
            $errors[] = "Failed to add user: " . $conn->error;
        }

        $stmt->close();
    }
}

include '../../includes/admin_header.php';
?>

<div class="admin-content">
    <div class="container-fluid">
        <div class="page-header mb-4">
            <div>
                <h2 class="page-title">
                    <i class="bi bi-person-plus me-2"></i>Add New User
                </h2>
                <p class="text-muted mb-0">Create a new user account</p>
            </div>
            <a href="users.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Users
            </a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="addUserForm" novalidate>
                    <div class="row g-4">
                        <div class="col-12">
                            <h5 class="section-title mb-3">
                                <i class="bi bi-info-circle me-2"></i>Account Information
                            </h5>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" required
                                   placeholder="Enter username" 
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                   minlength="3" maxlength="50">
                            <small class="text-muted">Must be 3-50 characters long</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required
                                   placeholder="Enter email address" 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            <small class="text-muted">Must be a valid email format</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required
                                   placeholder="Enter password" minlength="6">
                            <small class="text-muted">Must be at least 6 characters long</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">User Photo</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                            <small class="text-muted">Optional: Upload a profile photo (JPEG, PNG, GIF, or WebP)</small>
                        </div>

                        <div class="col-12">
                            <hr class="my-3">
                            <h5 class="section-title mb-3">
                                <i class="bi bi-shield-check me-2"></i>Role & Status
                            </h5>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">User Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="user" <?php echo (isset($_POST['role']) && $_POST['role'] == 'user') ? 'selected' : ''; ?>>
                                    <i class="bi bi-person me-2"></i>User
                                </option>
                                <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'selected' : ''; ?>>
                                    <i class="bi bi-shield-check me-2"></i>Admin
                                </option>
                            </select>
                            <small class="text-muted">Select the user's access level</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Account Status <span class="text-danger">*</span></label>
                            <select name="active" class="form-select" required>
                                <option value="1" <?php echo (!isset($_POST['active']) || $_POST['active'] == '1') ? 'selected' : ''; ?>>
                                    <i class="bi bi-check-circle me-2"></i>Active
                                </option>
                                <option value="0" <?php echo (isset($_POST['active']) && $_POST['active'] == '0') ? 'selected' : ''; ?>>
                                    <i class="bi bi-x-circle me-2"></i>Inactive
                                </option>
                            </select>
                            <small class="text-muted">Set the account activation status</small>
                        </div>

                        <div class="col-12">
                            <hr class="my-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary-green">
                                    <i class="bi bi-check-circle me-2"></i>Add User
                                </button>
                                <a href="users.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-2"></i>Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
.section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 1rem;
}

.form-label {
    margin-bottom: 0.5rem;
    color: #495057;
}

.form-control,
.form-select {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 0.625rem 1rem;
    color: #212529 !important;
    background-color: #fff !important;
}

.form-control:focus,
.form-select:focus {
    border-color: var(--primary-green);
    box-shadow: 0 0 0 3px rgba(0, 77, 38, 0.1);
    outline: none;
    color: #212529 !important;
    background-color: #fff !important;
}

.form-control::placeholder {
    color: #6c757d !important;
}

.btn-primary-green {
    background-color: var(--primary-green);
    border-color: var(--primary-green);
    color: white;
    font-weight: 600;
}

.btn-primary-green:hover {
    background-color: var(--secondary-green);
    border-color: var(--secondary-green);
    color: white;
}

.form-check-input:checked {
    background-color: var(--primary-green);
    border-color: var(--primary-green);
}

/* Only show invalid styling when field has been validated */
.form-control.is-invalid,
.form-select.is-invalid {
    border-color: #dc3545;
}

.form-control.is-invalid:focus,
.form-select.is-invalid:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
}

.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #dc3545;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addUserForm');
    const inputs = form.querySelectorAll('input[required], select[required]');
    let formSubmitted = false;
    
    // Track if form has been submitted
    form.addEventListener('submit', function(e) {
        formSubmitted = true;
    });
    
    // Set custom validation messages
    inputs.forEach(input => {
        input.addEventListener('invalid', function(e) {
            e.preventDefault();
            // Only show error if form has been submitted
            if (formSubmitted) {
                showFieldError(this);
            }
        });
        
        input.addEventListener('input', function() {
            // Clear error when user starts typing
            if (this.validity.valid) {
                clearFieldError(this);
            } else if (formSubmitted) {
                // Only show error if form was already submitted
                showFieldError(this);
            }
        });
        
        input.addEventListener('blur', function() {
            // Only validate on blur if form has been submitted
            if (formSubmitted && !this.validity.valid) {
                showFieldError(this);
            }
        });
    });
    
    // Custom validation for username
    const usernameInput = form.querySelector('input[name="username"]');
    if (usernameInput) {
        usernameInput.addEventListener('input', function() {
            const value = this.value.trim();
            if (formSubmitted && value && !/^[a-zA-Z0-9_]+$/.test(value)) {
                showCustomError(this, 'Username can only contain letters, numbers, and underscores.');
            } else if (this.validity.valid) {
                clearFieldError(this);
            }
        });
    }
    
    // Custom validation for password
    const passwordInput = form.querySelector('input[name="password"]');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            if (formSubmitted && this.value.length > 0 && this.value.length < 6) {
                showCustomError(this, 'Password must be at least 6 characters long.');
            } else if (this.validity.valid) {
                clearFieldError(this);
            }
        });
    }
    
    // Custom validation for form submission
    form.addEventListener('submit', function(e) {
        formSubmitted = true;
        let isValid = true;
        let firstInvalidField = null;
        
        inputs.forEach(input => {
            if (!input.validity.valid) {
                isValid = false;
                showFieldError(input);
                if (!firstInvalidField) {
                    firstInvalidField = input;
                }
            }
        });
        
        // Validate username format
        if (usernameInput && usernameInput.value.trim()) {
            if (!/^[a-zA-Z0-9_]+$/.test(usernameInput.value.trim())) {
                isValid = false;
                showCustomError(usernameInput, 'Username can only contain letters, numbers, and underscores.');
                if (!firstInvalidField) firstInvalidField = usernameInput;
            }
        }
        
        // Validate password length
        if (passwordInput && passwordInput.value) {
            if (passwordInput.value.length < 6) {
                isValid = false;
                showCustomError(passwordInput, 'Password must be at least 6 characters long.');
                if (!firstInvalidField) firstInvalidField = passwordInput;
            }
        }
        
        if (!isValid) {
            e.preventDefault();
            if (firstInvalidField) {
                firstInvalidField.focus();
                firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            showAlert('Please fill in all required fields correctly to continue.');
            return false;
        }
    });
    
    function showFieldError(field) {
        clearFieldError(field);
        
        let errorMessage = '';
        
        if (field.validity.valueMissing) {
            errorMessage = getRequiredMessage(field);
        } else if (field.validity.tooShort) {
            errorMessage = getMinLengthMessage(field);
        } else if (field.validity.tooLong) {
            errorMessage = getMaxLengthMessage(field);
        } else if (field.validity.typeMismatch) {
            errorMessage = getTypeMismatchMessage(field);
        } else {
            errorMessage = 'Please enter a valid value.';
        }
        
        showCustomError(field, errorMessage);
    }
    
    function showCustomError(field, message) {
        field.classList.add('is-invalid');
        
        let errorDiv = field.parentElement.querySelector('.invalid-feedback');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            field.parentElement.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
    }
    
    function clearFieldError(field) {
        field.classList.remove('is-invalid');
        const errorDiv = field.parentElement.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
    
    function getRequiredMessage(field) {
        const fieldName = field.previousElementSibling ? field.previousElementSibling.textContent.replace('*', '').trim() : 'This field';
        return `${fieldName} is required. Please enter details to continue.`;
    }
    
    function getMinLengthMessage(field) {
        if (field.name === 'username') {
            return 'Username must be at least 3 characters long.';
        } else if (field.name === 'password') {
            return 'Password must be at least 6 characters long.';
        }
        return `Please enter at least ${field.minLength} characters.`;
    }
    
    function getMaxLengthMessage(field) {
        return `Please enter no more than ${field.maxLength} characters.`;
    }
    
    function getTypeMismatchMessage(field) {
        if (field.type === 'email') {
            return 'Please enter a valid email address.';
        }
        return 'Please enter a valid value.';
    }
    
    function showAlert(message) {
        // Remove existing alert if any
        const existingAlert = document.querySelector('.validation-alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show validation-alert';
        alertDiv.innerHTML = `
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Error:</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        const formCard = form.closest('.card');
        formCard.insertBefore(alertDiv, formCard.querySelector('.card-body'));
        
        // Auto scroll to alert
        alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>

<?php include '../footer.php'; ?>
