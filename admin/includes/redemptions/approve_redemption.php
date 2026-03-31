<?php
ob_start();
session_start();

require_once '../../../includes/database.php';

// Initialize session and check permission
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../redemptions.php');
    exit();
}

$request_id = (int)$_POST['request_id'];
$admin_notes = mysqli_real_escape_string($connection, trim($_POST['admin_notes'] ?? ''));

// Get redemption request details
$request_query = "SELECT * FROM points_redemption_requests WHERE request_id = $request_id AND status = 'PENDING'";
$request_result = mysqli_query($connection, $request_query);

if (mysqli_num_rows($request_result) == 0) {
    $_SESSION['error_message'] = 'Redemption request not found or already processed.';
    header('Location: ../../redemptions.php');
    exit();
}

$request = mysqli_fetch_assoc($request_result);
$employee_id = $request['employee_id'];
$points = $request['points_requested'];
$month = $request['month'];
$year = $request['year'];

// Check if points are still eligible

$eligibility_query = "SELECT 
    COALESCE(SUM(CASE WHEN source_type IN ('ENGAGEMENT', 'CLIENT_FEEDBACK', 'MANUAL_ADJUSTMENT') AND points_type = 'EARNED' THEN points ELSE 0 END), 0) as eligible_points,
    COALESCE(SUM(CASE WHEN source_type = 'REDEMPTION' AND points_type = 'DEDUCTED' THEN points ELSE 0 END), 0) as redeemed_points
    FROM points_ledger 
    WHERE employee_id = $employee_id 
    AND MONTH(created_at) = $month 
    AND YEAR(created_at) = $year";

$eligibility_result = mysqli_query($connection, $eligibility_query);
$eligibility = mysqli_fetch_assoc($eligibility_result);

$net_eligible = max(0, $eligibility['eligible_points'] - 1000);
$available = max(0, $net_eligible - $eligibility['redeemed_points']);

if ($points > $available) {
    $_SESSION['error_message'] = "Points are no longer eligible for redemption. Available: $available, Requested: $points";
    header('Location: ../../redemptions.php');
    exit();
}

// Begin transaction
mysqli_begin_transaction($connection);

try {
    // Insert redemption deduction into points_ledger
    $insert_query = "INSERT INTO points_ledger 
                    (employee_id, source_type, source_id, awarded_by, points, points_type, description, notes, requires_approval, approved_by, approved_at, created_by)
                    VALUES 
                    ($employee_id, 'REDEMPTION', $request_id, $admin_id, $points, 'DEDUCTED', 'Cash redemption for " . date('F', mktime(0,0,0,$month,1)) . " $year', '$admin_notes', 0, $admin_id, NOW(), $admin_id)";
    
    if (!mysqli_query($connection, $insert_query)) {
        throw new Exception("Failed to insert redemption deduction: " . mysqli_error($connection));
    }
    
    // Update redemption request
    $update_query = "UPDATE points_redemption_requests 
                     SET status = 'APPROVED', 
                         reviewed_by = $admin_id, 
                         reviewed_at = NOW(),
                         notes = CONCAT(COALESCE(notes, ''), '\nAdmin Approval: ', '$admin_notes')
                     WHERE request_id = $request_id";
    
    if (!mysqli_query($connection, $update_query)) {
        throw new Exception("Failed to update request: " . mysqli_error($connection));
    }
    
    mysqli_commit($connection);
    $_SESSION['success_message'] = "Redemption request #$request_id approved successfully! $points points deducted.";
    
} catch (Exception $e) {
    mysqli_rollback($connection);
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
}

header('Location: ../../redemptions.php');
exit();
ob_end_flush();
?>