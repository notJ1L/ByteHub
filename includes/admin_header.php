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
    <link rel="stylesheet" href="/bytehub/assets/admin_style.css">
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block admin-sidebar">
            <div class="position-sticky pt-3">
                <h3 class="text-white p-3">ByteHub Admin</h3>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="/bytehub/admin/dashboard.php">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="/bytehub/admin/products/products.php">
                            <i class="bi bi-box-seam"></i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="/bytehub/admin/orders.php">
                            <i class="bi bi-receipt"></i> Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="/bytehub/admin/users/users.php">
                            <i class="bi bi-people"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="/bytehub/admin/categories/categories.php">
                            <i class="bi bi-tags"></i> Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="/bytehub/admin/brands/brands.php">
                            <i class="bi bi-building"></i> Brands
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="/bytehub/admin/expenses/expenses.php">
                            <i class="bi bi-wallet2"></i> Expenses
                        </a>
                    </li>
                </ul>
                <hr class="text-white">
                <div class="dropdown p-3">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle fs-4 me-2"></i>
                        <strong>Admin</strong>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                        <li><a class="dropdown-item" href="/bytehub/admin/logout.php">Sign out</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
