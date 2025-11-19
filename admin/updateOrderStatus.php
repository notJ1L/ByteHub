<?php
// Start output buffering FIRST - before any includes
if (!ob_get_level()) {
    ob_start();
}

// Process POST request IMMEDIATELY (before any includes that might output)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    // Include only what we need for POST processing
    include '../includes/db.php';
    include '../includes/functions.php';
    include '../includes/config.php';
    
    if (!isAdmin()) {
        if (ob_get_level()) ob_end_clean();
        redirect('../customer/index.php');
    }
    
    $id = $_GET['id'] ?? 0;
    $status = $_POST['status'];
    
    // Check if PHPMailer is properly installed and available
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
    
    // Get order info for email
    $stmt = $conn->prepare("SELECT o.*, u.username, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.user_id WHERE o.order_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    
    if (!$order) {
        if (ob_get_level()) ob_end_clean();
        redirect('orders.php?error=not_found');
    }

    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE order_id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();

    // Send email notification (if PHPMailer is available and properly configured)
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
            
            // Mailtrap sandbox port 2525 doesn't use encryption
            if (MAILTRAP_PORT != 2525) {
                if (defined($encryption_class . '::ENCRYPTION_STARTTLS')) {
                    $mail->SMTPSecure = constant($encryption_class . '::ENCRYPTION_STARTTLS');
                } else {
                    $mail->SMTPSecure = 'tls';
                }
            }
            $mail->Port       = MAILTRAP_PORT;

            $user_stmt = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
            $user_stmt->bind_param("i", $order['user_id']);
            $user_stmt->execute();
            $user = $user_stmt->get_result()->fetch_assoc();

            if ($user && !empty($user['email'])) {
                $mail->setFrom('no-reply@bytehub.com', 'ByteHub');
                $mail->addAddress($user['email']);

                $mail->isHTML(true);
                $mail->Subject = 'Your Order Status has been Updated';
                
                $order_items_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
                $order_items_stmt->bind_param("i", $id);
                $order_items_stmt->execute();
                $order_items = $order_items_stmt->get_result();

                $email_body = "<h1>Your order #{$order['order_code']} is now {$status}</h1>";
                $email_body .= "<table border='1' cellpadding='5'><tr><th>Product</th><th>Quantity</th><th>Price</th><th>Total</th></tr>";
                while ($item = $order_items->fetch_assoc()) {
                    $email_body .= "<tr><td>{$item['name_snapshot']}</td><td>{$item['quantity']}</td><td>{$item['unit_price_snapshot']}</td><td>{$item['line_total']}</td></tr>";
                }
                $email_body .= "</table>";
                $email_body .= "<p>Subtotal: {$order['subtotal']}</p>";
                $email_body .= "<p>Tax: {$order['tax']}</p>";
                $email_body .= "<p><strong>Grand Total: {$order['total']}</strong></p>";

                $mail->Body = $email_body;
                $mail->send();
            }
        } catch (Exception $e) {
            // Email sending failed, but order update was successful
        } catch (\Exception $e) {
            // Catch any other exceptions
        }
    }

    if (ob_get_level()) ob_end_clean();
    redirect("orders.php?status_updated=1");
}

// Normal page load (GET request) - include files and display page
include '../includes/db.php';
include '../includes/functions.php';
include '../includes/config.php';

if (!isAdmin()) {
    if (ob_get_level()) ob_end_clean();
    redirect('../customer/index.php');
}

// Check if PHPMailer is properly installed and available (for future use)
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

// Get order data for display
$stmt = $conn->prepare("SELECT o.*, u.username, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.user_id WHERE o.order_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    if (ob_get_level()) ob_end_clean();
    redirect('orders.php?error=not_found');
}

// End output buffering and start output (only if buffer exists)
if (ob_get_level()) {
    ob_end_flush();
}

// Now include header (after all redirects are handled)
include '../includes/admin_header.php';
?>

<div class="admin-content">
    <div class="container-fluid">
        <!-- Page Header -->
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

<!-- Bootstrap Icons -->
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
