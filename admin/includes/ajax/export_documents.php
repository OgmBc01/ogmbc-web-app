<?php
session_start();
include '../../../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    die('Unauthorized');
}

// Get filter parameters
$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : 'all';
$search = isset($_GET['search']) ? mysqli_real_escape_string($connection, $_GET['search']) : '';
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Build query
$where_conditions = ["d.is_active = 1"];
if ($filter_type != 'all') {
    $where_conditions[] = "d.document_type = '$filter_type'";
}
if ($search) {
    $where_conditions[] = "(d.document_title LIKE '%$search%' OR d.document_description LIKE '%$search%')";
}
if ($category_filter > 0) {
    $where_conditions[] = "EXISTS (SELECT 1 FROM document_category_mapping m WHERE m.document_id = d.document_id AND m.category_id = $category_filter)";
}

$where_clause = implode(" AND ", $where_conditions);

$query = "SELECT d.document_id, d.document_title, d.document_description, d.document_type,
          d.file_original_name, d.file_size, d.view_count, d.download_count,
          d.created_at, d.expires_at, d.is_active,
          CONCAT(u.first_name, ' ', u.last_name) as uploaded_by,
          GROUP_CONCAT(DISTINCT c.category_name) as categories
          FROM client_documents d
          LEFT JOIN users u ON d.uploaded_by = u.user_id
          LEFT JOIN document_category_mapping m ON d.document_id = m.document_id
          LEFT JOIN document_categories c ON m.category_id = c.category_id
          WHERE $where_clause
          GROUP BY d.document_id
          ORDER BY d.created_at DESC";

$result = mysqli_query($connection, $query);

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="documents_export_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Column headers
fputcsv($output, [
    'Document ID', 'Title', 'Description', 'Type', 'Filename', 
    'Size (KB)', 'Views', 'Downloads', 'Categories', 'Uploaded By', 
    'Upload Date', 'Expiration Date', 'Status'
]);

// Add data rows
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $row['document_id'],
        $row['document_title'],
        $row['document_description'],
        ucfirst($row['document_type']),
        $row['file_original_name'],
        round($row['file_size'] / 1024, 2),
        $row['view_count'],
        $row['download_count'],
        $row['categories'],
        $row['uploaded_by'],
        date('Y-m-d H:i', strtotime($row['created_at'])),
        $row['expires_at'] ? date('Y-m-d', strtotime($row['expires_at'])) : 'Never',
        $row['is_active'] ? 'Active' : 'Inactive'
    ]);
}

fclose($output);
?>