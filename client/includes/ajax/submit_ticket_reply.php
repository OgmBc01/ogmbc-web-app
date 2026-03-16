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


// Get client_id from session or fetch from clients table using user_id
$client_id = $_SESSION['client_id'] ?? 0;
if ($client_id <= 0 && isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
    $result_client = mysqli_query($connection, "SELECT client_id FROM clients WHERE user_id = $user_id");
    if ($result_client && mysqli_num_rows($result_client) > 0) {
        $row = mysqli_fetch_assoc($result_client);
        $client_id = $row['client_id'];
    }
}
if ($client_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_POST['ticket_id']) || !is_numeric($_POST['ticket_id']) || !isset($_POST['message'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$ticket_id = (int)$_POST['ticket_id'];
$message = mysqli_real_escape_string($connection, trim($_POST['message']));

// Verify ticket belongs to client


// Also get user_id from session for robust ownership check
$user_id = $_SESSION['user_id'] ?? 0;
// Debug log for troubleshooting
file_put_contents(__DIR__ . '/ticket_reply_debug.log', date('c') . " | ticket_id: $ticket_id | client_id: $client_id | user_id: $user_id\n", FILE_APPEND);

// Check ticket ownership by joining support_tickets to clients and matching user_id
$check_query = "SELECT t.ticket_id FROM support_tickets t INNER JOIN clients c ON t.client_id = c.client_id WHERE t.ticket_id = $ticket_id AND c.user_id = $user_id LIMIT 1";
$check_result = mysqli_query($connection, $check_query);
if (!$check_result) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($connection)]);
    exit;
}
if (mysqli_num_rows($check_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Ticket not found or not owned by you (ticket_id: ' . $ticket_id . ', user_id: ' . $user_id . ')']);
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
    
    // Log activity using the correct client_id for this user
    $user_id = $_SESSION['user_id'] ?? 0;
    $log_client_id = 0;
    if ($user_id > 0) {
        $result_client = mysqli_query($connection, "SELECT client_id FROM clients WHERE user_id = $user_id");
        if ($result_client && mysqli_num_rows($result_client) > 0) {
            $row = mysqli_fetch_assoc($result_client);
            $log_client_id = $row['client_id'];
        }
    }
    if ($log_client_id > 0) {
        $log_query = "INSERT INTO client_activity_log 
                     (client_id, activity_type, description, ip_address)
                     VALUES 
                     ($log_client_id, 'ticket_reply', 'Replied to ticket #$ticket_id', '{$_SERVER['REMOTE_ADDR']}')";
        mysqli_query($connection, $log_query);
    }
    
    echo json_encode(['success' => true, 'message' => 'Reply sent successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($connection)]);
}

ob_end_flush();
?>