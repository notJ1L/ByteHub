<?php
include '../includes/db.php';
include '../includes/functions.php';

$errors = [];
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';
    $photoName = null;

    // Basic validation
    if ($username === '' || $email === '' || $password === '' || $confirm === '') {
        $errors[] = 'All fields are required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    // Validate uploaded photo
    if (!empty($_FILES['photo']['name'])) {
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $errors[] = "Invalid photo format. Allowed: JPG, PNG, GIF, WEBP.";
        }
    }

    // Check if email exists
    if (!$errors) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Email already registered.';
        }
        $stmt->close();
    }

    if (!$errors) {
        // Handle photo upload
        if (!empty($_FILES['photo']['name'])) {
            $photoName = time() . '_' . $_FILES['photo']['name'];
            $uploadPath = "../uploads/users/" . $photoName;
            move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath);
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        // INSERT with new columns: photo, active, role
        $stmt = $conn->prepare("
            INSERT INTO users (username, email, password_hash, photo, active, role) 
            VALUES (?, ?, ?, ?, 1, 'user')
        ");

        $stmt->bind_param('ssss', $username, $email, $hash, $photoName);

        if ($stmt->execute()) {
            redirect('login.php');
        } else {
            $errors[] = 'Registration failed.';
        }

        $stmt->close();
    }
}
?>
<?php include '../includes/header.php'; ?>

<h2>Create Account</h2>

<?php if ($errors): ?>
  <div class="error"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
  <label>Username</label>
  <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required>

  <label>Email</label>
  <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

  <label>Password</label>
  <input type="password" name="password" required>

  <label>Confirm Password</label>
  <input type="password" name="confirm" required>

  <label>Profile Photo</label>
  <input type="file" name="photo">

  <button type="submit" class="btn">Register</button>
  <p>Already have an account? <a href="login.php">Login</a></p>
</form>

<?php include '../includes/footer.php'; ?>
