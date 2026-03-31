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

$activity_date = mysqli_real_escape_string($connection, $_POST['activity_date']);
$hours_worked = (float)$_POST['hours_worked'];
$clients_attended = mysqli_real_escape_string($connection, $_POST['clients_attended'] ?? '');
$work_location = mysqli_real_escape_string($connection, $_POST['work_location']);
$nature_of_work = mysqli_real_escape_string($connection, $_POST['nature_of_work']);

if (empty($activity_date) || empty($hours_worked) || empty($nature_of_work)) {
    $_SESSION['error_message'] = 'Please fill in all required fields';
    header('Location: activities.php');
    exit();
}

// Check if activity already exists for this date
$check_query = "SELECT activity_id FROM employee_activities 
                WHERE employee_id = $user_id AND activity_date = '$activity_date'";
$check_result = mysqli_query($connection, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    $update_query = "UPDATE employee_activities SET 
                     hours_worked = $hours_worked,
                     clients_attended = '$clients_attended',
                     work_location = '$work_location',
                     nature_of_work = '$nature_of_work',
                     updated_at = NOW()
                     WHERE employee_id = $user_id AND activity_date = '$activity_date'";
    
    if (mysqli_query($connection, $update_query)) {
        $_SESSION['success_message'] = 'Activity updated successfully';
    } else {
        $_SESSION['error_message'] = 'Database error: ' . mysqli_error($connection);
    }
} else {
    $insert_query = "INSERT INTO employee_activities 
                    (employee_id, activity_date, day_name, hours_worked, clients_attended, work_location, nature_of_work)
                    VALUES ($user_id, '$activity_date', '" . date('l', strtotime($activity_date)) . "', 
                            $hours_worked, '$clients_attended', '$work_location', '$nature_of_work')";
    
    if (mysqli_query($connection, $insert_query)) {
        $_SESSION['success_message'] = 'Activity saved successfully';
    } else {
        $_SESSION['error_message'] = 'Database error: ' . mysqli_error($connection);
    }
}

header('Location: /operations/activities.php#daily');
exit();
ob_end_flush();
?>