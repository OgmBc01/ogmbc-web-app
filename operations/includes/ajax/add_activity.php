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

$activity_date = mysqli_real_escape_string($connection, $_POST['activity_date']);
$hours_worked = (float)$_POST['hours_worked'];
$clients_attended = mysqli_real_escape_string($connection, $_POST['clients_attended'] ?? '');
$work_location = mysqli_real_escape_string($connection, $_POST['work_location']);
$nature_of_work = mysqli_real_escape_string($connection, $_POST['nature_of_work']);

if (empty($activity_date) || empty($hours_worked) || empty($nature_of_work)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit;
}


$insert_query = "INSERT INTO employee_activities 
    (employee_id, activity_date, day_name, hours_worked, clients_attended, work_location, nature_of_work)
    VALUES ($user_id, '$activity_date', '" . date('l', strtotime($activity_date)) . "', 
            $hours_worked, '$clients_attended', '$work_location', '$nature_of_work')";

if (mysqli_query($connection, $insert_query)) {
    echo json_encode(['success' => true, 'message' => 'Activity saved successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($connection)]);
}

ob_end_flush();
?>