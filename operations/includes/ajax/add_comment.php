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

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_POST['engagement_id']) || !is_numeric($_POST['engagement_id']) || !isset($_POST['comment'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$engagement_id = (int)$_POST['engagement_id'];
$comment = mysqli_real_escape_string($connection, trim($_POST['comment']));

if (empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
    exit;
}

// Verify engagement exists
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
    echo json_encode(['success' => true, 'message' => 'Comment added successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($connection)]);
}

ob_end_flush();
?>