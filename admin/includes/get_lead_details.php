<?php
// admin/includes/get_lead_details.php

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
$query = "SELECT * FROM leads WHERE id = $lead_id";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Lead not found']);
    exit;
}

$lead = mysqli_fetch_assoc($result);
echo json_encode(['success' => true, 'lead' => $lead]);

mysqli_close($connection);
?>