<?php
session_start();
include '../includes/db.php';
include '../includes/functions.php';

/* --- Require login --- */
if (!isset($_SESSION['user_id'])) {
  $_SESSION['redirect_after_login'] = 'checkout.php';
  redirect('login.php');
}

/* --- Check for empty cart --- */
if (empty($_SESSION['cart'])) {
  redirect('cart.php');
}

/* --- When the form is submitted --- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = (int)$_SESSION['user_id'];               // use logged-in user ID
  $payment_method = $_POST['payment_method'];
  $order_code = 'ORDER-' . rand(1000, 9999);

  $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
  $sql = "SELECT * FROM products WHERE product_id IN ($ids)";
  $result = $conn->query($sql);

  if (!$result) {
    die("Query failed: " . $conn->error);
  }

  $subtotal = 0;
  while ($row = $result->fetch_assoc()) {
    $subtotal += $row['price'] * $_SESSION['cart'][$row['product_id']];
  }
  $tax = round($subtotal * 0.12, 2);
  $total = round($subtotal + $tax, 2);

  /* --- Insert order --- */
  $stmt = $conn->prepare("INSERT INTO orders (user_id, order_code, payment_method, subtotal, tax, total, status) 
                          VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
  $stmt->bind_param('issddd', $user_id, $order_code, $payment_method, $subtotal, $tax, $total);
  $stmt->execute();
  $order_id = $stmt->insert_id;
  $stmt->close();

  /* --- Insert order items and update stock --- */
  $result->data_seek(0);
  while ($row = $result->fetch_assoc()) {
    $pid = (int)$row['product_id'];
    $qty = (int)$_SESSION['cart'][$pid];
    $line_total = $row['price'] * $qty;

    $stmt = $conn->prepare("INSERT INTO order_items 
      (name_snapshot, unit_price_snapshot, quantity, line_total, order_id, product_id)
      VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sdddid', $row['product_name'], $row['price'], $qty, $line_total, $order_id, $pid);
    $stmt->execute();
    $stmt->close();

    $conn->query("UPDATE products SET stock = stock - $qty WHERE product_id = $pid");
  }

  $_SESSION['cart'] = [];
  redirect("thankyou.php?code=$order_code");
}

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Page Header -->
            <div class="mb-4">
                <h1 class="display-5 fw-bold text-dark mb-2">Checkout</h1>
                <p class="text-muted">Review your order and complete your purchase</p>
            </div>

            <!-- Order Summary Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-cart-check me-2"></i>Order Summary
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
                    $sql = "SELECT * FROM products WHERE product_id IN ($ids)";
                    $result = $conn->query($sql);
                    
                    $subtotal = 0;
                    while ($row = $result->fetch_assoc()) {
                        $qty = $_SESSION['cart'][$row['product_id']];
                        $line_total = $row['price'] * $qty;
                        $subtotal += $line_total;
                    ?>
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div class="d-flex align-items-center">
                                <img src="/bytehub/uploads/products/<?php echo htmlspecialchars($row['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($row['product_name']); ?>" 
                                     class="me-3" 
                                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($row['product_name']); ?></h6>
                                    <small class="text-muted">Quantity: <?php echo $qty; ?></small>
                                </div>
                            </div>
                            <div class="text-end">
                                <strong>₱<?php echo number_format($line_total, 2); ?></strong>
                            </div>
                        </div>
                    <?php 
                    }
                    $tax = round($subtotal * 0.12, 2);
                    $total = round($subtotal + $tax, 2);
                    $result->data_seek(0);
                    ?>
                    
                    <div class="mt-3 pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal:</span>
                            <span>₱<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tax (12%):</span>
                            <span>₱<?php echo number_format($tax, 2); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong class="fs-5">Total:</strong>
                            <strong class="fs-5 text-primary-green">₱<?php echo number_format($total, 2); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Checkout Form -->
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-credit-card me-2"></i>Payment Information
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-4">
                            <label for="payment_method" class="form-label fw-semibold">
                                <i class="bi bi-wallet2 me-2"></i>Payment Method
                            </label>
                            <select name="payment_method" id="payment_method" class="form-select form-select-lg" required>
                                <option value="Cash on Delivery">Cash on Delivery</option>
                                <option value="Credit Card">Credit Card (Mock)</option>
                                <option value="GCash">GCash</option>
                                <option value="PayMaya">PayMaya</option>
                            </select>
                            <div class="form-text">Select your preferred payment method</div>
                        </div>

                        <div class="alert alert-info d-flex align-items-center" role="alert">
                            <i class="bi bi-info-circle me-2"></i>
                            <div>
                                <strong>Note:</strong> Your order will be processed once payment is confirmed.
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary-green btn-lg py-3">
                                <i class="bi bi-check-circle me-2"></i>Place Order
                            </button>
                            <a href="cart.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Back to Cart
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
.text-primary-green {
    color: var(--primary-green) !important;
}
</style>

<?php include '../includes/footer.php'; ?>
