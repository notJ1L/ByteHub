<?php
include '../includes/db.php';
include '../includes/functions.php';

$errors = [];
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if ($username === '' || $email === '' || $password === '' || $confirm === '') {
        $errors[] = 'All fields are required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

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
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $username, $email, $hash);
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

<form method="post">
  <label>Username</label>
  <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required>

  <label>Email</label>
  <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

  <label>Password</label>
  <input type="password" name="password" required>

  <label>Confirm Password</label>
  <input type="password" name="confirm" required>

  <button type="submit" class="btn">Register</button>
  <p>Already have an account? <a href="login.php">Login</a></p>
</form>

<?php include '../includes/footer.php'; ?>
