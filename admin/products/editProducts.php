<?php
session_start();
include '../../includes/db.php';
include '../../includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    redirect('index.php');
}

include '../../includes/admin_header.php';

$product_id = (int)$_GET['id'];

$res = $conn->query("SELECT * FROM products WHERE product_id = $product_id LIMIT 1");
$product = $res->fetch_assoc();

if (!$product) {
    echo '<div class="admin-content"><div class="container-fluid"><div class="alert alert-danger">Product not found.</div></div></div>';
    include '../footer.php';
    exit;
}

$cats = $conn->query("SELECT * FROM categories WHERE active=1");
$brands = $conn->query("SELECT * FROM brands WHERE active=1");

$moreImages = $conn->query("SELECT * FROM product_images WHERE product_id = $product_id");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['product_name']);
    $model = trim($_POST['model']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category_id = (int)$_POST['category_id'];
    $brand_id = (int)$_POST['brand_id'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $new_arrival = isset($_POST['new_arrival']) ? 1 : 0;
    $active = isset($_POST['active']) ? 1 : 0;
    $description = $_POST['description'];
    $specs = $_POST['specifications'];

    $stmt = $conn->prepare("
        UPDATE products SET
            product_name = ?,
            model = ?,
            price = ?,
            stock = ?,
            featured = ?,
            new_arrival = ?,
            active = ?,
            category_id = ?,
            brand_id = ?,
            description = ?,
            specifications = ?
        WHERE product_id = ?
    ");
    $stmt->bind_param("ssdiiiiisssi", $name, $model, $price, $stock, $featured, $new_arrival, $active, $category_id, $brand_id, $description, $specs, $product_id);
    $stmt->execute();

    if (!empty($_FILES['image']['name'])) {
        $mainImage = time() . '_' . $_FILES['image']['name'];
        $target = "../../uploads/products/" . $mainImage;

        move_uploaded_file($_FILES['image']['tmp_name'], $target);

        $stmt = $conn->prepare("UPDATE products SET image=? WHERE product_id=?");
        $stmt->bind_param("si", $mainImage, $product_id);
        $stmt->execute();
    }

    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['name'] as $key => $filename) {
            $imgName = time() . '_' . $filename;
            $uploadPath = "../../uploads/products/" . $imgName;

            if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $uploadPath)) {
                $stmt = $conn->prepare("INSERT INTO product_images (product_id, filename) VALUES (?, ?)");
                $stmt->bind_param("is", $product_id, $imgName);
                $stmt->execute();
            }
        }
    }

    redirect("products.php");
    exit;
}
?>

<div class="admin-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header mb-4">
            <div>
                <h2 class="page-title">
                    <i class="bi bi-pencil-square me-2"></i>Edit Product
                </h2>
                <p class="text-muted mb-0">Update product information</p>
            </div>
            <a href="products.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Products
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row g-4">
                        <!-- Basic Information -->
                        <div class="col-12">
                            <h5 class="section-title mb-3">
                                <i class="bi bi-info-circle me-2"></i>Basic Information
                            </h5>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="product_name" class="form-control" required
                                   value="<?= htmlspecialchars($product['product_name']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Model <span class="text-danger">*</span></label>
                            <input type="text" name="model" class="form-control" required
                                   value="<?= htmlspecialchars($product['model']) ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" name="price" class="form-control" required
                                       value="<?= $product['price'] ?>">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Stock Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="stock" class="form-control" required
                                   value="<?= $product['stock'] ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <?php while ($c = $cats->fetch_assoc()): ?>
                                    <option value="<?= $c['category_id'] ?>" 
                                        <?= $product['category_id'] == $c['category_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Brand <span class="text-danger">*</span></label>
                            <select name="brand_id" class="form-select" required>
                                <?php 
                                $brands->data_seek(0);
                                while ($b = $brands->fetch_assoc()): ?>
                                    <option value="<?= $b['brand_id'] ?>"
                                        <?= $product['brand_id'] == $b['brand_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($b['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Product Options</label>
                            <div class="mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="featured" id="featured"
                                           <?= $product['featured'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="featured">
                                        <i class="bi bi-star-fill text-warning me-1"></i>Featured Product
                                    </label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="new_arrival" id="new_arrival"
                                           <?= $product['new_arrival'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="new_arrival">
                                        <i class="bi bi-badge-new text-primary me-1"></i>New Arrival
                                    </label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="active" id="active"
                                           <?= $product['active'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="active">
                                        <i class="bi bi-toggle-on text-success me-1"></i>Active
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <hr class="my-3">
                            <h5 class="section-title mb-3">
                                <i class="bi bi-file-text me-2"></i>Description & Specifications
                            </h5>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Product Description</label>
                            <textarea name="description" rows="6" class="form-control"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Technical Specifications</label>
                            <textarea name="specifications" rows="6" class="form-control"><?= htmlspecialchars($product['specifications'] ?? '') ?></textarea>
                        </div>

                        <!-- Images -->
                        <div class="col-12">
                            <hr class="my-3">
                            <h5 class="section-title mb-3">
                                <i class="bi bi-images me-2"></i>Product Images
                            </h5>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Main Product Image</label>
                            <?php if ($product['image']): ?>
                                <div class="mb-2">
                                    <img src="../../uploads/products/<?= htmlspecialchars($product['image']) ?>" 
                                         alt="Current image" 
                                         class="img-thumbnail" 
                                         style="max-width: 200px; height: auto;">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">Leave blank to keep current image</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Add Additional Images</label>
                            <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                            <small class="text-muted">You can select multiple images</small>
                        </div>

                        <!-- Existing Additional Images -->
                        <?php if ($moreImages->num_rows > 0): ?>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Existing Additional Images</label>
                                <div class="row g-3">
                                    <?php 
                                    $moreImages->data_seek(0);
                                    while ($img = $moreImages->fetch_assoc()): ?>
                                        <div class="col-md-3">
                                            <div class="card">
                                                <img src="../../uploads/products/<?= htmlspecialchars($img['filename']) ?>" 
                                                     class="card-img-top" 
                                                     style="height: 150px; object-fit: cover;">
                                                <div class="card-body p-2">
                                                    <a href="deleteImage.php?id=<?= $img['image_id'] ?>&product=<?= $product_id ?>" 
                                                       class="btn btn-sm btn-danger w-100"
                                                       onclick="return confirm('Delete this image?');">
                                                        <i class="bi bi-trash me-1"></i>Delete
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Submit Buttons -->
                        <div class="col-12">
                            <hr class="my-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary-green">
                                    <i class="bi bi-check-circle me-2"></i>Save Changes
                                </button>
                                <a href="products.php" class="btn btn-outline-secondary">
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
</style>

<?php include '../footer.php'; ?>
