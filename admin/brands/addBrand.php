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

if (isset($_POST['save'])) {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $slug = isset($_POST['slug']) ? trim($_POST['slug']) : '';
    $active = isset($_POST['active']) ? (int)$_POST['active'] : 1;

    // Name Validation
    if (empty($name)) {
        $errors[] = 'Brand name is required.';
    } elseif (strlen($name) < 2) {
        $errors[] = 'Brand name must be at least 2 characters long.';
    } elseif (strlen($name) > 100) {
        $errors[] = 'Brand name must not exceed 100 characters.';
    }

    // Slug Validation
    if (empty($slug)) {
        $errors[] = 'Slug is required.';
    } elseif (strlen($slug) < 2) {
        $errors[] = 'Slug must be at least 2 characters long.';
    } elseif (strlen($slug) > 100) {
        $errors[] = 'Slug must not exceed 100 characters.';
    } elseif (!preg_match('/^[a-z0-9-]+$/', $slug)) {
        $errors[] = 'Slug can only contain lowercase letters, numbers, and hyphens.';
    }

    // Check for duplicate name
    if (empty($errors)) {
        $checkNameStmt = $conn->prepare("SELECT brand_id FROM brands WHERE name = ?");
        $checkNameStmt->bind_param("s", $name);
        $checkNameStmt->execute();
        $nameResult = $checkNameStmt->get_result();
        if ($nameResult->num_rows > 0) {
            $errors[] = 'Brand name already exists. Please use a different name.';
        }
        $checkNameStmt->close();
    }

    // Check for duplicate slug
    if (empty($errors)) {
        $checkStmt = $conn->prepare("SELECT brand_id FROM brands WHERE slug = ?");
        $checkStmt->bind_param("s", $slug);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "Slug already exists. Please use a different slug.";
        }
        $checkStmt->close();
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO brands (name, slug, active) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $name, $slug, $active);

        if ($stmt->execute()) {
            $stmt->close();
            ob_end_clean();
            redirect("brands.php?added=1");
        } else {
            $errors[] = "Failed to add brand: " . $conn->error;
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
                    <i class="bi bi-plus-circle me-2"></i>Add New Brand
                </h2>
                <p class="text-muted mb-0">Create a new product brand</p>
            </div>
            <a href="brands.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Brands
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
                <form method="post" id="addBrandForm" novalidate>
                    <div class="row g-4">
                        <div class="col-12">
                            <h5 class="section-title mb-3">
                                <i class="bi bi-info-circle me-2"></i>Brand Information
                            </h5>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Brand Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required
                                   placeholder="e.g., Apple, Samsung"
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                   minlength="2" maxlength="100">
                            <small class="text-muted">Must be 2-100 characters long</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Slug <span class="text-danger">*</span></label>
                            <input type="text" name="slug" class="form-control" required
                                   placeholder="e.g., apple, samsung"
                                   value="<?php echo isset($_POST['slug']) ? htmlspecialchars($_POST['slug']) : ''; ?>"
                                   minlength="2" maxlength="100" pattern="[a-z0-9-]+">
                            <small class="text-muted">URL-friendly identifier (lowercase letters, numbers, and hyphens only)</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="active" class="form-select" required>
                                <option value="1" <?php echo (!isset($_POST['active']) || $_POST['active'] == '1') ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo (isset($_POST['active']) && $_POST['active'] == '0') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                            <small class="text-muted">Set the brand activation status</small>
                        </div>

                        <div class="col-12">
                            <hr class="my-3">
                            <div class="d-flex gap-2">
                                <button name="save" type="submit" class="btn btn-primary-green">
                                    <i class="bi bi-check-circle me-2"></i>Save Brand
                                </button>
                                <a href="brands.php" class="btn btn-outline-secondary">
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

.section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 1rem;
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
    const form = document.getElementById('addBrandForm');
    const inputs = form.querySelectorAll('input[required], select[required]');
    let formSubmitted = false;
    
    form.addEventListener('submit', function(e) {
        formSubmitted = true;
    });
    
    inputs.forEach(input => {
        input.addEventListener('invalid', function(e) {
            e.preventDefault();
            if (formSubmitted) {
                showFieldError(this);
            }
        });
        
        input.addEventListener('input', function() {
            if (this.validity.valid) {
                clearFieldError(this);
            } else if (formSubmitted) {
                showFieldError(this);
            }
        });
        
        input.addEventListener('blur', function() {
            if (formSubmitted && !this.validity.valid) {
                showFieldError(this);
            }
        });
    });
    
    const slugInput = form.querySelector('input[name="slug"]');
    if (slugInput) {
        slugInput.addEventListener('input', function() {
            const value = this.value.trim();
            if (formSubmitted && value && !/^[a-z0-9-]+$/.test(value)) {
                showCustomError(this, 'Slug can only contain lowercase letters, numbers, and hyphens.');
            } else if (this.validity.valid) {
                clearFieldError(this);
            }
        });
    }
    
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
        
        if (slugInput && slugInput.value.trim()) {
            if (!/^[a-z0-9-]+$/.test(slugInput.value.trim())) {
                isValid = false;
                showCustomError(slugInput, 'Slug can only contain lowercase letters, numbers, and hyphens.');
                if (!firstInvalidField) firstInvalidField = slugInput;
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
        } else if (field.validity.patternMismatch) {
            errorMessage = 'Slug can only contain lowercase letters, numbers, and hyphens.';
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
        if (field.name === 'name') {
            return 'Brand name must be at least 2 characters long.';
        } else if (field.name === 'slug') {
            return 'Slug must be at least 2 characters long.';
        }
        return `Please enter at least ${field.minLength} characters.`;
    }
    
    function getMaxLengthMessage(field) {
        return `Please enter no more than ${field.maxLength} characters.`;
    }
    
    function showAlert(message) {
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
        
        alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>

<?php include '../footer.php'; ?>
