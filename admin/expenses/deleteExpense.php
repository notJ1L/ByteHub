<?php
include '../includes/db.php';
include '../includes/functions.php';

if (!isAdmin()) {
    redirect('../customer/index.php');
}

$id = $_GET['id'];

$conn->query("DELETE FROM expenses WHERE expenses_id = $id");

redirect("expenses.php?deleted=1");
?>
