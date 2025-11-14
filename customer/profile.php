<?php
include '../includes/db.php';
include '../includes/functions.php';
include '../includes/header.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = "";

// Fetch current user info
$sql = "SELECT username, email, photo FROM users WHERE user_id = $user_id LIMIT 1";
$user = $conn->query($sql)->fetch_assoc();

$username = $user['username'];
$email    = $user['email'];
$photo    = $user['photo'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if ($username === '' || $email === '') {
        $errors[] = "Username and email cannot be empty.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Handle password change only if provided
    $updatePassword = false;
    if ($password !== '' || $confirm !== '') {
        if ($password !== $confirm) {
            $errors[] = "Passwords do not match.";
        } else {
            $updatePassword = true;
            $hashed = password_hash($password, PASSWORD_DEFAULT);
        }
    }

    // Handle photo upload
    $newPhotoName = $photo; // Keep old photo if none uploaded
    if (!empty($_FILES['photo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];

        if (!in_array($ext, $allowed)) {
            $errors[] = "Invalid photo type. Allowed: JPG, JPEG, PNG, GIF, WEBP.";
        } else {
            $newPhotoName = time() . "_" . $_FILES['photo']['name'];
            move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/users/" . $newPhotoName);
        }
    }

    if (!$errors) {

        if ($updatePassword) {
            $stmt = $conn->prepare("
                UPDATE users SET username=?, email=?, photo=?, password_hash=? 
                WHERE user_id=?
            ");
            $stmt->bind_param('ssssi', $username, $email, $newPhotoName, $hashed, $user_id);
        } else {
            $stmt = $conn->prepare("
                UPDATE users SET username=?, email=?, photo=? 
                WHERE user_id=?
            ");
            $stmt->bind_param('sssi', $username, $email, $newPhotoName, $user_id);
        }

        if ($stmt->execute()) {
            $success = "Profile updated successfully!";
            $photo = $newPhotoName; // Update photo preview immediately
        } else {
            $errors[] = "Failed to update profile.";
        }
        $stmt->close();
    }
}
?>

<div class="profile-container">
    <h2>My Profile</h2>

    <?php if ($success): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="error">
            <?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <label>Current Photo:</label><br>
        <?php if ($photo): ?>
            <img src="../uploads/users/<?php echo $photo; ?>" width="120" height="120" style="border-radius:6px;">
        <?php else: ?>
            <p>No photo uploaded.</p>
        <?php endif; ?>
        <br><br>

        <label>Upload New Photo:</label>
        <input type="file" name="photo">

        <label class="mt-3">Username</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required>

        <label class="mt-3">Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

        <label class="mt-3">New Password (optional)</label>
        <input type="password" name="password">

        <label>Confirm New Password</label>
        <input type="password" name="confirm">

        <button type="submit" class="btn mt-4">Update Profile</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
