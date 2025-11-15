<?php
session_start();
include '../../includes/db.php';
include '../../includes/admin_header.php';
include '../../includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
  header('Location: index.php');
  exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $name = trim($_POST['product_name']);
  $model = trim($_POST['model']);
  $price = (float)$_POST['price'];
  $stock = (int)$_POST['stock'];
  $category_id = (int)$_POST['category_id'];
  $brand_id = (int)$_POST['brand_id'];
  $featured = isset($_POST['featured']) ? 1 : 0;
  $new_arrival = isset($_POST['new_arrival']) ? 1 : 0;
  $description = $_POST['description'];
  $specs = $_POST['specifications'];

  if (empty($name)) $errors[] = 'Product name is required.';
  if (empty($model)) $errors[] = 'Model is required.';
  if (empty($price)) $errors[] = 'Price is required.';
  if (empty($stock)) $errors[] = 'Stock is required.';

  if (empty($errors)) {
    $stmt = $conn->prepare("
      INSERT INTO products 
      (product_name, model, price, stock, image, featured, new_arrival, active, category_id, brand_id, description, specifications)
      VALUES 
      (?, ?, ?, ?, '', ?, ?, 1, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssdiisssss", $name, $model, $price, $stock, $featured, $new_arrival, $category_id, $brand_id, $description, $specs);
    $stmt->execute();

    $product_id = $conn->insert_id;

    // MAIN IMAGE UPLOAD
    if (!empty($_FILES['image']['name'])) {
        $mainImage = time() . '_' . $_FILES['image']['name'];
        $target = "../../uploads/products/" . $mainImage;

        move_uploaded_file($_FILES['image']['tmp_name'], $target);

        $stmt = $conn->prepare("UPDATE products SET image=? WHERE product_id=?");
        $stmt->bind_param("si", $mainImage, $product_id);
        $stmt->execute();
    }

    // MULTIPLE IMAGES UPLOAD
    if (!empty($_FILES['images']['name'][0])) {
      foreach ($_FILES['images']['name'] as $key => $filename) {
          $imgName = time() . '_' . $filename;
          $uploadPath = "../../uploads/products/" . $imgName;

          if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $uploadPath)) {
              $stmt = $conn->prepare("INSERT INTO product_images (product_id, filename) VALUES (?, ?)");
              $stmt->bind_param("is", $product_id, $imgName);
              $stmt->execute();
          }
      }
    }

    header('Location: products.php');
    exit;
  }
}

// get categories / brands
$cats = $conn->query("SELECT * FROM categories WHERE active=1");
$brands = $conn->query("SELECT * FROM brands WHERE active=1");
?>

<h2>Add Product</h2>

<?php if (!empty($errors)): ?>
  <div class="errors">
    <?php foreach ($errors as $error): ?>
      <p><?php echo $error; ?></p>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

  <label>Product Name:</label><br>
  <input type="text" name="product_name"><br><br>

  <label>Model:</label><br>
  <input type="text" name="model"><br><br>

  <label>Price:</label><br>
  <input type="number" step="0.01" name="price"><br><br>

  <label>Description:</label><br>
  <textarea name="description" rows="5" style="width:100%;"></textarea><br><br>

  <label>Specifications:</label><br>
  <textarea name="specifications" rows="5" style="width:100%;"></textarea><br><br>

  <label>Stock Quantity:</label><br>
  <input type="number" name="stock"><br><br>

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

  <label>Main Image:</label><br>
  <input type="file" name="image"><br><br>

  <label>Additional Photos:</label><br>
  <input type="file" name="images[]" multiple><br><br>

  <button type="submit" class="btn" style="background:#004d26; color:white;">Add Product</button>
</form>

<?php include '../../includes/footer.php'; ?>
