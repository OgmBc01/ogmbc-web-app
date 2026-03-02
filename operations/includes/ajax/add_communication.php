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

if (!isset($_POST['client_id']) || !is_numeric($_POST['client_id']) || !isset($_POST['comm_type'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$client_id = (int)$_POST['client_id'];
$comm_type = mysqli_real_escape_string($connection, $_POST['comm_type']);
$subject = mysqli_real_escape_string($connection, trim($_POST['subject'] ?? ''));
$message = mysqli_real_escape_string($connection, trim($_POST['message'] ?? ''));
$direction = isset($_POST['direction']) ? mysqli_real_escape_string($connection, $_POST['direction']) : 'outgoing';

// Verify client is associated with this user
$check_query = "SELECT c.client_id 
                FROM clients c
                JOIN engagements e ON c.client_id = e.client_id
                WHERE c.client_id = $client_id AND e.assigned_to = $user_id
                LIMIT 1";
$check_result = mysqli_query($connection, $check_query);

if (!$check_result || mysqli_num_rows($check_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Client not found or access denied']);
    exit;
}

// Insert communication
$insert_query = "INSERT INTO client_communications 
                (client_id, user_id, comm_type, direction, subject, message, created_at)
                VALUES ($client_id, $user_id, '$comm_type', '$direction', '$subject', '$message', NOW())";

if (mysqli_query($connection, $insert_query)) {
    echo json_encode(['success' => true, 'message' => 'Communication logged successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($connection)]);
}

ob_end_flush();
?>