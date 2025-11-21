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
  $price = isset($_POST['price']) ? trim($_POST['price']) : '';
  $stock = isset($_POST['stock']) ? trim($_POST['stock']) : '';
  $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
  $brand_id = isset($_POST['brand_id']) ? (int)$_POST['brand_id'] : 0;
  $featured = isset($_POST['featured']) ? 1 : 0;
  $new_arrival = isset($_POST['new_arrival']) ? 1 : 0;
  $description = isset($_POST['description']) ? trim($_POST['description']) : '';
  $specs = isset($_POST['specifications']) ? trim($_POST['specifications']) : '';

  // Product Name Validation
  if (empty($name)) {
    $errors[] = 'Product name is required.';
  } elseif (strlen($name) < 3) {
    $errors[] = 'Product name must be at least 3 characters long.';
  } elseif (strlen($name) > 255) {
    $errors[] = 'Product name must not exceed 255 characters.';
  }

  // Model Validation
  if (empty($model)) {
    $errors[] = 'Model is required.';
  } elseif (strlen($model) < 2) {
    $errors[] = 'Model must be at least 2 characters long.';
  } elseif (strlen($model) > 100) {
    $errors[] = 'Model must not exceed 100 characters.';
  }

  // Price Validation
  if (empty($price)) {
    $errors[] = 'Price is required.';
  } elseif (!is_numeric($price)) {
    $errors[] = 'Price must be a valid number.';
  } else {
    $price = (float)$price;
    if ($price <= 0) {
      $errors[] = 'Price must be greater than 0.';
    } elseif ($price > 999999.99) {
      $errors[] = 'Price must not exceed ₱999,999.99.';
    }
  }

  // Stock Validation
  if (empty($stock) && $stock !== '0') {
    $errors[] = 'Stock quantity is required.';
  } elseif (!is_numeric($stock)) {
    $errors[] = 'Stock must be a valid number.';
  } else {
    $stock = (int)$stock;
    if ($stock < 0) {
      $errors[] = 'Stock quantity cannot be negative.';
    } elseif ($stock > 999999) {
      $errors[] = 'Stock quantity must not exceed 999,999.';
    }
  }

  // Category Validation
  if (empty($category_id) || $category_id <= 0) {
    $errors[] = 'Please select a category.';
  } else {
    $catCheck = $conn->prepare("SELECT category_id FROM categories WHERE category_id = ? AND active = 1");
    $catCheck->bind_param("i", $category_id);
    $catCheck->execute();
    $catResult = $catCheck->get_result();
    if ($catResult->num_rows === 0) {
      $errors[] = 'Selected category is invalid or inactive.';
    }
    $catCheck->close();
  }

  // Brand Validation
  if (empty($brand_id) || $brand_id <= 0) {
    $errors[] = 'Please select a brand.';
  } else {
    $brandCheck = $conn->prepare("SELECT brand_id FROM brands WHERE brand_id = ? AND active = 1");
    $brandCheck->bind_param("i", $brand_id);
    $brandCheck->execute();
    $brandResult = $brandCheck->get_result();
    if ($brandResult->num_rows === 0) {
      $errors[] = 'Selected brand is invalid or inactive.';
    }
    $brandCheck->close();
  }

  // Description Validation (optional but if provided, check length)
  if (!empty($description) && strlen($description) > 5000) {
    $errors[] = 'Product description must not exceed 5000 characters.';
  }

  // Specifications Validation (optional but if provided, check length)
  if (!empty($specs) && strlen($specs) > 5000) {
    $errors[] = 'Technical specifications must not exceed 5000 characters.';
  }

  if (empty($errors)) {
    $conn->begin_transaction();

    try {
      $stmt = $conn->prepare("
        INSERT INTO products 
        (product_name, model, price, stock, image, featured, new_arrival, active, category_id, brand_id, description, specifications)
        VALUES 
        (?, ?, ?, ?, '', ?, ?, 1, ?, ?, ?, ?)
      ");
      $stmt->bind_param("ssdiisssss", $name, $model, $price, $stock, $featured, $new_arrival, $category_id, $brand_id, $description, $specs);
      
      if (!$stmt->execute()) {
        throw new Exception("Failed to insert product: " . $stmt->error);
      }

      $product_id = $conn->insert_id;
      $stmt->close();

      $uploadDir = "../../uploads/products/";
      if (!file_exists($uploadDir)) {
          mkdir($uploadDir, 0755, true);
      }

      $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
      $maxFileSize = 5 * 1024 * 1024;

      if (!empty($_FILES['image']['name'])) {
          if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
              switch ($_FILES['image']['error']) {
                  case UPLOAD_ERR_INI_SIZE:
                  case UPLOAD_ERR_FORM_SIZE:
                      throw new Exception('Main image file size exceeds the maximum allowed size (5MB).');
                  case UPLOAD_ERR_PARTIAL:
                      throw new Exception('Main image was only partially uploaded.');
                  case UPLOAD_ERR_NO_FILE:
                      break;
                  default:
                      throw new Exception('An error occurred while uploading the main image.');
              }
          } elseif ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
              $fileType = mime_content_type($_FILES['image']['tmp_name']);
              if (!in_array($fileType, $allowedTypes)) {
                  throw new Exception('Main image must be a valid image file (JPEG, PNG, GIF, or WebP).');
              } elseif ($_FILES['image']['size'] > $maxFileSize) {
                  throw new Exception('Main image size must be less than 5MB.');
              } else {
                  $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                  if (!in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                      throw new Exception('Main image file extension is not allowed.');
                  } else {
                      $mainImage = uniqid('main_', true) . '_' . time() . '.' . $fileExtension;
                      $target = $uploadDir . $mainImage;

                      if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                          $updateStmt = $conn->prepare("UPDATE products SET image=? WHERE product_id=?");
                          $updateStmt->bind_param("si", $mainImage, $product_id);
                          if (!$updateStmt->execute()) {
                              throw new Exception("Failed to update product image: " . $updateStmt->error);
                          }
                          $updateStmt->close();
                      } else {
                          throw new Exception('Failed to upload main image.');
                      }
                  }
              }
          }
      }

      if (!empty($_FILES['images']['name'][0])) {
          $maxAdditionalImages = 10;
          $uploadedCount = 0;
          
          foreach ($_FILES['images']['name'] as $key => $filename) {
              if ($uploadedCount >= $maxAdditionalImages) {
                  throw new Exception("Maximum {$maxAdditionalImages} additional images allowed.");
              }
              
              if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                  $fileType = mime_content_type($_FILES['images']['tmp_name'][$key]);
                  if (!in_array($fileType, $allowedTypes)) {
                      throw new Exception("Additional image #" . ($key + 1) . " must be a valid image file (JPEG, PNG, GIF, or WebP).");
                  }
                  
                  if ($_FILES['images']['size'][$key] > $maxFileSize) {
                      throw new Exception("Additional image #" . ($key + 1) . " size must be less than 5MB.");
                  }

                  $fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                  if (!in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                      throw new Exception("Additional image #" . ($key + 1) . " file extension is not allowed.");
                  }

                  $imgName = uniqid('img_', true) . '_' . time() . '_' . $key . '.' . $fileExtension;
                  $uploadPath = $uploadDir . $imgName;

                  if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $uploadPath)) {
                      $imgStmt = $conn->prepare("INSERT INTO product_images (product_id, filename) VALUES (?, ?)");
                      $imgStmt->bind_param("is", $product_id, $imgName);
                      if (!$imgStmt->execute()) {
                          throw new Exception("Failed to insert product image: " . $imgStmt->error);
                      }
                      $imgStmt->close();
                      $uploadedCount++;
                  } else {
                      throw new Exception("Failed to upload additional image #" . ($key + 1) . ".");
                  }
              } elseif ($_FILES['images']['error'][$key] !== UPLOAD_ERR_NO_FILE) {
                  throw new Exception("Error uploading additional image #" . ($key + 1) . ".");
              }
          }
      }

      $conn->commit();
      ob_end_clean();
      redirect('products.php');
    } catch (Exception $e) {
      $conn->rollback();
      $errors[] = $e->getMessage();
    }
  }
}

