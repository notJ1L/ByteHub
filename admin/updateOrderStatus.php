<?php
if (!ob_get_level()) {
    ob_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    include '../includes/db.php';
    include '../includes/functions.php';
    include '../includes/config.php';
    
    if (!isAdmin()) {
        if (ob_get_level()) ob_end_clean();
        redirect('../customer/index.php');
    }
    
    $id = $_GET['id'] ?? 0;
    $status = $_POST['status'];
    
    $phpmailer_available = false;
    
    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($autoload)) {
        require_once $autoload;
    }
    
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $legacyBase = __DIR__ . '/../includes/PHPMailer/';
        if (file_exists($legacyBase . 'PHPMailer.php')) {
            @require_once $legacyBase . 'Exception.php';
            @require_once $legacyBase . 'PHPMailer.php';
            @require_once $legacyBase . 'SMTP.php';
        }
    }
    
    if (class_exists('PHPMailer\PHPMailer\PHPMailer') || class_exists('PHPMailer')) {
        $phpmailer_available = true;
    }
    
    error_log("PHPMailer available: " . ($phpmailer_available ? 'YES' : 'NO'));
    error_log("MAILTRAP_HOST defined: " . (defined('MAILTRAP_HOST') ? 'YES (' . MAILTRAP_HOST . ')' : 'NO'));
    
    $stmt = $conn->prepare("SELECT o.*, u.username, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.user_id WHERE o.order_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    
    if (!$order) {
        if (ob_get_level()) ob_end_clean();
        redirect('orders.php?error=not_found');
    }

    $old_status = $order['status'];
    $status_changed = ($old_status !== $status);

    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE order_id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();

    $email_sent = false;
    $email_error = null;

    if ($status_changed && !empty($order['email'])) {
        if ($phpmailer_available && defined('MAILTRAP_HOST') && !empty(MAILTRAP_HOST)) {
        try {
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                $encryption_class = '\PHPMailer\PHPMailer\PHPMailer';
            } elseif (class_exists('PHPMailer')) {
                $mail = new PHPMailer(true);
                $encryption_class = 'PHPMailer';
            } else {
                throw new Exception('PHPMailer class not found');
            }

            $mail->isSMTP();
            $mail->Host       = MAILTRAP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAILTRAP_USER;
            $mail->Password   = MAILTRAP_PASS;
            
            if (MAILTRAP_PORT != 2525) {
                if (defined($encryption_class . '::ENCRYPTION_STARTTLS')) {
                    $mail->SMTPSecure = constant($encryption_class . '::ENCRYPTION_STARTTLS');
                } else {
                    $mail->SMTPSecure = 'tls';
                }
            }
            $mail->Port = MAILTRAP_PORT;

            $mail->setFrom('no-reply@bytehub.com', 'ByteHub');
            $mail->addAddress($order['email'], $order['username']);

            $mail->isHTML(true);
            $mail->Subject = 'Order Status Update - ' . $order['order_code'];
            
            $order_items_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $order_items_stmt->bind_param("i", $id);
            $order_items_stmt->execute();
            $order_items = $order_items_stmt->get_result();

            $status_colors = [
                'Pending' => '#ffc107',
                'Processing' => '#17a2b8',
                'Shipped' => '#007bff',
                'Completed' => '#28a745',
                'Cancelled' => '#dc3545'
            ];
            $status_color = $status_colors[$status] ?? '#6c757d';

            $email_body = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #004d26 0%, #006633 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                    .status-badge { display: inline-block; padding: 10px 20px; background: ' . $status_color . '; color: white; border-radius: 5px; font-weight: bold; font-size: 18px; margin: 20px 0; }
                    .order-info { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
                    .order-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                    .order-table th, .order-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
                    .order-table th { background: #f8f9fa; font-weight: bold; }
                    .total-section { background: white; padding: 20px; border-radius: 8px; margin-top: 20px; }
                    .total-row { display: flex; justify-content: space-between; padding: 8px 0; }
                    .grand-total { font-size: 20px; font-weight: bold; color: #004d26; border-top: 2px solid #004d26; padding-top: 10px; margin-top: 10px; }
                    .footer { text-align: center; margin-top: 30px; color: #6c757d; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>Order Status Update</h1>
                    </div>
                    <div class="content">
                        <p>Hello ' . htmlspecialchars($order['username']) . ',</p>
                        <p>Your order status has been updated:</p>
                        <div style="text-align: center;">
                            <div class="status-badge">' . htmlspecialchars($status) . '</div>
                        </div>
                        <div class="order-info">
                            <h3 style="margin-top: 0;">Order Details</h3>
                            <p><strong>Order Code:</strong> ' . htmlspecialchars($order['order_code']) . '</p>
                            <p><strong>Previous Status:</strong> ' . htmlspecialchars($old_status) . '</p>
                            <p><strong>New Status:</strong> ' . htmlspecialchars($status) . '</p>
                            <p><strong>Order Date:</strong> ' . date('F j, Y g:i A', strtotime($order['created_at'])) . '</p>
                        </div>
                        <h3>Order Items</h3>
                        <table class="order-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>';

            while ($item = $order_items->fetch_assoc()) {
                $email_body .= '
                                <tr>
                                    <td>' . htmlspecialchars($item['name_snapshot']) . '</td>
                                    <td>' . htmlspecialchars($item['quantity']) . '</td>
                                    <td>PHP' . number_format($item['unit_price_snapshot'], 2) . '</td>
                                    <td>PHP' . number_format($item['line_total'], 2) . '</td>
                                </tr>';
            }

            $email_body .= '
                            </tbody>
                        </table>
                        <div class="total-section">
                            <div class="total-row">
                                <span>Subtotal:</span>
                                <span>PHP' . number_format($order['subtotal'], 2) . '</span>
                            </div>
                            <div class="total-row">
                                <span>Tax:</span>
                                <span>PHP' . number_format($order['tax'], 2) . '</span>
                            </div>
                            <div class="total-row grand-total">
                                <span>Grand Total:</span>
                                <span>PHP' . number_format($order['total'], 2) . '</span>
                            </div>
                        </div>
                        <p>Thank you for shopping with ByteHub!</p>
                        <p>If you have any questions, please contact our support team.</p>
                    </div>
                    <div class="footer">
                        <p>This is an automated email from ByteHub. Please do not reply to this email.</p>
                    </div>
                </div>
            </body>
            </html>';

            $mail->Body = $email_body;
            $mail->AltBody = "Hello " . $order['username'] . ",\n\nYour order #" . $order['order_code'] . " status has been updated from " . $old_status . " to " . $status . ".\n\nThank you for shopping with ByteHub!";
            
            $mail->send();
            $email_sent = true;
            error_log("Order update email sent successfully to {$order['email']} for order #{$order['order_code']}");
        } catch (Exception $e) {
            $email_error = $e->getMessage();
            error_log("Order update email failed for order #{$order['order_code']}: " . $e->getMessage());
        } catch (\Exception $e) {
            $email_error = $e->getMessage();
            error_log("Order update email failed for order #{$order['order_code']}: " . $e->getMessage());
        }
        } else {
            $email_error = "PHPMailer not available or SMTP not configured";
            error_log("Order update email failed: PHPMailer not available or SMTP not configured for order #{$order['order_code']}");
        }
    } else {
        if (!$status_changed) {
            error_log("Order update email skipped: Status unchanged for order #{$order['order_code']}");
        } elseif (empty($order['email'])) {
            error_log("Order update email skipped: No email address for order #{$order['order_code']}");
        }
    }

    if (ob_get_level()) ob_end_clean();
    $redirect_url = "orders.php?status_updated=1";
    if ($email_sent) {
        $redirect_url .= "&email_sent=1";
    } elseif ($email_error) {
        $redirect_url .= "&email_error=" . urlencode($email_error);
    }
    redirect($redirect_url);
}

include '../includes/db.php';
include '../includes/functions.php';
include '../includes/config.php';

if (!isAdmin()) {
    if (ob_get_level()) ob_end_clean();
    redirect('../customer/index.php');
}

$phpmailer_path = __DIR__ . '/../includes/PHPMailer/';
$phpmailer_file = $phpmailer_path . 'PHPMailer.php';
$phpmailer_available = false;

if (file_exists($phpmailer_file)) {
    $file_content = file_get_contents($phpmailer_file);
    if (strlen(trim($file_content)) > 500 && 
        (strpos($file_content, 'class PHPMailer') !== false || 
         strpos($file_content, 'namespace PHPMailer') !== false)) {
        try {
            @require_once $phpmailer_path . 'Exception.php';
            @require_once $phpmailer_file;
            @require_once $phpmailer_path . 'SMTP.php';
            if (class_exists('PHPMailer\PHPMailer\PHPMailer') || class_exists('PHPMailer')) {
                $phpmailer_available = true;
            }
        } catch (Exception $e) {
            $phpmailer_available = false;
        } catch (\Exception $e) {
            $phpmailer_available = false;
        }
    }
}

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT o.*, u.username, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.user_id WHERE o.order_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    if (ob_get_level()) ob_end_clean();
    redirect('orders.php?error=not_found');
}

if (ob_get_level()) {
    ob_end_flush();
}

include '../includes/admin_header.php';
?>

<div class="admin-content">
    <div class="container-fluid">
        <div class="page-header mb-4">
            <div>
                <h2 class="page-title">
                    <i class="bi bi-pencil-square me-2"></i>Update Order Status
                </h2>
                <p class="text-muted mb-0">Order Code: <strong><?php echo htmlspecialchars($order['order_code']); ?></strong></p>
            </div>
            <a href="orders.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Orders
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="post">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Order Information</label>
                                <div class="p-3 bg-light rounded">
                                    <p class="mb-2"><strong>Order Code:</strong> <?php echo htmlspecialchars($order['order_code']); ?></p>
                                    <p class="mb-2"><strong>Customer:</strong> <?php echo htmlspecialchars($order['username']); ?> (<?php echo htmlspecialchars($order['email']); ?>)</p>
                                    <p class="mb-2"><strong>Current Status:</strong> 
                                        <span class="badge bg-<?php 
                                            echo $order['status'] == 'Completed' ? 'success' : 
                                                ($order['status'] == 'Processing' ? 'info' : 
                                                ($order['status'] == 'Shipped' ? 'primary' : 
                                                ($order['status'] == 'Pending' ? 'warning' : 'danger'))); 
                                        ?>">
                                            <?php echo htmlspecialchars($order['status']); ?>
                                        </span>
                                    </p>
                                    <p class="mb-0"><strong>Total:</strong> ₱<?php echo number_format($order['total'], 2); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Update Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="Pending" <?php echo $order['status']=='Pending'?'selected':''; ?>>Pending</option>
                                    <option value="Processing" <?php echo $order['status']=='Processing'?'selected':''; ?>>Processing</option>
                                    <option value="Shipped" <?php echo $order['status']=='Shipped'?'selected':''; ?>>Shipped</option>
                                    <option value="Completed" <?php echo $order['status']=='Completed'?'selected':''; ?>>Completed</option>
                                    <option value="Cancelled" <?php echo $order['status']=='Cancelled'?'selected':''; ?>>Cancelled</option>
                                </select>
                                <small class="text-muted">The customer will receive an email notification about this status change.</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex gap-2">
                        <button name="update" type="submit" class="btn btn-primary-green">
                            <i class="bi bi-check-circle me-2"></i>Update Status
                        </button>
                        <a href="orders.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
.form-label {
    margin-bottom: 0.5rem;
    color: #495057;
}

.form-select {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 0.625rem 1rem;
    color: #212529 !important;
    background-color: #fff !important;
}

.form-select:focus {
    border-color: var(--primary-green);
    box-shadow: 0 0 0 3px rgba(0, 77, 38, 0.1);
    outline: none;
    color: #212529 !important;
    background-color: #fff !important;
}

.btn-primary-green {
    background-color: var(--primary-green);
    border-color: var(--primary-green);
    color: white;
    font-weight: 600;
}

.btn-primary-green:hover {
    background-color: var(--secondary-green);
    border-color: var(--secondary-green);
    color: white;
}
</style>

<?php include 'footer.php'; ?>
