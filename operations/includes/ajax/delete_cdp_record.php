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

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid CDP record ID.";
    header("Location: ../../cdp.php");
    exit();
}

$cdp_id = (int)$_GET['id'];

// Verify ownership and check if still pending
$check_query = "SELECT cdp_id, document_file FROM cdp_records 
                WHERE cdp_id = $cdp_id AND employee_id = $user_id AND status = 'PENDING'";
$check_result = mysqli_query($connection, $check_query);

if (!$check_result || mysqli_num_rows($check_result) == 0) {
    $_SESSION['error_message'] = "Record not found or cannot be deleted.";
    header("Location: ../../cdp.php");
    exit();
}

$record = mysqli_fetch_assoc($check_result);

// Delete document file if exists
if (!empty($record['document_file'])) {
    $file_path = "../../../uploads/cdp_documents/" . $record['document_file'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}

// Delete record
$delete_query = "DELETE FROM cdp_records WHERE cdp_id = $cdp_id";
if (mysqli_query($connection, $delete_query)) {
    $_SESSION['success_message'] = "CDP record deleted successfully.";
} else {
    $_SESSION['error_message'] = "Error deleting record: " . mysqli_error($connection);
}

header("Location: ../../cdp.php");
exit();
?>