<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function redirect(string $path) {
    // Only send header if headers haven't been sent yet
    if (!headers_sent()) {
        header("Location: $path");
        exit;
    } else {
        // If headers already sent, use JavaScript redirect as fallback
        echo "<script>window.location.href = '$path';</script>";
        exit;
    }
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
    // List of bad/foul words to filter
    $bad_words = ['bad', 'foul', 'curse', 'damn', 'hell', 'stupid', 'idiot', 'hate', 'puta', 'bobo', 'gago', 'tangina', 'tanga'];
    
    // Use regex to match whole words only (case-insensitive)
    foreach ($bad_words as $word) {
        $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
        $text = preg_replace($pattern, '****', $text);
    }
    
    return $text;
}