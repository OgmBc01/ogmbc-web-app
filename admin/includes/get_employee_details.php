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

require_once __DIR__ . '/../../includes/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid employee ID']);
    exit;
}

$employee_id = (int)$_GET['id'];

// Updated query to join with users, departments, user_roles, and user_types tables
$sql = "SELECT 
            e.*, 
            u.username,
            u.user_status,
            u.created_at as user_created_at,
            u.role_id,
            u.type_id,
            r.role_name,
            r.role_level,
            t.type_name,
            d.id as department_id,
            d.dept_name as department_name,
            d.dept_code as department_code
        FROM employees e
        INNER JOIN users u ON e.user_id = u.user_id
        LEFT JOIN departments d ON e.department_id = d.id
        LEFT JOIN user_roles r ON u.role_id = r.role_id
        LEFT JOIN user_types t ON u.type_id = t.type_id
        WHERE e.employee_id = ?";

$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $employee = $result->fetch_assoc()) {
    // Ensure all fields exist
    $employee = array_map(function($value) {
        return $value === null ? '' : $value;
    }, $employee);
    echo json_encode([
        'success' => true,
        'employee' => $employee
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Employee not found'
    ]);
}
if ($result) {
    $result->free();
}
$stmt->close();
ob_end_flush();
?>