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
    redirect("brands.php?error=invalid_id");
}

$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM products WHERE brand_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$count = $result->fetch_assoc()['total'];
$stmt->close();

if ($count > 0) {
    ob_end_clean();
    redirect("brands.php?error=in_use&count=$count");
}

$stmt = $conn->prepare("DELETE FROM brands WHERE brand_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

ob_end_clean();
redirect("brands.php?deleted=1");
?>
