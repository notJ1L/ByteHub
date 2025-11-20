<?php
session_start();
include '../includes/db.php';
include '../includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    redirect('index.php');
}

include __DIR__ . '/../includes/admin_header.php';

$prod_count = $conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'];
$user_count = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$order_count = $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'];
$total_revenue = $conn->query("SELECT SUM(total) AS total_revenue FROM orders")->fetch_assoc()['total_revenue'] ?? 0;
$todays_revenue = $conn->query("SELECT SUM(total) AS todays_revenue FROM orders WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['todays_revenue'] ?? 0;
$pending_orders = $conn->query("SELECT COUNT(*) AS pending_orders FROM orders WHERE status = 'Pending'")->fetch_assoc()['pending_orders'];
$completed_orders = $conn->query("SELECT COUNT(*) AS completed_orders FROM orders WHERE status = 'Completed'")->fetch_assoc()['completed_orders'];
?>

<div class="admin-content">
    <div class="page-header mb-4">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_email']); ?>!</p>
        </div>
        <div class="page-actions">
            <span class="text-muted"><?php echo date('l, F j, Y'); ?></span>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <i class="bi bi-cash-coin"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-value">₱<?php echo number_format($total_revenue, 2); ?></div>
                    <div class="stat-change text-success">
                        <i class="bi bi-arrow-up"></i> All time
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-success">
                <div class="stat-icon">
                    <i class="bi bi-calendar-day"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Today's Revenue</div>
                    <div class="stat-value">₱<?php echo number_format($todays_revenue, 2); ?></div>
                    <div class="stat-change text-muted">
                        <i class="bi bi-clock"></i> Today
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-warning">
                <div class="stat-icon">
                    <i class="bi bi-receipt-cutoff"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Pending Orders</div>
                    <div class="stat-value"><?php echo $pending_orders; ?></div>
                    <div class="stat-change">
                        <a href="orders.php?status=Pending" class="text-decoration-none">View all →</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-info">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Completed Orders</div>
                    <div class="stat-value"><?php echo $completed_orders; ?></div>
                    <div class="stat-change">
                        <a href="orders.php?status=Completed" class="text-decoration-none">View all →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-4 col-md-6">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Products</div>
                    <div class="stat-value"><?php echo $prod_count; ?></div>
                    <div class="stat-change">
                        <a href="products/products.php" class="text-decoration-none">Manage products →</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-md-6">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Users</div>
                    <div class="stat-value"><?php echo $user_count; ?></div>
                    <div class="stat-change">
                        <a href="users/users.php" class="text-decoration-none">Manage users →</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-md-6">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Orders</div>
                    <div class="stat-value"><?php echo $order_count; ?></div>
                    <div class="stat-change">
                        <a href="orders.php" class="text-decoration-none">View all orders →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
.admin-content {
    padding: 2rem 1.5rem;
    background-color: #f8f9fa;
    min-height: calc(100vh - 60px);
    max-width: 100%;
    overflow-x: hidden;
    box-sizing: border-box;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2.5rem;
    padding: 0 0 1.5rem 0;
    border-bottom: 1px solid #e9ecef;
}

.page-title {
    font-size: 1.875rem;
    font-weight: 700;
    color: #212529;
    margin: 0 0 0.5rem;
    letter-spacing: -0.02em;
}

.page-subtitle {
    color: #6c757d;
    margin: 0;
    font-size: 1rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    border: 1px solid #e9ecef;
    height: 100%;
    box-sizing: border-box;
    overflow: hidden;
    word-wrap: break-word;
    max-width: 100%;
    width: 100%;
    min-width: 0;
}

.stat-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-color: #dee2e6;
}

.stat-card-primary {
    background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
    color: white;
    border: none;
}

.stat-card-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border: none;
}

.stat-card-warning {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    color: white;
    border: none;
}

.stat-card-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
    border: none;
}

.stat-icon {
    width: 70px;
    height: 70px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    flex-shrink: 0;
}

.stat-card-primary .stat-icon,
.stat-card-success .stat-icon,
.stat-card-warning .stat-icon,
.stat-card-info .stat-icon {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.stat-card:not(.stat-card-primary):not(.stat-card-success):not(.stat-card-warning):not(.stat-card-info) .stat-icon {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    color: var(--primary-green);
}

.stat-content {
    flex: 1;
    min-width: 0;
    overflow: hidden;
}

.stat-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-card:not(.stat-card-primary):not(.stat-card-success):not(.stat-card-warning):not(.stat-card-info) .stat-label {
    color: #6c757d;
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: white;
    margin-bottom: 0.5rem;
    word-break: break-word;
    overflow-wrap: break-word;
    max-width: 100%;
    overflow: hidden;
}

.stat-content {
    flex: 1;
    min-width: 0;
    overflow: hidden;
}

.stat-card:not(.stat-card-primary):not(.stat-card-success):not(.stat-card-warning):not(.stat-card-info) .stat-value {
    color: #212529;
}

.stat-change {
    font-size: 0.875rem;
}

.stat-change a {
    color: var(--primary-green);
    font-weight: 500;
}

.stat-card-primary .stat-change a,
.stat-card-success .stat-change a,
.stat-card-warning .stat-change a,
.stat-card-info .stat-change a {
    color: rgba(255, 255, 255, 0.9);
}
</style>

<?php include 'footer.php'; ?>
