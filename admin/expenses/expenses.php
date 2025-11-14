<?php
include '../../includes/db.php';
include '../../includes/admin_header.php';
include '../../includes/functions.php';

if (!isAdmin()) {
    header("Location: ../customer/index.php");
    exit();
}

$query = $conn->query("SELECT * FROM expenses ORDER BY expenses_id DESC");
?>

<div class="container mt-4">
    <h2>Expenses</h2>

    <a href="addExpense.php" class="btn btn-success mb-3">+ Add Expense</a>

    <table class="table table-bordered table-striped">
        <thead>
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
        <?php while($row = $query->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['expenses_id']; ?></td>
                <td><?php echo $row['title']; ?></td>
                <td>$<?php echo number_format($row['amount'], 2); ?></td>
                <td><?php echo $row['category']; ?></td>
                <td><?php echo $row['created_at']; ?></td>

                <td>
                    <a href="editExpense.php?id=<?php echo $row['expenses_id']; ?>" class="btn btn-primary btn-sm">Edit</a>

                    <a href="deleteExpense.php?id=<?php echo $row['expenses_id']; ?>"
                       onclick="return confirm('Delete this expense?');"
                       class="btn btn-danger btn-sm">
                       Delete
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>
