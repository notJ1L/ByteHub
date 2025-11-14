<?php
session_start();
include '../../includes/db.php';
include '../../includes/admin_header.php';
include '../../includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$product_id = (int)$_GET['id'];

$res = $conn->query("SELECT * FROM products WHERE product_id = $product_id LIMIT 1");
$product = $res->fetch_assoc();

if (!$product) {
    echo "Product not found.";
    exit;
}

$cats = $conn->query("SELECT * FROM categories WHERE active=1");
$brands = $conn->query("SELECT * FROM brands WHERE active=1");

$moreImages = $conn->query("SELECT * FROM product_images WHERE product_id = $product_id");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['product_name']);
    $model = trim($_POST['model']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category_id = (int)$_POST['category_id'];
    $brand_id = (int)$_POST['brand_id'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $new_arrival = isset($_POST['new_arrival']) ? 1 : 0;
    $active = isset($_POST['active']) ? 1 : 0;

    $conn->query("
        UPDATE products SET
            product_name = '$name',
            model = '$model',
            price = $price,
            stock = $stock,
            featured = $featured,
            new_arrival = $new_arrival,
            active = $active,
            category_id = $category_id,
            brand_id = $brand_id
        WHERE product_id = $product_id
    ");

    if (!empty($_FILES['image']['name'])) {
        $mainImage = time() . '_' . $_FILES['image']['name'];
        $target = "../../uploads/products/" . $mainImage;

        move_uploaded_file($_FILES['image']['tmp_name'], $target);

        $conn->query("UPDATE products SET image='$mainImage' WHERE product_id=$product_id");
    }

    if (!empty($_FILES['images']['name'][0])) {

        foreach ($_FILES['images']['name'] as $key => $filename) {

            $imgName = time() . '_' . $filename;
            $uploadPath = "../../uploads/products/" . $imgName;

            if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $uploadPath)) {

                $conn->query("
                    INSERT INTO product_images (product_id, filename)
                    VALUES ($product_id, '$imgName')
                ");
            }
        }
    }

    header("Location: products.php");
    exit;
}
?>

<h2>Edit Product</h2>

<form method="POST" enctype="multipart/form-data">

    <label>Product Name:</label><br>
    <input type="text" name="product_name" value="<?= $product['product_name'] ?>" required><br><br>

    <label>Model:</label><br>
    <input type="text" name="model" value="<?= $product['model'] ?>" required><br><br>

    <label>Price:</label><br>
    <input type="number" name="price" step="0.01" value="<?= $product['price'] ?>" required><br><br>

    <label>Stock:</label><br>
    <input type="number" name="stock" value="<?= $product['stock'] ?>" required><br><br>

    <label>Category:</label><br>
    <select name="category_id">
        <?php while ($c = $cats->fetch_assoc()): ?>
            <option value="<?= $c['category_id'] ?>" 
                <?= $product['category_id'] == $c['category_id'] ? 'selected' : '' ?>>
                <?= $c['name'] ?>
            </option>
        <?php endwhile; ?>
    </select><br><br>

    <label>Brand:</label><br>
    <select name="brand_id">
        <?php while ($b = $brands->fetch_assoc()): ?>
            <option value="<?= $b['brand_id'] ?>"
                <?= $product['brand_id'] == $b['brand_id'] ? 'selected' : '' ?>>
                <?= $b['name'] ?>
            </option>
        <?php endwhile; ?>
    </select><br><br>

    <label><input type="checkbox" name="featured" <?= $product['featured'] ? 'checked' : '' ?>> Featured</label><br>
    <label><input type="checkbox" name="new_arrival" <?= $product['new_arrival'] ? 'checked' : '' ?>> New Arrival</label><br><br>
    <label><input type="checkbox" name="active" <?= $product['active'] ? 'checked' : '' ?>> Active</label><br><br>

    <label>Main Image:</label><br>
    <?php if ($product['image']): ?>
        <img src="../../uploads/products/<?= $product['image'] ?>" width="120"><br>
    <?php endif; ?>
    <input type="file" name="image"><br><br>

    <label>Additional Images:</label><br>
    <input type="file" name="images[]" multiple><br><br>

    <h4>Existing Additional Images</h4>
    <?php while ($img = $moreImages->fetch_assoc()): ?>
        <div style="margin-bottom:10px;">
            <img src="../../uploads/products/<?= $img['filename'] ?>" width="120" style="border:1px solid #ccc;">
            <a href="deleteImage.php?id=<?= $img['image_id'] ?>&product=<?= $product_id ?>" 
               class="btn btn-danger btn-sm"
               onclick="return confirm('Delete this image?');">
               Delete
            </a>
        </div>
    <?php endwhile; ?>

    <br><br>

    <button class="btn btn-primary">Save Changes</button>
</form>

<?php include '../../includes/footer.php'; ?>
