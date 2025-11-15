<?php
include '../includes/db.php';
include '../includes/functions.php';
include '../includes/header.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['id'] ?? 0;

// Verify the order belongs to the logged-in user
$order = $conn->query("
    SELECT o.*, u.username, u.email 
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.user_id
    WHERE o.order_id = $order_id AND o.user_id = $user_id
")->fetch_assoc();

if (!$order) {
    echo '<div class="container my-5"><div class="alert alert-danger">Order not found or you do not have permission to view this order.</div></div>';
    include '../includes/footer.php';
    exit;
}

$items = $conn->query("
    SELECT * FROM order_items 
    WHERE order_id = $order_id
");

function getStatusBadge($status) {
    $badges = [
        'Pending' => 'bg-warning text-dark',
        'Processing' => 'bg-info text-white',
        'Shipped' => 'bg-primary text-white',
        'Completed' => 'bg-success text-white',
        'Cancelled' => 'bg-danger text-white'
    ];
    return $badges[$status] ?? 'bg-secondary text-white';
}

function getStatusIcon($status) {
    $icons = [
        'Pending' => 'clock-history',
        'Processing' => 'gear',
        'Shipped' => 'truck',
        'Completed' => 'check-circle',
        'Cancelled' => 'x-circle'
    ];
    return $icons[$status] ?? 'question-circle';
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="mb-4">
                <a href="myorders.php" class="btn btn-outline-secondary mb-3">
                    <i class="bi bi-arrow-left me-2"></i>Back to Orders
                </a>
                <h1 class="display-5 fw-bold text-dark mb-2">Order Details</h1>
                <p class="text-muted">Order Code: <strong><?php echo htmlspecialchars($order['order_code']); ?></strong></p>
            </div>

            <div class="row g-4">
                <!-- Order Information -->
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-info-circle me-2"></i>Order Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted d-block">Order Code</small>
                                <strong><?php echo htmlspecialchars($order['order_code']); ?></strong>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">Status</small>
                                <span class="badge <?php echo getStatusBadge($order['status']); ?>">
                                    <i class="bi bi-<?php echo getStatusIcon($order['status']); ?> me-1"></i>
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </span>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">Payment Method</small>
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-wallet2 me-1"></i><?php echo htmlspecialchars($order['payment_method']); ?>
                                </span>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">Order Date</small>
                                <strong><?php echo date('F j, Y', strtotime($order['created_at'])); ?></strong><br>
                                <small class="text-muted"><?php echo date('g:i A', strtotime($order['created_at'])); ?></small>
                            </div>
                            <hr>
                            <div>
                                <small class="text-muted d-block">Total Amount</small>
                                <h3 class="text-primary-green mb-0">₱<?php echo number_format($order['total'], 2); ?></h3>
                            </div>
                            
                            <!-- Cancel Order Button (only for Pending/Processing orders) -->
                            <?php if (in_array($order['status'], ['Pending', 'Processing'])): ?>
                                <hr>
                                <div class="d-grid">
                                    <a href="cancel_order.php?id=<?php echo $order_id; ?>" 
                                       class="btn btn-outline-danger"
                                       onclick="return confirm('Are you sure you want to cancel this order? This action cannot be undone.');">
                                        <i class="bi bi-x-circle me-2"></i>Cancel Order
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-bag-check me-2"></i>Order Items
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4">Product</th>
                                            <th>Unit Price</th>
                                            <th>Quantity</th>
                                            <th class="text-end pe-4">Line Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php while($item = $items->fetch_assoc()): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <strong><?php echo htmlspecialchars($item['name_snapshot']); ?></strong>
                                            </td>
                                            <td>₱<?php echo number_format($item['unit_price_snapshot'], 2); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td class="text-end pe-4">
                                                <strong class="text-primary-green">₱<?php echo number_format($item['line_total'], 2); ?></strong>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold ps-4">Total:</td>
                                            <td class="text-end pe-4">
                                                <strong class="text-primary-green fs-5">₱<?php echo number_format($order['total'], 2); ?></strong>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
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

.card {
    border: none;
    border-radius: 12px;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    padding: 1.25rem 1.5rem;
}

.table thead th {
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.table tbody tr {
    transition: background-color 0.2s ease;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.badge {
    padding: 0.5em 0.75em;
    font-weight: 500;
}
</style>

<?php include '../includes/footer.php'; ?>

