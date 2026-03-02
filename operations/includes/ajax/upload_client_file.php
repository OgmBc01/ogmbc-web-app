<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

set_exception_handler(function($e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
});

set_error_handler(function($errno, $errstr) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $errstr]);
    exit;
});

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Fatal error: ' . $error['message']]);
        exit;
    }
});

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../includes/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_POST['client_id']) || !is_numeric($_POST['client_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid client ID']);
    exit;
}

$client_id = (int)$_POST['client_id'];
$engagement_id = !empty($_POST['engagement_id']) ? (int)$_POST['engagement_id'] : 'NULL';
$description = mysqli_real_escape_string($connection, trim($_POST['description'] ?? ''));

// Verify client access
$check_query = "SELECT c.client_id 
                FROM clients c
                JOIN engagements e ON c.client_id = e.client_id
                WHERE c.client_id = $client_id AND e.assigned_to = $user_id
                LIMIT 1";
$check_result = mysqli_query($connection, $check_query);

if (!$check_result || mysqli_num_rows($check_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Client not found or access denied']);
    exit;
}

// Handle file upload
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Please select a file to upload']);
    exit;
}

$file = $_FILES['file'];
$file_name = $file['name'];
$file_tmp = $file['tmp_name'];
$file_size = $file['size'];
$file_type = $file['type'];

// Validate file type
$allowed_ext = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx', 'xls', 'xlsx'];
$ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

if (!in_array($ext, $allowed_ext)) {
    echo json_encode(['success' => false, 'message' => 'File type not allowed. Allowed: PDF, JPG, PNG, GIF, DOC, DOCX, XLS, XLSX']);
    exit;
}

// Validate file size (10MB max)
if ($file_size > 10 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File size too large. Maximum size: 10MB']);
    exit;
}

// Create upload directory
$upload_dir = "../uploads/client_files/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Generate unique filename
$new_filename = "client_" . $client_id . "_" . time() . "_" . rand(1000, 9999) . "." . $ext;
$target_path = $upload_dir . $new_filename;

if (move_uploaded_file($file_tmp, $target_path)) {
    // Save to database
    $engagement_value = ($engagement_id !== 'NULL') ? $engagement_id : 'NULL';
    
    $insert_query = "INSERT INTO client_files 
                    (client_id, engagement_id, uploaded_by, file_name, file_path, file_size, file_type, description)
                    VALUES ($client_id, $engagement_value, 'staff', '$file_name', '$new_filename', $file_size, '$file_type', '$description')";
    
    if (mysqli_query($connection, $insert_query)) {
        echo json_encode(['success' => true, 'message' => 'File uploaded successfully']);
    } else {
        // Delete file if database insert fails
        unlink($target_path);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($connection)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error uploading file']);
}

ob_end_flush();
?>