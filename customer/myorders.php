<?php
include '../includes/db.php';
include '../includes/functions.php';
include '../includes/header.php';

if (!is_logged_in()) {
  redirect('login.php');
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM orders 
        WHERE user_id = $user_id 
        ORDER BY order_id DESC";

$result = $conn->query($sql);

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
                <h1 class="display-5 fw-bold text-dark mb-2">My Orders</h1>
                <p class="text-muted">Track and manage your orders</p>
            </div>

            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($result->num_rows > 0): ?>
                <!-- Orders List -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-bag-check me-2"></i>Order History
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Order Code</th>
                                        <th>Payment Method</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th class="text-end pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold"><?php echo htmlspecialchars($row['order_code']); ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <i class="bi bi-wallet2 me-1"></i><?php echo htmlspecialchars($row['payment_method']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-primary-green">₱<?php echo number_format($row['total'], 2); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo getStatusBadge($row['status']); ?>">
                                                <i class="bi bi-<?php echo getStatusIcon($row['status']); ?> me-1"></i>
                                                <?php echo htmlspecialchars($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('M j, Y', strtotime($row['created_at'])); ?><br>
                                                <span class="text-muted"><?php echo date('g:i A', strtotime($row['created_at'])); ?></span>
                                            </small>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group" role="group">
                                                <a href="myorders_view.php?id=<?php echo $row['order_id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye me-1"></i>View
                                                </a>
                                                <?php if (in_array($row['status'], ['Pending', 'Processing'])): ?>
                                                    <a href="cancel_order.php?id=<?php echo $row['order_id']; ?>" 
                                                       class="btn btn-sm btn-outline-danger"
                                                       onclick="return confirm('Are you sure you want to cancel this order?');">
                                                        <i class="bi bi-x-circle me-1"></i>Cancel
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Order Statistics -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card shadow-sm border-0 bg-primary-green text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-bag-check" style="font-size: 2.5rem;"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0">Total Orders</h6>
                                        <h3 class="mb-0"><?php echo $result->num_rows; ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-clock-history text-warning" style="font-size: 2.5rem;"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0 text-muted">Pending</h6>
                                        <?php
                                        $result->data_seek(0);
                                        $pending = 0;
                                        while($r = $result->fetch_assoc()) {
                                            if ($r['status'] == 'Pending') $pending++;
                                        }
                                        ?>
                                        <h3 class="mb-0"><?php echo $pending; ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-check-circle text-success" style="font-size: 2.5rem;"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0 text-muted">Completed</h6>
                                        <?php
                                        $result->data_seek(0);
                                        $completed = 0;
                                        while($r = $result->fetch_assoc()) {
                                            if ($r['status'] == 'Completed') $completed++;
                                        }
                                        ?>
                                        <h3 class="mb-0"><?php echo $completed; ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- Empty State -->
                <div class="card shadow-sm text-center py-5">
                    <div class="card-body">
                        <i class="bi bi-bag-x" style="font-size: 5rem; color: #6c757d;"></i>
                        <h3 class="mt-3 mb-2">No orders yet</h3>
                        <p class="text-muted mb-4">You haven't placed any orders. Start shopping to see your orders here.</p>
                        <a href="index.php" class="btn btn-primary-green btn-lg">
                            <i class="bi bi-arrow-left me-2"></i>Start Shopping
                        </a>
                    </div>
                </div>
            <?php endif; ?>
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

.badge {
    padding: 0.5em 0.75em;
    font-weight: 500;
}
</style>

<?php include '../includes/footer.php'; ?>
