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
    $_SESSION['error_message'] = 'Please select a client';
    header('Location: activities.php#tasks');
    exit();
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
                     payment_status = '$payment_status',
                     updated_at = NOW()
                     WHERE task_id = $task_id AND employee_id = $user_id";
    
    if (mysqli_query($connection, $update_query)) {
        $_SESSION['success_message'] = 'Task updated successfully';
    } else {
        $_SESSION['error_message'] = 'Database error: ' . mysqli_error($connection);
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
        $_SESSION['success_message'] = 'Task added successfully';
    } else {
        $_SESSION['error_message'] = 'Database error: ' . mysqli_error($connection);
    }
}

header('Location: activities.php#tasks');
exit();
ob_end_flush();
?>