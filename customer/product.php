<?php
include '../includes/db.php';
include '../includes/functions.php';
include '../includes/header.php';

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT p.*, b.name as brand_name, c.name as category_name FROM products p JOIN brands b ON p.brand_id = b.brand_id LEFT JOIN categories c ON p.category_id = c.category_id WHERE p.product_id = ? AND p.active = 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $p = $result->fetch_assoc();

    $gallery_stmt = $conn->prepare("SELECT filename FROM product_images WHERE product_id = ?");
    $gallery_stmt->bind_param('i', $id);
    $gallery_stmt->execute();
    $gallery = $gallery_stmt->get_result();
    
    // Fetch reviews
    $reviews_stmt = $conn->prepare("
        SELECT r.*, u.username 
        FROM reviews r 
        JOIN users u ON r.user_id = u.user_id 
        WHERE r.product_id = ? 
        ORDER BY r.created_at DESC
    ");
    $reviews_stmt->bind_param('i', $id);
    $reviews_stmt->execute();
    $reviews_result = $reviews_stmt->get_result();
    
    // Calculate average rating
    $avg_rating_stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE product_id = ?");
    $avg_rating_stmt->bind_param('i', $id);
    $avg_rating_stmt->execute();
    $avg_result = $avg_rating_stmt->get_result();
    $avg_data = $avg_result->fetch_assoc();
    $avg_rating = $avg_data['avg_rating'] ? round($avg_data['avg_rating'], 1) : 0;
    $total_reviews = $avg_data['total_reviews'];
    
    // Check if user can review
    $can_review = false;
    $user_has_review = false;
    $user_review_id = null;
    if (is_logged_in() && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $purchase_check = $conn->prepare("
            SELECT o.order_id 
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'Completed'
            LIMIT 1
        ");
        $purchase_check->bind_param("ii", $user_id, $id);
        $purchase_check->execute();
        $purchase_result = $purchase_check->get_result();
        $can_review = $purchase_result->num_rows > 0;
        
        $user_review_check = $conn->prepare("SELECT review_id FROM reviews WHERE product_id = ? AND user_id = ?");
        $user_review_check->bind_param("ii", $id, $user_id);
        $user_review_check->execute();
        $user_review_result = $user_review_check->get_result();
        if ($user_review_result->num_rows > 0) {
            $user_has_review = true;
            $user_review_id = $user_review_result->fetch_assoc()['review_id'];
        }
    }
?>

<div class="container my-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <?php if ($p['category_name']): ?>
                <li class="breadcrumb-item"><a href="category.php?cat=<?php echo urlencode(strtolower($p['category_name'])); ?>"><?php echo htmlspecialchars($p['category_name']); ?></a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($p['product_name']); ?></li>
        </ol>
    </nav>

    <div class="row g-4 mb-5">
        <!-- Product Images -->
        <div class="col-lg-6">
            <div class="product-image-container">
                <div class="main-image-wrapper">
                    <img id="mainProductImage" 
                         src="/bytehub/uploads/products/<?php echo htmlspecialchars($p['image']); ?>" 
                         class="main-product-image" 
                         alt="<?php echo htmlspecialchars($p['product_name']); ?>"
                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22400%22%3E%3Crect fill=%22%23ddd%22 width=%22400%22 height=%22400%22/%3E%3Ctext fill=%22%23999%22 font-family=%22sans-serif%22 font-size=%2220%22 dy=%2210.5%22 font-weight=%22bold%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22%3ENo Image%3C/text%3E%3C/svg%3E'">
                    <?php if ($p['new_arrival']): ?>
                        <span class="product-badge badge-new">New Arrival</span>
                    <?php endif; ?>
                    <?php if ($p['featured']): ?>
                        <span class="product-badge badge-featured">Featured</span>
                    <?php endif; ?>
                </div>
                <?php if ($gallery->num_rows > 0): ?>
                    <div class="thumbnail-gallery mt-3">
                        <div class="thumbnail-wrapper">
                            <img src="/bytehub/uploads/products/<?php echo htmlspecialchars($p['image']); ?>" 
                                 class="thumbnail-image active" 
                                 onclick="changeImage(this.src)"
                                 alt="Main image">
                            <?php while ($g = $gallery->fetch_assoc()): ?>
                                <img src="/bytehub/uploads/products/<?php echo htmlspecialchars($g['filename']); ?>" 
                                     class="thumbnail-image" 
                                     onclick="changeImage(this.src)"
                                     alt="Gallery image">
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-lg-6">
            <div class="product-info">
                <div class="product-brand-category mb-3">
                    <span class="badge bg-light text-dark me-2"><?php echo htmlspecialchars($p['brand_name']); ?></span>
                    <?php if ($p['category_name']): ?>
                        <span class="badge bg-info"><?php echo htmlspecialchars($p['category_name']); ?></span>
                    <?php endif; ?>
                </div>
                
                <h1 class="product-name mb-3"><?php echo htmlspecialchars($p['product_name']); ?></h1>
                
                <?php if (!empty($p['model'])): ?>
                    <p class="product-model text-muted mb-3">
                        <i class="bi bi-tag me-2"></i>Model: <?php echo htmlspecialchars($p['model']); ?>
                    </p>
                <?php endif; ?>
                
                <!-- Rating Display -->
                <?php if ($total_reviews > 0): ?>
                    <div class="product-rating mb-3">
                        <div class="d-flex align-items-center">
                            <div class="rating-stars me-2">
                                <?php 
                                $full_stars = floor($avg_rating);
                                $half_star = ($avg_rating - $full_stars) >= 0.5;
                                for ($i = 0; $i < $full_stars; $i++) echo '<i class="bi bi-star-fill text-warning"></i>';
                                if ($half_star) echo '<i class="bi bi-star-half text-warning"></i>';
                                for ($i = $full_stars + ($half_star ? 1 : 0); $i < 5; $i++) echo '<i class="bi bi-star text-warning"></i>';
                                ?>
                            </div>
                            <span class="rating-value fw-bold me-2"><?php echo $avg_rating; ?></span>
                            <span class="rating-count text-muted">(<?php echo $total_reviews; ?> review<?php echo $total_reviews != 1 ? 's' : ''; ?>)</span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="product-price-section mb-4">
                    <div class="price-display">
                        <span class="price-label">Price</span>
                        <span class="price-value">₱<?php echo number_format($p['price'], 2); ?></span>
                    </div>
                </div>
                
                <div class="product-stock-section mb-4">
                    <?php if ($p['stock'] > 0): ?>
                        <div class="stock-badge in-stock">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <strong>In Stock</strong> - <?php echo $p['stock']; ?> available
                        </div>
                    <?php else: ?>
                        <div class="stock-badge out-of-stock">
                            <i class="bi bi-x-circle-fill me-2"></i>
                            <strong>Out of Stock</strong>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Add to Cart Section -->
                <?php if ($p['stock'] > 0): ?>
                    <form method="post" action="cart.php" class="product-actions-form">
                        <input type="hidden" name="id" value="<?php echo $p['product_id']; ?>">
                        
                        <div class="quantity-selector-modern mb-4">
                            <label for="quantity" class="form-label fw-semibold">
                                <i class="bi bi-123 me-2"></i>Quantity
                            </label>
                            <div class="quantity-controls">
                                <button type="button" class="btn btn-outline-secondary" onclick="changeQty(-1)">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" 
                                       name="qty" 
                                       id="quantity" 
                                       value="1" 
                                       min="1" 
                                       max="<?php echo $p['stock']; ?>" 
                                       class="form-control text-center"
                                       readonly>
                                <button type="button" class="btn btn-outline-secondary" onclick="changeQty(1)">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                            <small class="text-muted">Max: <?php echo $p['stock']; ?> units</small>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" name="add_to_cart" class="btn btn-add-to-cart btn-lg">
                                <i class="bi bi-cart-plus me-2"></i>Add to Cart
                            </button>
                            <button type="submit" name="buy_now" class="btn btn-buy-now btn-lg">
                                <i class="bi bi-lightning-fill me-2"></i>Buy Now
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        This product is currently out of stock. Please check back later.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Product Details Sections -->
    <div class="product-details-sections">
        <!-- Description Section -->
        <?php if (!empty($p['description'])): ?>
        <div class="product-section-card">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="bi bi-file-text me-2"></i>Description
                </h3>
            </div>
            <div class="section-content">
                <div class="product-description"><?php echo nl2br(htmlspecialchars($p['description'])); ?></div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Specifications Section -->
        <?php
        $specs = $p['specifications'];
        if (!empty($specs)):
        ?>
        <div class="product-section-card">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="bi bi-list-ul me-2"></i>Specifications
                </h3>
            </div>
            <div class="section-content">
                <?php
                $lines = explode("\n", $specs);
                if (count($lines) > 0) {
                    echo '<div class="specs-table-wrapper">';
                    echo '<table class="table table-specs">';
                    $isHeader = true;
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (empty($line)) continue;
                        
                        if (preg_match('/^[-|:]+$/', $line)) {
                            $isHeader = false;
                            continue;
                        }
                        
                        $cells = explode('|', $line);
                        $cells = array_filter($cells, function($cell) {
                            return trim($cell) !== '';
                        });
                        $cells = array_values($cells);
                        
                        if (count($cells) >= 2) {
                            echo '<tr>';
                            foreach ($cells as $cell) {
                                $cell = trim($cell);
                                if ($isHeader) {
                                    echo '<th>' . htmlspecialchars($cell) . '</th>';
                                } else {
                                    echo '<td>' . htmlspecialchars($cell) . '</td>';
                                }
                            }
                            echo '</tr>';
                            $isHeader = false;
                        }
                    }
                    echo '</table>';
                    echo '</div>';
                } else {
                    echo '<p style="white-space: pre-wrap;">' . htmlspecialchars($specs) . '</p>';
                }
                ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Reviews Section -->
        <div class="product-section-card">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="bi bi-star me-2"></i>Reviews
                    <?php if ($total_reviews > 0): ?>
                        <span class="badge bg-primary ms-2"><?php echo $total_reviews; ?></span>
                    <?php endif; ?>
                </h3>
            </div>
            <div class="section-content">
                <?php if ($total_reviews > 0): ?>
                    <div class="rating-summary-compact">
                        <div class="rating-display-compact">
                            <div class="rating-number-compact"><?php echo $avg_rating; ?></div>
                            <div class="rating-stars-compact">
                                <?php 
                                $full_stars = floor($avg_rating);
                                $half_star = ($avg_rating - $full_stars) >= 0.5;
                                for ($i = 0; $i < $full_stars; $i++) echo '<i class="bi bi-star-fill"></i>';
                                if ($half_star) echo '<i class="bi bi-star-half"></i>';
                                for ($i = $full_stars + ($half_star ? 1 : 0); $i < 5; $i++) echo '<i class="bi bi-star"></i>';
                                ?>
                            </div>
                            <div class="rating-count-compact">Based on <?php echo $total_reviews; ?> review<?php echo $total_reviews != 1 ? 's' : ''; ?></div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Add Review Form -->
                <?php if (is_logged_in()): ?>
                    <?php if ($can_review && !$user_has_review): ?>
                        <div class="add-review-compact">
                            <h5 class="mb-2">Write a Review</h5>
                            <form method="POST" action="add_review.php?product_id=<?php echo $id; ?>">
                                <div class="mb-2">
                                    <label for="rating" class="form-label fw-semibold small">Rating</label>
                                    <select name="rating" id="rating" class="form-select form-select-sm">
                                        <option value="">Select rating...</option>
                                        <option value="5">5 Stars - Excellent</option>
                                        <option value="4">4 Stars - Very Good</option>
                                        <option value="3">3 Stars - Good</option>
                                        <option value="2">2 Stars - Fair</option>
                                        <option value="1">1 Star - Poor</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label for="comment" class="form-label fw-semibold small">Your Review</label>
                                    <textarea name="comment" id="comment" class="form-control form-control-sm" rows="4" placeholder="Share your experience..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary-green btn-sm">
                                    <i class="bi bi-send me-1"></i>Submit Review
                                </button>
                            </form>
                        </div>
                    <?php elseif (!$can_review): ?>
                        <div class="alert alert-info py-2 mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <small>You must purchase this product before you can leave a review.</small>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info py-2 mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>Please <a href="login.php" class="alert-link">login</a> to leave a review.</small>
                    </div>
                <?php endif; ?>
                
                <!-- Reviews List -->
                <div class="reviews-list-compact">
                    <?php if ($reviews_result->num_rows > 0): ?>
                        <?php while ($review = $reviews_result->fetch_assoc()): ?>
                            <div class="review-item">
                                <div class="review-header-compact">
                                    <div class="reviewer-info-compact">
                                        <div class="reviewer-avatar-compact">
                                            <i class="bi bi-person-circle"></i>
                                        </div>
                                        <div>
                                            <h6 class="reviewer-name-compact mb-0"><?php echo htmlspecialchars($review['username']); ?></h6>
                                            <div class="review-rating-compact">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="bi bi-star<?php echo $i <= $review['rating'] ? '-fill text-warning' : ''; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="review-date-compact text-muted">
                                        <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                                    </small>
                                </div>
                                <div class="review-body-compact">
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                </div>
                                <?php if (is_logged_in() && isset($_SESSION['user_id']) && $_SESSION['user_id'] == $review['user_id']): ?>
                                    <div class="review-actions-compact">
                                        <a href="edit_review.php?id=<?php echo $review['review_id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil me-1"></i>Edit
                                        </a>
                                        <a href="delete_review.php?id=<?php echo $review['review_id']; ?>&product_id=<?php echo $id; ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Are you sure you want to delete this review?');">
                                            <i class="bi bi-trash me-1"></i>Delete
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-reviews-compact">
                            <i class="bi bi-inbox"></i>
                            <p class="mt-2 mb-0 text-muted small">No reviews yet. Be the first to review this product!</p>
                        </div>
                    <?php endif; ?>
                </div>
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

.product-image-container {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid #e9ecef;
}

.main-image-wrapper {
    position: relative;
    background: #f8f9fa;
    border-radius: 12px;
    padding: 2rem;
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.main-product-image {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: transform 0.3s ease;
}

.main-image-wrapper:hover .main-product-image {
    transform: scale(1.05);
}

.product-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    z-index: 2;
}

.badge-new {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
}

.badge-featured {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    color: white;
}

.thumbnail-gallery {
    overflow-x: auto;
    padding: 0.5rem 0;
}

.thumbnail-wrapper {
    display: flex;
    gap: 0.75rem;
}

.thumbnail-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid #e9ecef;
    cursor: pointer;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.thumbnail-image:hover,
.thumbnail-image.active {
    border-color: var(--primary-green);
    transform: scale(1.05);
}

.product-info {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid #e9ecef;
    height: 100%;
}

.product-name {
    font-size: 2rem;
    font-weight: 700;
    color: #212529;
    line-height: 1.2;
}

.product-model {
    font-size: 0.95rem;
}

.product-price-section {
    padding: 1.5rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
}

.price-display {
    display: flex;
    flex-direction: column;
}

.price-label {
    font-size: 0.875rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.price-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--primary-green);
}

