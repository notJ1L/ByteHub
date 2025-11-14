<?php
include '../../includes/db.php';
include '../../includes/functions.php';
include '../../includes/admin_header.php';

if (!isAdmin()) {
    redirect('../index.php');
}

$user_id = $_GET['id'];
$res = $conn->query("SELECT * FROM users WHERE user_id = $user_id");
$user = $res->fetch_assoc();

$photo = $user['photo'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $role     = $_POST['role'];
    $active   = $_POST['active'];

    $newPhoto = $photo;

    if (!empty($_FILES['photo']['name'])) {
        $newPhoto = time() . "_" . $_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], "../../uploads/users/" . $newPhoto);
    }

    $stmt = $conn->prepare("
        UPDATE users 
        SET username=?, email=?, role=?, active=?, photo=? 
        WHERE user_id=?
    ");
    $stmt->bind_param("sssisi", $username, $email, $role, $active, $newPhoto, $user_id);

    if ($stmt->execute()) {
        redirect('users.php');
    }

    $stmt->close();
}
?>

<h2>Edit User</h2>

<form method="POST" enctype="multipart/form-data">

    <label>Username</label>
    <input type="text" name="username" value="<?php echo $user['username']; ?>" required>

    <label>Email</label>
    <input type="email" name="email" value="<?php echo $user['email']; ?>" required>

    <label>Role</label>
    <select name="role">
        <option value="user" <?php if ($user['role']=='user') echo 'selected'; ?>>User</option>
        <option value="admin" <?php if ($user['role']=='admin') echo 'selected'; ?>>Admin</option>
    </select>

    <label>Status</label>
    <select name="active">
        <option value="1" <?php if ($user['active']==1) echo 'selected'; ?>>Active</option>
        <option value="0" <?php if ($user['active']==0) echo 'selected'; ?>>Inactive</option>
    </select>

    <label>Current Photo:</label><br>
    <?php if ($user['photo']): ?>
        <img src="../../uploads/users/<?php echo $user['photo']; ?>" width="80"><br>
    <?php endif; ?>

    <label>Upload New Photo</label>
    <input type="file" name="photo">

    <button class="btn btn-primary mt-3">Save Changes</button>
</form>

<?php include '../../includes/footer.php'; ?>
