<?php
session_start();
include '../includes/db.php';
include '../includes/functions.php';
require_once '../includes/config.php';
require_once '../vendor/autoload.php';

/* --- Require login --- */
if (!isset($_SESSION['user_id'])) {
  $_SESSION['login_message'] = 'Please log in to proceed to checkout.';
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

  /* --- Send confirmation email to the user (non-blocking) --- */
  try {
    // Get user email
    $userStmt = $conn->prepare("SELECT email, username FROM users WHERE user_id = ?");
    $userStmt->bind_param('i', $user_id);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $userData = $userResult->fetch_assoc();
    $userStmt->close();

    if ($userData && !empty($userData['email'])) {
      // Fetch order items for email
      $itemsStmt = $conn->prepare("SELECT name_snapshot, unit_price_snapshot, quantity, line_total FROM order_items WHERE order_id = ?");
      $itemsStmt->bind_param('i', $order_id);
      $itemsStmt->execute();
      $itemsResult = $itemsStmt->get_result();
      $orderItems = [];
      while ($item = $itemsResult->fetch_assoc()) {
        $orderItems[] = $item;
      }
      $itemsStmt->close();

      $mail = new PHPMailer\PHPMailer\PHPMailer(true);

      $mail->isSMTP();
      $mail->Host       = MAILTRAP_HOST;
      $mail->SMTPAuth   = true;
      $mail->Username   = MAILTRAP_USER;
      $mail->Password   = MAILTRAP_PASS;
      // Mailtrap sandbox port 2525 doesn't use encryption
      if (MAILTRAP_PORT != 2525) {
          $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
      }
      $mail->Port       = MAILTRAP_PORT;
      $mail->CharSet    = 'UTF-8';

      $mail->setFrom('noreply@bytehub.com', 'ByteHub');
      $mail->addAddress($userData['email'], $userData['username'] ?? '');

      $mail->isHTML(true);
      $mail->Subject = 'Your ByteHub Order ' . $order_code;

      // Professional HTML email template
      $body  = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
      $body .= '<title>Your ByteHub Order ' . htmlspecialchars($order_code) . '</title>';
      $body .= '</head><body style="font-family: Arial, sans-serif; background-color:#f5f5f5; padding:24px;">';
      $body .= '<table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);">';
      
      // Header
      $body .= '<tr><td style="background:linear-gradient(135deg, #004d26 0%, #1e7a34 100%);color:#ffffff;padding:20px 24px;font-size:22px;font-weight:bold;">ByteHub</td></tr>';
      
      // Content
      $body .= '<tr><td style="padding:24px;">';
      $body .= '<h2 style="margin:0 0 16px 0;color:#212529;font-size:24px;">Thank you for your order!</h2>';
      $body .= '<p style="margin:0 0 12px 0;color:#495057;font-size:15px;">Hi ' . htmlspecialchars($userData['username'] ?? 'there') . ',</p>';
      $body .= '<p style="margin:0 0 20px 0;color:#495057;font-size:15px;">We\'ve received your order and are getting it ready for you.</p>';
      
      // Order Code
      $body .= '<div style="background:#f8f9fa;padding:12px 16px;border-radius:6px;margin-bottom:20px;border-left:4px solid #004d26;">';
      $body .= '<strong style="color:#6c757d;font-size:13px;">Order Code:</strong> ';
      $body .= '<span style="color:#004d26;font-weight:bold;font-size:16px;">' . htmlspecialchars($order_code) . '</span>';
      $body .= '</div>';
      
      // Products List
      $body .= '<h3 style="margin:24px 0 12px 0;color:#212529;font-size:18px;font-weight:600;">Order Items</h3>';
      $body .= '<table cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;margin-bottom:20px;">';
      $body .= '<thead><tr style="background:#f8f9fa;">';
      $body .= '<th style="padding:12px;text-align:left;border-bottom:2px solid #dee2e6;color:#495057;font-size:13px;font-weight:600;">Product</th>';
      $body .= '<th style="padding:12px;text-align:center;border-bottom:2px solid #dee2e6;color:#495057;font-size:13px;font-weight:600;">Qty</th>';
      $body .= '<th style="padding:12px;text-align:right;border-bottom:2px solid #dee2e6;color:#495057;font-size:13px;font-weight:600;">Unit Price</th>';
      $body .= '<th style="padding:12px;text-align:right;border-bottom:2px solid #dee2e6;color:#495057;font-size:13px;font-weight:600;">Subtotal</th>';
      $body .= '</tr></thead><tbody>';
      
      foreach ($orderItems as $item) {
        $body .= '<tr style="border-bottom:1px solid #e9ecef;">';
        $body .= '<td style="padding:12px;color:#212529;font-size:14px;">' . htmlspecialchars($item['name_snapshot']) . '</td>';
        $body .= '<td style="padding:12px;text-align:center;color:#495057;font-size:14px;">' . (int)$item['quantity'] . '</td>';
        $body .= '<td style="padding:12px;text-align:right;color:#495057;font-size:14px;">&#8369;' . number_format($item['unit_price_snapshot'], 2) . '</td>';
        $body .= '<td style="padding:12px;text-align:right;color:#212529;font-size:14px;font-weight:600;">&#8369;' . number_format($item['line_total'], 2) . '</td>';
        $body .= '</tr>';
      }
      
      $body .= '</tbody></table>';
      
      // Order Summary
      $body .= '<table cellpadding="0" cellspacing="0" style="width:100%;margin:20px 0;border-collapse:collapse;">';
      $body .= '<tr><td style="padding:8px 0;color:#6c757d;font-size:14px;">Subtotal:</td><td style="padding:8px 0;text-align:right;color:#212529;font-size:14px;">&#8369;' . number_format($subtotal, 2) . '</td></tr>';
      $body .= '<tr><td style="padding:8px 0;color:#6c757d;font-size:14px;">Tax (12%):</td><td style="padding:8px 0;text-align:right;color:#212529;font-size:14px;">&#8369;' . number_format($tax, 2) . '</td></tr>';
      $body .= '<tr><td style="padding:12px 0;border-top:2px solid #dee2e6;font-weight:bold;color:#212529;font-size:16px;">Grand Total:</td>';
      $body .= '<td style="padding:12px 0;border-top:2px solid #dee2e6;text-align:right;font-weight:bold;color:#004d26;font-size:18px;">&#8369;' . number_format($total, 2) . '</td></tr>';
      $body .= '</table>';
      
      $body .= '<p style="margin:20px 0;color:#495057;font-size:14px;line-height:1.6;">You can view your full order details and track its status at any time in your ByteHub account under <strong>My Orders</strong>.</p>';
      $body .= '<p style="margin:24px 0 8px 0;color:#495057;font-size:15px;">Thank you for shopping with ByteHub!</p>';
      $body .= '<p style="margin:0;color:#6c757d;font-size:13px;">If you didn\'t place this order, please contact our support team immediately.</p>';
      $body .= '</td></tr>';
      
      // Footer
      $body .= '<tr><td style="background:#f1f3f5;padding:16px 24px;text-align:center;font-size:12px;color:#868e96;border-top:1px solid #dee2e6;">&copy; ' . date('Y') . ' ByteHub. All rights reserved.</td></tr>';
      $body .= '</table></body></html>';

      // Plain text alternative
      $altBody = "Thank you for your order at ByteHub!\n\n";
      $altBody .= "Order Code: " . $order_code . "\n\n";
      $altBody .= "Order Items:\n";
      foreach ($orderItems as $item) {
        $altBody .= "- " . $item['name_snapshot'] . " (Qty: " . (int)$item['quantity'] . ") - ₱" . number_format($item['line_total'], 2) . "\n";
      }
      $altBody .= "\nSubtotal: ₱" . number_format($subtotal, 2) . "\n";
      $altBody .= "Tax (12%): ₱" . number_format($tax, 2) . "\n";
      $altBody .= "Grand Total: ₱" . number_format($total, 2) . "\n\n";
      $altBody .= "You can view your order details in My Orders.";

      $mail->Body    = $body;
      $mail->AltBody = $altBody;

      // Send without breaking checkout flow if it fails
      $mail->send();
    }
  } catch (Throwable $e) {
    // Fail silently; order placement should not break because of email
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
