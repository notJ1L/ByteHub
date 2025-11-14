<?php
include '../includes/db.php';
include '../includes/functions.php';

$id = $_GET['id'];

$imgQuery = $conn->query("SELECT image FROM products WHERE product_id = $id");
$image = $imgQuery->fetch_assoc()['image'];

$conn->query("DELETE FROM products WHERE product_id = $id");

if (file_exists("../uploads/" . $image)) {
    unlink("../uploads/" . $image);
}

header("Location: products.php?deleted=1");
exit();
?>
