<?php
// admin/includes/export_leads.php

require_once __DIR__ . '/../../includes/database.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=ratio_calculator_leads_' . date('Y-m-d') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, [
    'ID', 'Full Name', 'Email', 'Phone', 'Company Name', 'Industry',
    'Status', 'Ratios Count', 'First Interaction', 'Last Interaction',
    'Consent Given', 'Source', 'Created At'
]);

// Fetch leads
$query = "SELECT * FROM leads ORDER BY created_at DESC";
$result = mysqli_query($connection, $query);

while ($lead = mysqli_fetch_assoc($result)) {
    $ratios = json_decode($lead['ratios_calculated'], true);
    $ratio_count = is_array($ratios) ? count($ratios) : 0;
    
    fputcsv($output, [
        $lead['id'],
        $lead['full_name'],
        $lead['email'],
        $lead['phone'],
        $lead['company_name'],
        $lead['industry'],
        $lead['status'],
        $ratio_count,
        $lead['first_interaction'],
        $lead['last_interaction'],
        $lead['consent_given'] ? 'Yes' : 'No',
        $lead['source'],
        $lead['created_at']
    ]);
}

fclose($output);
mysqli_close($connection);
?>