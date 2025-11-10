<?php
session_start();
include '../includes/db.php';
include '../includes/functions.php';
include '../includes/header.php';

/* --- Require login --- */
if (!isset($_SESSION['user_id'])) {
  $_SESSION['redirect_after_login'] = 'checkout.php';
  header('Location: login.php');
  exit;
}

/* --- Check for empty cart --- */
if (empty($_SESSION['cart'])) {
  echo "<p>Your cart is empty. <a href='index.php'>Go shopping</a></p>";
  include '../includes/footer.php';
  exit;
}

/* --- When the form is submitted --- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = (int)$_SESSION['user_id'];               // use logged-in user ID
  $payment_method = $_POST['payment_method'];
  $order_code = 'ORDER-' . rand(1000, 9999);

  $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
  $sql = "SELECT * FROM products WHERE product_id IN ($ids)";
  $result = $conn->query($sql);

  if (!$result) {
    die("Query failed: " . $conn->error);
  }

  $subtotal = 0;
  while ($row = $result->fetch_assoc()) {
    $subtotal += $row['price'] * $_SESSION['cart'][$row['product_id']];
  }
  $tax = round($subtotal * 0.12, 2);
  $total = round($subtotal + $tax, 2);

  /* --- Insert order --- */
  $stmt = $conn->prepare("INSERT INTO orders (user_id, order_code, payment_method, subtotal, tax, total, status) 
                          VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
  $stmt->bind_param('issddd', $user_id, $order_code, $payment_method, $subtotal, $tax, $total);
  $stmt->execute();
  $order_id = $stmt->insert_id;
  $stmt->close();

  /* --- Insert order items and update stock --- */
  $result->data_seek(0);
  while ($row = $result->fetch_assoc()) {
    $pid = (int)$row['product_id'];
    $qty = (int)$_SESSION['cart'][$pid];
    $line_total = $row['price'] * $qty;

    $stmt = $conn->prepare("INSERT INTO order_items 
      (name_snapshot, unit_price_snapshot, quantity, line_total, order_id, product_id)
      VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sdddid', $row['product_name'], $row['price'], $qty, $line_total, $order_id, $pid);
    $stmt->execute();
    $stmt->close();

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
