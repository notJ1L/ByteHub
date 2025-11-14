<?php
session_start();
include '../../includes/db.php';
include '../../includes/admin_header.php';
include '../../includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
  header('Location: index.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['product_name']);
  $model = trim($_POST['model']);
  $price = (float)$_POST['price'];
  $stock = (int)$_POST['stock'];
  $category_id = (int)$_POST['category_id'];
  $brand_id = (int)$_POST['brand_id'];
  $featured = isset($_POST['featured']) ? 1 : 0;
  $new_arrival = isset($_POST['new_arrival']) ? 1 : 0;

  $conn->query("INSERT INTO products (product_name, model, price, stock, image, featured, new_arrival, active, category_id, brand_id)
                VALUES ('$name', '$model', $price, $stock, '', $featured, $new_arrival, 1, $category_id, $brand_id)");
  header('Location: products.php');
  exit;
}

$cats = $conn->query("SELECT * FROM categories WHERE active=1");
$brands = $conn->query("SELECT * FROM brands WHERE active=1");
?>

<h2>Add Product</h2>
<form method="POST">
  <label>Product Name:</label><br>
  <input type="text" name="product_name" required><br><br>

  <label>Model:</label><br>
  <input type="text" name="model" required><br><br>

  <label>Price:</label><br>
  <input type="number" step="0.01" name="price" required><br><br>

  <label>Stock Quantity:</label><br>
  <input type="number" name="stock" required><br><br>

  <label>Category:</label><br>
  <select name="category_id">
    <?php while($c = $cats->fetch_assoc()): ?>
      <option value="<?= $c['category_id'] ?>"><?= $c['name'] ?></option>
    <?php endwhile; ?>
  </select><br><br>

  <label>Brand:</label><br>
  <select name="brand_id">
    <?php while($b = $brands->fetch_assoc()): ?>
      <option value="<?= $b['brand_id'] ?>"><?= $b['name'] ?></option>
    <?php endwhile; ?>
  </select><br><br>

  <label><input type="checkbox" name="featured"> Featured</label><br>
  <label><input type="checkbox" name="new_arrival"> New Arrival</label><br><br>

  <button type="submit" class="btn" style="background:#004d26; color:white;">Add Product</button>
</form>

<?php include '../../includes/footer.php'; ?>
