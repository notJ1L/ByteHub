<?php
include '../includes/db.php';
include '../includes/header.php';

$category_slug = $_GET['cat'] ?? '';

// Fetch category details
$cat_stmt = $conn->prepare("SELECT * FROM categories WHERE slug = ?");
$cat_stmt->bind_param('s', $category_slug);
$cat_stmt->execute();
$category = $cat_stmt->get_result()->fetch_assoc();

if (!$category) {
    echo "<p>Category not found.</p>";
    include '../includes/footer.php';
    exit;
}

// Filtering and Sorting
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
    'bestselling' => 'p.product_id DESC', // Placeholder for a real metric
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

// Fetch brands for filtering
$brands_stmt = $conn->prepare("SELECT * FROM brands WHERE active = 1");
$brands_stmt->execute();
$brands = $brands_stmt->get_result();
?>

<div class="row">
    <!-- Sidebar -->
    <div class="col-md-3">
        <div class="filter-sidebar">
            <h4>Filters</h4>
            <hr>
            <form method="GET">
                <input type="hidden" name="cat" value="<?php echo htmlspecialchars($category_slug); ?>">

                <!-- Price Filter -->
                <h5>Price</h5>
                <div class="d-flex">
                    <input type="number" name="min_price" class="form-control me-2" placeholder="Min" value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>">
                    <input type="number" name="max_price" class="form-control" placeholder="Max" value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>">
                </div>

                <!-- Brand Filter -->
                <h5 class="mt-4">Brand</h5>
                <?php while ($brand = $brands->fetch_assoc()): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="brand[]" value="<?php echo $brand['brand_id']; ?>" id="brand<?php echo $brand['brand_id']; ?>" <?php echo in_array($brand['brand_id'], $_GET['brand'] ?? []) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="brand<?php echo $brand['brand_id']; ?>">
                            <?php echo htmlspecialchars($brand['name']); ?>
                        </label>
                    </div>
                <?php endwhile; ?>

                <button type="submit" class="btn btn-primary-green w-100 mt-4">Apply Filters</button>
            </form>
        </div>
    </div>

    <!-- Product Grid -->
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3><?php echo htmlspecialchars($category['name']); ?></h3>
            <form method="GET" class="d-flex">
                <input type="hidden" name="cat" value="<?php echo htmlspecialchars($category_slug); ?>">
                <select name="sort" class="form-select" onchange="this.form.submit()">
                    <option value="bestselling" <?php echo ($sort == 'bestselling' ? 'selected' : ''); ?>>Bestselling</option>
                    <option value="price_asc" <?php echo ($sort == 'price_asc' ? 'selected' : ''); ?>>Price: Low to High</option>
                    <option value="price_desc" <?php echo ($sort == 'price_desc' ? 'selected' : ''); ?>>Price: High to Low</option>
                    <option value="name_asc" <?php echo ($sort == 'name_asc' ? 'selected' : ''); ?>>Name: A-Z</option>
                    <option value="name_desc" <?php echo ($sort == 'name_desc' ? 'selected' : ''); ?>>Name: Z-A</option>
                </select>
            </form>
        </div>

        <div class="row">
            <?php if ($products->num_rows > 0): ?>
                <?php while ($product = $products->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card product-card-new h-100">
                            <a href="product.php?id=<?php echo $product['product_id']; ?>">
                                <img src="/bytehub/uploads/products/<?php echo $product['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                            </a>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><a href="product.php?id=<?php echo $product['product_id']; ?>" class="text-dark text-decoration-none"><?php echo htmlspecialchars($product['product_name']); ?></a></h5>
                                <p class="text-muted"><?php echo htmlspecialchars($product['brand_name']); ?></p>
                                <p class="card-text fs-5 fw-bold mt-auto">₱<?php echo number_format($product['price'], 2); ?></p>
                                <form method="post" action="cart.php" class="d-grid gap-2">
                                    <input type="hidden" name="id" value="<?php echo $product['product_id']; ?>">
                                    <input type="hidden" name="qty" value="1">
                                    <button type="submit" name="buy_now" class="btn btn-dark">Buy Now</button>
                                    <button type="submit" name="add_to_cart" class="btn btn-outline-secondary">Add to Cart</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No products found matching your criteria.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

