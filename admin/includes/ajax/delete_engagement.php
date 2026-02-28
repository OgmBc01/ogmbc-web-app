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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid engagement ID']);
    exit;
}

$engagement_id = (int)$_GET['id'];

// Check if engagement has evidence
$check_evidence = "SELECT COUNT(*) as count FROM evidence WHERE engagement_id = $engagement_id";
$evidence_result = mysqli_query($connection, $check_evidence);
$evidence_row = mysqli_fetch_assoc($evidence_result);

// Check if engagement has ledger entries
$check_ledger = "SELECT COUNT(*) as count FROM points_ledger WHERE source_type = 'ENGAGEMENT' AND source_id = $engagement_id";
$ledger_result = mysqli_query($connection, $check_ledger);
$ledger_row = mysqli_fetch_assoc($ledger_result);

if ($evidence_row['count'] > 0 || $ledger_row['count'] > 0) {
    echo json_encode(['success' => false, 'message' => 'Cannot delete engagement with evidence or points awarded']);
    exit;
}

// Delete deadline change requests first
$delete_requests = "DELETE FROM deadline_change_requests WHERE engagement_id = $engagement_id";
mysqli_query($connection, $delete_requests);

// Delete status history
$delete_history = "DELETE FROM engagement_status_history WHERE engagement_id = $engagement_id";
mysqli_query($connection, $delete_history);

// Delete engagement
$delete_query = "DELETE FROM engagements WHERE engagement_id = $engagement_id";
if (mysqli_query($connection, $delete_query)) {
    echo json_encode(['success' => true, 'message' => 'Engagement deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting engagement: ' . mysqli_error($connection)]);
}

ob_end_flush();
?>