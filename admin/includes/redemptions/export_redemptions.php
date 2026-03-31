<?php
ob_start();
session_start();

require_once '../../../includes/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit();
}

$status = isset($_GET['status']) ? $_GET['status'] : 'ALL';
$search = isset($_GET['search']) ? mysqli_real_escape_string($connection, $_GET['search']) : '';

// Build query
$where = [];
if ($status != 'ALL') {
    $where[] = "rr.status = '$status'";
}
if (!empty($search)) {
    $where[] = "(e.first_name LIKE '%$search%' OR e.last_name LIKE '%$search%' OR e.employee_id LIKE '%$search%')";
}
$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$query = "SELECT rr.*, 
                 e.first_name, e.last_name, e.employee_id,
                 u.username
          FROM points_redemption_requests rr
          JOIN employees e ON rr.employee_id = e.employee_id
          JOIN users u ON rr.employee_id = u.user_id
          $where_clause
          ORDER BY rr.requested_at DESC";

$result = mysqli_query($connection, $query);

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="redemptions_export_' . date('Y-m-d') . '.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add headers
fputcsv($output, [
    'Request ID',
    'Employee ID',
    'Employee Name',
    'Employee Code',
    'Period',
    'Points Requested',
    'Amount (AED)',
    'Status',
    'Request Date',
    'Review Date',
    'Notes'
]);

// Add data
while ($row = mysqli_fetch_assoc($result)) {
    $month_name = date('F', mktime(0, 0, 0, $row['month'], 1));
    
    fputcsv($output, [
        $row['request_id'],
        $row['employee_id'],
        $row['first_name'] . ' ' . $row['last_name'],
        $row['employee_id'],
        $month_name . ' ' . $row['year'],
        $row['points_requested'],
        $row['points_requested'],
        $row['status'],
        $row['requested_at'],
        $row['reviewed_at'] ?? '',
        $row['notes'] ?? ''
    ]);
}

fclose($output);
ob_end_flush();
?>