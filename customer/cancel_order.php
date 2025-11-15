<?php
include '../includes/db.php';
include '../includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['id'] ?? 0;

// Process POST request FIRST (before any output)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_cancel'])) {
    // Verify the order belongs to the logged-in user and can be cancelled
    $order = $conn->query("
        SELECT * FROM orders 
        WHERE order_id = $order_id AND user_id = $user_id
    ")->fetch_assoc();
    
    if (!$order) {
        $_SESSION['error'] = "Order not found or you do not have permission to cancel this order.";
        redirect('myorders.php');
    }
    
    // Only allow cancellation of Pending or Processing orders
    if (!in_array($order['status'], ['Pending', 'Processing'])) {
        $_SESSION['error'] = "This order cannot be cancelled. Only Pending or Processing orders can be cancelled.";
        redirect('myorders_view.php?id=' . $order_id);
    }
    
    $stmt = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE order_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Order has been cancelled successfully.";
        redirect('myorders.php');
    } else {
        $_SESSION['error'] = "Failed to cancel order. Please try again.";
        redirect('myorders_view.php?id=' . $order_id);
    }
}

// Verify the order belongs to the logged-in user and can be cancelled (for display)
$order = $conn->query("
    SELECT * FROM orders 
    WHERE order_id = $order_id AND user_id = $user_id
")->fetch_assoc();

if (!$order) {
    $_SESSION['error'] = "Order not found or you do not have permission to cancel this order.";
    redirect('myorders.php');
}

// Only allow cancellation of Pending or Processing orders
if (!in_array($order['status'], ['Pending', 'Processing'])) {
    $_SESSION['error'] = "This order cannot be cancelled. Only Pending or Processing orders can be cancelled.";
    redirect('myorders_view.php?id=' . $order_id);
}

// Now include header (after all redirects are handled)
include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-exclamation-triangle text-warning me-2"></i>Cancel Order
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Are you sure you want to cancel this order?</strong>
                        <p class="mb-0 mt-2">Order Code: <strong><?php echo htmlspecialchars($order['order_code']); ?></strong></p>
                        <p class="mb-0">Total Amount: <strong>₱<?php echo number_format($order['total'], 2); ?></strong></p>
                    </div>
                    
                    <p class="text-muted">This action cannot be undone. Once cancelled, you will need to place a new order if you wish to purchase these items again.</p>
                    
                    <form method="POST" class="mt-4">
                        <div class="d-flex gap-2">
                            <button type="submit" name="confirm_cancel" class="btn btn-danger">
                                <i class="bi bi-x-circle me-2"></i>Yes, Cancel Order
                            </button>
                            <a href="myorders_view.php?id=<?php echo $order_id; ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Go Back
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
.card {
    border: none;
    border-radius: 12px;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    padding: 1.25rem 1.5rem;
}
</style>

<?php include '../includes/footer.php'; ?>

