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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid rule ID']);
    exit;
}

$rule_id = (int)$_GET['id'];

// Check if rule is used in any engagements
$check_query = "SELECT COUNT(*) as engagement_count FROM engagements WHERE rule_version_id = $rule_id";
$check_result = mysqli_query($connection, $check_query);
$row = mysqli_fetch_assoc($check_result);

if ($row['engagement_count'] > 0) {
    echo json_encode(['success' => false, 'message' => 'Cannot delete rule that is used in engagements']);
    exit;
}

$delete_query = "DELETE FROM service_point_rules WHERE rule_id = $rule_id";
if (mysqli_query($connection, $delete_query)) {
    echo json_encode(['success' => true, 'message' => 'Rule deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting rule: ' . mysqli_error($connection)]);
}

ob_end_flush();
?>