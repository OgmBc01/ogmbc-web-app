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

$week_start = mysqli_real_escape_string($connection, $_POST['week_start']);
$week_end = date('Y-m-d', strtotime('sunday this week', strtotime($week_start)));

$schedule_data = [];
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

foreach ($days as $day) {
    $schedule_data[$day] = [
        'hours' => isset($_POST['hours'][$day]) ? (float)$_POST['hours'][$day] : 9,
        'place' => isset($_POST['place'][$day]) ? mysqli_real_escape_string($connection, $_POST['place'][$day]) : 'OGMBC',
        'clients' => isset($_POST['clients'][$day]) ? mysqli_real_escape_string($connection, $_POST['clients'][$day]) : ''
    ];
}

$schedule_json = json_encode($schedule_data);

// Check if schedule already exists
$check_query = "SELECT schedule_id FROM employee_weekly_schedule 
                WHERE employee_id = $user_id AND week_start_date = '$week_start'";
$check_result = mysqli_query($connection, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    $update_query = "UPDATE employee_weekly_schedule SET 
                     schedule_data = '$schedule_json',
                     week_end_date = '$week_end',
                     updated_at = NOW()
                     WHERE employee_id = $user_id AND week_start_date = '$week_start'";
    
    if (mysqli_query($connection, $update_query)) {
        $_SESSION['success_message'] = 'Schedule updated successfully';
    } else {
        $_SESSION['error_message'] = 'Database error: ' . mysqli_error($connection);
    }
} else {
    $insert_query = "INSERT INTO employee_weekly_schedule 
                    (employee_id, week_start_date, week_end_date, schedule_data)
                    VALUES ($user_id, '$week_start', '$week_end', '$schedule_json')";
    
    if (mysqli_query($connection, $insert_query)) {
        $_SESSION['success_message'] = 'Schedule saved successfully';
    } else {
        $_SESSION['error_message'] = 'Database error: ' . mysqli_error($connection);
    }
}

header('Location: /sales/activities.php#daily');
exit();
ob_end_flush();
?>