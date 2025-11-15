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

<nav class="navbar navbar-expand-lg navbar-dark bg-primary-green shadow-sm">
    <div class="container-fluid px-4">
        <!-- Brand/Logo -->
        <a class="navbar-brand d-flex align-items-center fw-bold" href="/bytehub/customer/index.php">
            <i class="bi bi-cpu me-2" style="font-size: 1.5rem;"></i>
            <span style="font-size: 1.5rem; letter-spacing: 0.5px;">ByteHub</span>
        </a>
        
        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Navbar Content -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Navigation Links -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php 
                $cats_query->data_seek(0); // Reset pointer
                while($cat = $cats_query->fetch_assoc()): ?>
                    <li class="nav-item">
                        <a class="nav-link px-3 py-2 rounded" href="/bytehub/customer/category.php?cat=<?php echo $cat['slug']; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>
            
            <!-- Search Bar -->
            <form class="d-flex me-3 mb-2 mb-lg-0" action="/bytehub/customer/search.php" method="GET">
                <div class="input-group" style="max-width: 400px;">
                    <input class="form-control border-0" 
                           type="search" 
                           name="q" 
                           placeholder="Search products..." 
                           aria-label="Search"
                           style="background-color: rgba(255, 255, 255, 0.1); color: white; border-radius: 25px 0 0 25px;">
                    <button class="btn btn-light border-0" 
                            type="submit" 
                            style="border-radius: 0 25px 25px 0; background-color: rgba(255, 255, 255, 0.2); color: white;">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
            
            <!-- Right Side Actions -->
            <ul class="navbar-nav align-items-center">
                <!-- Cart -->
                <li class="nav-item me-2">
                    <a class="nav-link position-relative px-3 py-2 rounded" href="/bytehub/customer/cart.php">
                        <i class="bi bi-cart3" style="font-size: 1.3rem;"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark" id="cartBadge" style="display: none;">
                            0
                        </span>
                    </a>
                </li>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <!-- User Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center px-3 py-2 rounded" 
                           href="#" 
                           id="navbarDropdown" 
                           role="button" 
                           data-bs-toggle="dropdown" 
                           aria-expanded="false">
                            <i class="bi bi-person-circle me-2" style="font-size: 1.3rem;"></i>
                            <span>Account</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="navbarDropdown" style="border-radius: 10px; min-width: 200px;">
                            <li>
                                <a class="dropdown-item py-2" href="/bytehub/customer/profile.php">
                                    <i class="bi bi-person me-2"></i>My Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="/bytehub/customer/myorders.php">
                                    <i class="bi bi-bag-check me-2"></i>My Orders
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-2"></li>
                            <li>
                                <a class="dropdown-item py-2 text-danger" href="/bytehub/customer/logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Login Button -->
                    <li class="nav-item">
                        <a class="nav-link btn btn-light text-dark px-4 py-2 rounded-pill ms-2" 
                           href="/bytehub/customer/login.php"
                           style="font-weight: 600;">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
.navbar {
    padding: 0.75rem 0;
    background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%) !important;
}

.navbar-brand {
    font-size: 1.5rem;
    transition: transform 0.3s ease;
}

.navbar-brand:hover {
    transform: scale(1.05);
    color: rgba(255, 255, 255, 0.9) !important;
}

.navbar-nav .nav-link {
    transition: all 0.3s ease;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.9) !important;
}

.navbar-nav .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.15);
    color: white !important;
}

.navbar-nav .nav-link.active {
    background-color: rgba(255, 255, 255, 0.2);
    color: white !important;
}

.dropdown-menu {
    border: 1px solid rgba(0, 0, 0, 0.1);
}

.dropdown-item {
    transition: all 0.2s ease;
    border-radius: 5px;
    margin: 2px 5px;
}

.dropdown-item:hover {
    background-color: var(--primary-green);
    color: white !important;
}

.dropdown-item i {
    width: 20px;
}

.navbar-toggler {
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 5px;
}

.navbar-toggler:focus {
    box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
}

/* Search input placeholder color */
.form-control::placeholder {
    color: rgba(255, 255, 255, 0.6) !important;
}

.form-control:focus {
    background-color: rgba(255, 255, 255, 0.15) !important;
    color: white !important;
    box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
}

/* Cart badge animation */
#cartBadge {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: translate(-50%, -50%) scale(1);
    }
    50% {
        transform: translate(-50%, -50%) scale(1.1);
    }
}

@media (max-width: 991px) {
    .navbar-nav {
        margin-top: 1rem;
    }
    
    .navbar-nav .nav-link {
        padding: 0.75rem 1rem;
    }
    
    .input-group {
        max-width: 100% !important;
        margin-bottom: 1rem;
    }
}
</style>

<script>
// Update cart badge count (you can integrate this with your cart system)
document.addEventListener('DOMContentLoaded', function() {
    // Check if cart exists in session/localStorage and update badge
    // This is a placeholder - integrate with your actual cart system
    const cartCount = 0; // Replace with actual cart count
    const cartBadge = document.getElementById('cartBadge');
    if (cartCount > 0) {
        cartBadge.textContent = cartCount;
        cartBadge.style.display = 'block';
    }
});
</script>

<main class="container mt-4">
