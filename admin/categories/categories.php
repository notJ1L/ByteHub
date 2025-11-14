<?php
include '../../includes/db.php';
include '../../includes/admin_header.php';
include '../../includes/functions.php';


if (!isAdmin()) {
    header("Location: ../customer/index.php");
    exit();
}

$catQuery = $conn->query("SELECT * FROM categories ORDER BY category_id DESC");
?>

<div class="container mt-4">
    <h2>Manage Categories</h2>

    <a href="addCategory.php" class="btn btn-success mb-3">+ Add Category</a>

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
        <?php while($row = $catQuery->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['category_id']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['slug']; ?></td>
                <td><?php echo $row['active'] ? 'Yes' : 'No'; ?></td>

                <td>
                    <a href="editCategory.php?id=<?php echo $row['category_id']; ?>" class="btn btn-primary btn-sm">Edit</a>

                    <a href="deleteCategory.php?id=<?php echo $row['category_id']; ?>"
                        class="btn btn-danger btn-sm"
                        onclick="return confirm('Delete this category?');">
                        Delete
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>