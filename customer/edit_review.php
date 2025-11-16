<?php
include '../includes/db.php';
include '../includes/functions.php';
include '../includes/header.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$review_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$errors = [];

// Fetch the review and ensure it belongs to the current user
$stmt = $conn->prepare("SELECT * FROM reviews WHERE review_id = ? AND user_id = ?");
$stmt->bind_param("ii", $review_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$review = $result->fetch_assoc();

if (!$review) {
    die('Review not found or you do not have permission to edit it.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    
    // Validate rating
    if (empty($rating) || $rating < 1 || $rating > 5) {
        $errors[] = 'Please select a valid rating.';
    }
    
    // Validate comment
    if (empty($comment)) {
        $errors[] = 'Comment is required.';
    }
    
    if (empty($errors)) {
        // Filter bad words using regex
        $comment = filter_bad_words($comment);
        
        $stmt = $conn->prepare("UPDATE reviews SET rating = ?, comment = ? WHERE review_id = ?");
        $stmt->bind_param("isi", $rating, $comment, $review_id);
        $stmt->execute();

        redirect('product.php?id=' . $review['product_id']);
    }
}
?>

<h2>Edit Review</h2>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST">
    <label>Rating:</label><br>
    <select name="rating">
        <option value="">Select rating...</option>
        <?php for ($i = 5; $i >= 1; $i--): ?>
            <option value="<?php echo $i; ?>" <?php echo ($review['rating'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?> Stars</option>
        <?php endfor; ?>
    </select><br><br>

    <label>Comment:</label><br>
    <textarea name="comment" rows="5" style="width:100%;"><?php echo htmlspecialchars($review['comment']); ?></textarea><br><br>

    <button type="submit" class="btn">Update Review</button>
</form>

<?php include '../includes/footer.php'; ?>
