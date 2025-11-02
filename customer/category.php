<?php
include '../includes/db.php';
include '../includes/header.php';

$category_id = $_GET['id'] ?? 0;

$sql = "SELECT name FROM categories WHERE category_id = $category_id";
$cat_result = $conn->query($sql);
$category = $cat_result && $cat_result->num_rows > 0 ? $cat_result->fetch_assoc()['name'] : 'Unknown';

echo "<h2>Products in $category</h2>";

$sql = "SELECT * FROM products WHERE category_id = $category_id AND active = 1";
$result = $conn->query($sql);

echo "<div class='product-grid'>";
if ($result && $result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    echo "
    <div class='product-card'>
      <img src='../assets/uploads/{$row['image']}' alt='{$row['product_name']}'>
      <h3>{$row['product_name']}</h3>
      <p>$" . number_format($row['price'], 2) . "</p>
      <a href='product.php?id={$row['product_id']}' class='btn'>View</a>
    </div>
    ";
  }
} else {
  echo "<p>No products found in this category.</p>";
}
echo "</div>";

include '../includes/footer.php';
?>
