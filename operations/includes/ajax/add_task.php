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

$task_id = isset($_POST['task_id']) ? (int)$_POST['task_id'] : 0;
$client_id = (int)$_POST['client_id'];
$job_type = mysqli_real_escape_string($connection, $_POST['job_type']);
$status = mysqli_real_escape_string($connection, $_POST['status']);
$remarks = mysqli_real_escape_string($connection, $_POST['remarks']);
$date_started = !empty($_POST['date_started']) ? mysqli_real_escape_string($connection, $_POST['date_started']) : 'NULL';
$date_given_for_review = !empty($_POST['date_given_for_review']) ? mysqli_real_escape_string($connection, $_POST['date_given_for_review']) : 'NULL';
$estimated_completion_date = !empty($_POST['estimated_completion_date']) ? mysqli_real_escape_string($connection, $_POST['estimated_completion_date']) : 'NULL';
$reason_for_pending = mysqli_real_escape_string($connection, $_POST['reason_for_pending']);
$invoicing_status = mysqli_real_escape_string($connection, $_POST['invoicing_status']);
$payment_status = mysqli_real_escape_string($connection, $_POST['payment_status']);

if (empty($client_id)) {
    echo json_encode(['success' => false, 'message' => 'Please select a client']);
    exit;
}

if ($task_id > 0) {
    // Update existing task
    $update_query = "UPDATE employee_tasks SET 
                     client_id = $client_id,
                     job_type = '$job_type',
                     status = '$status',
                     remarks = '$remarks',
                     date_started = " . ($date_started != 'NULL' ? "'$date_started'" : "NULL") . ",
                     date_given_for_review = " . ($date_given_for_review != 'NULL' ? "'$date_given_for_review'" : "NULL") . ",
                     estimated_completion_date = " . ($estimated_completion_date != 'NULL' ? "'$estimated_completion_date'" : "NULL") . ",
                     reason_for_pending = '$reason_for_pending',
                     invoicing_status = '$invoicing_status',
                     payment_status = '$payment_status'
                     WHERE task_id = $task_id AND employee_id = $user_id";
    
    if (mysqli_query($connection, $update_query)) {
        echo json_encode(['success' => true, 'message' => 'Task updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($connection)]);
    }
} else {
    // Insert new task
    $insert_query = "INSERT INTO employee_tasks 
                    (employee_id, client_id, job_type, status, remarks, date_started, 
                     date_given_for_review, estimated_completion_date, reason_for_pending, 
                     invoicing_status, payment_status)
                    VALUES ($user_id, $client_id, '$job_type', '$status', '$remarks', 
                            " . ($date_started != 'NULL' ? "'$date_started'" : "NULL") . ",
                            " . ($date_given_for_review != 'NULL' ? "'$date_given_for_review'" : "NULL") . ",
                            " . ($estimated_completion_date != 'NULL' ? "'$estimated_completion_date'" : "NULL") . ",
                            '$reason_for_pending', '$invoicing_status', '$payment_status')";
    
    if (mysqli_query($connection, $insert_query)) {
        echo json_encode(['success' => true, 'message' => 'Task added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($connection)]);
    }
}

ob_end_flush();
?>