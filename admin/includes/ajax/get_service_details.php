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
    echo json_encode(['success' => false, 'message' => 'Invalid service ID']);
    exit;
}

$service_id = (int)$_GET['id'];

// Get service details
$service_query = "SELECT * FROM service_types WHERE service_id = $service_id";
$service_result = mysqli_query($connection, $service_query);

if ($service_result && mysqli_num_rows($service_result) > 0) {
    $service = mysqli_fetch_assoc($service_result);
    
    // Get rules for this service
    $rules_query = "SELECT * FROM service_point_rules WHERE service_id = $service_id ORDER BY rule_version DESC";
    $rules_result = mysqli_query($connection, $rules_query);
    
    $rules = [];
    while ($rule = mysqli_fetch_assoc($rules_result)) {
        $rules[] = $rule;
    }
    
    // Clean up null values
    $service = array_map(function($value) {
        return $value === null ? '' : $value;
    }, $service);
    
    echo json_encode([
        'success' => true,
        'service' => $service,
        'rules' => $rules
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Service not found'
    ]);
}

if ($service_result) {
    mysqli_free_result($service_result);
}
if (isset($rules_result) && $rules_result) {
    mysqli_free_result($rules_result);
}

ob_end_flush();
?>