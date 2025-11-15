<?php
include '../includes/db.php';
include '../includes/functions.php';
include '../includes/admin_header.php';

if (!isAdmin()) {
    header("Location: ../customer/index.php");
    exit();
}

$search = $_GET['search'] ?? '';

$sql = "SELECT o.*, u.username 
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        WHERE o.order_code LIKE ? OR u.username LIKE ?
        ORDER BY o.order_id DESC";

$stmt = $conn->prepare($sql);
$search_param = "%$search%";
$stmt->bind_param('ss', $search_param, $search_param);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Orders</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search by Order Code or User..." value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th>ID</th>
                        <th>Order Code</th>
                        <th>User</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['order_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['order_code']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td>₱<?php echo number_format($row['total'], 2); ?></td>
                        <td><span class="badge bg-<?php echo getStatusBadgeClass($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></td>
                        <td>
                            <a href="viewOrder.php?id=<?php echo $row['order_id']; ?>" class="btn btn-sm btn-outline-info">View</a>
                            <a href="updateOrderStatus.php?id=<?php echo $row['order_id']; ?>" class="btn btn-sm btn-outline-warning">Update</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
function getStatusBadgeClass($status) {
    switch (strtolower($status)) {
        case 'completed': return 'success';
        case 'shipped': return 'primary';
        case 'pending': return 'warning';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}

include '../includes/footer.php';
?>