include '../../includes/admin_header.php';

$cats = $conn->query("SELECT * FROM categories WHERE active=1");
$brands = $conn->query("SELECT * FROM brands WHERE active=1");
?>

<div class="admin-content">
    <div class="container-fluid">
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
                <form method="POST" enctype="multipart/form-data" id="addProductForm" novalidate>
                    <div class="row g-4">
                        <div class="col-12">
                            <h5 class="section-title mb-3">
                                <i class="bi bi-info-circle me-2"></i>Basic Information
                            </h5>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="product_name" class="form-control" required
                                   placeholder="Enter product name" 
                                   value="<?php echo isset($_POST['product_name']) ? htmlspecialchars($_POST['product_name']) : ''; ?>"
                                   minlength="3" maxlength="255">
                            <small class="text-muted">Must be 3-255 characters long</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Model <span class="text-danger">*</span></label>
                            <input type="text" name="model" class="form-control" required
                                   placeholder="Enter model number" 
                                   value="<?php echo isset($_POST['model']) ? htmlspecialchars($_POST['model']) : ''; ?>"
                                   minlength="2" maxlength="100">
                            <small class="text-muted">Must be 2-100 characters long</small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" name="price" class="form-control" required
                                       placeholder="0.00" 
                                       value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>"
                                       min="0.01" max="999999.99">
                            </div>
                            <small class="text-muted">Must be greater than ₱0.00</small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Stock Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="stock" class="form-control" required
                                   placeholder="0" 
                                   value="<?php echo isset($_POST['stock']) ? htmlspecialchars($_POST['stock']) : ''; ?>"
                                   min="0" max="999999">
                            <small class="text-muted">Must be 0 or greater</small>
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

                        <div class="col-12">
                            <hr class="my-3">
                            <h5 class="section-title mb-3">
                                <i class="bi bi-file-text me-2"></i>Description & Specifications
                            </h5>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Product Description</label>
                            <textarea name="description" rows="6" class="form-control" 
                                      placeholder="Enter detailed product description..."
                                      maxlength="5000"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            <small class="text-muted">Maximum 5000 characters</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Technical Specifications</label>
                            <textarea name="specifications" rows="6" class="form-control" 
                                      placeholder="Enter technical specifications (one per line)..."
                                      maxlength="5000"><?php echo isset($_POST['specifications']) ? htmlspecialchars($_POST['specifications']) : ''; ?></textarea>
                            <small class="text-muted">Maximum 5000 characters</small>
                        </div>

                        <div class="col-12">
                            <hr class="my-3">
                            <h5 class="section-title mb-3">
                                <i class="bi bi-images me-2"></i>Product Images
                            </h5>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Main Product Image</label>
                            <input type="file" name="image" class="form-control" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                            <small class="text-muted">JPEG, PNG, GIF, or WebP (max 5MB). This will be the primary product image</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Additional Product Images</label>
                            <input type="file" name="images[]" class="form-control" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" multiple>
                            <small class="text-muted">JPEG, PNG, GIF, or WebP (max 5MB each, up to 10 images)</small>
                        </div>

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
    const form = document.getElementById('addProductForm');
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
        
        const priceInput = form.querySelector('input[name="price"]');
        if (priceInput && priceInput.value) {
            const price = parseFloat(priceInput.value);
            if (price <= 0) {
                isValid = false;
                showCustomError(priceInput, 'Price must be greater than ₱0.00');
                if (!firstInvalidField) firstInvalidField = priceInput;
            }
        }
        
        const stockInput = form.querySelector('input[name="stock"]');
        if (stockInput && stockInput.value !== '') {
            const stock = parseInt(stockInput.value);
            if (stock < 0) {
                isValid = false;
                showCustomError(stockInput, 'Stock quantity cannot be negative');
                if (!firstInvalidField) firstInvalidField = stockInput;
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
        if (field.name === 'product_name') {
            return 'Product name must be at least 3 characters long.';
        } else if (field.name === 'model') {
            return 'Model must be at least 2 characters long.';
        }
        return `Please enter at least ${field.minLength} characters.`;
    }
    
    function getMaxLengthMessage(field) {
        return `Please enter no more than ${field.maxLength} characters.`;
    }
    
    function getMinValueMessage(field) {
        if (field.name === 'price') {
            return 'Price must be greater than ₱0.00.';
        } else if (field.name === 'stock') {
            return 'Stock quantity cannot be negative.';
        }
        return `Value must be at least ${field.min}.`;
    }
    
    function getMaxValueMessage(field) {
        return `Value must not exceed ${field.max}.`;
    }
    
    function getTypeMismatchMessage(field) {
        if (field.type === 'email') {
            return 'Please enter a valid email address.';
        }
        return 'Please enter a valid value.';
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
