<?php
include '../../includes/db.php';
include '../../includes/admin_header.php';
include '../../includes/functions.php';

$search = $_GET['search'] ?? '';

$sql = "SELECT p.*, c.name AS category_name, b.name AS brand_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        WHERE p.product_name LIKE ? OR p.model LIKE ?
        ORDER BY p.product_id DESC";

$stmt = $conn->prepare($sql);
$search_param = "%$search%";
$stmt->bind_param('ss', $search_param, $search_param);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Manage Products</h3>
        <div class="card-tools">
            <a href="addproducts.php" class="btn btn-primary-green">+ Add New Product</a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search for products..." value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th>ID</th>
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['product_id']; ?></td>
                        <td><img src="/bytehub/uploads/products/<?php echo $row['image']; ?>" width="50" class="img-thumbnail"></td>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['model']); ?></td>
                        <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['brand_name']); ?></td>
                        <td>₱<?php echo number_format($row['price'], 2); ?></td>
                        <td><?php echo $row['stock']; ?></td>
                        <td><span class="badge bg-<?php echo $row['featured'] ? 'success' : 'secondary'; ?>"><?php echo $row['featured'] ? 'Yes' : 'No'; ?></span></td>
                        <td><span class="badge bg-<?php echo $row['new_arrival'] ? 'info' : 'secondary'; ?>"><?php echo $row['new_arrival'] ? 'Yes' : 'No'; ?></span></td>
                        <td><span class="badge bg-<?php echo $row['active'] ? 'success' : 'secondary'; ?>"><?php echo $row['active'] ? 'Yes' : 'No'; ?></span></td>
                        <td>
                            <a href="editProducts.php?id=<?php echo $row['product_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            <a href="deleteProducts.php?id=<?php echo $row['product_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
