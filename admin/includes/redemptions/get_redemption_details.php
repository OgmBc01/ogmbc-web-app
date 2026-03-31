<?php
ob_start();
session_start();

require_once '../../../includes/database.php';
header('Content-Type: application/json');

// Initialize session and check permission
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$request_id = (int)$_GET['id'];


$query = "SELECT rr.*, 
           e.first_name, e.last_name, e.employee_id,
           u.username
       FROM points_redemption_requests rr
       LEFT JOIN employees e ON rr.employee_id = e.user_id
       LEFT JOIN users u ON rr.employee_id = u.user_id
       WHERE rr.request_id = $request_id";

$result = mysqli_query($connection, $query);

if (mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);
    $month_name = date('F', mktime(0, 0, 0, $data['month'], 1));
    
    echo json_encode([
        'success' => true,
        'data' => [
            'request_id' => $data['request_id'],
            'employee_name' => $data['first_name'] . ' ' . $data['last_name'],
            'employee_id' => $data['employee_id'],
            'month' => $data['month'],
            'month_name' => $month_name,
            'year' => $data['year'],
            'points_requested' => $data['points_requested'],
            'status' => $data['status'],
            'requested_at' => $data['requested_at'],
            'reviewed_at' => $data['reviewed_at'],
            'employee_notes' => $data['notes'],
            'admin_notes' => $data['notes']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Request not found']);
}

ob_end_flush();
?>