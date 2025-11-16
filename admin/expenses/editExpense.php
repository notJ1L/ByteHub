<?php
ob_start();
session_start();
include '../../includes/db.php';
include '../../includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    ob_end_clean();
    redirect('../index.php');
}

$id = (int)$_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM expenses WHERE expenses_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$expense = $result->fetch_assoc();
$stmt->close();

if (!$expense) {
    ob_end_clean();
    redirect("expenses.php?error=not_found");
}

$errors = [];

if (isset($_POST['update'])) {
    $title = trim($_POST['title']);
    $amount = (float)$_POST['amount'];
    $category = trim($_POST['category']);
    $notes = trim($_POST['notes']);

    // Validation
    if (empty($title)) {
        $errors[] = "Title is required.";
    }
    if (empty($amount) || $amount <= 0) {
        $errors[] = "Amount must be greater than 0.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE expenses SET title=?, amount=?, category=?, notes=? WHERE expenses_id = ?");
        $stmt->bind_param("sdssi", $title, $amount, $category, $notes, $id);

        if ($stmt->execute()) {
            $stmt->close();
            ob_end_clean();
            redirect("expenses.php?updated=1");
        } else {
            $errors[] = "Failed to update expense: " . $conn->error;
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
                    <i class="bi bi-pencil-square me-2"></i>Edit Expense
                </h2>
                <p class="text-muted mb-0">Update expense information</p>
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
                            <input type="text" name="title" class="form-control"
                                   value="<?php echo htmlspecialchars($expense['title']); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" name="amount" class="form-control"
                                       value="<?php echo htmlspecialchars($expense['amount']); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category</label>
                            <input type="text" name="category" class="form-control"
                                   value="<?php echo htmlspecialchars($expense['category']); ?>"
                                   placeholder="e.g., Office Supplies, Utilities">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date</label>
                            <input type="text" class="form-control" 
                                   value="<?php echo date('F j, Y', strtotime($expense['created_at'])); ?>" 
                                   disabled>
                            <small class="text-muted">Expense date cannot be changed</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" class="form-control" rows="4"
                                      placeholder="Additional notes about this expense..."><?php echo htmlspecialchars($expense['notes']); ?></textarea>
                        </div>

                        <div class="col-12">
                            <hr class="my-3">
                            <div class="d-flex gap-2">
                                <button name="update" type="submit" class="btn btn-primary-green">
                                    <i class="bi bi-check-circle me-2"></i>Update Expense
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
