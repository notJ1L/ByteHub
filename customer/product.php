<?php
include '../includes/db.php';
include '../includes/functions.php';
include '../includes/header.php';

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT p.*, b.name as brand_name FROM products p JOIN brands b ON p.brand_id = b.brand_id WHERE p.product_id = ? AND p.active = 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $p = $result->fetch_assoc();

    $gallery_stmt = $conn->prepare("SELECT filename FROM product_images WHERE product_id = ?");
    $gallery_stmt->bind_param('i', $id);
    $gallery_stmt->execute();
    $gallery = $gallery_stmt->get_result();
?>
<div class="row">
    <!-- Product Image Gallery -->
    <div class="col-md-6">
        <img id="mainProductImage" src="/bytehub/uploads/products/<?php echo $p['image']; ?>" class="img-fluid rounded mb-3" alt="<?php echo htmlspecialchars($p['product_name']); ?>">
        <div class="d-flex">
            <img src="/bytehub/uploads/products/<?php echo $p['image']; ?>" width="80" class="img-thumbnail me-2" onclick="changeImage(this.src)" style="cursor:pointer;">
            <?php while ($g = $gallery->fetch_assoc()): ?>
                <img src="/bytehub/uploads/products/<?php echo $g['filename']; ?>" width="80" class="img-thumbnail me-2" onclick="changeImage(this.src)" style="cursor:pointer;">
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Product Details -->
    <div class="col-md-6">
        <h2><?php echo htmlspecialchars($p['product_name']); ?></h2>
        <p class="text-muted"><?php echo htmlspecialchars($p['brand_name']); ?></p>
        <p class="fs-4 fw-bold">₱<?php echo number_format($p['price'], 2); ?></p>
        <p class="text-success"><?php echo ($p['stock'] > 0) ? 'Available: In Stock' : 'Out of Stock'; ?></p>

        <form method="post" action="cart.php">
            <input type="hidden" name="id" value="<?php echo $p['product_id']; ?>">
            <div class="d-flex align-items-center mb-3">
                <label for="quantity" class="me-3">Quantity</label>
                <div class="quantity-selector">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="changeQty(-1)">-</button>
                    <input type="number" name="qty" id="quantity" value="1" min="1" max="<?php echo $p['stock']; ?>" class="form-control text-center mx-2" style="width: 60px;">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="changeQty(1)">+</button>
                </div>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" name="add_to_cart" class="btn btn-warning btn-lg">Add to Cart</button>
                <button type="submit" name="buy_now" class="btn btn-dark btn-lg">Buy It Now</button>
            </div>
        </form>
    </div>
</div>

<!-- Description, Specs, Reviews Tabs -->
<div class="mt-5">
    <ul class="nav nav-tabs" id="productTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab" aria-controls="description" aria-selected="true">Description</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs" type="button" role="tab" aria-controls="specs" aria-selected="false">Specifications</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab" aria-controls="reviews" aria-selected="false">Reviews</button>
        </li>
    </ul>
    <div class="tab-content p-3 border-top-0 border">
        <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
            <?php echo nl2br(htmlspecialchars($p['description'])); ?>
        </div>
        <div class="tab-pane fade" id="specs" role="tabpanel" aria-labelledby="specs-tab">
            <?php echo nl2br(htmlspecialchars($p['specifications'])); ?>
        </div>
        <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
            <!-- Reviews Section -->
        </div>
    </div>
</div>

<script>
function changeImage(src) {
    document.getElementById("mainProductImage").src = src;
}

function changeQty(amount) {
    const qtyInput = document.getElementById('quantity');
    let currentValue = parseInt(qtyInput.value);
    let newValue = currentValue + amount;
    if (newValue < 1) newValue = 1;
    if (newValue > <?php echo $p['stock']; ?>) newValue = <?php echo $p['stock']; ?>;
    qtyInput.value = newValue;
}
</script>

<?php
} else {
    echo "<p>Product not found.</p>";
}

include '../includes/footer.php';
?>
