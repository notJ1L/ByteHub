<?php
include '../includes/db.php';
include '../includes/functions.php';
include '../includes/admin_header.php';

if (!isAdmin()) {
    header("Location: ../customer/index.php");
    exit();
}

$orderQuery = $conn->query("
    SELECT o.*, u.username 
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_id DESC
");
?>

<div class="container mt-4">
    <h2>Orders</h2>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Order Code</th>
                <th>User</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php while($row = $orderQuery->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['order_id']; ?></td>
            <td><?php echo $row['order_code']; ?></td>
            <td><?php echo $row['username']; ?></td>
            <td>$<?php echo number_format($row['total'], 2); ?></td>
            <td><?php echo $row['status']; ?></td>
            <td><?php echo $row['created_at']; ?></td>

            <td>
                <a href="viewOrder.php?id=<?php echo $row['order_id']; ?>" 
                   class="btn btn-primary btn-sm">View</a>

                <a href="updateOrderStatus.php?id=<?php echo $row['order_id']; ?>" 
                   class="btn btn-warning btn-sm">Update</a>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

</div>

<?php include '../includes/footer.php'; ?>
