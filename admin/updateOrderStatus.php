<?php
include '../includes/db.php';
include '../includes/functions.php';
include '../includes/admin_header.php';

if (!isAdmin()) {
    header("Location: ../customer/index.php");
    exit();
}

$id = $_GET['id'];

$order = $conn->query("SELECT * FROM orders WHERE order_id = $id")->fetch_assoc();
if (!$order) die("Order not found.");

if (isset($_POST['update'])) {
    $status = $_POST['status'];

    $conn->query("UPDATE orders SET status='$status' WHERE order_id=$id");

    header("Location: orders.php?status_updated=1");
    exit();
}
?>

<div class="container mt-4">
    <h2>Update Order Status</h2>

    <form method="post" class="mt-3">

        <label>Status:</label>
        <select name="status" class="form-control">
            <option value="Pending"   <?php echo $order['status']=='Pending'?'selected':''; ?>>Pending</option>
            <option value="Processing" <?php echo $order['status']=='Processing'?'selected':''; ?>>Processing</option>
            <option value="Shipped"   <?php echo $order['status']=='Shipped'?'selected':''; ?>>Shipped</option>
            <option value="Completed" <?php echo $order['status']=='Completed'?'selected':''; ?>>Completed</option>
            <option value="Cancelled" <?php echo $order['status']=='Cancelled'?'selected':''; ?>>Cancelled</option>
        </select>

        <button name="update" class="btn btn-primary mt-3">Update Status</button>
        <a href="orders.php" class="btn btn-secondary mt-3">Cancel</a>
    </form>

</div>

<?php include '../includes/footer.php'; ?>
