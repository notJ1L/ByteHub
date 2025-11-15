<?php
/**
 * Admin Credentials Creator
 * Run this file once to create admin login credentials
 * 
 * Default Credentials:
 * Email: admin@bytehub.com
 * Password: admin123
 * 
 * IMPORTANT: Delete this file after creating admin credentials for security!
 */

include '../includes/db.php';

$email = 'admin@bytehub.com';
$password = 'admin123';
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Check if admin already exists
$check = $conn->query("SELECT * FROM admin WHERE email = '$email'");

if ($check->num_rows > 0) {
    echo "<h2>Admin Already Exists</h2>";
    echo "<p>An admin with email <strong>$email</strong> already exists.</p>";
    echo "<p>If you want to reset the password, delete the existing admin first or use a different email.</p>";
} else {
    // Insert admin
    $sql = "INSERT INTO admin (email, password_hash) VALUES ('$email', '$password_hash')";
    
    if ($conn->query($sql)) {
        echo "<h2 style='color: green;'>✓ Admin Created Successfully!</h2>";
        echo "<div style='background: #f0f0f0; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h3>Login Credentials:</h3>";
        echo "<p><strong>Email:</strong> $email</p>";
        echo "<p><strong>Password:</strong> $password</p>";
        echo "</div>";
        echo "<p style='color: red;'><strong>⚠ IMPORTANT:</strong> Please delete this file (create_admin.php) after noting down the credentials!</p>";
        echo "<p><a href='/bytehub/admin/index.php' style='display: inline-block; padding: 10px 20px; background: #004d26; color: white; text-decoration: none; border-radius: 5px;'>Go to Admin Login</a></p>";
    } else {
        echo "<h2 style='color: red;'>Error Creating Admin</h2>";
        echo "<p>" . $conn->error . "</p>";
    }
}

$conn->close();
?>

