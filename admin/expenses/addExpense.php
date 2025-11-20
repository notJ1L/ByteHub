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
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $amount = isset($_POST['amount']) ? trim($_POST['amount']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
    $created_at = date("Y-m-d H:i:s");

    // Title Validation
    if (empty($title)) {
        $errors[] = 'Expense title is required.';
    } elseif (strlen($title) < 2) {
        $errors[] = 'Expense title must be at least 2 characters long.';
    } elseif (strlen($title) > 255) {
        $errors[] = 'Expense title must not exceed 255 characters.';
    }

    // Amount Validation
    if (empty($amount)) {
        $errors[] = 'Amount is required.';
    } elseif (!is_numeric($amount)) {
        $errors[] = 'Amount must be a valid number.';
    } else {
        $amount = (float)$amount;
        if ($amount <= 0) {
            $errors[] = 'Amount must be greater than ₱0.00.';
        } elseif ($amount > 99999999.99) {
            $errors[] = 'Amount must not exceed ₱99,999,999.99.';
        }
    }

    // Category Validation (optional but if provided, check length)
    if (!empty($category) && strlen($category) > 100) {
        $errors[] = 'Category must not exceed 100 characters.';
    }

    // Notes Validation (optional but if provided, check length)
    if (!empty($notes) && strlen($notes) > 1000) {
        $errors[] = 'Notes must not exceed 1000 characters.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO expenses (title, amount, category, notes, created_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sdsss", $title, $amount, $category, $notes, $created_at);

        if ($stmt->execute()) {
            $stmt->close();
            ob_end_clean();
            redirect("expenses.php?added=1");
        } else {
            $errors[] = "Failed to add expense: " . $conn->error;
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
                    <i class="bi bi-plus-circle me-2"></i>Add New Expense
                </h2>
                <p class="text-muted mb-0">Record a new expense</p>
            </div>
            <a href="expenses.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Expenses
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
                <form method="post" id="addExpenseForm" novalidate>
                    <div class="row g-4">
                        <div class="col-12">
                            <h5 class="section-title mb-3">
                                <i class="bi bi-info-circle me-2"></i>Expense Information
                            </h5>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Expense Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required
                                   placeholder="e.g., Office Supplies, Utilities"
                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                                   minlength="2" maxlength="255">
                            <small class="text-muted">Must be 2-255 characters long</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" name="amount" class="form-control" required
                                       placeholder="0.00"
                                       value="<?php echo isset($_POST['amount']) ? htmlspecialchars($_POST['amount']) : ''; ?>"
                                       min="0.01" max="99999999.99">
                            </div>
                            <small class="text-muted">Must be greater than ₱0.00</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category</label>
                            <input type="text" name="category" class="form-control"
                                   placeholder="e.g., Office Supplies, Utilities, Marketing"
                                   value="<?php echo isset($_POST['category']) ? htmlspecialchars($_POST['category']) : ''; ?>"
                                   maxlength="100">
                            <small class="text-muted">Optional: Maximum 100 characters</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date</label>
                            <input type="text" class="form-control" 
                                   value="<?php echo date('F j, Y'); ?>" 
                                   disabled>
                            <small class="text-muted">Current date will be used</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" class="form-control" rows="4"
                                      placeholder="Additional notes about this expense..."
                                      maxlength="1000"><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                            <small class="text-muted">Optional: Maximum 1000 characters</small>
                        </div>

                        <div class="col-12">
                            <hr class="my-3">
                            <div class="d-flex gap-2">
                                <button name="save" type="submit" class="btn btn-primary-green">
                                    <i class="bi bi-check-circle me-2"></i>Save Expense
                                </button>
                                <a href="expenses.php" class="btn btn-outline-secondary">
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

.form-control:disabled {
    background-color: #e9ecef !important;
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
    const form = document.getElementById('addExpenseForm');
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
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
        
        // Validate amount
        const amountInput = form.querySelector('input[name="amount"]');
        if (amountInput && amountInput.value) {
            const amount = parseFloat(amountInput.value);
            if (amount <= 0) {
                isValid = false;
                showCustomError(amountInput, 'Amount must be greater than ₱0.00');
                if (!firstInvalidField) firstInvalidField = amountInput;
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
        } else if (field.validity.rangeUnderflow) {
            errorMessage = getMinValueMessage(field);
        } else if (field.validity.rangeOverflow) {
            errorMessage = getMaxValueMessage(field);
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
        if (field.name === 'title') {
            return 'Expense title must be at least 2 characters long.';
        }
        return `Please enter at least ${field.minLength} characters.`;
    }
    
    function getMaxLengthMessage(field) {
        return `Please enter no more than ${field.maxLength} characters.`;
    }
    
    function getMinValueMessage(field) {
        if (field.name === 'amount') {
            return 'Amount must be greater than ₱0.00.';
        }
        return `Value must be at least ${field.min}.`;
    }
    
    function getMaxValueMessage(field) {
        return `Value must not exceed ${field.max}.`;
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
