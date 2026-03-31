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
$current_month = date('m');
$current_year = date('Y');


// Get eligible points for current month
// Points from ENGAGEMENT, CLIENT_FEEDBACK, and MANUAL_ADJUSTMENT (excluding CDP)
$points_query = "SELECT 
    COALESCE(SUM(CASE WHEN source_type IN ('ENGAGEMENT', 'CLIENT_FEEDBACK', 'MANUAL_ADJUSTMENT') AND points_type = 'EARNED' THEN points ELSE 0 END), 0) as eligible_points,
    COALESCE(SUM(CASE WHEN source_type = 'REDEMPTION' AND points_type = 'DEDUCTED' THEN points ELSE 0 END), 0) as redeemed_points
    FROM points_ledger 
    WHERE employee_id = $user_id 
    AND MONTH(created_at) = $current_month 
    AND YEAR(created_at) = $current_year";

$points_result = mysqli_query($connection, $points_query);
$points_data = mysqli_fetch_assoc($points_result);

$total_eligible = $points_data['eligible_points'];
$redeemed = $points_data['redeemed_points'];
$net_eligible = max(0, $total_eligible - 1000);
$available_for_redemption = max(0, $net_eligible - $redeemed);

// Check if there's already a pending/approved request for this month
$request_query = "SELECT request_id, status, points_requested 
                  FROM points_redemption_requests 
                  WHERE employee_id = $user_id 
                  AND month = $current_month 
                  AND year = $current_year
                  AND status IN ('PENDING', 'APPROVED')";
$request_result = mysqli_query($connection, $request_query);
$existing_request = mysqli_fetch_assoc($request_result);

$response = [
    'success' => true,
    'eligible' => $available_for_redemption > 0,
    'available_points' => $available_for_redemption,
    'total_month_points' => $total_eligible,
    'already_redeemed' => $redeemed,
    'has_request' => $existing_request ? true : false,
    'request_status' => $existing_request ? $existing_request['status'] : null,
    'requested_points' => $existing_request ? $existing_request['points_requested'] : null
];

echo json_encode($response);
ob_end_flush();
?>