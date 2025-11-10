<?php
session_start();
include '../includes/db.php';
include '../includes/functions.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if (isset($_POST['add_to_cart'])) {
  $id = $_POST['id'];
  $qty = (int)$_POST['qty'];
  $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + $qty;
  echo "<p>Added to cart!</p>";
}

echo "<h2>Your Cart</h2>";
if (empty($_SESSION['cart'])) {
  echo "<p>Your cart is empty.</p>";
} else {
  $ids = implode(',', array_keys($_SESSION['cart']));
  $sql = "SELECT * FROM products WHERE product_id IN ($ids)";
  $result = $conn->query($sql);

  $total = 0;
  echo "<table border='1' cellpadding='10'>
        <tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr>";
  while($row = $result->fetch_assoc()) {
    $qty = $_SESSION['cart'][$row['product_id']];
    $subtotal = $row['price'] * $qty;
    $total += $subtotal;
    echo "<tr>
            <td>{$row['product_name']}</td>
            <td>$qty</td>
            <td>$" . number_format($row['price'], 2) . "</td>
            <td>$" . number_format($subtotal, 2) . "</td>
          </tr>";
  }
  echo "<tr><td colspan='3'><strong>Total</strong></td><td>$" . number_format($total, 2) . "</td></tr></table>";
}

if (!empty($_SESSION['cart'])): ?>
  <div style="margin-top: 20px;">
    <a href="checkout.php" class="btn" 
       style="background-color:#004d26; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;">
       Proceed to Checkout
    </a>
  </div>
<?php endif;

include '../includes/footer.php';
?>
