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

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['client_id']) || !is_numeric($_GET['client_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid client ID']);
    exit;
}

$client_id = (int)$_GET['client_id'];

$query = "SELECT engagement_id, title, status 
          FROM engagements 
          WHERE client_id = $client_id 
          ORDER BY created_at DESC";
$result = mysqli_query($connection, $query);

$engagements = [];
while ($row = mysqli_fetch_assoc($result)) {
    $engagements[] = $row;
}

echo json_encode(['success' => true, 'engagements' => $engagements]);

ob_end_flush();
?>