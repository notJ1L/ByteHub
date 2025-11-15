<?php
include '../../includes/db.php';
include '../../includes/admin_header.php';
include '../../includes/functions.php';

if (!isAdmin()) {
    header("Location: ../customer/index.php");
    exit();
}

$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM categories WHERE name LIKE ? OR slug LIKE ? ORDER BY category_id DESC";
$stmt = $conn->prepare($sql);
$search_param = "%$search%";
$stmt->bind_param('ss', $search_param, $search_param);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Manage Categories</h3>
        <div class="card-tools">
            <a href="addCategory.php" class="btn btn-primary-green">+ Add Category</a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search for categories..." value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['category_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['slug']); ?></td>
                        <td><span class="badge bg-<?php echo $row['active'] ? 'success' : 'secondary'; ?>"><?php echo $row['active'] ? 'Yes' : 'No'; ?></span></td>
                        <td>
                            <a href="editCategory.php?id=<?php echo $row['category_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            <a href="deleteCategory.php?id=<?php echo $row['category_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this category?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>