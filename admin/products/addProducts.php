<?php
ob_start();
session_start();
include '../../includes/db.php';
include '../../includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
  ob_end_clean();
  redirect('index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $name = trim($_POST['product_name']);
  $model = trim($_POST['model']);
  $price = (float)$_POST['price'];
  $stock = (int)$_POST['stock'];
  $category_id = (int)$_POST['category_id'];
  $brand_id = (int)$_POST['brand_id'];
  $featured = isset($_POST['featured']) ? 1 : 0;
  $new_arrival = isset($_POST['new_arrival']) ? 1 : 0;
  $description = $_POST['description'];
  $specs = $_POST['specifications'];

  if (empty($name)) $errors[] = 'Product name is required.';
  if (empty($model)) $errors[] = 'Model is required.';
  if (empty($price)) $errors[] = 'Price is required.';
  if (empty($stock)) $errors[] = 'Stock is required.';

  if (empty($errors)) {
    $stmt = $conn->prepare("
      INSERT INTO products 
      (product_name, model, price, stock, image, featured, new_arrival, active, category_id, brand_id, description, specifications)
      VALUES 
      (?, ?, ?, ?, '', ?, ?, 1, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssdiisssss", $name, $model, $price, $stock, $featured, $new_arrival, $category_id, $brand_id, $description, $specs);
    $stmt->execute();

    $product_id = $conn->insert_id;

    // Ensure uploads directory exists
    $uploadDir = "../../uploads/products/";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // MAIN IMAGE UPLOAD
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $mainImage = time() . '_' . $_FILES['image']['name'];
        $target = $uploadDir . $mainImage;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $stmt = $conn->prepare("UPDATE products SET image=? WHERE product_id=?");
            $stmt->bind_param("si", $mainImage, $product_id);
            $stmt->execute();
        }
    }

    // MULTIPLE IMAGES UPLOAD
    if (!empty($_FILES['images']['name'][0])) {
      foreach ($_FILES['images']['name'] as $key => $filename) {
          if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
              $imgName = time() . '_' . $filename;
              $uploadPath = $uploadDir . $imgName;

              if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $uploadPath)) {
                  $stmt = $conn->prepare("INSERT INTO product_images (product_id, filename) VALUES (?, ?)");
                  $stmt->bind_param("is", $product_id, $imgName);
                  $stmt->execute();
              }
          }
      }
    }

    ob_end_clean();
    redirect('products.php');
  }
}

include '../../includes/admin_header.php';

// get categories / brands
$cats = $conn->query("SELECT * FROM categories WHERE active=1");
$brands = $conn->query("SELECT * FROM brands WHERE active=1");
?>

<div class="admin-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header mb-4">
            <div>
                <h2 class="page-title">
                    <i class="bi bi-plus-circle me-2"></i>Add New Product
                </h2>
                <p class="text-muted mb-0">Create a new product listing</p>
            </div>
            <a href="products.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Products
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
                                   placeholder="Enter product name" value="<?php echo isset($_POST['product_name']) ? htmlspecialchars($_POST['product_name']) : ''; ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Model <span class="text-danger">*</span></label>
                            <input type="text" name="model" class="form-control" required
                                   placeholder="Enter model number" value="<?php echo isset($_POST['model']) ? htmlspecialchars($_POST['model']) : ''; ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" name="price" class="form-control" required
                                       placeholder="0.00" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Stock Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="stock" class="form-control" required
                                   placeholder="0" value="<?php echo isset($_POST['stock']) ? htmlspecialchars($_POST['stock']) : ''; ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php while($c = $cats->fetch_assoc()): ?>
                                    <option value="<?= $c['category_id'] ?>" 
                                        <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $c['category_id']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($c['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Brand <span class="text-danger">*</span></label>
                            <select name="brand_id" class="form-select" required>
                                <option value="">Select Brand</option>
                                <?php 
                                $brands->data_seek(0);
                                while($b = $brands->fetch_assoc()): ?>
                                    <option value="<?= $b['brand_id'] ?>"
                                        <?php echo (isset($_POST['brand_id']) && $_POST['brand_id'] == $b['brand_id']) ? 'selected' : ''; ?>>
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
                                           <?php echo (isset($_POST['featured'])) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="featured">
                                        <i class="bi bi-star-fill text-warning me-1"></i>Featured Product
                                    </label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="new_arrival" id="new_arrival"
                                           <?php echo (isset($_POST['new_arrival'])) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="new_arrival">
                                        <i class="bi bi-badge-new text-primary me-1"></i>New Arrival
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
                            <textarea name="description" rows="6" class="form-control" 
                                      placeholder="Enter detailed product description..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Technical Specifications</label>
                            <textarea name="specifications" rows="6" class="form-control" 
                                      placeholder="Enter technical specifications (one per line)..."><?php echo isset($_POST['specifications']) ? htmlspecialchars($_POST['specifications']) : ''; ?></textarea>
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
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">This will be the primary product image</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Additional Product Images</label>
                            <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                            <small class="text-muted">You can select multiple images</small>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="col-12">
                            <hr class="my-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary-green">
                                    <i class="bi bi-check-circle me-2"></i>Add Product
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
