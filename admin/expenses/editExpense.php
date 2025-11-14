<?php
include '../../includes/db.php';
include '../../includes/admin_header.php';
include '../../includes/functions.php';

if (!isAdmin()) {
    header("Location: ../customer/index.php");
    exit();
}

$id = $_GET['id'];

$expense = $conn->query("SELECT * FROM expenses WHERE expenses_id = $id")->fetch_assoc();
if (!$expense) die("Expense not found.");

if (isset($_POST['update'])) {
    $title = $_POST['title'];
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $notes = $_POST['notes'];

    $sql = "UPDATE expenses SET 
            title='$title',
            amount='$amount',
            category='$category',
            notes='$notes'
            WHERE expenses_id = $id";

    if ($conn->query($sql)) {
        header("Location: expenses.php?updated=1");
        exit();
    } else {
        $error = "Failed to update expense!";
    }
}
?>

<div class="container mt-4">
    <h2>Edit Expense</h2>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post">
        <label>Title:</label>
        <input type="text" name="title" class="form-control"
               value="<?php echo $expense['title']; ?>" required>

        <label class="mt-3">Amount:</label>
        <input type="number" step="0.01" name="amount" class="form-control"
               value="<?php echo $expense['amount']; ?>" required>

        <label class="mt-3">Category:</label>
        <input type="text" name="category" class="form-control"
               value="<?php echo $expense['category']; ?>">

        <label class="mt-3">Notes:</label>
        <textarea name="notes" class="form-control"><?php echo $expense['notes']; ?></textarea>

        <button name="update" class="btn btn-primary mt-4">Update Expense</button>
        <a href="expenses.php" class="btn btn-secondary mt-4">Cancel</a>
    </form>

</div>

<?php include '../../includes/footer.php'; ?>
