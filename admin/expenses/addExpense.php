<?php
include '../../includes/db.php';
include '../../includes/admin_header.php';
include '../../includes/functions.php';

if (!isAdmin()) {
    header("Location: ../customer/index.php");
    exit();
}

if (isset($_POST['save'])) {
    $title = $_POST['title'];
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $notes = $_POST['notes'];
    $created_at = date("Y-m-d H:i:s");

    $sql = "INSERT INTO expenses (title, amount, category, notes, created_at)
            VALUES ('$title', '$amount', '$category', '$notes', '$created_at')";

    if ($conn->query($sql)) {
        header("Location: expenses.php?added=1");
        exit();
    } else {
        $error = "Failed to add expense!";
    }
}
?>

<div class="container mt-4">
    <h2>Add Expense</h2>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post">
        <label>Title:</label>
        <input type="text" name="title" class="form-control" required>

        <label class="mt-3">Amount:</label>
        <input type="number" step="0.01" name="amount" class="form-control" required>

        <label class="mt-3">Category:</label>
        <input type="text" name="category" class="form-control">

        <label class="mt-3">Notes:</label>
        <textarea name="notes" class="form-control"></textarea>

        <button name="save" class="btn btn-success mt-4">Save Expense</button>
        <a href="expenses.php" class="btn btn-secondary mt-4">Cancel</a>
    </form>

</div>

<?php include '../../includes/footer.php'; ?>
