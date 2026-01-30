<?php
// Robust error handling for AJAX: always return JSON, catch fatal errors
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

require_once __DIR__ . '/../../includes/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid lead ID']);
    exit;
}

$lead_id = (int)$_GET['id'];

$query = "DELETE FROM leads WHERE id = $lead_id";
$result = mysqli_query($connection, $query);

if ($result && mysqli_affected_rows($connection) > 0) {
    echo json_encode(['success' => true, 'message' => 'Lead deleted successfully']);
} else {
    $check_query = "SELECT id FROM leads WHERE id = $lead_id";
    $check_result = mysqli_query($connection, $check_query);
    if ($check_result && mysqli_num_rows($check_result) === 0) {
        echo json_encode(['success' => false, 'message' => 'Lead not found']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete lead. Please try again.']);
    }
    if ($check_result) {
        mysqli_free_result($check_result);
    }
}
ob_end_flush();
?>