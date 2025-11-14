<?php
include '../../includes/db.php';
include '../../includes/functions.php';

if (!isAdmin()) {
    redirect('../index.php');
}

$image_id = (int)$_GET['id'];
$product_id = (int)$_GET['product'];

$res = $conn->query("SELECT filename FROM product_images WHERE image_id=$image_id");
$img = $res->fetch_assoc();

if ($img) {
    unlink("../../uploads/products/" . $img['filename']);
}

$conn->query("DELETE FROM product_images WHERE image_id=$image_id");

redirect("editProducts.php?id=$product_id");
?>
