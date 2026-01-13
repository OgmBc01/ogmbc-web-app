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

$query = "SELECT * FROM enquiries WHERE enquiry_id = ?";
$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, "i", $enquiry_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($enquiry = mysqli_fetch_assoc($result)) {
    echo json_encode([
        'success' => true,
        'enquiry' => $enquiry
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Enquiry not found']);
}

mysqli_stmt_close($stmt);
mysqli_close($connection);
?>