<?php
include '../includes/header.php';
$order_code = $_GET['code'] ?? 'Unknown';
?>

<h2>Order Successful</h2>
<p>Your order has been placed successfully.</p>
<p>Order Code: <strong><?php echo htmlspecialchars($order_code); ?></strong></p>
<a href="index.php" class="btn">Return to Shop</a>

<?php include '../includes/footer.php'; ?>
