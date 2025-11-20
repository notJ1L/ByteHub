<?php
include '../../includes/db.php';
include '../../includes/functions.php';

if (!isAdmin()) {
    redirect('../customer/index.php');
}

$id = $_GET['id'] ?? 0;

$catQuery = $conn->query("SELECT * FROM categories WHERE category_id = $id LIMIT 1");
$category = $catQuery->fetch_assoc();

if (!$category) {
    die("Category not found.");
}

if (isset($_POST['update'])) {
    $name = $_POST['name'];
    $slug = $_POST['slug'];
    $active = $_POST['active'];

    $stmt = $conn->prepare("UPDATE categories SET name=?, slug=?, active=? WHERE category_id = ?");
    $stmt->bind_param("ssii", $name, $slug, $active, $id);

    if ($stmt->execute()) {
        redirect("categories.php?updated=1");
    } else {
        $error = "Failed to update category!";
    }
    $stmt->close();
}

include '../../includes/admin_header.php';
?>

<div class="admin-content">
    <div class="container-fluid">
        <div class="page-header mb-4">
            <div>
                <h2 class="page-title">
                    <i class="bi bi-pencil-square me-2"></i>Edit Category
                </h2>
                <p class="text-muted mb-0">Update category information</p>
            </div>
            <a href="categories.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Categories
            </a>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="post">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                   value="<?php echo htmlspecialchars($category['name']); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Slug <span class="text-danger">*</span></label>
                            <input type="text" name="slug" class="form-control"
                                   value="<?php echo htmlspecialchars($category['slug']); ?>" required>
                            <small class="text-muted">URL-friendly identifier (e.g., "laptops", "smartphones")</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="active" class="form-select" required>
                                <option value="1" <?php echo $category['active'] ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo !$category['active'] ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <hr class="my-3">
                            <div class="d-flex gap-2">
                                <button name="update" type="submit" class="btn btn-primary-green">
                                    <i class="bi bi-check-circle me-2"></i>Update Category
                                </button>
                                <a href="categories.php" class="btn btn-outline-secondary">
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
