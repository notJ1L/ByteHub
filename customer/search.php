<?php
include '../includes/db.php';
include '../includes/header.php';

echo '<form action="search.php" method="GET" style="display:inline;">
  <input type="text" name="q" placeholder="Search..." required>
  <button type="submit">Search</button>
</form>';

$keyword = $_GET['q'] ?? '';
$sql = "SELECT * FROM products WHERE product_name LIKE '%$keyword%' OR model LIKE '%$keyword%' OR price LIKE '%$keyword%'";
$result = $conn->query($sql);

echo "<h2>Search results for '$keyword'</h2>";
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
  echo "<p>No products found.</p>";
}
echo "</div>";

include '../includes/footer.php';
?>
