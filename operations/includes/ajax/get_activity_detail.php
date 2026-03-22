<?php
// Fetch activity details by ID (AJAX endpoint)
include '../../../includes/database.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$activity_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$activity_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid activity ID']);
    exit;
}

$query = "SELECT * FROM employee_activities WHERE activity_id = $activity_id AND employee_id = $user_id";
$result = mysqli_query($connection, $query);
if ($activity = mysqli_fetch_assoc($result)) {
    echo json_encode(['success' => true, 'activity' => $activity]);
} else {
    echo json_encode(['success' => false, 'message' => 'Activity not found']);
}
