<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'No ID provided']);
    exit;
}

$enquiry_id = intval($_GET['id']);

$query = "UPDATE enquiries SET is_read = 1 WHERE enquiry_id = ?";
$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, "i", $enquiry_id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'message' => 'Marked as read']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

mysqli_stmt_close($stmt);
mysqli_close($connection);
?>