.stock-badge {
    padding: 1rem 1.5rem;
    border-radius: 12px;
    font-size: 1rem;
    display: flex;
    align-items: center;
}

.stock-badge.in-stock {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.stock-badge.out-of-stock {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.quantity-selector-modern {
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 12px;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.75rem;
}

.quantity-controls input {
    width: 80px;
    font-size: 1.25rem;
    font-weight: 600;
    border: 2px solid #dee2e6;
}

.quantity-controls .btn {
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}

.btn-add-to-cart {
    background: var(--primary-green);
    color: white;
    border: none;
    border-radius: 12px;
    padding: 1rem;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
}

.btn-add-to-cart:hover {
    background: var(--secondary-green);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 77, 38, 0.3);
}

.btn-buy-now {
    background: #212529;
    color: white;
    border: none;
    border-radius: 12px;
    padding: 1rem;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
}

.btn-buy-now:hover {
    background: #000;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

/* Modern Product Details Sections */
.product-details-sections {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    margin-top: 2rem;
}

.product-section-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e9ecef;
    overflow: hidden;
    transition: box-shadow 0.3s ease;
}

.product-section-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.section-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-bottom: 2px solid #e9ecef;
    padding: 1rem 1.25rem;
}

.section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #212529;
    margin: 0;
    display: flex;
    align-items: center;
}

