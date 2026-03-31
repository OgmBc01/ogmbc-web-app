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
$rejection_reason = mysqli_real_escape_string($connection, trim($_POST['rejection_reason'] ?? ''));

if (empty($rejection_reason)) {
    $_SESSION['error_message'] = 'Please provide a reason for rejection.';
    header('Location: ../../redemptions.php');
    exit();
}

// Check if request exists and is pending
$check_query = "SELECT * FROM points_redemption_requests WHERE request_id = $request_id AND status = 'PENDING'";
$check_result = mysqli_query($connection, $check_query);

if (mysqli_num_rows($check_result) == 0) {
    $_SESSION['error_message'] = 'Redemption request not found or already processed.';
    header('Location: ../../redemptions.php');
    exit();
}

// Update redemption request
$update_query = "UPDATE points_redemption_requests 
                 SET status = 'REJECTED', 
                     reviewed_by = $admin_id, 
                     reviewed_at = NOW(),
                     notes = CONCAT(COALESCE(notes, ''), '\nAdmin Rejection: ', '$rejection_reason')
                 WHERE request_id = $request_id";

if (mysqli_query($connection, $update_query)) {
    $_SESSION['success_message'] = "Redemption request #$request_id rejected successfully.";
} else {
    $_SESSION['error_message'] = "Error rejecting request: " . mysqli_error($connection);
}

header('Location: ../../redemptions.php');
exit();
ob_end_flush();
?>