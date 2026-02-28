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

if (!isset($_GET['service_id']) || !is_numeric($_GET['service_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid service ID']);
    exit;
}

$service_id = (int)$_GET['service_id'];

$query = "SELECT rule_id, rule_version, base_points, penalty_type, penalty_value, 
                 penalty_unit, threshold_days, threshold_award, floor_points, effective_date
          FROM service_point_rules 
          WHERE service_id = $service_id AND is_active = 1
          ORDER BY rule_version DESC";
$result = mysqli_query($connection, $query);

$rules = [];
while ($row = mysqli_fetch_assoc($result)) {
    $rules[] = $row;
}

echo json_encode(['success' => true, 'rules' => $rules]);

ob_end_flush();
?>