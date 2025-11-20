<?php
ob_start();
session_start();
include '../../includes/db.php';
include '../../includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    ob_end_clean();
    redirect('../index.php');
}

$id = (int)$_GET['id'];

if (!$id) {
    ob_end_clean();
    redirect("products.php?error=invalid_id");
}

$stmt = $conn->prepare("SELECT image FROM products WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if ($product && !empty($product['image'])) {
    $imagePath = "../../uploads/products/" . $product['image'];
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
}

$stmt = $conn->prepare("SELECT filename FROM product_images WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$imagesResult = $stmt->get_result();

while ($img = $imagesResult->fetch_assoc()) {
    $imgPath = "../../uploads/products/" . $img['filename'];
    if (file_exists($imgPath)) {
        unlink($imgPath);
    }
}

$stmt = $conn->prepare("DELETE FROM product_images WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

ob_end_clean();
redirect("products.php?deleted=1");
?>
