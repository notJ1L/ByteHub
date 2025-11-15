<?php
include '../includes/db.php';
include '../includes/functions.php';
include '../includes/header.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$product_id = $_GET['product_id'];
$user_id = $_SESSION['user_id'];

// Check if user has purchased this product before
$stmt = $conn->prepare("
    SELECT o.order_id 
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'Completed'
");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    die('You can only review products you have purchased.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);

    $stmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $product_id, $user_id, $rating, $comment);
    $stmt->execute();

    redirect('product.php?id=' . $product_id);
}
?>

<h2>Add Review</h2>

<form method="POST">
    <label>Rating:</label><br>
    <select name="rating" required>
        <option value="5">5 Stars</option>
        <option value="4">4 Stars</option>
        <option value="3">3 Stars</option>
        <option value="2">2 Stars</option>
        <option value="1">1 Star</option>
    </select><br><br>

    <label>Comment:</label><br>
    <textarea name="comment" rows="5" style="width:100%;"></textarea><br><br>

    <button type="submit" class="btn">Submit Review</button>
</form>

<?php include '../includes/footer.php'; ?>
