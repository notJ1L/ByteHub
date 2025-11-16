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
    redirect("users.php?error=invalid_id");
}

// Prevent deleting yourself
if ($id == $_SESSION['admin_id']) {
    ob_end_clean();
    redirect("users.php?error=cannot_delete_self");
}

// Get user photo
$stmt = $conn->prepare("SELECT photo FROM users WHERE user_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && !empty($user['photo'])) {
    $photoPath = "../../uploads/users/" . $user['photo'];
    if (file_exists($photoPath)) {
        unlink($photoPath);
    }
}

// Delete order_items for all orders belonging to this user
// (order_items has ON DELETE NO ACTION, so we need to delete them manually)
$stmt = $conn->prepare("
    DELETE oi FROM order_items oi
    INNER JOIN orders o ON oi.order_id = o.order_id
    WHERE o.user_id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

// Delete orders for this user (will cascade from users table, but doing it explicitly)
$stmt = $conn->prepare("DELETE FROM orders WHERE user_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

// Delete reviews for this user (has CASCADE, but being explicit)
$stmt = $conn->prepare("DELETE FROM reviews WHERE user_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

// Delete user from database
$stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

ob_end_clean();
redirect("users.php?deleted=1");
?>

