<?php
session_start();
include '../includes/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }

$id = (int)$_GET['id'];
$conn->query("DELETE FROM products WHERE product_id=$id");
header('Location: products.php');
exit;
?>