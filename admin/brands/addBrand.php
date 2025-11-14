<?php
include '../includes/db.php';
include '../includes/functions.php';
include '../includes/admin_header.php';

if (!isAdmin()) {
    header("Location: ../customer/index.php");
    exit();
}

if (isset($_POST['save'])) {
    $name = $_POST['name'];
    $slug = $_POST['slug'];
    $active = $_POST['active'];

    $sql = "INSERT INTO brands (name, slug, active)
            VALUES ('$name', '$slug', '$active')";

    if ($conn->query($sql)) {
        header("Location: brands.php?added=1");
        exit();
    } else {
        $error = "Failed to add brand!";
    }
}
?>

<div class="container mt-4">
    <h2>Add Brand</h2>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post">
        <label>Name:</label>
        <input type="text" name="name" class="form-control" required>

        <label class="mt-3">Slug:</label>
        <input type="text" name="slug" class="form-control" required>

        <label class="mt-3">Active:</label>
        <select name="active" class="form-control">
            <option value="1">Yes</option>
            <option value="0">No</option>
        </select>

        <button name="save" class="btn btn-success mt-4">Save Brand</button>
        <a href="brands.php" class="btn btn-secondary mt-4">Cancel</a>
    </form>

</div>

<?php include '../includes/footer.php'; ?>
