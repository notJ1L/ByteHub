<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/db.php';

$cats_query = $conn->query("SELECT * FROM categories WHERE active=1");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ByteHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/bytehub/assets/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary-green">
    <div class="container-fluid">
        <a class="navbar-brand" href="/bytehub/customer/index.php">ByteHub</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php while($cat = $cats_query->fetch_assoc()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/bytehub/customer/category.php?cat=<?php echo $cat['slug']; ?>"><?php echo $cat['name']; ?></a>
                    </li>
                <?php endwhile; ?>
            </ul>
            <form class="d-flex" action="/bytehub/customer/search.php" method="GET">
                <input class="form-control me-2" type="search" name="q" placeholder="Search" aria-label="Search">
                <button class="btn btn-outline-light" type="submit">Search</button>
            </form>
            <ul class="navbar-nav ms-3">
                <li class="nav-item"><a class="nav-link" href="/bytehub/customer/cart.php">Cart</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Account
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="/bytehub/customer/profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="/bytehub/customer/myorders.php">My Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/bytehub/customer/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/bytehub/customer/login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container mt-4">
