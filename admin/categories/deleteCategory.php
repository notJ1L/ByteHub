<?php
include '../includes/db.php';
include '../includes/functions.php';

if (!isAdmin()) {
    redirect('../customer/index.php');
}

$id = $_GET['id'];

// Check if products use this category
$check = $conn->query("SELECT COUNT(*) AS total FROM products WHERE category_id = $id");
$count = $check->fetch_assoc()['total'];

if ($count > 0) {
    die("Cannot delete category. It is being used by $count products.");
}

$conn->query("DELETE FROM categories WHERE category_id = $id");

redirect("categories.php?deleted=1");
?>
