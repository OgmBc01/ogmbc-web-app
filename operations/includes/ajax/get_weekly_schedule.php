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

if (!isset($_GET['week_start']) || empty($_GET['week_start'])) {
    echo json_encode(['success' => false, 'message' => 'Week start date required']);
    exit;
}

$week_start = mysqli_real_escape_string($connection, $_GET['week_start']);
$week_end = date('Y-m-d', strtotime('sunday this week', strtotime($week_start)));

$query = "SELECT * FROM employee_weekly_schedule 
          WHERE employee_id = $user_id 
          AND week_start_date = '$week_start'";
$result = mysqli_query($connection, $query);

if (mysqli_num_rows($result) > 0) {
    $schedule = mysqli_fetch_assoc($result);
    echo json_encode([
        'success' => true,
        'schedule' => json_decode($schedule['schedule_data'], true)
    ]);
} else {
    echo json_encode(['success' => true, 'schedule' => null]);
}

ob_end_flush();
?>