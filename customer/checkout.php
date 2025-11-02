<?php
session_start();
include '../includes/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

if (empty($_SESSION['cart'])) {
  echo "<p>Your cart is empty. <a href='index.php'>Go shopping</a></p>";
  include '../includes/footer.php';
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = 1; // Replace with $_SESSION['user_id'] if login system is active
  $payment_method = $_POST['payment_method'];
  $order_code = 'ORDER-' . rand(1000, 9999);

  $ids = implode(',', array_keys($_SESSION['cart']));
  $sql = "SELECT * FROM products WHERE product_id IN ($ids)";
  $result = $conn->query($sql);

  $subtotal = 0;
  while ($row = $result->fetch_assoc()) {
    $subtotal += $row['price'] * $_SESSION['cart'][$row['product_id']];
  }
  $tax = $subtotal * 0.12;
  $total = $subtotal + $tax;

  $conn->query("INSERT INTO orders (user_id, order_code, payment_method, subtotal, tax, total, status) 
                VALUES ($user_id, '$order_code', '$payment_method', $subtotal, $tax, $total, 'Pending')");
  $order_id = $conn->insert_id;

  $result->data_seek(0);
  while ($row = $result->fetch_assoc()) {
    $pid = $row['product_id'];
    $qty = $_SESSION['cart'][$pid];
    $line_total = $row['price'] * $qty;

    $conn->query("INSERT INTO order_items (name_snapshot, unit_price_snapshot, quantity, line_total, order_id, product_id)
                  VALUES ('{$row['product_name']}', {$row['price']}, $qty, $line_total, $order_id, $pid)");

    $conn->query("UPDATE products SET stock = stock - $qty WHERE product_id = $pid");
  }

  $_SESSION['cart'] = [];
  header("Location: thankyou.php?code=$order_code");
  exit;
}
?>

<h2>Checkout</h2>
<form method="POST">
  <label>Payment Method:</label><br>
  <select name="payment_method" required>
    <option value="Cash">Cash on Delivery</option>
    <option value="Credit Card">Credit Card (Mock)</option>
  </select><br><br>
  <button type="submit" class="btn">Place Order</button>
</form>

<?php include '../includes/footer.php'; ?>
