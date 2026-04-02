<?php
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

function sendResponse($success, $has_dependencies = false, $dependencies = []) {
    echo json_encode([
        'success' => $success,
        'has_dependencies' => $has_dependencies,
        'dependencies' => $dependencies
    ]);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    sendResponse(false);
}

$db_path = __DIR__ . '/includes/database.php';
if (!file_exists($db_path)) {
    sendResponse(false);
}
require_once $db_path;

if (!$connection) {
    sendResponse(false);
}

$client_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$client_id) {
    sendResponse(false);
}

$has_dependencies = false;
$dependencies = [];

// Check for engagements
$eng_check = "SELECT COUNT(*) as count FROM engagements WHERE client_id = ?";
$eng_stmt = mysqli_prepare($connection, $eng_check);
mysqli_stmt_bind_param($eng_stmt, "i", $client_id);
mysqli_stmt_execute($eng_stmt);
$eng_result = mysqli_stmt_get_result($eng_stmt);
$eng_count = mysqli_fetch_assoc($eng_result)['count'];
if ($eng_count > 0) {
    $has_dependencies = true;
    $dependencies[] = "$eng_count engagement(s)";
}
mysqli_stmt_close($eng_stmt);

// Check for files
$file_check = "SELECT COUNT(*) as count FROM client_files WHERE client_id = ?";
$file_stmt = mysqli_prepare($connection, $file_check);
mysqli_stmt_bind_param($file_stmt, "i", $client_id);
mysqli_stmt_execute($file_stmt);
$file_result = mysqli_stmt_get_result($file_stmt);
$file_count = mysqli_fetch_assoc($file_result)['count'];
if ($file_count > 0) {
    $has_dependencies = true;
    $dependencies[] = "$file_count file(s)";
}
mysqli_stmt_close($file_stmt);

// Check for feedback
$feedback_check = "SELECT COUNT(*) as count FROM client_feedback WHERE client_id = ?";
$feedback_stmt = mysqli_prepare($connection, $feedback_check);
mysqli_stmt_bind_param($feedback_stmt, "i", $client_id);
mysqli_stmt_execute($feedback_stmt);
$feedback_result = mysqli_stmt_get_result($feedback_stmt);
$feedback_count = mysqli_fetch_assoc($feedback_result)['count'];
if ($feedback_count > 0) {
    $has_dependencies = true;
    $dependencies[] = "$feedback_count feedback(s)";
}
mysqli_stmt_close($feedback_stmt);

// Check for communications
$comm_check = "SELECT COUNT(*) as count FROM client_communications WHERE client_id = ?";
$comm_stmt = mysqli_prepare($connection, $comm_check);
mysqli_stmt_bind_param($comm_stmt, "i", $client_id);
mysqli_stmt_execute($comm_stmt);
$comm_result = mysqli_stmt_get_result($comm_stmt);
$comm_count = mysqli_fetch_assoc($comm_result)['count'];
if ($comm_count > 0) {
    $has_dependencies = true;
    $dependencies[] = "$comm_count communication(s)";
}
mysqli_stmt_close($comm_stmt);

sendResponse(true, $has_dependencies, $dependencies);
?>