<?php
// admin/includes/export_enquiries.php

require_once __DIR__ . '/../../includes/database.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get optional filters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=service_enquiries_' . date('Y-m-d') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, [
    'Enquiry ID', 
    'Name', 
    'Email', 
    'Contact Number', 
    'Service', 
    'Sub Service',
    'Message', 
    'Status', 
    'Read Status', 
    'Submitted At'
]);

// Build the query
$query = "SELECT 
            enquiry_id,
            name,
            email,
            contact,
            service,
            sub_service,
            message,
            status,
            is_read,
            submitted_at
          FROM enquiries 
          WHERE 1=1";

// Add date filters if provided
if ($start_date && $end_date) {
    $query .= " AND DATE(submitted_at) BETWEEN '$start_date' AND '$end_date'";
}

$query .= " ORDER BY submitted_at DESC";

// Execute query
$result = mysqli_query($connection, $query);

if (!$result) {
    die("Database query failed: " . mysqli_error($connection));
}

// Output data
while ($enquiry = mysqli_fetch_assoc($result)) {
    // Format read status
    $read_status = $enquiry['is_read'] ? 'Read' : 'Unread';
    
    // Clean message for CSV
    $message = $enquiry['message'];
    $message = str_replace(["\r\n", "\r", "\n"], " ", $message);
    $message = htmlspecialchars_decode($message, ENT_QUOTES);
    
    if (strlen($message) > 500) {
        $message = substr($message, 0, 500) . '...';
    }
    
    fputcsv($output, [
        $enquiry['enquiry_id'],
        htmlspecialchars_decode($enquiry['name'], ENT_QUOTES),
        $enquiry['email'],
        $enquiry['contact'] ?: 'N/A',
        htmlspecialchars_decode($enquiry['service'], ENT_QUOTES),
        htmlspecialchars_decode($enquiry['sub_service'] ?: '', ENT_QUOTES),
        $message,
        ucfirst($enquiry['status']),
        $read_status,
        $enquiry['submitted_at']
    ]);
}

// Close connections
fclose($output);
mysqli_close($connection);
exit;
?>