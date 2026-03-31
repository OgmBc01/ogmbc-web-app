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
$current_month = date('m');
$current_year = date('Y');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit();
}

$points_requested = (int)$_POST['points_to_redeem'];
$notes = mysqli_real_escape_string($connection, trim($_POST['notes'] ?? ''));
$month = (int)$_POST['month'];
$year = (int)$_POST['year'];

// Validate month/year matches current
if ($month != $current_month || $year != $current_year) {
    $_SESSION['error_message'] = 'Invalid redemption period.';
    exit();
}

if ($points_requested <= 0) {
    $_SESSION['error_message'] = 'Please enter a valid number of points to redeem.';
    exit();
}

// Check if there's already a pending/approved request for this month
$check_query = "SELECT request_id, status FROM points_redemption_requests 
                WHERE employee_id = $user_id AND month = $month AND year = $year 
                AND status IN ('PENDING', 'APPROVED')";
$check_result = mysqli_query($connection, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    $_SESSION['error_message'] = 'You already have a redemption request for this month.';
    exit();
}

// Calculate eligible points for this month (ENGAGEMENT + CLIENT_FEEDBACK only)
$points_query = "SELECT 
    COALESCE(SUM(CASE WHEN source_type IN ('ENGAGEMENT', 'CLIENT_FEEDBACK', 'MANUAL_ADJUSTMENT') AND points_type = 'EARNED' THEN points ELSE 0 END), 0) as eligible_points,
    COALESCE(SUM(CASE WHEN source_type = 'REDEMPTION' AND points_type = 'DEDUCTED' THEN points ELSE 0 END), 0) as redeemed_points
    FROM points_ledger 
    WHERE employee_id = $user_id 
    AND MONTH(created_at) = $month 
    AND YEAR(created_at) = $year";

$points_result = mysqli_query($connection, $points_query);
$points_data = mysqli_fetch_assoc($points_result);

$total_eligible = $points_data['eligible_points'];
$redeemed = $points_data['redeemed_points'];
$net_eligible = max(0, $total_eligible - 1000);
$available = max(0, $net_eligible - $redeemed);

if ($points_requested > $available) {
    $_SESSION['error_message'] = "You can only redeem up to $available points this month.";
    exit();
}

// Insert redemption request
$insert_query = "INSERT INTO points_redemption_requests 
                (employee_id, month, year, points_requested, notes, status)
                VALUES ($user_id, $month, $year, $points_requested, '$notes', 'PENDING')";


if (mysqli_query($connection, $insert_query)) {
    $_SESSION['success_message'] = "Redemption request submitted successfully! Waiting for admin approval.";
} else {
    $error_msg = mysqli_error($connection);
    if (strpos($error_msg, 'Duplicate entry') !== false) {
        $_SESSION['error_message'] = "You have already submitted a redemption request for this month. Please wait for admin review.";
    } else {
        $_SESSION['error_message'] = "Error submitting request: " . $error_msg;
    }
}

// Always redirect back to wallet page after processing
header('Location: /operations/wallet.php');
ob_end_flush();
exit();
?>