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

if (!isset($_POST['expense_id']) || !is_numeric($_POST['expense_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid expense ID']);
    exit;
}

$expense_id = (int)$_POST['expense_id'];

// Check if expense exists and belongs to user
$check_query = "SELECT receipt_file FROM employee_expenses 
                WHERE expense_id = $expense_id AND employee_id = $user_id AND status = 'Pending'";
$check_result = mysqli_query($connection, $check_query);

if (!$check_result || mysqli_num_rows($check_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Expense not found or cannot be deleted']);
    exit;
}

$expense = mysqli_fetch_assoc($check_result);

// Delete receipt file if exists
if (!empty($expense['receipt_file'])) {
    $upload_dir = "../../uploads/expenses/";
    if (file_exists($upload_dir . $expense['receipt_file'])) {
        unlink($upload_dir . $expense['receipt_file']);
    }
}

$delete_query = "DELETE FROM employee_expenses WHERE expense_id = $expense_id AND employee_id = $user_id";

if (mysqli_query($connection, $delete_query)) {
    echo json_encode(['success' => true, 'message' => 'Expense deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($connection)]);
}

ob_end_flush();
?>