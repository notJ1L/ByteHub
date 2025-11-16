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
    redirect("expenses.php?error=invalid_id");
}

// Delete expense
$stmt = $conn->prepare("DELETE FROM expenses WHERE expenses_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

ob_end_clean();
redirect("expenses.php?deleted=1");
?>
