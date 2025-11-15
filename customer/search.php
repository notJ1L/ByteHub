<?php
include '../includes/db.php';
include '../includes/header.php';

$keyword = $_GET['q'] ?? '';

// Prepare search query with proper escaping
$search_term = '%' . $keyword . '%';
$stmt = $conn->prepare("SELECT p.*, b.name as brand_name, c.name as category_name 
                        FROM products p 
                        LEFT JOIN brands b ON p.brand_id = b.brand_id 
                        LEFT JOIN categories c ON p.category_id = c.category_id
                        WHERE (p.product_name LIKE ? OR p.model LIKE ? OR p.description LIKE ?) 
                        AND p.active = 1
                        ORDER BY p.product_name ASC");
$stmt->bind_param('sss', $search_term, $search_term, $search_term);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container my-5">
    <!-- Search Header -->
    <div class="search-header mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="search-title">
                    <?php if (!empty($keyword)): ?>
                        Search Results for "<span class="text-primary-green"><?php echo htmlspecialchars($keyword); ?></span>"
                    <?php else: ?>
                        Search Products
                    <?php endif; ?>
                </h1>
                <p class="search-subtitle text-muted mb-0">
                    <?php if ($result->num_rows > 0): ?>
                        Found <?php echo $result->num_rows; ?> product<?php echo $result->num_rows != 1 ? 's' : ''; ?>
                    <?php else: ?>
                        No products found
                    <?php endif; ?>
                </p>
            </div>
        </div>
        
        <!-- Search Bar -->
        <div class="search-bar-modern">
            <form action="search.php" method="GET" class="search-form">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" 
                           name="q" 
                           class="form-control border-start-0 ps-0" 
                           placeholder="Search for products, brands, or categories..." 
                           value="<?php echo htmlspecialchars($keyword); ?>"
                           autofocus>
                    <button type="submit" class="btn btn-primary-green px-4">
                        <i class="bi bi-search me-2"></i>Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Search Results -->
    <?php if ($result->num_rows > 0): ?>
        <div class="row g-4">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="product-card-modern">
                        <div class="product-image-wrapper">
                            <a href="product.php?id=<?php echo $row['product_id']; ?>">
                                <img src="/bytehub/uploads/products/<?php echo htmlspecialchars($row['image']); ?>" 
                                     class="product-image" 
                                     alt="<?php echo htmlspecialchars($row['product_name']); ?>"
                                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%23ddd%22 width=%22200%22 height=%22200%22/%3E%3Ctext fill=%22%23999%22 font-family=%22sans-serif%22 font-size=%2214%22 dy=%2210.5%22 font-weight=%22bold%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22%3ENo Image%3C/text%3E%3C/svg%3E'">
                            </a>
                            <?php if ($row['new_arrival']): ?>
                                <span class="badge-new">New</span>
                            <?php endif; ?>
                            <?php if ($row['featured']): ?>
                                <span class="badge-featured">Featured</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-body">
                            <div class="product-meta mb-2">
                                <span class="product-brand"><?php echo htmlspecialchars($row['brand_name'] ?? 'Unknown Brand'); ?></span>
                                <span class="product-category"><?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?></span>
                            </div>
                            <h5 class="product-title">
                                <a href="product.php?id=<?php echo $row['product_id']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($row['product_name']); ?>
                                </a>
                            </h5>
                            <?php if (!empty($row['model'])): ?>
                                <p class="product-model text-muted mb-2">Model: <?php echo htmlspecialchars($row['model']); ?></p>
                            <?php endif; ?>
                            <p class="product-price">₱<?php echo number_format($row['price'], 2); ?></p>
                            <div class="product-stock mb-3">
                                <?php if ($row['stock'] > 0): ?>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>In Stock (<?php echo $row['stock']; ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle me-1"></i>Out of Stock
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="product-actions">
                                <a href="product.php?id=<?php echo $row['product_id']; ?>" class="btn btn-view-details w-100">
                                    <i class="bi bi-eye me-2"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-search-state">
            <div class="text-center py-5">
                <i class="bi bi-search" style="font-size: 5rem; color: #dee2e6;"></i>
                <h3 class="mt-4 mb-3">No products found</h3>
                <p class="text-muted mb-4">
                    <?php if (!empty($keyword)): ?>
                        We couldn't find any products matching "<strong><?php echo htmlspecialchars($keyword); ?></strong>"
                    <?php else: ?>
                        Start searching for products above
                    <?php endif; ?>
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="index.php" class="btn btn-primary-green">
                        <i class="bi bi-house me-2"></i>Browse All Products
                    </a>
                    <a href="category.php?cat=cpus" class="btn btn-outline-secondary">
                        <i class="bi bi-grid me-2"></i>View Categories
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
.search-header {
    padding-bottom: 2rem;
    border-bottom: 2px solid #e9ecef;
}

.search-title {
    font-size: 2rem;
    font-weight: 700;
    color: #212529;
    margin-bottom: 0.5rem;
}

.text-primary-green {
    color: var(--primary-green) !important;
}

.search-subtitle {
    font-size: 1rem;
}

.search-bar-modern {
    margin-top: 1.5rem;
}

.search-form .input-group-lg .form-control {
    border-radius: 12px 0 0 12px;
    border: 2px solid #e9ecef;
    padding: 0.875rem 1.25rem;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.search-form .input-group-lg .form-control:focus {
    border-color: var(--primary-green);
    box-shadow: 0 0 0 0.2rem rgba(0, 77, 38, 0.15);
    outline: none;
}

.search-form .input-group-text {
    border: 2px solid #e9ecef;
    border-right: none;
    border-radius: 12px 0 0 12px;
    background: white;
}

.search-form .btn-primary-green {
    border-radius: 0 12px 12px 0;
    padding: 0.875rem 2rem;
    font-weight: 600;
    border: 2px solid var(--primary-green);
}

.product-card-modern {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    border: 1px solid #e9ecef;
}

.product-card-modern:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
    border-color: var(--primary-green);
}

.product-image-wrapper {
    position: relative;
    overflow: hidden;
    background: #f8f9fa;
    padding: 2rem;
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: transform 0.3s ease;
}

.product-card-modern:hover .product-image {
    transform: scale(1.1);
}

.badge-new,
.badge-featured {
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

.product-body {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.product-meta {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.product-brand {
    font-size: 0.75rem;
    color: var(--primary-green);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

.product-category {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: capitalize;
}

.product-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #212529;
    line-height: 1.4;
}

.product-title a {
    color: inherit;
    transition: color 0.3s ease;
}

.product-title a:hover {
    color: var(--primary-green);
}

.product-model {
    font-size: 0.875rem;
}

.product-price {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--primary-green);
    margin-bottom: 0.75rem;
}

.product-stock {
    margin-bottom: 1rem;
}

.product-actions {
    margin-top: auto;
}

.btn-view-details {
    background: var(--primary-green);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-view-details:hover {
    background: var(--secondary-green);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 77, 38, 0.3);
}

.empty-search-state {
    background: white;
    border-radius: 16px;
    padding: 4rem 2rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
}

@media (max-width: 768px) {
    .search-title {
        font-size: 1.5rem;
    }
    
    .search-form .input-group-lg {
        flex-direction: column;
    }
    
    .search-form .input-group-lg .form-control,
    .search-form .input-group-lg .input-group-text,
    .search-form .btn-primary-green {
        border-radius: 12px !important;
        margin-bottom: 0.5rem;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
