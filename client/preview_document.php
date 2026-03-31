<?php
// No output before this point - this file handles ONLY file previews
session_start();

// Include database connection only
require_once '../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['client_id'])) {
    header('Location: ../login.php');
    exit();
}

$client_id = (int)$_SESSION['client_id'];

// Get document ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid document ID');
}

$document_id = (int)$_GET['id'];

// Check access and get document details
$access_query = "SELECT d.* FROM client_documents d
                 WHERE d.document_id = $document_id 
                 AND d.is_active = 1
                 AND (d.document_type = 'general' 
                      OR EXISTS (SELECT 1 FROM document_client_access 
                                 WHERE document_id = d.document_id 
                                 AND client_id = $client_id))
                 AND (d.expires_at IS NULL OR d.expires_at > CURDATE())";
$access_result = mysqli_query($connection, $access_query);
$document = mysqli_fetch_assoc($access_result);

if (!$document) {
    die('Document not found or access denied');
}

// Check if file exists
$file_path = $document['file_path'];
if (!file_exists($file_path)) {
    die('File not found on server');
}

// Log view
$log_query = "INSERT INTO document_access_logs (document_id, client_id, access_type, ip_address, user_agent) 
              VALUES ($document_id, $client_id, 'view', '{$_SERVER['REMOTE_ADDR']}', '{$_SERVER['HTTP_USER_AGENT']}')";
mysqli_query($connection, $log_query);

// Update view count
mysqli_query($connection, "UPDATE client_documents SET view_count = view_count + 1 WHERE document_id = $document_id");

$file_name = $document['file_original_name'];
$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

// Clear any output buffers
if (ob_get_level()) {
    ob_end_clean();
}

// For PDFs, display inline in browser
if ($file_ext === 'pdf') {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($file_name) . '"');
    header('Content-Length: ' . filesize($file_path));
    readfile($file_path);
    exit();
}

// For images, display inline
if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
    $content_types = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif'
    ];
    header('Content-Type: ' . $content_types[$file_ext]);
    header('Content-Disposition: inline; filename="' . basename($file_name) . '"');
    readfile($file_path);
    exit();
}

// For other files, force download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit();
?>