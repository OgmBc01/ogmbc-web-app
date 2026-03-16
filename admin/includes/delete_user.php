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

// Check for foreign key references
$references = [];

// Check clients table (assigned_sales_id)
$client_ref_query = "SELECT COUNT(*) as count FROM clients WHERE assigned_sales_id = $user_id";
$client_ref_result = mysqli_query($connection, $client_ref_query);
$client_ref_count = mysqli_fetch_assoc($client_ref_result)['count'];
if ($client_ref_count > 0) {
    $references['clients'] = $client_ref_count;
}

// Check employees table
$employee_ref_query = "SELECT COUNT(*) as count FROM employees WHERE user_id = $user_id";
$employee_ref_result = mysqli_query($connection, $employee_ref_query);
$employee_ref_count = mysqli_fetch_assoc($employee_ref_result)['count'];
if ($employee_ref_count > 0) {
    $references['employees'] = $employee_ref_count;
}

// If there are references and not confirmed via POST, return warning
$confirm = isset($_POST['confirm']) && $_POST['confirm'] == '1';

if (!empty($references) && !$confirm) {
    echo json_encode([
        'success' => false,
        'require_confirmation' => true,
        'references' => $references,
        'message' => 'This user has references in other tables. Please confirm deletion.'
    ]);
    exit;
}

// Proceed with deletion
mysqli_begin_transaction($connection);

try {
    // Nullify foreign key references
    if ($client_ref_count > 0) {
        $nullify_clients = "UPDATE clients SET assigned_sales_id = NULL WHERE assigned_sales_id = $user_id";
        if (!mysqli_query($connection, $nullify_clients)) {
            throw new Exception('Error updating clients: ' . mysqli_error($connection));
        }
    }
    
    // Handle employees - set user_id to NULL or delete based on your business logic
    if ($employee_ref_count > 0) {
        // Option 1: Set to NULL
        $nullify_employees = "UPDATE employees SET user_id = NULL WHERE user_id = $user_id";
        if (!mysqli_query($connection, $nullify_employees)) {
            throw new Exception('Error updating employees: ' . mysqli_error($connection));
        }
    }
    
    // Delete the user
    $delete_query = "DELETE FROM users WHERE user_id = $user_id";
    if (!mysqli_query($connection, $delete_query)) {
        throw new Exception('Error deleting user: ' . mysqli_error($connection));
    }
    
    mysqli_commit($connection);
    
    $msg = "User '$username' deleted successfully!";
    if (!empty($references)) {
        $ref_details = [];
        foreach ($references as $key => $count) {
            $ref_details[] = "$count " . $key;
        }
        $msg .= " References in " . implode(', ', $ref_details) . " were set to NULL.";
    }
    
    echo json_encode([
        'success' => true,
        'message' => $msg
    ]);
    
} catch (Exception $e) {
    mysqli_rollback($connection);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

ob_end_flush();
?>