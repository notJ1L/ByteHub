<?php
session_start();
include '../includes/db.php';
include '../includes/functions.php';
include '../includes/header.php';

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if (isset($_POST['add_to_cart'])) {
  $id = $_POST['id'];
  $qty = (int)$_POST['qty'];
  $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + $qty;
}

if (isset($_POST['update_cart'])) {
  foreach ($_POST['qty'] as $id => $qty) {
    if ($qty <= 0) {
      unset($_SESSION['cart'][$id]);
    } else {
      $_SESSION['cart'][$id] = (int)$qty;
    }
  }
}

if (isset($_GET['remove'])) {
  unset($_SESSION['cart'][$_GET['remove']]);
  redirect('cart.php');
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <div class="mb-4">
                <h1 class="display-5 fw-bold text-dark mb-2">Your Cart</h1>
                <p class="text-muted">Review your items before checkout</p>
            </div>

            <?php if (empty($_SESSION['cart'])): ?>
                <div class="card shadow-sm text-center py-5">
                    <div class="card-body">
                        <i class="bi bi-cart-x" style="font-size: 5rem; color: #6c757d;"></i>
                        <h3 class="mt-3 mb-2">Your cart is empty</h3>
                        <p class="text-muted mb-4">Looks like you haven't added any items to your cart yet.</p>
                        <a href="index.php" class="btn btn-primary-green btn-lg">
                            <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <form method="POST" action="cart.php">
                    <div class="row">
                        <div class="col-lg-8 mb-4">
                            <div class="card shadow-sm">
                                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-cart3 me-2"></i>Cart Items
                                    </h5>
                                    <span class="badge bg-primary-green"><?php echo count($_SESSION['cart']); ?> item(s)</span>
                                </div>
                                <div class="card-body p-0">
                                    <?php
                                    $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
                                    $sql = "SELECT * FROM products WHERE product_id IN ($ids)";
                                    $result = $conn->query($sql);
                                    
                                    $total = 0;
                                    $item_count = 0;
                                    while($row = $result->fetch_assoc()):
                                        $qty = $_SESSION['cart'][$row['product_id']];
                                        $subtotal = $row['price'] * $qty;
                                        $total += $subtotal;
                                        $item_count++;
                                    ?>
                                        <div class="cart-item p-4 border-bottom">
                                            <div class="row align-items-center">
                                                <div class="col-md-2 mb-3 mb-md-0">
                                                    <img src="/bytehub/uploads/products/<?php echo htmlspecialchars($row['image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($row['product_name']); ?>" 
                                                         class="img-fluid rounded"
                                                         style="max-height: 100px; object-fit: cover; width: 100%;">
                                                </div>
                                                
                                                <div class="col-md-4 mb-3 mb-md-0">
                                                    <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($row['product_name']); ?></h6>
                                                    <small class="text-muted">Stock: <?php echo $row['stock']; ?></small>
                                                    <div class="mt-2">
                                                        <a href="cart.php?remove=<?php echo $row['product_id']; ?>" 
                                                           class="text-danger text-decoration-none"
                                                           onclick="return confirm('Remove this item from cart?');">
                                                            <i class="bi bi-trash me-1"></i>Remove
                                                        </a>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-3 mb-3 mb-md-0">
                                                    <label class="form-label small text-muted">Quantity</label>
                                                    <div class="input-group" style="max-width: 120px;">
                                                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="changeQty(<?php echo $row['product_id']; ?>, -1)">-</button>
                                                        <input type="number" 
                                                               name="qty[<?php echo $row['product_id']; ?>]" 
                                                               value="<?php echo $qty; ?>" 
                                                               min="1" 
                                                               max="<?php echo $row['stock']; ?>"
                                                               class="form-control form-control-sm text-center"
                                                               id="qty_<?php echo $row['product_id']; ?>"
                                                               required>
                                                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="changeQty(<?php echo $row['product_id']; ?>, 1)">+</button>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-3 text-md-end">
                                                    <div class="mb-1">
                                                        <small class="text-muted d-block">Unit Price</small>
                                                        <span>₱<?php echo number_format($row['price'], 2); ?></span>
                                                    </div>
                                                    <div>
                                                        <strong class="fs-5 text-primary-green">₱<?php echo number_format($subtotal, 2); ?></strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                                <div class="card-footer bg-white border-top">
                                    <button type="submit" name="update_cart" class="btn btn-outline-primary">
                                        <i class="bi bi-arrow-clockwise me-2"></i>Update Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card shadow-sm sticky-top" style="top: 20px;">
                                <div class="card-header bg-white border-bottom">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-receipt me-2"></i>Order Summary
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Subtotal:</span>
                                        <span>₱<?php echo number_format($total, 2); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Tax (12%):</span>
                                        <span>₱<?php echo number_format($total * 0.12, 2); ?></span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between mb-4">
                                        <strong class="fs-5">Total:</strong>
                                        <strong class="fs-5 text-primary-green">₱<?php echo number_format($total * 1.12, 2); ?></strong>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <a href="checkout.php" class="btn btn-primary-green btn-lg">
                                            <i class="bi bi-credit-card me-2"></i>Proceed to Checkout
                                        </a>
                                        <a href="index.php" class="btn btn-outline-secondary">
                                            <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                                        </a>
                                    </div>
                                    
                                    <div class="mt-3 p-3 bg-light rounded">
                                        <small class="text-muted">
                                            <i class="bi bi-shield-check me-1"></i>
                                            Secure checkout guaranteed
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
.cart-item {
    transition: background-color 0.2s ease;
}

.cart-item:hover {
    background-color: #f8f9fa;
}

.text-primary-green {
    color: var(--primary-green) !important;
}

.card {
    border: none;
    border-radius: 12px;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    padding: 1.25rem 1.5rem;
}

.btn-primary-green {
    background-color: var(--primary-green);
    border-color: var(--primary-green);
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary-green:hover {
    background-color: var(--secondary-green);
    border-color: var(--secondary-green);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 77, 38, 0.3);
    color: white;
}
</style>

<script>
function changeQty(productId, change) {
    const input = document.getElementById('qty_' + productId);
    let currentValue = parseInt(input.value);
    let maxValue = parseInt(input.max);
    let newValue = currentValue + change;
    
    if (newValue < 1) newValue = 1;
    if (newValue > maxValue) newValue = maxValue;
    
    input.value = newValue;
}
</script>

<?php include '../includes/footer.php'; ?>
