<?php
include '../includes/db.php';
include '../includes/header.php';

$id = $_GET['id'] ?? 0;
$sql = "SELECT * FROM products WHERE product_id = $id AND active = 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
  $p = $result->fetch_assoc();
  echo "
    <div class='product-details'>
      <img src='../assets/uploads/{$p['image']}' alt='{$p['product_name']}'>
      <h2>{$p['product_name']}</h2>
      <p>Price: $" . number_format($p['price'], 2) . "</p>
      <p>Stock: {$p['stock']}</p>
      <p>Model: {$p['model']}</p>
      <form method='post' action='cart.php'>
        <input type='hidden' name='id' value='{$p['product_id']}'>
        <input type='number' name='qty' value='1' min='1' max='{$p['stock']}'>
        <button type='submit' name='add_to_cart' class='btn'>Add to Cart</button>
      </form>
    </div>
  ";
} else {
  echo "<p>Product not found.</p>";
}

include '../includes/footer.php';
?>
