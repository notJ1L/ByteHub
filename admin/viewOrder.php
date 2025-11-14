<?php
include '../includes/db.php';
include '../includes/functions.php';
include '../includes/admin_header.php';

if (!isAdmin()) {
    header("Location: ../customer/index.php");
    exit();
}

$id = $_GET['id'];

$order = $conn->query("
SELECT o.*, u.username, u.email 
FROM orders o
LEFT JOIN users u ON o.user_id = u.user_id
WHERE order_id = $id
")->fetch_assoc();

if (!$order) {
    die("Order not found.");
}

$items = $conn->query("
SELECT * FROM order_items 
WHERE order_id = $id
");
?>

<div class="container mt-4">
    <h2>Order Details</h2>

    <h4 class="mt-3">Order Information</h4>
    <p><strong>Order Code:</strong> <?php echo $order['order_code']; ?></p>
    <p><strong>User:</strong> <?php echo $order['username']; ?> (<?php echo $order['email']; ?>)</p>
    <p><strong>Status:</strong> <?php echo $order['status']; ?></p>
    <p><strong>Created:</strong> <?php echo $order['created_at']; ?></p>

    <h4 class="mt-3">Items</h4>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Unit Price</th>
                <th>Qty</th>
                <th>Line Total</th>
            </tr>
        </thead>

        <tbody>
        <?php while($i = $items->fetch_assoc()): ?>
            <tr>
                <td><?php echo $i['name_snapshot']; ?></td>
                <td>$<?php echo number_format($i['unit_price_snapshot'], 2); ?></td>
                <td><?php echo $i['quantity']; ?></td>
                <td>$<?php echo number_format($i['line_total'], 2); ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <h3>Total: $<?php echo number_format($order['total'], 2); ?></h3>

    <a href="orders.php" class="btn btn-secondary mt-3">Back to Orders</a>
</div>

<?php include '../includes/footer.php'; ?>
