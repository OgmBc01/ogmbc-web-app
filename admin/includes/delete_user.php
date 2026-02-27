<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

set_exception_handler(function($e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
});

set_error_handler(function($errno, $errstr) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $errstr]);
    exit;
});

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Fatal error: ' . $error['message']]);
        exit;
    }
});

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get user ID
if (!isset($_POST['user_id']) || !is_numeric($_POST['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

$user_id = (int)$_POST['user_id'];
$current_user_id = (int)$_SESSION['user_id'];

// Prevent deleting yourself
if ($user_id == $current_user_id) {
    echo json_encode(['success' => false, 'message' => 'You cannot delete your own account']);
    exit;
}

// First check if user exists
$check_query = "SELECT user_id, username FROM users WHERE user_id = $user_id";
$check_result = mysqli_query($connection, $check_query);

if (!$check_result || mysqli_num_rows($check_result) === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$user = mysqli_fetch_assoc($check_result);
$username = $user['username'];

// Delete the user
$delete_query = "DELETE FROM users WHERE user_id = $user_id";
if (mysqli_query($connection, $delete_query)) {
    echo json_encode([
        'success' => true, 
        'message' => "User '$username' deleted successfully!"
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Error deleting user: ' . mysqli_error($connection)
    ]);
}

ob_end_flush();
?>