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

$stmt = $conn->prepare("SELECT * FROM brands WHERE brand_id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$brand = $result->fetch_assoc();
$stmt->close();

if (!$brand) {
    ob_end_clean();
    redirect("brands.php?error=not_found");
}

$errors = [];

if (isset($_POST['update'])) {
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

    // Check if slug already exists (excluding current brand)
    if (empty($errors)) {
        $checkStmt = $conn->prepare("SELECT brand_id FROM brands WHERE slug = ? AND brand_id != ?");
        $checkStmt->bind_param("si", $slug, $id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "Slug already exists. Please use a different slug.";
        }
        $checkStmt->close();
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE brands SET name=?, slug=?, active=? WHERE brand_id = ?");
        $stmt->bind_param("ssii", $name, $slug, $active, $id);

        if ($stmt->execute()) {
            $stmt->close();
            ob_end_clean();
            redirect("brands.php?updated=1");
        } else {
            $errors[] = "Failed to update brand: " . $conn->error;
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
                    <i class="bi bi-pencil-square me-2"></i>Edit Brand
                </h2>
                <p class="text-muted mb-0">Update brand information</p>
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
                            <input type="text" name="name" class="form-control"
                                   value="<?php echo htmlspecialchars($brand['name']); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Slug <span class="text-danger">*</span></label>
                            <input type="text" name="slug" class="form-control"
                                   value="<?php echo htmlspecialchars($brand['slug']); ?>" required>
                            <small class="text-muted">URL-friendly identifier (e.g., "apple", "samsung")</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="active" class="form-select" required>
                                <option value="1" <?php echo $brand['active'] ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo !$brand['active'] ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <hr class="my-3">
                            <div class="d-flex gap-2">
                                <button name="update" type="submit" class="btn btn-primary-green">
                                    <i class="bi bi-check-circle me-2"></i>Update Brand
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
