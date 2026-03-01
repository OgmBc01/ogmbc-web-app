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

if (!isset($_SESSION['client_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$client_id = $_SESSION['client_id'];

if (!isset($_POST['ticket_id']) || !is_numeric($_POST['ticket_id']) || !isset($_POST['message'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$ticket_id = (int)$_POST['ticket_id'];
$message = mysqli_real_escape_string($connection, trim($_POST['message']));

// Verify ticket belongs to client
$check_query = "SELECT ticket_id FROM support_tickets WHERE ticket_id = $ticket_id AND client_id = $client_id";
$check_result = mysqli_query($connection, $check_query);

if (mysqli_num_rows($check_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Ticket not found']);
    exit;
}

// Insert reply
$insert_query = "INSERT INTO ticket_replies (ticket_id, user_id, message, is_staff, created_at)
                 VALUES ($ticket_id, $client_id, '$message', 0, NOW())";

if (mysqli_query($connection, $insert_query)) {
    // Update ticket status if it was resolved
    $update_query = "UPDATE support_tickets SET status = 'in_progress' 
                     WHERE ticket_id = $ticket_id AND status = 'resolved'";
    mysqli_query($connection, $update_query);
    
    // Log activity
    $log_query = "INSERT INTO client_activity_log 
                 (client_id, activity_type, description, ip_address)
                 VALUES 
                 ($client_id, 'ticket_reply', 'Replied to ticket #$ticket_id', '{$_SERVER['REMOTE_ADDR']}')";
    mysqli_query($connection, $log_query);
    
    echo json_encode(['success' => true, 'message' => 'Reply sent successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($connection)]);
}

ob_end_flush();
?>