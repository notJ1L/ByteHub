<?php
include '../../includes/db.php';
include '../../includes/functions.php';
include '../../includes/admin_header.php';

if (!isAdmin()) {
    header("Location: ../customer/index.php");
    exit();
}

$id = $_GET['id'];

// fetch category
$catQuery = $conn->query("SELECT * FROM categories WHERE category_id = $id LIMIT 1");
$category = $catQuery->fetch_assoc();

if (!$category) {
    die("Category not found.");
}

if (isset($_POST['update'])) {
    $name = $_POST['name'];
    $slug = $_POST['slug'];
    $active = $_POST['active'];

    $sql = "UPDATE categories SET
            name='$name',
            slug='$slug',
            active='$active'
            WHERE category_id = $id";

    if ($conn->query($sql)) {
        header("Location: categories.php?updated=1");
        exit();
    } else {
        $error = "Failed to update category!";
    }
}
?>

<div class="container mt-4">
    <h2>Edit Category</h2>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post">
        <label>Name:</label>
        <input type="text" name="name" class="form-control"
               value="<?php echo $category['name']; ?>" required>

        <label class="mt-3">Slug:</label>
        <input type="text" name="slug" class="form-control"
               value="<?php echo $category['slug']; ?>" required>

        <label class="mt-3">Active:</label>
        <select name="active" class="form-control">
            <option value="1" <?php echo $category['active'] ? 'selected' : ''; ?>>Yes</option>
            <option value="0" <?php echo !$category['active'] ? 'selected' : ''; ?>>No</option>
        </select>

        <button name="update" class="btn btn-primary mt-4">Update Category</button>
        <a href="categories.php" class="btn btn-secondary mt-4">Cancel</a>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
