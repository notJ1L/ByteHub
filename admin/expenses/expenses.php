<?php
include '../../includes/db.php';
include '../../includes/admin_header.php';
include '../../includes/functions.php';

if (!isAdmin()) {
    header("Location: ../customer/index.php");
    exit();
}

$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM expenses WHERE title LIKE ? OR category LIKE ? ORDER BY expenses_id DESC";
$stmt = $conn->prepare($sql);
$search_param = "%$search%";
$stmt->bind_param('ss', $search_param, $search_param);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Manage Expenses</h3>
        <div class="card-tools">
            <a href="addExpense.php" class="btn btn-primary-green">+ Add Expense</a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search for expenses..." value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Amount</th>
                        <th>Category</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['expenses_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td>₱<?php echo number_format($row['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                        <td>
                            <a href="editExpense.php?id=<?php echo $row['expenses_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            <a href="deleteExpense.php?id=<?php echo $row['expenses_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this expense?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
