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
    echo json_encode(['success' => false, 'message' => 'Invalid department ID']);
    exit;
}

$dept_id = (int)$_GET['id'];

// Get department details with manager name from employees table
$dept_query = "SELECT d.*, 
               CONCAT(e.first_name, ' ', e.last_name) as manager_name
               FROM departments d
               LEFT JOIN employees e ON d.manager = e.employee_id
               WHERE d.id = $dept_id";
$dept_result = mysqli_query($connection, $dept_query);

if ($dept_result && mysqli_num_rows($dept_result) > 0) {
    $department = mysqli_fetch_assoc($dept_result);
    
    // Get employees in this department - using your actual table structure
    $emp_query = "SELECT employee_id, first_name, last_name, 
                  field_of_study, qualification, highest_graduation,
                  year_of_graduation, created_at
                  FROM employees 
                  WHERE department_id = $dept_id 
                  ORDER BY first_name";
    $emp_result = mysqli_query($connection, $emp_query);
    
    $employees = [];
    while ($emp = mysqli_fetch_assoc($emp_result)) {
        $employees[] = $emp;
    }
    
    // Also get employee count for this department
    $count_query = "SELECT COUNT(*) as total FROM employees WHERE department_id = $dept_id";
    $count_result = mysqli_query($connection, $count_query);
    $count_row = mysqli_fetch_assoc($count_result);
    
    // Clean up null values
    $department = array_map(function($value) {
        return $value === null ? '' : $value;
    }, $department);
    
    echo json_encode([
        'success' => true,
        'department' => $department,
        'employees' => $employees,
        'employee_count' => $count_row['total']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Department not found'
    ]);
}

// Free result sets
if ($dept_result) {
    mysqli_free_result($dept_result);
}
if (isset($emp_result) && $emp_result) {
    mysqli_free_result($emp_result);
}
if (isset($count_result) && $count_result) {
    mysqli_free_result($count_result);
}

ob_end_flush();
?>