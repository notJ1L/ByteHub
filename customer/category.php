<?php
include '../includes/db.php';
include '../includes/header.php';

$category_id = null;

// Check if "id" or "cat" is passed
if (isset($_GET['id'])) {
  $category_id = (int)$_GET['id'];
} elseif (isset($_GET['cat'])) {
  $slug = $_GET['cat'];
  $sql = "SELECT category_id FROM categories WHERE slug = '$slug'";
  $result = $conn->query($sql);
  if ($result && $result->num_rows > 0) {
    $category_id = $result->fetch_assoc()['category_id'];
  }
}

if (!$category_id) {
  echo "<p>Category not found.</p>";
  include '../includes/footer.php';
  exit;
}

// Fetch category name
$sql = "SELECT name FROM categories WHERE category_id = $category_id";
$cat_result = $conn->query($sql);
$category = $cat_result->fetch_assoc()['name'] ?? 'Unknown';

echo "<h2>$category</h2>";

// Get all products in this category
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
