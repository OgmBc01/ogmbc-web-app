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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid transaction ID']);
    exit;
}

$ledger_id = (int)$_GET['id'];

// Get transaction details
$query = "SELECT pl.*, 
          CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
          CONCAT(a.first_name, ' ', a.last_name) as approved_by_name
          FROM points_ledger pl
          LEFT JOIN users u ON pl.created_by = u.user_id
          LEFT JOIN users a ON pl.approved_by = a.user_id
          WHERE pl.ledger_id = $ledger_id AND pl.employee_id = $user_id";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Transaction not found']);
    exit;
}

$transaction = mysqli_fetch_assoc($result);

// Decode calculation data if exists
$calculation_data = null;
if (!empty($transaction['calculation_data'])) {
    $calculation_data = json_decode($transaction['calculation_data'], true);
}

echo json_encode([
    'success' => true,
    'data' => [
        'id' => $transaction['ledger_id'],
        'source' => $transaction['source_type'],
        'source_id' => $transaction['source_id'],
        'points' => (int)$transaction['points'],
        'points_type' => $transaction['points_type'],
        'description' => $transaction['description'],
        'notes' => $transaction['notes'],
        'requires_approval' => (bool)$transaction['requires_approval'],
        'approved' => !is_null($transaction['approved_by']),
        'approved_by' => $transaction['approved_by_name'],
        'approved_at' => $transaction['approved_at'],
        'created_at' => $transaction['created_at'],
        'created_by' => $transaction['created_by_name'],
        'calculation_data' => $calculation_data
    ]
]);

ob_end_flush();
?>