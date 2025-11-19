<?php
// Admin logout: destroy admin session and redirect to admin login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear admin-specific session data
unset($_SESSION['admin_id'], $_SESSION['admin_email']);

// Optionally clear all session data if desired:
// $_SESSION = [];
// session_destroy();

header('Location: index.php');
exit;
?>

