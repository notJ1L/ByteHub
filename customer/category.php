<?php
include '../includes/db.php';
include '../includes/header.php';

$category_slug = $_GET['cat'] ?? '';

$cat_stmt = $conn->prepare("SELECT * FROM categories WHERE slug = ?");
$cat_stmt->bind_param('s', $category_slug);
$cat_stmt->execute();
$category = $cat_stmt->get_result()->fetch_assoc();

if (!$category) {
    echo "<div class='container my-5'><div class='alert alert-danger'>Category not found.</div></div>";
    include '../includes/footer.php';
    exit;
}

$where_clauses = ['p.category_id = ?', 'p.active = 1'];
$params = [$category['category_id']];
$types = 'i';

if (!empty($_GET['brand'])) {
    $brands = implode(',', array_fill(0, count($_GET['brand']), '?'));
    $where_clauses[] = "p.brand_id IN ($brands)";
    foreach ($_GET['brand'] as $brand) {
        $params[] = $brand;
        $types .= 'i';
    }
}

if (!empty($_GET['min_price'])) {
    $where_clauses[] = 'p.price >= ?';
    $params[] = $_GET['min_price'];
    $types .= 'd';
}

if (!empty($_GET['max_price'])) {
    $where_clauses[] = 'p.price <= ?';
    $params[] = $_GET['max_price'];
    $types .= 'd';
}

$sort_options = [
    'bestselling' => 'p.product_id DESC',
    'price_asc' => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'name_asc' => 'p.product_name ASC',
    'name_desc' => 'p.product_name DESC'
];
$sort = $_GET['sort'] ?? 'bestselling';
$order_by = $sort_options[$sort] ?? $sort_options['bestselling'];

$sql = "SELECT p.*, b.name as brand_name FROM products p JOIN brands b ON p.brand_id = b.brand_id WHERE " . implode(' AND ', $where_clauses) . " ORDER BY $order_by";
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();

$brands_stmt = $conn->prepare("SELECT DISTINCT b.* FROM brands b JOIN products p ON b.brand_id = p.brand_id WHERE p.category_id = ? AND b.active = 1 AND p.active = 1");
$brands_stmt->bind_param('i', $category['category_id']);
$brands_stmt->execute();
$brands = $brands_stmt->get_result();

$price_range_stmt = $conn->prepare("SELECT MIN(price) as min_price, MAX(price) as max_price FROM products WHERE category_id = ? AND active = 1");
$price_range_stmt->bind_param('i', $category['category_id']);
$price_range_stmt->execute();
$price_range = $price_range_stmt->get_result()->fetch_assoc();
?>

