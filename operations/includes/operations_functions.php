<?php
// Centralized Session & Authentication Functions for Operations Area
// Include this file at the top of all operations pages that need session/authentication

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Initialize session with security settings
 * Call this at the beginning of all pages that need session
 */
function initSession() {
    // Regenerate session ID periodically for security
    if (!isset($_SESSION['CREATED'])) {
        session_regenerate_id(true);
        $_SESSION['CREATED'] = time();
    } else if (time() - $_SESSION['CREATED'] > 1800) {
        // Regenerate session ID every 30 minutes
        session_regenerate_id(true);
        $_SESSION['CREATED'] = time();
    }
    // Set secure session cookie parameters
    if (PHP_SESSION_ACTIVE) {
        $cookieParams = session_get_cookie_params();
        setcookie(
            session_name(),
            session_id(),
            [
                'expires' => time() + 7200, // 2 hours
                'path' => $cookieParams['path'],
                'domain' => $cookieParams['domain'],
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );
    }
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user's role ID
 * @return int|null
 */
function getCurrentRoleId() {
    return $_SESSION['role_id'] ?? null;
}

/**
 * Get current user's type ID
 * @return int|null
 */
function getCurrentTypeId() {
    return $_SESSION['type_id'] ?? null;
}

/**
 * Check if current user is admin (role_id 1, type_id 7)
 * @return bool
 */
function isAdmin() {
    return (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1 && 
            isset($_SESSION['type_id']) && $_SESSION['type_id'] == 7);
}

/**
 * Check if current user is operations employee (role_id 4, type_id 1)
 * @return bool
 */
function isOperations() {
    return (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 4 && 
            isset($_SESSION['type_id']) && $_SESSION['type_id'] == 1);
}

/**
 * Check if current user is client (role_id 4, type_id 2)
 * @return bool
 */
function isClient() {
    return (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 4 && 
            isset($_SESSION['type_id']) && $_SESSION['type_id'] == 2);
}

/**
 * Require authentication - redirect to login if not authenticated
 * @param string $loginPage
 */
function requireAuth($loginPage = 'index.php') {
    if (!isLoggedIn()) {
        header("Location: $loginPage");
        exit();
    }
}

/**
 * Require specific role/type combination
 * @param int $requiredRoleId
 * @param int $requiredTypeId
 * @param string $redirectPage
 */
function requireRole($requiredRoleId, $requiredTypeId, $redirectPage = 'index.php') {
    if (!isLoggedIn() || 
        getCurrentRoleId() != $requiredRoleId || 
        getCurrentTypeId() != $requiredTypeId) {
        header("Location: $redirectPage");
        exit();
    }
}

/**
 * Clear all session data and destroy session
 */
function logout() {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Set success message in session
 * @param string $message
 */
function setSuccessMessage($message) {
    $_SESSION['success_message'] = $message;
}

/**
 * Set error message in session
 * @param string $message
 */
function setErrorMessage($message) {
    $_SESSION['error_message'] = $message;
}

/**
 * Get and clear success message
 * @return string|null
 */
function getSuccessMessage() {
    $message = $_SESSION['success_message'] ?? null;
    unset($_SESSION['success_message']);
    return $message;
}

/**
 * Get and clear error message
 * @return string|null
 */
function getErrorMessage() {
    $message = $_SESSION['error_message'] ?? null;
    unset($_SESSION['error_message']);
    return $message;
}

// Try to load Composer's autoloader so PHPMailer (and other libs) are available.
$composerAutoload = __DIR__ . '/./vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
} else {
    error_log("Composer autoload not found at: $composerAutoload. PHPMailer classes may be unavailable.");
}

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
