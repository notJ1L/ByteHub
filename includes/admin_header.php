<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ByteHub Admin</title>
  <link rel="stylesheet" href="/bytehub/assets/css/style.css">
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #f5f5f5;
    }

    /* Header styling */
    header {
      background-color: #004d26;
      color: white;
      padding: 15px 40px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    header h1 {
      margin: 0;
      font-size: 1.5em;
    }

    header h1 a {
      color: white;
      text-decoration: none;
    }

    nav a {
      color: white;
      text-decoration: none;
      margin-left: 25px;
      font-weight: bold;
      transition: color 0.3s;
    }

    nav a:hover {
      color: #ffcc00;
    }

    main {
      padding: 30px;
    }
  </style>
</head>
<body>
  <header>
    <h1><a href="/bytehub/admin/dashboard.php">ByteHub Admin</a></h1>
    <nav>
      <a href="/bytehub/admin/dashboard.php">Dashboard</a>
      <a href="/bytehub/admin/products/products.php">Products</a>
      <a href="/bytehub/admin/orders.php">Orders</a>
      <a href="/bytehub/admin/categories/categories.php">Categories</a>
      <a href="/bytehub/admin/brands/brands.php">Brands</a>
      <a href="/bytehub/admin/expenses/expenses.php">Expenses</a>
      <a href="/bytehub/admin/logout.php" style="color:#ffcccc;">Logout</a>
    </nav>
  </header>
  <main>
