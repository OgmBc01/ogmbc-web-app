<?php
// No output before this point - this file handles ONLY file downloads
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

// Log download
$log_query = "INSERT INTO document_access_logs (document_id, client_id, access_type, ip_address, user_agent) 
              VALUES ($document_id, $client_id, 'download', '{$_SERVER['REMOTE_ADDR']}', '{$_SERVER['HTTP_USER_AGENT']}')";
mysqli_query($connection, $log_query);

// Update download count
mysqli_query($connection, "UPDATE client_documents SET download_count = download_count + 1 WHERE document_id = $document_id");

// Get file info
$file_name = $document['file_original_name'];
$file_size = filesize($file_path);
$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

// Set content type based on file extension
$content_types = [
    'pdf' => 'application/pdf',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls' => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'txt' => 'text/plain',
    'zip' => 'application/zip'
];

$content_type = $content_types[$file_ext] ?? 'application/octet-stream';

// Clear any output buffers
if (ob_get_level()) {
    ob_end_clean();
}

// Set headers for download
header('Content-Type: ' . $content_type);
header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
header('Content-Length: ' . $file_size);
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Read and output file
readfile($file_path);
exit();
?>