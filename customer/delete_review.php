<?php
include '../includes/db.php';
include '../includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$review_id = $_GET['id'] ?? 0;
$product_id = $_GET['product_id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Verify the review belongs to the current user
$stmt = $conn->prepare("SELECT review_id FROM reviews WHERE review_id = ? AND user_id = ?");
$stmt->bind_param("ii", $review_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Delete the review
    $delete_stmt = $conn->prepare("DELETE FROM reviews WHERE review_id = ?");
    $delete_stmt->bind_param("i", $review_id);
    $delete_stmt->execute();
    
    redirect('product.php?id=' . $product_id);
} else {
    die('Review not found or you do not have permission to delete it.');
}
?>

