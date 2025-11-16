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
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $active = (int)$_POST['active'];

    // Validation
    if (empty($name)) {
        $errors[] = "Brand name is required.";
    }
    if (empty($slug)) {
        $errors[] = "Slug is required.";
    }

    // Check if slug already exists
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
        <!-- Page Header -->
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
                <form method="post">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required
                                   placeholder="e.g., Apple, Samsung"
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Slug <span class="text-danger">*</span></label>
                            <input type="text" name="slug" class="form-control" required
                                   placeholder="e.g., apple, samsung"
                                   value="<?php echo isset($_POST['slug']) ? htmlspecialchars($_POST['slug']) : ''; ?>">
                            <small class="text-muted">URL-friendly identifier (lowercase, no spaces)</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="active" class="form-select" required>
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
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
