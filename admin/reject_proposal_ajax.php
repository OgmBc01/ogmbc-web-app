<?php
// reject_proposal_ajax.php
session_start();

// Add output buffering and error handling
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors, log them instead

// Check session and user role
if(!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$allowed_roles = ['manager', 'ceo', 'admin'];
if(!in_array($_SESSION['user_role'], $allowed_roles)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Include database
include dirname(__DIR__) . '/includes/database.php';

// Check request method
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get POST data with validation
$client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
$proposal_id = isset($_POST['proposal_id']) ? intval($_POST['proposal_id']) : 0;
$user_role = $_SESSION['user_role'];
$user_id = $_SESSION['user_id'];
$reject_reason = isset($_POST['reject_reason']) ? trim($_POST['reject_reason']) : '';
$reject_action = isset($_POST['reject_action']) ? trim($_POST['reject_action']) : 'revise';
$review_notes = isset($_POST['review_notes']) ? trim($_POST['review_notes']) : '';
$checklist = isset($_POST['checklist']) ? $_POST['checklist'] : [];

// Validate input
if($client_id <= 0) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid client ID']);
    exit();
}

if(empty($reject_reason)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Rejection reason is required']);
    exit();
}

try {
    // Begin transaction
    if(!$connection->begin_transaction()) {
        throw new Exception('Could not start transaction');
    }
    
    // Determine new status based on reject action
    $new_status = '';
    if($reject_action === 'revise') {
        $new_status = 'Proposal Needs Revision';
    } elseif($reject_action === 'cancel') {
        $new_status = 'Proposal Cancelled';
    } else {
        $new_status = 'Proposal Rejected';
    }
    
    // Update client status
    $update_sql = "UPDATE clients SET client_status = ? WHERE client_id = ?";
    $update_stmt = $connection->prepare($update_sql);
    if(!$update_stmt) {
        throw new Exception('Failed to prepare update statement: ' . $connection->error);
    }
    
    $update_stmt->bind_param("si", $new_status, $client_id);
    if(!$update_stmt->execute()) {
        throw new Exception('Failed to update client status: ' . $update_stmt->error);
    }
    $update_stmt->close();
    
    // Prepare checklist data
    $checklist_json = !empty($checklist) ? json_encode($checklist) : json_encode([]);
    
    // Save rejection record to proposal_reviews table
    $reject_sql = "INSERT INTO proposal_reviews (
                    proposal_id, 
                    client_id, 
                    reviewed_by, 
                    reviewer_role, 
                    review_notes, 
                    checklist_items, 
                    rejection_reason, 
                    rejection_action, 
                    review_result, 
                    reviewed_at
                  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'rejected', NOW())";
    
    $reject_stmt = $connection->prepare($reject_sql);
    if(!$reject_stmt) {
        throw new Exception('Failed to prepare reject statement: ' . $connection->error);
    }
    
    $reject_stmt->bind_param(
        "iiissssss", 
        $proposal_id, 
        $client_id, 
        $user_id, 
        $user_role, 
        $review_notes, 
        $checklist_json, 
        $reject_reason, 
        $reject_action
    );
    
    if(!$reject_stmt->execute()) {
        throw new Exception('Failed to save rejection record: ' . $reject_stmt->error);
    }
    $reject_stmt->close();
    
    // Commit transaction
    if(!$connection->commit()) {
        throw new Exception('Failed to commit transaction');
    }
    
    // Clean output buffer and return success
    ob_end_clean();
    echo json_encode([
        'success' => true, 
        'message' => 'Proposal rejected successfully',
        'new_status' => $new_status,
        'review_id' => $connection->insert_id
    ]);
    
} catch(Exception $e) {
    // Rollback on error
    if(isset($connection) && method_exists($connection, 'rollback')) {
        $connection->rollback();
    }
    
    // Clean buffer and return error
    ob_end_clean();
    error_log('Reject Proposal Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit();
}

// Make sure no extra output
ob_end_flush();
?>