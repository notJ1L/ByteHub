<?php
include '../../includes/db.php';
include '../../includes/admin_header.php';
include '../../includes/functions.php';

$sql = "SELECT p.*, c.name AS category_name, b.name AS brand_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        ORDER BY p.product_id DESC";

$result = $conn->query($sql);
?>

<div class="container mt-4">
    <h2 class="mb-4">Manage Products</h2>

    <a href="addproducts.php" class="btn btn-success mb-3">+ Add New Product</a>

    <table class="table table-bordered table-striped">
        <thead>
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
                <th>New Arrival</th>
                <th>Active</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['product_id']; ?></td>

                <td><img src="../uploads/<?php echo $row['image']; ?>" width="50"></td>

                <td><?php echo $row['product_name']; ?></td>
                <td><?php echo $row['model']; ?></td>

                <td><?php echo $row['category_name']; ?></td>
                <td><?php echo $row['brand_name']; ?></td>

                <td>$<?php echo number_format($row['price'], 2); ?></td>

                <td><?php echo $row['stock']; ?></td>

                <td><?php echo $row['featured'] ? 'Yes' : 'No'; ?></td>

                <td><?php echo $row['new_arrival'] ? 'Yes' : 'No'; ?></td>

                <td><?php echo $row['active'] ? 'Yes' : 'No'; ?></td>

                <td>
                    <a href="editProducts.php?id=<?php echo $row['product_id']; ?>" class="btn btn-primary btn-sm">Edit</a>

                    <a href="deleteProducts.php?id=<?php echo $row['product_id']; ?>"
                        class="btn btn-danger btn-sm"
                        onclick="return confirm('Delete this product?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>
