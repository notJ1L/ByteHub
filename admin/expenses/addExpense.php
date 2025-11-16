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
    $title = trim($_POST['title']);
    $amount = (float)$_POST['amount'];
    $category = trim($_POST['category']);
    $notes = trim($_POST['notes']);
    $created_at = date("Y-m-d H:i:s");

    // Validation
    if (empty($title)) {
        $errors[] = "Title is required.";
    }
    if (empty($amount) || $amount <= 0) {
        $errors[] = "Amount must be greater than 0.";
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
        <!-- Page Header -->
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
                <form method="post">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required
                                   placeholder="e.g., Office Supplies, Utilities"
                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" name="amount" class="form-control" required
                                       placeholder="0.00"
                                       value="<?php echo isset($_POST['amount']) ? htmlspecialchars($_POST['amount']) : ''; ?>">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category</label>
                            <input type="text" name="category" class="form-control"
                                   placeholder="e.g., Office Supplies, Utilities, Marketing"
                                   value="<?php echo isset($_POST['category']) ? htmlspecialchars($_POST['category']) : ''; ?>">
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
                                      placeholder="Additional notes about this expense..."><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
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

<!-- Bootstrap Icons -->
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
</style>

<?php include '../footer.php'; ?>
