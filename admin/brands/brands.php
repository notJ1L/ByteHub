<?php
include '../../includes/db.php';
include '../../includes/functions.php';
include '../../includes/admin_header.php';

if (!isAdmin()) {
    header("Location: ../customer/index.php");
    exit();
}

$brandQuery = $conn->query("SELECT * FROM brands ORDER BY brand_id DESC");
?>

<div class="container mt-4">
    <h2>Manage Brands</h2>

    <a href="addBrand.php" class="btn btn-success mb-3">+ Add Brand</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Slug</th>
                <th>Active</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php while($row = $brandQuery->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['brand_id']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['slug']; ?></td>
                <td><?php echo $row['active'] ? 'Yes' : 'No'; ?></td>

                <td>
                    <a href="editBrand.php?id=<?php echo $row['brand_id']; ?>" class="btn btn-primary btn-sm">Edit</a>

                    <a href="deleteBrand.php?id=<?php echo $row['brand_id']; ?>" 
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Delete this brand?');">
                       Delete
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>
