<?php
// admin/includes/ajax/close_ticket.php
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

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['ticket_id']) || !is_numeric($input['ticket_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid ticket ID']);
    exit;
}
$ticket_id = (int)$input['ticket_id'];

$user_id = $_SESSION['user_id'];

// Update ticket status to closed
$update_query = "UPDATE support_tickets SET status = 'closed', updated_at = NOW() WHERE ticket_id = $ticket_id";
if (mysqli_query($connection, $update_query)) {
    // Add system message
    $system_message = "Ticket closed by admin.";
    $reply_query = "INSERT INTO ticket_replies (ticket_id, user_id, message, is_staff, created_at) VALUES ($ticket_id, $user_id, '$system_message', 1, NOW())";
    mysqli_query($connection, $reply_query);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to close ticket: ' . mysqli_error($connection)]);
}
ob_end_flush();
