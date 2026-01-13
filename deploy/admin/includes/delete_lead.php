<?php
// admin/includes/delete_lead.php

require_once __DIR__ . '/../../includes/database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'No ID provided']);
    exit;
}

$lead_id = intval($_GET['id']);
$query = "DELETE FROM leads WHERE id = $lead_id";
$result = mysqli_query($connection, $query);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Lead deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting lead: ' . mysqli_error($connection)]);
}

mysqli_close($connection);
?>