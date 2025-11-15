<?php
session_start();
include '../includes/db.php';
include __DIR__ . '/../includes/admin_header.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// Fetch stats
$prod_count = $conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'];
$user_count = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$order_count = $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'];
$total_revenue = $conn->query("SELECT SUM(total) AS total_revenue FROM orders")->fetch_assoc()['total_revenue'] ?? 0;
$todays_revenue = $conn->query("SELECT SUM(total) AS todays_revenue FROM orders WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['todays_revenue'] ?? 0;
$pending_orders = $conn->query("SELECT COUNT(*) AS pending_orders FROM orders WHERE status = 'Pending'")->fetch_assoc()['pending_orders'];

?>

<h1 class="h2">Dashboard</h1>
<p>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_email']); ?>!</p>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="card stat-card bg-primary-green text-white">
            <div class="card-body">
                <i class="bi bi-cash-coin"></i>
                <div class="stat-text">
                    <span>Total Revenue</span>
                    <h2>₱<?php echo number_format($total_revenue, 2); ?></h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card bg-secondary-green text-white">
            <div class="card-body">
                <i class="bi bi-calendar-day"></i>
                <div class="stat-text">
                    <span>Today's Revenue</span>
                    <h2>₱<?php echo number_format($todays_revenue, 2); ?></h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card bg-info text-white">
            <div class="card-body">
                <i class="bi bi-receipt"></i>
                <div class="stat-text">
                    <span>Pending Orders</span>
                    <h2><?php echo $pending_orders; ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body">
                <i class="bi bi-box-seam"></i>
                <div class="stat-text">
                    <span>Total Products</span>
                    <h2><?php echo $prod_count; ?></h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body">
                <i class="bi bi-people"></i>
                <div class="stat-text">
                    <span>Total Users</span>
                    <h2><?php echo $user_count; ?></h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body">
                <i class="bi bi-graph-up"></i>
                <div class="stat-text">
                    <span>Total Orders</span>
                    <h2><?php echo $order_count; ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

