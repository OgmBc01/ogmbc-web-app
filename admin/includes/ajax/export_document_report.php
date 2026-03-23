<?php

session_start();
include '../../../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    die('Unauthorized');
}

// Get parameters
$date_range = isset($_GET['date_range']) ? $_GET['date_range'] : 'month';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Get all documents for report
$query = "SELECT 
    d.document_id,
    d.document_title,
    d.document_description,
    d.document_type,
    d.file_original_name,
    d.view_count,
    d.download_count,
    d.created_at as upload_date,
    d.expires_at,
    d.is_active,
    CONCAT(u.first_name, ' ', u.last_name) as uploaded_by,
    GROUP_CONCAT(DISTINCT c.category_name) as categories,
    (SELECT COUNT(DISTINCT client_id) FROM document_client_access WHERE document_id = d.document_id) as client_count
    FROM client_documents d
    LEFT JOIN users u ON d.uploaded_by = u.user_id
    LEFT JOIN document_category_mapping m ON d.document_id = m.document_id
    LEFT JOIN document_categories c ON m.category_id = c.category_id
    WHERE d.created_at BETWEEN '$start_date' AND '$end_date'
    GROUP BY d.document_id
    ORDER BY d.created_at DESC";

$result = mysqli_query($connection, $query);

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="document_report_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// Add UTF-8 BOM
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Headers
fputcsv($output, [
    'Document ID',
    'Title',
    'Description',
    'Type',
    'Filename',
    'Categories',
    'Views',
    'Downloads',
    'Upload Date',
    'Expiration Date',
    'Status',
    'Uploaded By',
    'Client Access Count'
]);

// Data rows
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $row['document_id'],
        $row['document_title'],
        $row['document_description'],
        ucfirst($row['document_type']),
        $row['file_original_name'],
        $row['categories'],
        $row['view_count'],
        $row['download_count'],
        date('Y-m-d', strtotime($row['upload_date'])),
        $row['expires_at'] ? date('Y-m-d', strtotime($row['expires_at'])) : 'Never',
        $row['is_active'] ? 'Active' : 'Inactive',
        $row['uploaded_by'],
        $row['client_count']
    ]);
}

fclose($output);
?>