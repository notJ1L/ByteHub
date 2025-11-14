<?php
include '../includes/db.php';
include '../includes/functions.php';

if (!isAdmin()) {
    header("Location: ../customer/index.php");
    exit();
}

$id = $_GET['id'];

$conn->query("DELETE FROM expenses WHERE expenses_id = $id");

header("Location: expenses.php?deleted=1");
exit();
?>
