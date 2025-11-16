<?php
ob_start();
session_start();
include '../../includes/db.php';
include '../../includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    ob_end_clean();
    redirect('../index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $role     = $_POST['role'];
    $active   = $_POST['active'];

    // Validation
    if (empty($username)) {
        $errors[] = "Username is required.";
    }
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    // Check if email already exists
    if (empty($errors)) {
        $checkStmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "Email already exists. Please use a different email address.";
        }
        $checkStmt->close();
    }

    if (empty($errors)) {
        $photoName = null;

        // Ensure uploads directory exists
        $uploadDir = "../../uploads/users/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Upload photo
        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photoName = time() . "_" . $_FILES['photo']['name'];
            $target = $uploadDir . $photoName;
            move_uploaded_file($_FILES['photo']['tmp_name'], $target);
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            INSERT INTO users (username, email, password_hash, photo, role, active)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssssi", $username, $email, $hash, $photoName, $role, $active);

        if ($stmt->execute()) {
            $stmt->close();
            ob_end_clean();
            redirect('users.php');
        } else {
            $errors[] = "Failed to add user: " . $conn->error;
        }

        $stmt->close();
    }
}

include '../../includes/admin_header.php';
?>

<h2>Add User</h2>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

    <label>Username</label>
    <input type="text" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>

    <label>Email</label>
    <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <label>Role</label>
    <select name="role">
        <option value="user" <?php echo (isset($_POST['role']) && $_POST['role'] == 'user') ? 'selected' : ''; ?>>User</option>
        <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
    </select>

    <label>Status</label>
    <select name="active">
        <option value="1" <?php echo (!isset($_POST['active']) || $_POST['active'] == '1') ? 'selected' : ''; ?>>Active</option>
        <option value="0" <?php echo (isset($_POST['active']) && $_POST['active'] == '0') ? 'selected' : ''; ?>>Inactive</option>
    </select>

    <label>Photo</label>
    <input type="file" name="photo">

    <button class="btn btn-success mt-3">Add User</button>
</form>

<?php include '../footer.php'; ?>
