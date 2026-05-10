<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: activities.php');
    exit();
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
    $_SESSION['error_message'] = 'Please fill in all required fields';
    header('Location: /sales/activities.php#expenses');
    exit();
}

$client_value = ($client_id !== 'NULL') ? $client_id : 'NULL';

$insert_query = "INSERT INTO employee_expenses 
                (employee_id, expense_date, client_id, expense_type, mode_of_transport, amount, description, receipt_file)
                VALUES ($user_id, '$expense_date', $client_value, '$expense_type', '$mode_of_transport', 
                        $amount, '$description', '$receipt_file')";

if (mysqli_query($connection, $insert_query)) {
    $_SESSION['success_message'] = 'Expense submitted successfully';
} else {
    $_SESSION['error_message'] = 'Database error: ' . mysqli_error($connection);
}

header('Location: /sales/activities.php#expenses');
exit();
ob_end_flush();
?>