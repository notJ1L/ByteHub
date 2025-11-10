<?php
session_start();
include '../includes/db.php';
include __DIR__ . '/../includes/admin_header.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$prod_count = $conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'];
$user_count = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$order_count = $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'];
$revenue_row = $conn->query("SELECT SUM(total) AS total_revenue FROM orders")->fetch_assoc();
$total_revenue = $revenue_row['total_revenue'] ?? 0;
?>

<h2>Admin Dashboard</h2>
<p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_email']); ?>!</p>

<div style="display: flex; gap: 20px; flex-wrap: wrap; margin-top: 20px;">
  <div style="background: #004d26; color: white; padding: 20px; border-radius: 10px; flex: 1;">
    <h3>Products</h3>
    <p style="font-size: 1.5em;"><?php echo $prod_count; ?></p>
  </div>

  <div style="background: #1e7a34; color: white; padding: 20px; border-radius: 10px; flex: 1;">
    <h3>Users</h3>
    <p style="font-size: 1.5em;"><?php echo $user_count; ?></p>
  </div>

  <div style="background: #2f8f46; color: white; padding: 20px; border-radius: 10px; flex: 1;">
    <h3>Orders</h3>
    <p style="font-size: 1.5em;"><?php echo $order_count; ?></p>
  </div>

  <div style="background: #3fa85c; color: white; padding: 20px; border-radius: 10px; flex: 1;">
    <h3>Total Revenue</h3>
    <p style="font-size: 1.5em;">₱<?php echo number_format($total_revenue, 2); ?></p>
  </div>
</div>

<?php include '../includes/footer.php'; ?>

