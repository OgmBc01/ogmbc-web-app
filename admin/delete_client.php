<?php
session_start();
header('Content-Type: application/json');

// Enable error logging for debugging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Function to send JSON response and exit
function sendResponse($success, $message, $data = []) {
    $response = array_merge(['success' => $success, 'message' => $message], $data);
    echo json_encode($response);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendResponse(false, 'Unauthorized access');
}

// Include database connection
require_once dirname(__DIR__) . '/includes/database.php';
if (!isset($connection) || !$connection) {
    sendResponse(false, 'Database connection not found');
}

// Get client ID from request
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$client_id = isset($data['id']) ? (int)$data['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

if (!$client_id) {
    sendResponse(false, 'Invalid client ID');
}

// Start transaction
mysqli_begin_transaction($connection);

try {
    // Get the user_id associated with this client
    $user_query = "SELECT user_id FROM clients WHERE client_id = ?";
    $user_stmt = mysqli_prepare($connection, $user_query);
    mysqli_stmt_bind_param($user_stmt, "i", $client_id);
    mysqli_stmt_execute($user_stmt);
    $user_result = mysqli_stmt_get_result($user_stmt);
    $user_data = mysqli_fetch_assoc($user_result);
    $user_id = $user_data['user_id'] ?? null;
    mysqli_stmt_close($user_stmt);
    
    // Delete related records (use prepared statements)
    // Only delete from tables that actually have a client_id column
    $tables = ['client_files', 'client_communications', 'client_notes', 'client_documents', 'client_feedback'];
    foreach ($tables as $table) {
        // Check if the table has a client_id column before attempting delete
        $col_check = mysqli_query($connection, "SHOW COLUMNS FROM `$table` LIKE 'client_id'");
        if (mysqli_num_rows($col_check) > 0) {
            $delete_stmt = mysqli_prepare($connection, "DELETE FROM $table WHERE client_id = ?");
            if ($delete_stmt) {
                mysqli_stmt_bind_param($delete_stmt, "i", $client_id);
                mysqli_stmt_execute($delete_stmt);
                mysqli_stmt_close($delete_stmt);
            }
        }
    }
    
    // Update engagements
    $col_check = mysqli_query($connection, "SHOW COLUMNS FROM `engagements` LIKE 'client_id'");
    if (mysqli_num_rows($col_check) > 0) {
        $update_stmt = mysqli_prepare($connection, "UPDATE engagements SET client_id = NULL WHERE client_id = ?");
        if ($update_stmt) {
            mysqli_stmt_bind_param($update_stmt, "i", $client_id);
            mysqli_stmt_execute($update_stmt);
            mysqli_stmt_close($update_stmt);
        }
    }
    
    // Delete the client
    $delete_client_stmt = mysqli_prepare($connection, "DELETE FROM clients WHERE client_id = ?");
    mysqli_stmt_bind_param($delete_client_stmt, "i", $client_id);
    mysqli_stmt_execute($delete_client_stmt);
    $client_deleted = mysqli_stmt_affected_rows($delete_client_stmt) > 0;
    mysqli_stmt_close($delete_client_stmt);
    
    // Delete the associated user if exists
    if ($user_id && $client_deleted) {
        $user_check_stmt = mysqli_prepare($connection, "SELECT user_id FROM users WHERE user_id = ?");
        mysqli_stmt_bind_param($user_check_stmt, "i", $user_id);
        mysqli_stmt_execute($user_check_stmt);
        $user_check_result = mysqli_stmt_get_result($user_check_stmt);
        
        if (mysqli_num_rows($user_check_result) > 0) {
            $delete_user_stmt = mysqli_prepare($connection, "DELETE FROM users WHERE user_id = ?");
            mysqli_stmt_bind_param($delete_user_stmt, "i", $user_id);
            mysqli_stmt_execute($delete_user_stmt);
            mysqli_stmt_close($delete_user_stmt);
        }
        mysqli_stmt_close($user_check_stmt);
    }
    
    mysqli_commit($connection);
    sendResponse(true, 'Client deleted successfully');
    
} catch (Exception $e) {
    mysqli_rollback($connection);
    error_log("Delete client error: " . $e->getMessage());
    sendResponse(false, 'Error deleting client: ' . $e->getMessage());
}
?>