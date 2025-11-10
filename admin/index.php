<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $password = $_POST['password'];

  $sql = "SELECT * FROM admin WHERE email='$email'";
  $result = $conn->query($sql);

  if ($result && $result->num_rows === 1) {
    $admin = $result->fetch_assoc();
    if (password_verify($password, $admin['password_hash'])) {
      $_SESSION['admin_id'] = $admin['admin_id'];
      $_SESSION['admin_email'] = $admin['email'];
      header('Location: dashboard.php');
      exit;
    } else {
      echo "<p>Invalid credentials.</p>";
    }
  } else {
    echo "<p>No account found.</p>";
  }
}
?>

<h2>Admin Login</h2>
<form method="POST">
  <label>Email:</label><br>
  <input type="email" name="email" required><br>
  <label>Password:</label><br>
  <input type="password" name="password" required><br>
  <button type="submit">Login</button>
</form>
