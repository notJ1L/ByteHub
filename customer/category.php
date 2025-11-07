<?php
include '../includes/db.php';
include '../includes/header.php';

$category_id = null;

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

$sort = $_GET['sort'] ?? '';
$order_by = '';

if ($sort == 'price_asc') $order_by = 'ORDER BY price ASC';
elseif ($sort == 'price_desc') $order_by = 'ORDER BY price DESC';
elseif ($sort == 'name_asc') $order_by = 'ORDER BY product_name ASC';
elseif ($sort == 'name_desc') $order_by = 'ORDER BY product_name DESC';

$sql = "SELECT name FROM categories WHERE category_id = $category_id";
$cat_result = $conn->query($sql);
$category = $cat_result->fetch_assoc()['name'] ?? 'Unknown';

echo "<h2>$category</h2>";

$sql = "SELECT * FROM products WHERE category_id = $category_id AND active = 1 $order_by";
$result = $conn->query($sql);

echo '<form method="GET">';
echo '<input type="hidden" name="cat" value="'.htmlspecialchars($_GET['cat'] ?? '').'">';
echo '<select name="sort" onchange="this.form.submit()">
        <option value="">Sort by...</option>
        <option value="price_asc">Price: Low to High</option>
        <option value="price_desc">Price: High to Low</option>
        <option value="name_asc">Name: A–Z</option>
        <option value="name_desc">Name: Z–A</option>
      </select>';
echo '</form>';

echo "<div class='product-grid'>";
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
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

