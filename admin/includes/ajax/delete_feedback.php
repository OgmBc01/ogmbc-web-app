<?php
// Start output buffering
ob_start();

// Disable error display
ini_set('display_errors', 0);
error_reporting(0);

// Set JSON header
header('Content-Type: application/json');

try {
    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized access');
    }

    // Check user role for delete permission
    $user_id = $_SESSION['user_id'];
    $role_query = "SELECT role_name FROM users u LEFT JOIN user_roles r ON u.role_id = r.role_id WHERE u.user_id = $user_id";
    $role_result = mysqli_query($connection, $role_query);
    $user_role = mysqli_fetch_assoc($role_result)['role_name'] ?? '';
    
    $can_delete = ($user_role == 'ceo_gm' || $user_role == 'hr_admin' || $user_role == 'admin_staff');
    
    if (!$can_delete) {
        throw new Exception('You do not have permission to delete feedback');
    }

    // Include database connection
    require_once __DIR__ . '/../../../includes/database.php';

    if (!$connection) {
        throw new Exception('Database connection failed');
    }

    // Get feedback ID
    $feedback_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if (!$feedback_id) {
        throw new Exception('Invalid feedback ID');
    }

    // Check if feedback can be deleted (only pending feedback)
    $check_query = "SELECT is_validated, is_rejected FROM client_feedback WHERE feedback_id = ?";
    $check_stmt = mysqli_prepare($connection, $check_query);
    mysqli_stmt_bind_param($check_stmt, "i", $feedback_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $feedback = mysqli_fetch_assoc($check_result);
    mysqli_stmt_close($check_stmt);

    if (!$feedback) {
        throw new Exception('Feedback not found');
    }

    if ($feedback['is_validated'] == 1) {
        throw new Exception('Cannot delete validated feedback');
    }

    if ($feedback['is_rejected'] == 1) {
        throw new Exception('Cannot delete rejected feedback');
    }

    // Delete feedback
    $delete_query = "DELETE FROM client_feedback WHERE feedback_id = ?";
    $delete_stmt = mysqli_prepare($connection, $delete_query);
    mysqli_stmt_bind_param($delete_stmt, "i", $feedback_id);
    mysqli_stmt_execute($delete_stmt);

    if (mysqli_stmt_affected_rows($delete_stmt) > 0) {
        ob_clean();
        echo json_encode(['success' => true, 'message' => 'Feedback deleted successfully.']);
    } else {
        throw new Exception('Failed to delete feedback');
    }

    mysqli_stmt_close($delete_stmt);

} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

ob_end_flush();
?>