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

$user_id = $_SESSION['user_id'];

$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get filter parameters
$type_filter = isset($_POST['type']) ? $_POST['type'] : '';
$source_filter = isset($_POST['source']) ? $_POST['source'] : '';
$month_filter = isset($_POST['month']) ? (int)$_POST['month'] : 0;
$year_filter = isset($_POST['year']) ? (int)$_POST['year'] : 0;

// Build where clause
$where = ["employee_id = $user_id"];

if (!empty($type_filter)) {
    $where[] = "points_type = '" . mysqli_real_escape_string($connection, $type_filter) . "'";
}
if (!empty($source_filter)) {
    $where[] = "source_type = '" . mysqli_real_escape_string($connection, $source_filter) . "'";
}
if ($month_filter > 0 && $year_filter > 0) {
    $where[] = "MONTH(created_at) = $month_filter AND YEAR(created_at) = $year_filter";
} else if ($year_filter > 0) {
    $where[] = "YEAR(created_at) = $year_filter";
}

$where_clause = implode(' AND ', $where);

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM points_ledger WHERE $where_clause";
$count_result = mysqli_query($connection, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $per_page);

// Get transactions
$query = "SELECT * FROM points_ledger 
          WHERE $where_clause 
          ORDER BY created_at DESC 
          LIMIT $offset, $per_page";
$result = mysqli_query($connection, $query);

$transactions = [];
while ($row = mysqli_fetch_assoc($result)) {
    $transactions[] = [
        'id' => $row['ledger_id'],
        'date' => date('M d, Y', strtotime($row['created_at'])),
        'time' => date('H:i', strtotime($row['created_at'])),
        'source' => $row['source_type'],
        'description' => $row['description'],
        'notes' => $row['notes'],
        'points_type' => $row['points_type'],
        'points' => (int)$row['points'],
        'requires_approval' => (bool)$row['requires_approval'],
        'approved' => !is_null($row['approved_by'])
    ];
}

echo json_encode([
    'success' => true,
    'data' => [
        'transactions' => $transactions,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_records' => $total_records,
            'per_page' => $per_page,
            'has_next' => $page < $total_pages,
            'has_prev' => $page > 1
        ]
    ]
]);

ob_end_flush();
?>