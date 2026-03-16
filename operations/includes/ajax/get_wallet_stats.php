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
$current_year = date('Y');
$current_month = date('m');

// Get lifetime stats
$lifetime_query = "SELECT 
    COALESCE(SUM(CASE WHEN points_type IN ('EARNED', 'ADJUSTMENT') THEN points ELSE 0 END), 0) as total_points,
    COALESCE(SUM(CASE WHEN points_type = 'DEDUCTED' THEN points ELSE 0 END), 0) as total_deducted,
    COUNT(*) as total_transactions
    FROM points_ledger 
    WHERE employee_id = $user_id";
$lifetime_result = mysqli_query($connection, $lifetime_query);
$lifetime = mysqli_fetch_assoc($lifetime_result);

// Get current month stats
$month_query = "SELECT 
    COALESCE(SUM(CASE WHEN points_type IN ('EARNED', 'ADJUSTMENT') THEN points ELSE 0 END), 0) as month_points,
    COALESCE(SUM(CASE WHEN points_type = 'DEDUCTED' THEN points ELSE 0 END), 0) as month_deducted
    FROM points_ledger 
    WHERE employee_id = $user_id 
    AND MONTH(created_at) = $current_month 
    AND YEAR(created_at) = $current_year";
$month_result = mysqli_query($connection, $month_query);
$month = mysqli_fetch_assoc($month_result);

$month_net = $month['month_points'] - $month['month_deducted'];
$cashable = max(0, $month_net - 1000);

// Get source breakdown
$source_query = "SELECT 
    source_type,
    COUNT(*) as count,
    SUM(points) as total_points
    FROM points_ledger 
    WHERE employee_id = $user_id AND points_type = 'EARNED'
    GROUP BY source_type
    ORDER BY total_points DESC";
$source_result = mysqli_query($connection, $source_query);

$sources = [];
while ($row = mysqli_fetch_assoc($source_result)) {
    $sources[] = [
        'type' => $row['source_type'],
        'count' => $row['count'],
        'points' => (int)$row['total_points'],
        'percentage' => $lifetime['total_points'] > 0 
            ? round(($row['total_points'] / $lifetime['total_points']) * 100, 1) 
            : 0
    ];
}

// Get recent transactions (last 5)
$recent_query = "SELECT * FROM points_ledger 
                 WHERE employee_id = $user_id 
                 ORDER BY created_at DESC 
                 LIMIT 5";
$recent_result = mysqli_query($connection, $recent_query);

$recent = [];
while ($row = mysqli_fetch_assoc($recent_result)) {
    $recent[] = [
        'id' => $row['ledger_id'],
        'type' => $row['source_type'],
        'points_type' => $row['points_type'],
        'points' => $row['points'],
        'description' => $row['description'],
        'created_at' => $row['created_at'],
        'requires_approval' => (bool)$row['requires_approval'],
        'approved' => !is_null($row['approved_by'])
    ];
}

echo json_encode([
    'success' => true,
    'data' => [
        'lifetime' => [
            'total' => (int)$lifetime['total_points'],
            'deducted' => (int)$lifetime['total_deducted'],
            'net' => (int)($lifetime['total_points'] - $lifetime['total_deducted']),
            'transactions' => (int)$lifetime['total_transactions']
        ],
        'month' => [
            'earned' => (int)$month['month_points'],
            'deducted' => (int)$month['month_deducted'],
            'net' => (int)$month_net,
            'cashable' => (int)$cashable,
            'projected_aed' => (int)$cashable
        ],
        'sources' => $sources,
        'recent' => $recent
    ]
]);

ob_end_flush();
?>