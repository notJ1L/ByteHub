<?php
include '../includes/db.php';
include '../includes/functions.php';

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Email and password are required.';
    }

    if (!$errors) {
        $stmt = $conn->prepare("SELECT user_id, username, password_hash FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            if (password_verify($password, $row['password_hash'])) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$row['user_id'];
                $_SESSION['username'] = $row['username'];
                redirect(after_login_redirect_path());
            } else {
                $errors[] = 'Invalid credentials.';
            }
        } else {
            $errors[] = 'Invalid credentials.';
        }
        $stmt->close();
    }
}
?>
<?php include '../includes/header.php'; ?>

<h2>Login</h2>
<?php if ($errors): ?>
  <div class="error"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?></div>
<?php endif; ?>

<form method="post">
  <label>Email</label>
  <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

  <label>Password</label>
  <input type="password" name="password" required>

  <button type="submit" class="btn">Login</button>
  <p>No account yet? <a href="register.php">Register</a></p>
</form>

<?php include '../includes/footer.php'; ?>
