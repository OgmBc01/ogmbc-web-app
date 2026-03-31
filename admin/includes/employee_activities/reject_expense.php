<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../includes/database.php';

// Check admin permission
$admin_roles = ['admin_staff', 'ceo_gm', 'hr_admin'];
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $admin_roles)) {
    header('Location: ../index.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = 'Invalid expense ID';
    header('Location: employee_activities.php?tab=expenses');
    exit();
}

$expense_id = (int)$_GET['id'];

$update_query = "UPDATE employee_expenses SET 
                 status = 'Rejected', 
                 approved_by = {$_SESSION['user_id']}, 
                 approved_at = NOW() 
                 WHERE expense_id = $expense_id";

if (mysqli_query($connection, $update_query)) {
    $_SESSION['success_message'] = 'Expense rejected';
} else {
    $_SESSION['error_message'] = 'Error rejecting expense: ' . mysqli_error($connection);
}

header('Location: employee_activities.php?tab=expenses');
exit();
ob_end_flush();
?>