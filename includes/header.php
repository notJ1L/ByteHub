<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>ByteHub</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header>
  <h1><a href="index.php">ByteHub</a></h1>
  <nav>
    <a href="category.php?cat=cpus">CPUs</a>
    <a href="category.php?cat=gpus">GPUs</a>
    <a href="cart.php">Cart</a>
    <?php if(isset($_SESSION['user_id'])): ?>
      <a href="logout.php">Logout</a>
    <?php else: ?>
      <a href="login.php">Login</a>
    <?php endif; ?>
  </nav>
</header>
<main>
