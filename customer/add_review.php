<?php
include '../includes/db.php';
include '../includes/functions.php';

// All redirects and checks must happen BEFORE including header.php
if (!is_logged_in()) {
    redirect('login.php');
}

$product_id = $_GET['product_id'] ?? 0;
$user_id = $_SESSION['user_id'];
$errors = [];

// Fetch product information
$product_stmt = $conn->prepare("SELECT p.*, b.name as brand_name FROM products p JOIN brands b ON p.brand_id = b.brand_id WHERE p.product_id = ? AND p.active = 1");
$product_stmt->bind_param("i", $product_id);
$product_stmt->execute();
$product_result = $product_stmt->get_result();
if ($product_result->num_rows == 0) {
    // Need to include header before showing error
    include '../includes/header.php';
    echo '<div class="container my-5"><div class="alert alert-danger">Product not found.</div></div>';
    include '../includes/footer.php';
    exit;
}
$product = $product_result->fetch_assoc();

// Check if user already has a review for this product
$existing_review = $conn->prepare("SELECT review_id FROM reviews WHERE product_id = ? AND user_id = ?");
$existing_review->bind_param("ii", $product_id, $user_id);
$existing_review->execute();
$existing_result = $existing_review->get_result();
if ($existing_result->num_rows > 0) {
    redirect('product.php?id=' . $product_id);
}

// Check if user has purchased this product before (any status except Cancelled)
$stmt = $conn->prepare("
    SELECT o.order_id 
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    WHERE o.user_id = ? AND oi.product_id = ? AND o.status != 'Cancelled'
");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();
$can_review = $result->num_rows > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    
    // Validate rating
    if (empty($rating) || $rating < 1 || $rating > 5) {
        $errors[] = 'Please select a valid rating.';
    }
    
    // Validate comment
    if (empty($comment)) {
        $errors[] = 'Comment is required.';
    }
    
    if (empty($errors)) {
        // Filter bad words using regex
        $comment = filter_bad_words($comment);
        
        $stmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $product_id, $user_id, $rating, $comment);
        $stmt->execute();

        redirect('product.php?id=' . $product_id);
    }
}

// Now include header after all redirects are done
include '../includes/header.php';

// Show error if user hasn't purchased
if (!$can_review) {
    echo '<div class="container my-5"><div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>You can only review products you have purchased.</div></div>';
    include '../includes/footer.php';
    exit;
}
?>

