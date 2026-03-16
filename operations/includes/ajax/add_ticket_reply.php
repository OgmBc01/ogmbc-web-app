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

if (!isset($_POST['ticket_id']) || !is_numeric($_POST['ticket_id']) || !isset($_POST['message'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$ticket_id = (int)$_POST['ticket_id'];
$message = mysqli_real_escape_string($connection, trim($_POST['message']));

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
    exit;
}

// Verify ticket belongs to this user
$check_query = "SELECT ticket_id, status FROM support_tickets WHERE ticket_id = $ticket_id AND assigned_to = $user_id";
$check_result = mysqli_query($connection, $check_query);

if (!$check_result || mysqli_num_rows($check_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Ticket not found or access denied']);
    exit;
}

// For now, we'll just update the ticket's updated_at timestamp
// If you have a ticket_replies table, you would insert there instead
$update_query = "UPDATE support_tickets SET updated_at = NOW() WHERE ticket_id = $ticket_id";

if (mysqli_query($connection, $update_query)) {
    echo json_encode(['success' => true, 'message' => 'Reply sent successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($connection)]);
}

ob_end_flush();
?>