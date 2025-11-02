<?php 
include '../includes/db.php'; 
include '../includes/header.php';
?>


<h2>Featured Products</h2>
<div class="product-grid">
<?php
$sql = "SELECT * FROM products WHERE featured = 1 AND active = 1 LIMIT 6";
$result = $conn->query($sql);

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
  echo "<p>No featured products yet.</p>";
}
?>
</div>



<h2>New Arrivals</h2>
<div class="product-grid">
<?php
$sql = "SELECT * FROM products WHERE new_arrival = 1 AND active = 1 ORDER BY created_at DESC LIMIT 6";
$result = $conn->query($sql);

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
  echo "<p>No new arrivals yet.</p>";
}
?>
</div>


<h2>Shop by Category</h2>
<div class="category-grid">
<?php
$sql = "SELECT * FROM categories WHERE active = 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    echo "
    <div class='category-card'>
      <a href='category.php?id={$row['category_id']}'>
        <img src='../assets/images/{$row['slug']}.png' alt='{$row['name']}'>
        <h3>{$row['name']}</h3>
      </a>
    </div>
    ";
  }
} else {
  echo "<p>No categories available.</p>";
}
?>
</div>


<?php include '../includes/footer.php'; ?>