<div class="container my-5">
    <div class="category-header mb-4">
        <h1 class="category-title"><?php echo htmlspecialchars($category['name']); ?></h1>
        <p class="category-description text-muted">Explore our collection of <?php echo strtolower(htmlspecialchars($category['name'])); ?> products</p>
    </div>

    <div class="row">
        <div class="col-lg-3 mb-4">
            <div class="filter-sidebar-modern">
                <div class="filter-header">
                    <h5 class="filter-title">
                        <i class="bi bi-funnel me-2"></i>Filters
                    </h5>
                    <a href="category.php?cat=<?php echo htmlspecialchars($category_slug); ?>" class="btn-reset-filters">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                </div>
                
                <form method="GET" id="filterForm">
                    <input type="hidden" name="cat" value="<?php echo htmlspecialchars($category_slug); ?>">
                    
                    <div class="filter-section">
                        <h6 class="filter-section-title">
                            <i class="bi bi-currency-dollar me-2"></i>Price Range
                        </h6>
                        <div class="price-inputs">
                            <div class="input-group mb-2">
                                <span class="input-group-text">₱</span>
                                <input type="number" 
                                       name="min_price" 
                                       class="form-control" 
                                       placeholder="Min" 
                                       value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>"
                                       min="0"
                                       step="0.01">
                            </div>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" 
                                       name="max_price" 
                                       class="form-control" 
                                       placeholder="Max" 
                                       value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>"
                                       min="0"
                                       step="0.01">
                            </div>
                        </div>
                        <?php if ($price_range): ?>
                            <small class="text-muted">
                                Range: ₱<?php echo number_format($price_range['min_price'], 2); ?> - ₱<?php echo number_format($price_range['max_price'], 2); ?>
                            </small>
                        <?php endif; ?>
                    </div>

                    <!-- Brand Filter -->
                    <div class="filter-section">
                        <h6 class="filter-section-title">
                            <i class="bi bi-building me-2"></i>Brands
                        </h6>
                        <div class="brand-filters">
                            <?php 
                            $brands->data_seek(0);
                            while ($brand = $brands->fetch_assoc()): 
                                $is_checked = in_array($brand['brand_id'], $_GET['brand'] ?? []);
                            ?>
                                <div class="form-check-modern">
                                    <input class="form-check-input-modern" 
                                           type="checkbox" 
                                           name="brand[]" 
                                           value="<?php echo $brand['brand_id']; ?>" 
                                           id="brand<?php echo $brand['brand_id']; ?>" 
                                           <?php echo $is_checked ? 'checked' : ''; ?>
                                           onchange="document.getElementById('filterForm').submit();">
                                    <label class="form-check-label-modern" for="brand<?php echo $brand['brand_id']; ?>">
                                        <?php echo htmlspecialchars($brand['name']); ?>
                                    </label>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn-apply-filters">
                        <i class="bi bi-check-circle me-2"></i>Apply Filters
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="product-toolbar mb-4">
                <div class="product-count">
                    <strong><?php echo $products->num_rows; ?></strong> product(s) found
                </div>
                <form method="GET" class="sort-form">
                    <input type="hidden" name="cat" value="<?php echo htmlspecialchars($category_slug); ?>">
                    <?php if (!empty($_GET['brand'])): ?>
                        <?php foreach ($_GET['brand'] as $brand): ?>
                            <input type="hidden" name="brand[]" value="<?php echo htmlspecialchars($brand); ?>">
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (!empty($_GET['min_price'])): ?>
                        <input type="hidden" name="min_price" value="<?php echo htmlspecialchars($_GET['min_price']); ?>">
                    <?php endif; ?>
                    <?php if (!empty($_GET['max_price'])): ?>
                        <input type="hidden" name="max_price" value="<?php echo htmlspecialchars($_GET['max_price']); ?>">
                    <?php endif; ?>
                    <div class="input-group" style="max-width: 250px;">
                        <label class="input-group-text bg-white border-end-0">
                            <i class="bi bi-sort-down"></i>
                        </label>
                        <select name="sort" class="form-select border-start-0" onchange="this.form.submit()">
                            <option value="bestselling" <?php echo ($sort == 'bestselling' ? 'selected' : ''); ?>>Bestselling</option>
                            <option value="price_asc" <?php echo ($sort == 'price_asc' ? 'selected' : ''); ?>>Price: Low to High</option>
                            <option value="price_desc" <?php echo ($sort == 'price_desc' ? 'selected' : ''); ?>>Price: High to Low</option>
                            <option value="name_asc" <?php echo ($sort == 'name_asc' ? 'selected' : ''); ?>>Name: A-Z</option>
                            <option value="name_desc" <?php echo ($sort == 'name_desc' ? 'selected' : ''); ?>>Name: Z-A</option>
                        </select>
                    </div>
                </form>
            </div>

            <?php if ($products->num_rows > 0): ?>
                <div class="row g-4">
                    <?php while ($product = $products->fetch_assoc()): ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="product-card-modern">
                                <div class="product-image-wrapper">
                                    <a href="product.php?id=<?php echo $product['product_id']; ?>">
                                        <img src="/bytehub/uploads/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                             class="product-image" 
                                             alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                    </a>
                                    <?php if ($product['new_arrival']): ?>
                                        <span class="badge-new">New</span>
                                    <?php endif; ?>
                                    <?php if ($product['featured']): ?>
                                        <span class="badge-featured">Featured</span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-body">
                                    <div class="product-brand"><?php echo htmlspecialchars($product['brand_name']); ?></div>
                                    <h5 class="product-title">
                                        <a href="product.php?id=<?php echo $product['product_id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($product['product_name']); ?>
                                        </a>
                                    </h5>
                                    <p class="product-price">₱<?php echo number_format($product['price'], 2); ?></p>
                                    <div class="product-actions">
                                        <?php if (isset($_SESSION['user_id'])): ?>
                                            <form method="post" action="cart.php" class="d-grid gap-2">
                                                <input type="hidden" name="id" value="<?php echo $product['product_id']; ?>">
                                                <input type="hidden" name="qty" value="1">
                                                <button type="submit" name="add_to_cart" class="btn btn-add-cart">
                                                    <i class="bi bi-cart-plus me-2"></i>Add to Cart
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <a href="product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-view-details w-100">
                                            <i class="bi bi-eye me-2"></i>View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: #dee2e6;"></i>
                    <h4 class="mt-3">No products found</h4>
                    <p class="text-muted">Try adjusting your filters to see more results.</p>
                    <a href="category.php?cat=<?php echo htmlspecialchars($category_slug); ?>" class="btn btn-primary-green mt-3">
                        <i class="bi bi-arrow-counterclockwise me-2"></i>Reset Filters
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
.category-header {
    text-align: center;
    padding-bottom: 2rem;
    border-bottom: 2px solid #e9ecef;
}

