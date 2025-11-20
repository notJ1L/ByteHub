<?php
include '../includes/db.php';
include '../includes/functions.php';

if (!isAdmin()) {
    redirect('../customer/index.php');
}

include '../includes/admin_header.php';

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$sort_order = $_GET['sort'] ?? 'id_desc';

$sql = "SELECT o.*, u.username 
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        WHERE (o.order_code LIKE ? OR u.username LIKE ?)";

$params = ['ss', "%$search%", "%$search%"];

if ($status_filter) {
    $sql .= " AND o.status = ?";
    $params[0] .= 's';
    $params[] = $status_filter;
}

if ($start_date && $end_date) {
    $sql .= " AND o.created_at BETWEEN ? AND ?";
    $params[0] .= 'ss';
    $params[] = $start_date;
    $params[] = $end_date;
}

switch ($sort_order) {
    case 'total_asc':
        $sql .= " ORDER BY o.total ASC";
        break;
    case 'total_desc':
        $sql .= " ORDER BY o.total DESC";
        break;
    case 'id_asc':
        $sql .= " ORDER BY o.order_id ASC";
        break;
    default:
        $sql .= " ORDER BY o.order_id DESC";
}

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param(...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}

function getStatusBadgeClass($status) {
    switch (strtolower($status)) {
        case 'completed': return 'success';
        case 'processing': return 'info';
        case 'shipped': return 'primary';
        case 'pending': return 'warning';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}

function getStatusIcon($status) {
    switch (strtolower($status)) {
        case 'completed': return 'check-circle';
        case 'processing': return 'gear';
        case 'shipped': return 'truck';
        case 'pending': return 'clock-history';
        case 'cancelled': return 'x-circle';
        default: return 'question-circle';
    }
}
?>

<div class="admin-content" style="padding-left: 2.5rem; max-width: calc(100vw - 280px); overflow-x: hidden; box-sizing: border-box;">
    <div class="page-header mb-4">
        <div>
            <h1 class="page-title">Orders Management</h1>
            <p class="page-subtitle">View and manage all customer orders</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-funnel me-2"></i>Filters & Search
                </h5>
                <a href="orders.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="filter-form">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" 
                                   name="search" 
                                   class="form-control" 
                                   placeholder="Order code or username..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="Pending" <?php echo $status_filter == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Processing" <?php echo $status_filter == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="Shipped" <?php echo $status_filter == 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="Completed" <?php echo $status_filter == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="Cancelled" <?php echo $status_filter == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sort By</label>
                        <select name="sort" class="form-select">
                            <option value="id_desc" <?php echo $sort_order == 'id_desc' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="id_asc" <?php echo $sort_order == 'id_asc' ? 'selected' : ''; ?>>Oldest First</option>
                            <option value="total_desc" <?php echo $sort_order == 'total_desc' ? 'selected' : ''; ?>>Highest Total</option>
                            <option value="total_asc" <?php echo $sort_order == 'total_asc' ? 'selected' : ''; ?>>Lowest Total</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary-green">
                            <i class="bi bi-funnel-fill me-2"></i>Apply Filters
                        </button>
                        <a href="orders.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="bi bi-receipt-cutoff me-2"></i>Orders List
            </h5>
            <span class="badge bg-primary-green"><?php echo $result->num_rows; ?> order(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Order Code</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4"><?php echo $row['order_id']; ?></td>
                                <td>
                                    <strong class="text-primary-green"><?php echo htmlspecialchars($row['order_code']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($row['username'] ?? 'N/A'); ?></td>
                                <td>
                                    <strong>₱<?php echo number_format($row['total'], 2); ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo getStatusBadgeClass($row['status']); ?>">
                                        <i class="bi bi-<?php echo getStatusIcon($row['status']); ?> me-1"></i>
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('M j, Y', strtotime($row['created_at'])); ?><br>
                                        <span><?php echo date('g:i A', strtotime($row['created_at'])); ?></span>
                                    </small>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group" role="group">
                                        <a href="viewOrder.php?id=<?php echo $row['order_id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="updateOrderStatus.php?id=<?php echo $row['order_id']; ?>" 
                                           class="btn btn-sm btn-outline-success" 
                                           title="Update Status">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-3 mb-0">No orders found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
.admin-content {
    padding: 2rem 0;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid #e9ecef;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    color: #212529;
    margin: 0 0 0.5rem;
}

.page-subtitle {
    color: #6c757d;
    margin: 0;
    font-size: 1rem;
}

.card {
    border: none;
    border-radius: 12px;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    padding: 1.25rem 1.5rem;
}

.filter-form .form-label {
    font-size: 0.875rem;
    color: #495057;
    margin-bottom: 0.5rem;
}

.table thead th {
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    color: #6c757d;
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
