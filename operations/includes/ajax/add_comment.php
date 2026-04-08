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

require_once __DIR__ . '/../../../includes/database.php';
header('Content-Type: application/json');

// Check if user is logged in

// Debug: Output session and POST info for troubleshooting
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized',
        'debug' => [
            'session' => $_SESSION,
            'post' => $_POST,
            'cookies' => $_COOKIE,
            'session_id' => session_id(),
        ]
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get POST data
if (!isset($_POST['engagement_id']) || !is_numeric($_POST['engagement_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid engagement ID']);
    exit;
}

if (!isset($_POST['comment']) || empty(trim($_POST['comment']))) {
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
    exit;
}

$engagement_id = (int)$_POST['engagement_id'];
$comment = mysqli_real_escape_string($connection, trim($_POST['comment']));

// Verify engagement exists (open to any logged-in user)
$check_query = "SELECT engagement_id FROM engagements WHERE engagement_id = $engagement_id";
$check_result = mysqli_query($connection, $check_query);

if (!$check_result || mysqli_num_rows($check_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Engagement not found']);
    exit;
}

// Insert comment
$insert_query = "INSERT INTO task_comments (engagement_id, user_id, comment, created_at)
                 VALUES ($engagement_id, $user_id, '$comment', NOW())";

if (mysqli_query($connection, $insert_query)) {
    // Get the user's name for the response
    $user_query = "SELECT CONCAT(first_name, ' ', last_name) as user_name FROM users WHERE user_id = $user_id";
    $user_result = mysqli_query($connection, $user_query);
    $user_name = 'You';
    if ($user_result && $row = mysqli_fetch_assoc($user_result)) {
        $user_name = htmlspecialchars($row['user_name']);
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Comment added successfully',
        'comment_id' => mysqli_insert_id($connection),
        'user_name' => $user_name,
        'created_at' => date('M d, H:i')
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($connection)]);
}

ob_end_flush();
?>