.category-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #212529;
    margin-bottom: 0.5rem;
}

.category-description {
    font-size: 1.1rem;
}

.filter-sidebar-modern {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    position: sticky;
    top: 100px;
}

.filter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
}

.filter-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0;
    color: #212529;
}

.btn-reset-filters {
    color: #6c757d;
    text-decoration: none;
    font-size: 0.875rem;
    transition: color 0.3s ease;
}

.btn-reset-filters:hover {
    color: var(--primary-green);
}

.filter-section {
    margin-bottom: 2rem;
}

.filter-section-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.price-inputs .form-control {
    border-radius: 8px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
    color: #212529 !important;
    background-color: #fff !important;
}

.price-inputs .form-control::placeholder {
    color: #6c757d !important;
    opacity: 1;
}

.price-inputs .form-control:focus {
    border-color: var(--primary-green);
    box-shadow: 0 0 0 0.2rem rgba(0, 77, 38, 0.15);
    color: #212529 !important;
    background-color: #fff !important;
}

.brand-filters {
    max-height: 300px;
    overflow-y: auto;
    padding-right: 0.5rem;
}

.brand-filters::-webkit-scrollbar {
    width: 6px;
}

.brand-filters::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.brand-filters::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.brand-filters::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.form-check-modern {
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    border-radius: 8px;
    transition: background-color 0.2s ease;
}

.form-check-modern:hover {
    background-color: #f8f9fa;
}

.form-check-input-modern {
    width: 1.25rem;
    height: 1.25rem;
    margin-right: 0.75rem;
    cursor: pointer;
    accent-color: var(--primary-green);
}

.form-check-label-modern {
    cursor: pointer;
    font-size: 0.95rem;
    color: #495057;
    user-select: none;
}

.btn-apply-filters {
    width: 100%;
    background: var(--primary-green);
    color: white;
    border: none;
    padding: 0.875rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    margin-top: 1rem;
}

.btn-apply-filters:hover {
    background: var(--secondary-green);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 77, 38, 0.3);
}

.product-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.product-count {
    color: #6c757d;
    font-size: 0.95rem;
}

.sort-form .form-select {
    border-radius: 8px;
    border: 2px solid #e9ecef;
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}

.sort-form .form-select:focus {
    border-color: var(--primary-green);
    box-shadow: 0 0 0 0.2rem rgba(0, 77, 38, 0.15);
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
}

.product-card-modern:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
}

.product-image-wrapper {
    position: relative;
    overflow: hidden;
    background: #f8f9fa;
    padding: 1.5rem;
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
    transform: scale(1.05);
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

.product-brand {
    font-size: 0.85rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.product-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
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

.product-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-green);
    margin-bottom: 1rem;
}

.product-actions {
    margin-top: auto;
}

.btn-add-cart {
    width: 100%;
    background: #212529;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-add-cart:hover {
    background: #000;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
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

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
}

@media (max-width: 991px) {
    .filter-sidebar-modern {
        position: static;
        margin-bottom: 2rem;
    }
    
    .product-toolbar {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .sort-form .input-group {
        max-width: 100% !important;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
