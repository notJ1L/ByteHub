<?php
include '../includes/db.php';
include '../includes/functions.php';

if (!isAdmin()) {
    redirect('../customer/index.php');
}

include '../includes/admin_header.php';

$id = (int)$_GET['id'];

// Use MySQL view for order details
$order_stmt = $conn->prepare("
    SELECT DISTINCT 
        order_id,
        order_code,
        status,
        order_date,
        username,
        email
    FROM order_details_view 
    WHERE order_id = ?
    LIMIT 1
");
$order_stmt->bind_param('i', $id);
$order_stmt->execute();
$order = $order_stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found.");
}

// Get order totals from orders table (not in view)
$totals_stmt = $conn->prepare("SELECT subtotal, tax, total, created_at, payment_method FROM orders WHERE order_id = ?");
$totals_stmt->bind_param('i', $id);
$totals_stmt->execute();
$totals = $totals_stmt->get_result()->fetch_assoc();

// Merge totals with order data
$order = array_merge($order, $totals);

// Get order items from view
$items_stmt = $conn->prepare("
    SELECT 
        product_name,
        quantity,
        unit_price_snapshot,
        line_total
    FROM order_details_view 
    WHERE order_id = ?
");
$items_stmt->bind_param('i', $id);
$items_stmt->execute();
$items = $items_stmt->get_result();
?>

<div class="container mt-4">
    <h2>Order Details</h2>

    <h4 class="mt-3">Order Information</h4>
    <p><strong>Order Code:</strong> <?php echo htmlspecialchars($order['order_code']); ?></p>
    <p><strong>User:</strong> <?php echo htmlspecialchars($order['username']); ?> (<?php echo htmlspecialchars($order['email']); ?>)</p>
    <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
    <p><strong>Created:</strong> <?php echo htmlspecialchars($order['created_at']); ?></p>

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
                <td><?php echo htmlspecialchars($i['product_name']); ?></td>
                <td>$<?php echo number_format($i['unit_price_snapshot'], 2); ?></td>
                <td><?php echo $i['quantity']; ?></td>
                <td>$<?php echo number_format($i['line_total'], 2); ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <h3>Total: $<?php echo number_format($order['total'], 2); ?></h3>

    <a href="orders.php" class="action-btn btn-edit mt-3"><i class="fas fa-arrow-left"></i>Back to Orders</a>
</div>

<?php include 'footer.php'; ?>
