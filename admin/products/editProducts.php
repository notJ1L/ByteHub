<?php
include '../../includes/db.php';
include '../../includes/functions.php';
include '../../includes/admin_header.php';

$id = $_GET['id'];

// Fetch product
$sql = "SELECT * FROM products WHERE product_id = $id LIMIT 1";
$product = $conn->query($sql)->fetch_assoc();

// Fetch categories
$catQuery = $conn->query("SELECT * FROM categories ORDER BY name ASC");

// Fetch brands
$brandQuery = $conn->query("SELECT * FROM brands ORDER BY name ASC");

// Handle update
if (isset($_POST['update'])) {
    $name = $_POST['product_name'];
    $model = $_POST['model'];
    $category = $_POST['category_id'];
    $brand = $_POST['brand_id'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    $featured = $_POST['featured'];
    $new_arrival = $_POST['new_arrival'];
    $active = $_POST['active'];

    $imageName = $product['image'];

    // If user chose new image
    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . "_" . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . $imageName);
    }

    $update = "UPDATE products SET
                product_name='$name',
                model='$model',
                category_id='$category',
                brand_id='$brand',
                price='$price',
                stock='$stock',
                featured='$featured',
                new_arrival='$new_arrival',
                active='$active',
                image='$imageName'
                WHERE product_id = $id";

    if ($conn->query($update)) {
        header("Location: products.php?updated=1");
        exit();
    } else {
        $error = "Update failed!";
    }
}
?>

<div class="container mt-4">
    <h2>Edit Product</h2>

    <?php if(isset($error)){ echo "<div class='alert alert-danger'>$error</div>"; } ?>

    <form method="post" enctype="multipart/form-data">

        <label>Product Name:</label>
        <input type="text" name="product_name" class="form-control" value="<?php echo $product['product_name']; ?>" required>

        <label class="mt-3">Model:</label>
        <input type="text" name="model" class="form-control" value="<?php echo $product['model']; ?>" required>

        <label class="mt-3">Category:</label>
        <select name="category_id" class="form-control">
            <?php while($cat = $catQuery->fetch_assoc()): ?>
                <option value="<?php echo $cat['category_id']; ?>"
                    <?php echo ($product['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                    <?php echo $cat['name']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label class="mt-3">Brand:</label>
        <select name="brand_id" class="form-control">
            <?php while($brand = $brandQuery->fetch_assoc()): ?>
                <option value="<?php echo $brand['brand_id']; ?>"
                    <?php echo ($product['brand_id'] == $brand['brand_id']) ? 'selected' : ''; ?>>
                    <?php echo $brand['name']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label class="mt-3">Price:</label>
        <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $product['price']; ?>" required>

        <label class="mt-3">Stock:</label>
        <input type="number" name="stock" class="form-control" value="<?php echo $product['stock']; ?>" required>

        <label class="mt-3">Featured:</label>
        <select name="featured" class="form-control">
            <option value="1" <?php echo $product['featured'] ? 'selected' : ''; ?>>Yes</option>
            <option value="0" <?php echo !$product['featured'] ? 'selected' : ''; ?>>No</option>
        </select>

        <label class="mt-3">New Arrival:</label>
        <select name="new_arrival" class="form-control">
            <option value="1" <?php echo $product['new_arrival'] ? 'selected' : ''; ?>>Yes</option>
            <option value="0" <?php echo !$product['new_arrival'] ? 'selected' : ''; ?>>No</option>
        </select>

        <label class="mt-3">Active:</label>
        <select name="active" class="form-control">
            <option value="1" <?php echo $product['active'] ? 'selected' : ''; ?>>Active</option>
            <option value="0" <?php echo !$product['active'] ? 'selected' : ''; ?>>Inactive</option>
        </select>

        <label class="mt-3">Current Image:</label><br>
        <img src="../uploads/<?php echo $product['image']; ?>" width="120"><br>

        <label class="mt-3">Upload New Image:</label>
        <input type="file" name="image" class="form-control">

        <button name="update" class="btn btn-primary mt-4">Update Product</button>
        <a href="products.php" class="btn btn-secondary mt-4">Cancel</a>
    </form>

</div>

<?php include '../../includes/footer.php'; ?>