<div class="container my-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="product.php?id=<?php echo $product_id; ?>"><?php echo htmlspecialchars($product['product_name']); ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Write a Review</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Product Info Card -->
            <div class="review-product-card mb-4">
                <div class="d-flex align-items-center">
                    <div class="review-product-image">
                        <img src="/bytehub/uploads/products/<?php echo htmlspecialchars($product['image']); ?>" 
                             alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22%3E%3Crect fill=%22%23ddd%22 width=%22100%22 height=%22100%22/%3E%3Ctext fill=%22%23999%22 font-family=%22sans-serif%22 font-size=%2214%22 dy=%2210.5%22 font-weight=%22bold%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22%3ENo Image%3C/text%3E%3C/svg%3E'">
                    </div>
                    <div class="review-product-info ms-3">
                        <h5 class="mb-1"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                        <p class="text-muted mb-0 small">
                            <span class="badge bg-light text-dark me-2"><?php echo htmlspecialchars($product['brand_name']); ?></span>
                            <span class="text-muted">₱<?php echo number_format($product['price'], 2); ?></span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Review Form Card -->
            <div class="review-form-card">
                <div class="review-form-header">
                    <h3 class="review-form-title">
                        <i class="bi bi-star me-2"></i>Write Your Review
                    </h3>
                    <p class="text-muted mb-0 small">Share your experience with this product</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-modern">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <div>
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" class="review-form">
                    <div class="mb-4">
                        <label for="rating" class="form-label fw-semibold">
                            <i class="bi bi-star-fill text-warning me-2"></i>Rating
                        </label>
                        <select name="rating" id="rating" class="form-select form-select-lg" required>
                            <option value="">Select your rating...</option>
                            <option value="5" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 5) ? 'selected' : ''; ?>>5 Stars - Excellent</option>
                            <option value="4" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 4) ? 'selected' : ''; ?>>4 Stars - Very Good</option>
                            <option value="3" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 3) ? 'selected' : ''; ?>>3 Stars - Good</option>
                            <option value="2" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 2) ? 'selected' : ''; ?>>2 Stars - Fair</option>
                            <option value="1" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 1) ? 'selected' : ''; ?>>1 Star - Poor</option>
                        </select>
                        <small class="form-text text-muted">How would you rate this product?</small>
                    </div>

                    <div class="mb-4">
                        <label for="comment" class="form-label fw-semibold">
                            <i class="bi bi-chat-left-text me-2"></i>Your Review
                        </label>
                        <textarea name="comment" 
                                  id="comment" 
                                  class="form-control" 
                                  rows="6" 
                                  placeholder="Share your thoughts about this product. What did you like? What could be improved?"
                                  required><?php echo isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : ''; ?></textarea>
                        <small class="form-text text-muted">Be honest and helpful to other customers</small>
                    </div>

                    <div class="review-form-actions">
                        <a href="product.php?id=<?php echo $product_id; ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary-green">
                            <i class="bi bi-send me-2"></i>Submit Review
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
.breadcrumb {
    background: transparent;
    padding: 0;
    margin-bottom: 1.5rem;
}

.breadcrumb-item a {
    color: var(--primary-green);
    text-decoration: none;
}

.breadcrumb-item.active {
    color: #6c757d;
}

.review-product-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e9ecef;
}

.review-product-image {
    width: 80px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.review-product-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.review-product-info h5 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #212529;
    margin: 0;
}

.review-form-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid #e9ecef;
    overflow: hidden;
}

.review-form-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-bottom: 2px solid #e9ecef;
    padding: 1.5rem 2rem;
}

.review-form-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #212529;
    margin: 0 0 0.5rem 0;
    display: flex;
    align-items: center;
}

.review-form-title i {
    color: var(--primary-green);
    font-size: 1.6rem;
}

.review-form {
    padding: 2rem;
}

.review-form .form-label {
    font-size: 1rem;
    color: #212529;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
}

.review-form .form-label i {
    font-size: 1.1rem;
}

.review-form .form-select,
.review-form .form-control {
    border: 2px solid #dee2e6;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
    color: #212529 !important;
    background-color: #ffffff !important;
}

.review-form .form-select:focus,
.review-form .form-control:focus {
    border-color: var(--primary-green);
    box-shadow: 0 0 0 0.2rem rgba(0, 77, 38, 0.25);
    outline: none;
}

.review-form textarea {
    resize: vertical;
    min-height: 150px;
}

.review-form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e9ecef;
}

.review-form-actions .btn {
    padding: 0.75rem 2rem;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.review-form-actions .btn-outline-secondary {
    border: 2px solid #dee2e6;
}

.review-form-actions .btn-outline-secondary:hover {
    background-color: #6c757d;
    border-color: #6c757d;
    color: white;
}

.alert-modern {
    border-radius: 10px;
    border: none;
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.15);
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.5rem;
}

.alert-modern ul {
    padding-left: 1.5rem;
}

.alert-modern li {
    margin-bottom: 0.25rem;
}

@media (max-width: 768px) {
    .review-form-header {
        padding: 1.25rem 1.5rem;
    }
    
    .review-form {
        padding: 1.5rem;
    }
    
    .review-form-actions {
        flex-direction: column;
    }
    
    .review-form-actions .btn {
        width: 100%;
    }
    
    .review-product-card {
        padding: 1rem;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
