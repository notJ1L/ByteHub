<?php
include '../includes/header.php';
$order_code = $_GET['code'] ?? 'Unknown';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm text-center">
                <div class="card-body py-5">
                    <div class="mb-4">
                        <div class="success-icon mx-auto mb-3">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <h1 class="display-4 fw-bold text-success mb-3">Order Successful!</h1>
                        <p class="lead text-muted">Your order has been placed successfully.</p>
                    </div>
                    
                    <div class="alert alert-info d-inline-block mb-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-receipt me-2" style="font-size: 1.5rem;"></i>
                            <div class="text-start">
                                <strong>Order Code:</strong><br>
                                <span class="fs-4 fw-bold text-primary-green"><?php echo htmlspecialchars($order_code); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <p class="text-muted mb-4">
                        We've sent a confirmation email with your order details. 
                        You can track your order status in <a href="myorders.php">My Orders</a>.
                    </p>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="myorders.php" class="btn btn-primary-green btn-lg px-5">
                            <i class="bi bi-bag-check me-2"></i>View My Orders
                        </a>
                        <a href="index.php" class="btn btn-outline-secondary btn-lg px-5">
                            <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
.success-icon {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.success-icon i {
    font-size: 4rem;
    color: white;
}

.text-primary-green {
    color: var(--primary-green) !important;
}

.card {
    border: none;
    border-radius: 12px;
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
</style>

<?php include '../includes/footer.php'; ?>
