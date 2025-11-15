<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function redirect(string $path) {
    header("Location: $path");
    exit;
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function after_login_redirect_path(): string {
    if (!empty($_SESSION['redirect_after_login'])) {
        $to = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
        return $to;
    }
    return '../customer/index.php';
}

function isAdmin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return isset($_SESSION['admin_id']);
}

function filter_bad_words($text) {
    $bad_words = ['bad', 'foul', 'curse']; // Add more words to the array
    return str_ireplace($bad_words, '****', $text);
}