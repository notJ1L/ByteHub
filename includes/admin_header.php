<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ByteHub Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="/bytehub/assets/admin_style.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="admin-wrapper">
    <nav class="admin-sidebar d-none d-md-block">
            <div class="sidebar-content">
                <!-- Logo/Brand Section -->
                <div class="sidebar-brand">
                    <div class="brand-logo">
                        <i class="bi bi-cpu-fill"></i>
                    </div>
                    <div class="brand-text">
                        <h4 class="brand-title">ByteHub</h4>
                        <span class="brand-subtitle">Admin Panel</span>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <div class="sidebar-nav">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>" href="/bytehub/admin/dashboard.php">
                                <span class="nav-icon"><i class="bi bi-speedometer2"></i></span>
                                <span class="nav-text">Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'products') !== false) ? 'active' : ''; ?>" href="/bytehub/admin/products/products.php">
                                <span class="nav-icon"><i class="bi bi-box-seam"></i></span>
                                <span class="nav-text">Products</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'orders.php') ? 'active' : ''; ?>" href="/bytehub/admin/orders.php">
                                <span class="nav-icon"><i class="bi bi-receipt-cutoff"></i></span>
                                <span class="nav-text">Orders</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'users') !== false) ? 'active' : ''; ?>" href="/bytehub/admin/users/users.php">
                                <span class="nav-icon"><i class="bi bi-people"></i></span>
                                <span class="nav-text">Users</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'categories') !== false) ? 'active' : ''; ?>" href="/bytehub/admin/categories/categories.php">
                                <span class="nav-icon"><i class="bi bi-tags"></i></span>
                                <span class="nav-text">Categories</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'brands') !== false) ? 'active' : ''; ?>" href="/bytehub/admin/brands/brands.php">
                                <span class="nav-icon"><i class="bi bi-building"></i></span>
                                <span class="nav-text">Brands</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'expenses') !== false) ? 'active' : ''; ?>" href="/bytehub/admin/expenses/expenses.php">
                                <span class="nav-icon"><i class="bi bi-wallet2"></i></span>
                                <span class="nav-text">Expenses</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- User Profile Section -->
                <div class="sidebar-footer">
                    <div class="user-profile">
                        <div class="user-avatar">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <div class="user-info">
                            <div class="user-name"><?php echo htmlspecialchars($_SESSION['admin_email'] ?? 'Admin'); ?></div>
                            <div class="user-role">Administrator</div>
                        </div>
                        <div class="user-dropdown">
                            <div class="dropdown">
                                <a href="#" class="dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                                    <li>
                                        <a class="dropdown-item" href="/bytehub/customer/index.php" target="_blank">
                                            <i class="bi bi-house me-2"></i>View Store
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="/bytehub/admin/logout.php">
                                            <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </nav>

    <main class="admin-main-content">
