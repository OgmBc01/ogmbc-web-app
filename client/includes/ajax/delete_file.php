<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

set_exception_handler(function ($exception) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $exception->getMessage()]);
    exit;
});

set_error_handler(function ($errno, $errstr) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $errstr]);
    exit;
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
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
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid file ID']);
    exit;
}

$file_id = (int) $_GET['id'];

$query = "SELECT file_id, file_name, file_path FROM client_files WHERE file_id = $file_id AND client_id = $client_id AND uploaded_by = 'client' LIMIT 1";
$result = mysqli_query($connection, $query);

if (!$result) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($connection)]);
    exit;
}

if (mysqli_num_rows($result) === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'File not found']);
    exit;
}

$file = mysqli_fetch_assoc($result);
$file_path = realpath(__DIR__ . '/../../../uploads/client_files');
$absolute_file_path = $file_path ? $file_path . DIRECTORY_SEPARATOR . $file['file_path'] : '';

$delete_query = "DELETE FROM client_files WHERE file_id = $file_id AND client_id = $client_id AND uploaded_by = 'client' LIMIT 1";

if (!mysqli_query($connection, $delete_query)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error deleting file record: ' . mysqli_error($connection)]);
    exit;
}

if ($absolute_file_path !== '' && file_exists($absolute_file_path) && !unlink($absolute_file_path)) {
    error_log('Unable to remove client file from disk: ' . $absolute_file_path);
}

$safe_file_name = mysqli_real_escape_string($connection, $file['file_name']);
$ip_address = mysqli_real_escape_string($connection, $_SERVER['REMOTE_ADDR'] ?? '');
$log_query = "INSERT INTO client_activity_log (client_id, activity_type, description, ip_address) VALUES ($client_id, 'file_delete', 'Deleted file: $safe_file_name', '$ip_address')";
mysqli_query($connection, $log_query);

echo json_encode(['success' => true, 'message' => 'File deleted successfully']);
exit;
?>