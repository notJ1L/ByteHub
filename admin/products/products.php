<?php
include '../../includes/db.php';
include '../../includes/functions.php';

if (!isAdmin()) {
    redirect('../index.php');
}

include '../../includes/admin_header.php';

$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$brand_filter = $_GET['brand'] ?? '';
$status_filter = $_GET['status'] ?? '';
$sort_order = $_GET['sort'] ?? 'newest';

// Fetch categories and brands for dropdowns
$categories_result = $conn->query("SELECT * FROM categories WHERE active = 1 ORDER BY name");
$brands_result = $conn->query("SELECT * FROM brands WHERE active = 1 ORDER BY name");

$sql = "SELECT p.*, c.name AS category_name, b.name AS brand_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        WHERE (p.product_name LIKE ? OR p.model LIKE ?)";

$params = ['ss', "%$search%", "%$search%"];

if ($category_filter) {
    $sql .= " AND p.category_id = ?";
    $params[0] .= 'i';
    $params[] = $category_filter;
}
if ($brand_filter) {
    $sql .= " AND p.brand_id = ?";
    $params[0] .= 'i';
    $params[] = $brand_filter;
}
if ($status_filter) {
    if ($status_filter == 'active') {
        $sql .= " AND p.active = 1";
    } elseif ($status_filter == 'inactive') {
        $sql .= " AND p.active = 0";
    }
}

switch ($sort_order) {
    case 'price_asc':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'name_asc':
        $sql .= " ORDER BY p.product_name ASC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY p.product_name DESC";
        break;
    default:
        $sql .= " ORDER BY p.product_id DESC";
}

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param(...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
?>

<div class="admin-content">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div>
            <h1 class="page-title">Products Management</h1>
            <p class="page-subtitle">Manage your product inventory</p>
        </div>
        <div>
            <a href="addproducts.php" class="btn btn-primary-green">
                <i class="bi bi-plus-circle me-2"></i>Add New Product
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-funnel me-2"></i>Filters & Search
                </h5>
                <a href="products.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="filter-form-modern">
                <div class="row g-3">
                    <!-- Search Bar -->
                    <div class="col-12">
                        <label class="form-label">Search</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" 
                                   name="search" 
                                   class="form-control" 
                                   placeholder="Search by name, model, or description..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    
                    <!-- Filters Row -->
                    <div class="col-md-3">
                        <label for="category" class="form-label">Category</label>
                        <select name="category" id="category" class="form-select">
                            <option value="">All Categories</option>
                            <?php 
                            $categories_result->data_seek(0);
                            while ($cat = $categories_result->fetch_assoc()): ?>
                            <option value="<?php echo $cat['category_id']; ?>" <?php echo $category_filter == $cat['category_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="brand" class="form-label">Brand</label>
                        <select name="brand" id="brand" class="form-select">
                            <option value="">All Brands</option>
                            <?php 
                            $brands_result->data_seek(0);
                            while ($brand = $brands_result->fetch_assoc()): ?>
                            <option value="<?php echo $brand['brand_id']; ?>" <?php echo $brand_filter == $brand['brand_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($brand['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="sort" class="form-label">Sort By</label>
                        <select name="sort" id="sort" class="form-select">
                            <option value="newest" <?php echo $sort_order == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="price_asc" <?php echo $sort_order == 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_desc" <?php echo $sort_order == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="name_asc" <?php echo $sort_order == 'name_asc' ? 'selected' : ''; ?>>Name: A-Z</option>
                            <option value="name_desc" <?php echo $sort_order == 'name_desc' ? 'selected' : ''; ?>>Name: Z-A</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary-green w-100">
                            <i class="bi bi-funnel-fill me-1"></i>Apply
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table Card -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="bi bi-box-seam me-2"></i>Products List
            </h5>
            <span class="badge bg-primary-green"><?php echo $result->num_rows; ?> product(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Model</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Featured</th>
                            <th>New</th>
                            <th>Active</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4"><?php echo $row['product_id']; ?></td>
                                <td>
                                    <img src="/bytehub/uploads/products/<?php echo htmlspecialchars($row['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($row['product_name']); ?>" 
                                         width="60" 
                                         height="60" 
                                         class="img-thumbnail rounded"
                                         style="object-fit: cover;">
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['product_name']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($row['model']); ?></td>
                                <td>
                                    <span class="badge bg-light text-dark"><?php echo htmlspecialchars($row['category_name'] ?? 'N/A'); ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($row['brand_name'] ?? 'N/A'); ?></span>
                                </td>
                                <td>
                                    <strong class="text-primary-green">₱<?php echo number_format($row['price'], 2); ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $row['stock'] > 0 ? 'success' : 'danger'; ?>">
                                        <?php echo $row['stock']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $row['featured'] ? 'warning' : 'secondary'; ?>">
                                        <?php echo $row['featured'] ? 'Yes' : 'No'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $row['new_arrival'] ? 'info' : 'secondary'; ?>">
                                        <?php echo $row['new_arrival'] ? 'Yes' : 'No'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $row['active'] ? 'success' : 'danger'; ?>">
                                        <?php echo $row['active'] ? 'Yes' : 'No'; ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group" role="group">
                                        <a href="editProducts.php?id=<?php echo $row['product_id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="deleteProducts.php?id=<?php echo $row['product_id']; ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Are you sure you want to delete this product?');"
                                           title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="12" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-3 mb-0">No products found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<?php include '../footer.php'; ?>
