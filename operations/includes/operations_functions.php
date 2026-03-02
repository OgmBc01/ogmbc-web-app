<?php
// Reusable session inactivity timeout checker
if (!function_exists('enforce_session_timeout')) {
function enforce_session_timeout($timeout_seconds = 1800, $redirect = '../index.php?error=session&reason=inactivity') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Only enforce for logged-in users
    if (!isset($_SESSION['user_id'])) {
        return;
    }

    $now = time();
    if (isset($_SESSION['last_activity'])) {
        $elapsed = $now - intval($_SESSION['last_activity']);
        if ($elapsed > intval($timeout_seconds)) {
            // expire session and redirect
            session_unset();
            session_destroy();
            header("Location: {$redirect}");
            exit();
        }
    }

    // update last activity timestamp
    $_SESSION['last_activity'] = $now;
}
} // Close function_exists block for enforce_session_timeout

// Function to sanitize HTML content //
if (!function_exists('sanitizeHTML')) {
function sanitizeHTML($html) {
    // Use built-in PHP filter for basic sanitization
    return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
}
} // Close function_exists block for sanitizeHTML
