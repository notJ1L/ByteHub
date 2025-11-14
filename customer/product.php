<?php
include '../includes/db.php';
include '../includes/functions.php';
include '../includes/header.php';

$id = $_GET['id'] ?? 0;
$sql = "SELECT * FROM products WHERE product_id = $id AND active = 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $p = $result->fetch_assoc();

    // Fetch gallery images
    $gallery = $conn->query("SELECT filename FROM product_images WHERE product_id = $id");
?>
    <div class="product-details">

        <!-- Main Image -->
        <div class="main-image">
            <img id="mainProductImage"
                 src="../uploads/products/<?php echo $p['image']; ?>"
                 alt="<?php echo $p['product_name']; ?>"
                 width="350"
                 style="border:1px solid #ccc;">
        </div>

        <!-- Thumbnails -->
        <div class="thumbnail-row" style="margin-top:15px; display:flex; gap:10px;">
            <!-- Main image thumbnail -->
            <img src="../uploads/products/<?php echo $p['image']; ?>"
                 width="70"
                 onclick="changeImage(this.src)"
                 style="cursor:pointer; border:1px solid #aaa;">

            <!-- Additional gallery images -->
            <?php while ($g = $gallery->fetch_assoc()): ?>
                <img src="../uploads/products/<?php echo $g['filename']; ?>"
                     width="70"
                     onclick="changeImage(this.src)"
                     style="cursor:pointer; border:1px solid #aaa;">
            <?php endwhile; ?>
        </div>

        <!-- Product Info -->
        <h2><?php echo $p['product_name']; ?></h2>
        <p>Price: $<?php echo number_format($p['price'], 2); ?></p>
        <p>Stock: <?php echo $p['stock']; ?></p>
        <p>Model: <?php echo $p['model']; ?></p>
        
        <!-- Tabs -->
        <div class="tabs" style="margin-top:30px;">
          <button onclick="showTab('desc')" class="tab-btn">Description</button>
          <button onclick="showTab('specs')" class="tab-btn">Specifications</button>
        </div>

        <!-- Description -->
        <div id="desc" class="tab-content">
          <?php echo nl2br($p['description']); ?>
        </div>

        <!-- Specifications -->
        <div id="specs" class="tab-content" style="display:none;">
           <?php echo nl2br($p['specifications']); ?>
        </div>

<script>
function showTab(tab) {
    document.getElementById('desc').style.display = 'none';
    document.getElementById('specs').style.display = 'none';

    document.getElementById(tab).style.display = 'block';
}
</script>


        <form method="post" action="cart.php">
            <input type="hidden" name="id" value="<?php echo $p['product_id']; ?>">
            <input type="number" name="qty" value="1" min="1" max="<?php echo $p['stock']; ?>">
            <button type="submit" name="add_to_cart" class="btn">Add to Cart</button>
        </form>

    </div>

    <script>
    // JS: Click thumbnail → change main image
    function changeImage(src) {
        document.getElementById("mainProductImage").src = src;
    }
    </script>

<?php
} else {
    echo "<p>Product not found.</p>";
}

include '../includes/footer.php';
?>
