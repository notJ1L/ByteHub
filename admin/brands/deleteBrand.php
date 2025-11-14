<?php
include '../includes/db.php';
include '../includes/functions.php';

if (!isAdmin()) {
    header("Location: ../customer/index.php");
    exit();
}

$id = $_GET['id'];

// Check if products use this brand
$check = $conn->query("SELECT COUNT(*) AS total FROM products WHERE brand_id = $id");
$count = $check->fetch_assoc()['total'];

if ($count > 0) {
    die("Cannot delete brand. It is being used by $count products.");
}

$conn->query("DELETE FROM brands WHERE brand_id = $id");

header("Location: brands.php?deleted=1");
exit();
?>