.section-title i {
    color: var(--primary-green);
    font-size: 1.2rem;
}

.section-content {
    padding: 1.25rem;
}

.product-description {
    margin: 0;
    padding: 0;
    line-height: 1.7;
    color: #495057;
    white-space: pre-wrap;
    font-size: 0.95rem;
}

.specs-table-wrapper {
    overflow-x: auto;
}

.table-specs {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.table-specs {
    margin-bottom: 0 !important;
}

.table-specs th {
    background: #f8f9fa;
    font-weight: 600;
    padding: 0.625rem 0.875rem;
    border: 1px solid #dee2e6;
    color: #495057;
    font-size: 0.875rem;
}

.table-specs td {
    padding: 0.625rem 0.875rem;
    border: 1px solid #dee2e6;
    color: #212529;
    font-size: 0.9rem;
}

.table-specs tr:first-child th {
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}

.table-specs tr:last-child td {
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
}

/* Rating Summary */
.rating-summary-compact {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 10px;
    padding: 1rem;
    text-align: center;
    margin-bottom: 1.25rem;
}

.rating-display-compact {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.rating-number-compact {
    font-size: 2.25rem;
    font-weight: 700;
    color: var(--primary-green);
    line-height: 1;
}

.rating-stars-compact {
    font-size: 1.15rem;
    color: #ffc107;
    line-height: 1;
}

.rating-stars-compact i {
    margin: 0 0.1rem;
}

.rating-count-compact {
    color: #6c757d;
    font-size: 0.875rem;
}

/* Add Review Form */
.add-review-compact {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1rem;
    border: 1px solid #e9ecef;
    margin-bottom: 1.25rem;
}

.add-review-compact h5 {
    font-size: 1rem;
    margin-bottom: 0.75rem;
    font-weight: 600;
    color: #212529;
}

/* Reviews List */
.reviews-list-compact {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.review-item {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 1rem;
    transition: all 0.3s ease;
}

.review-item:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border-color: var(--primary-green);
}

.review-header-compact {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #dee2e6;
}

.reviewer-info-compact {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.reviewer-avatar-compact {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.reviewer-name-compact {
    font-weight: 600;
    color: #212529;
    margin: 0;
    font-size: 0.95rem;
}

.review-rating-compact {
    color: #ffc107;
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

.review-date-compact {
    font-size: 0.85rem;
}

.review-body-compact {
    color: #495057;
    line-height: 1.7;
    font-size: 0.9rem;
}

.review-actions-compact {
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid #dee2e6;
    display: flex;
    gap: 0.5rem;
}

.empty-reviews-compact {
    text-align: center;
    padding: 2rem 1rem;
    color: #6c757d;
}

.empty-reviews-compact i {
    font-size: 2.5rem;
    color: #dee2e6;
    margin-bottom: 0.5rem;
}

@media (max-width: 768px) {
    .product-name {
        font-size: 1.5rem;
    }
    
    .price-value {
        font-size: 2rem;
    }
    
    .product-details-sections {
        gap: 1rem;
    }
    
    .section-content {
        padding: 1rem;
    }
    
    .section-header {
        padding: 0.875rem 1rem;
    }
}
</style>

<script>
function changeImage(src) {
    document.getElementById("mainProductImage").src = src;
    // Update active thumbnail
    document.querySelectorAll('.thumbnail-image').forEach(img => {
        img.classList.remove('active');
        if (img.src === src) {
            img.classList.add('active');
        }
    });
}

function changeQty(amount) {
    const qtyInput = document.getElementById('quantity');
    let currentValue = parseInt(qtyInput.value);
    let newValue = currentValue + amount;
    if (newValue < 1) newValue = 1;
    if (newValue > <?php echo $p['stock']; ?>) newValue = <?php echo $p['stock']; ?>;
    qtyInput.value = newValue;
}
</script>

<?php
} else {
    echo '<div class="container my-5"><div class="alert alert-danger">Product not found.</div></div>';
}

include '../includes/footer.php';
?>
