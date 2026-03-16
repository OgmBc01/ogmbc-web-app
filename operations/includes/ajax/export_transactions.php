<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../includes/database.php';

if (!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}

$user_id = $_SESSION['user_id'];

// Get filter parameters
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';
$source_filter = isset($_GET['source']) ? $_GET['source'] : '';
$month_filter = isset($_GET['month']) ? (int)$_GET['month'] : 0;
$year_filter = isset($_GET['year']) ? (int)$_GET['year'] : 0;

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

// Get all transactions for export
$query = "SELECT * FROM points_ledger 
          WHERE $where_clause 
          ORDER BY created_at DESC";
$result = mysqli_query($connection, $query);

// Set filename
$filename = 'points_transactions_' . date('Y-m-d') . '.csv';

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add CSV headers
fputcsv($output, [
    'Date',
    'Time',
    'Source',
    'Type',
    'Points',
    'Description',
    'Notes',
    'Status',
    'Approved'
]);

// Add data rows
while ($row = mysqli_fetch_assoc($result)) {
    $sign = $row['points_type'] == 'EARNED' ? '+' : ($row['points_type'] == 'DEDUCTED' ? '-' : '±');
    $status = $row['requires_approval'] ? ($row['approved_by'] ? 'Approved' : 'Pending') : 'Auto-approved';
    
    fputcsv($output, [
        date('Y-m-d', strtotime($row['created_at'])),
        date('H:i:s', strtotime($row['created_at'])),
        ucwords(str_replace('_', ' ', $row['source_type'])),
        $row['points_type'],
        $sign . abs($row['points']),
        $row['description'] ?? '',
        $row['notes'] ?? '',
        $status,
        $row['approved_by'] ? 'Yes' : 'No'
    ]);
}

fclose($output);
exit();
?>