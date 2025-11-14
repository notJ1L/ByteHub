<?php
include '../../includes/db.php';
include '../../includes/functions.php';
include '../../includes/admin_header.php';

if (!isAdmin()) {
    redirect('../index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $role     = $_POST['role'];
    $active   = $_POST['active'];

    $photoName = null;

    // Upload photo
    if (!empty($_FILES['photo']['name'])) {
        $photoName = time() . "_" . $_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], "../../uploads/users/" . $photoName);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO users (username, email, password_hash, photo, role, active)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssssi", $username, $email, $hash, $photoName, $role, $active);

    if ($stmt->execute()) {
        redirect('users.php');
    } else {
        $errors[] = "Failed to add user.";
    }

    $stmt->close();
}
?>

<h2>Add User</h2>

<form method="POST" enctype="multipart/form-data">

    <label>Username</label>
    <input type="text" name="username" required>

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <label>Role</label>
    <select name="role">
        <option value="user">User</option>
        <option value="admin">Admin</option>
    </select>

    <label>Status</label>
    <select name="active">
        <option value="1">Active</option>
        <option value="0">Inactive</option>
    </select>

    <label>Photo</label>
    <input type="file" name="photo">

    <button class="btn btn-success mt-3">Add User</button>
</form>

<?php include '../../includes/footer.php'; ?>
