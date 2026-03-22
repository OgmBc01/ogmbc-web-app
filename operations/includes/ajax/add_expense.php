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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$expense_date = mysqli_real_escape_string($connection, $_POST['expense_date']);
$client_id = !empty($_POST['client_id']) ? (int)$_POST['client_id'] : 'NULL';
$expense_type = mysqli_real_escape_string($connection, $_POST['expense_type']);
$mode_of_transport = mysqli_real_escape_string($connection, $_POST['mode_of_transport'] ?? '');
$amount = (float)$_POST['amount'];
$description = mysqli_real_escape_string($connection, $_POST['description'] ?? '');

// Handle file upload
$receipt_file = '';
if (isset($_FILES['receipt_file']) && $_FILES['receipt_file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['receipt_file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
    
    if (in_array($ext, $allowed)) {
        $upload_dir = "../../uploads/expenses/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $new_filename = "expense_" . $user_id . "_" . time() . "_" . rand(1000, 9999) . "." . $ext;
        $target_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $receipt_file = $new_filename;
        }
    }
}

if (empty($expense_date) || empty($amount) || $amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit;
}

$client_value = ($client_id !== 'NULL') ? $client_id : 'NULL';

$insert_query = "INSERT INTO employee_expenses 
                (employee_id, expense_date, client_id, expense_type, mode_of_transport, amount, description, receipt_file)
                VALUES ($user_id, '$expense_date', $client_value, '$expense_type', '$mode_of_transport', 
                        $amount, '$description', '$receipt_file')";

if (mysqli_query($connection, $insert_query)) {
    echo json_encode(['success' => true, 'message' => 'Expense submitted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($connection)]);
}

ob_end_flush();
?>