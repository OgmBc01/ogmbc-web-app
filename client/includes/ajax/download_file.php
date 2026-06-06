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

$client_id = isset($_SESSION['client_id']) ? (int) $_SESSION['client_id'] : (int) ($_SESSION['user_id'] ?? 0);

if ($client_id <= 0) {
    header('HTTP/1.0 401 Unauthorized');
    echo 'Unauthorized';
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('HTTP/1.0 400 Bad Request');
    echo 'Invalid file ID';
    exit;
}

$file_id = (int)$_GET['id'];

// Verify file belongs to client
$query = "SELECT * FROM client_files WHERE file_id = $file_id AND client_id = $client_id";
$result = mysqli_query($connection, $query);

if (mysqli_num_rows($result) == 0) {
    header('HTTP/1.0 404 Not Found');
    echo 'File not found';
    exit;
}

$file = mysqli_fetch_assoc($result);
$file_path = __DIR__ . '/../../../uploads/client_files/' . $file['file_path'];

if (!file_exists($file_path)) {
    header('HTTP/1.0 404 Not Found');
    echo 'File not found on server';
    exit;
}

// Log download
$log_query = "INSERT INTO client_activity_log 
             (client_id, activity_type, description, ip_address)
             VALUES 
             ($client_id, 'file_download', 'Downloaded file: {$file['file_name']}', '{$_SERVER['REMOTE_ADDR']}')";
mysqli_query($connection, $log_query);

// Clear any previous output
ob_clean();

// Set headers for download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $file['file_name'] . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Output file
readfile($file_path);
exit;
?>