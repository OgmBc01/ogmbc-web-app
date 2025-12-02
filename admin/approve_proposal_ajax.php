<?php
// approve_proposal_ajax.php
session_start();

// Add output buffering and error handling
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

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

// Get POST data
$client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
$proposal_id = isset($_POST['proposal_id']) ? intval($_POST['proposal_id']) : 0;
$user_role = $_SESSION['user_role'];
$user_id = $_SESSION['user_id'];
$review_notes = isset($_POST['review_notes']) ? trim($_POST['review_notes']) : '';
$checklist = isset($_POST['checklist']) ? $_POST['checklist'] : [];
$signature_data = isset($_POST['signature_data']) ? trim($_POST['signature_data']) : '';
$company_stamp = isset($_POST['company_stamp']) ? trim($_POST['company_stamp']) : '';

// Validate input
if($client_id <= 0) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid client ID']);
    exit();
}

try {
    // Begin transaction
    if(!$connection->begin_transaction()) {
        throw new Exception('Could not start transaction');
    }
    
    // Determine new status based on user role
    $new_status = '';
    if($user_role === 'manager') {
        $new_status = 'Manager Approved Proposal';
    } elseif($user_role === 'ceo') {
        $new_status = 'CEO Approved Proposal';
    } elseif($user_role === 'admin') {
        $new_status = 'Admin Approved Proposal';
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
    
    // Save approval record
    $review_sql = "INSERT INTO proposal_reviews (
                    proposal_id, 
                    client_id, 
                    reviewed_by, 
                    reviewer_role, 
                    review_notes, 
                    checklist_items, 
                    signature_data, 
                    company_stamp, 
                    review_result, 
                    reviewed_at
                  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'approved', NOW())";
    
    $review_stmt = $connection->prepare($review_sql);
    if(!$review_stmt) {
        throw new Exception('Failed to prepare review statement: ' . $connection->error);
    }
    
    $review_stmt->bind_param(
        "iiissssss", 
        $proposal_id, 
        $client_id, 
        $user_id, 
        $user_role, 
        $review_notes, 
        $checklist_json, 
        $signature_data, 
        $company_stamp
    );
    
    if(!$review_stmt->execute()) {
        throw new Exception('Failed to save approval record: ' . $review_stmt->error);
    }
    $review_id = $connection->insert_id;
    $review_stmt->close();
    
    // Save signature if provided
    if(!empty($signature_data)) {
        $signature_sql = "INSERT INTO signatures (
                          user_id, 
                          signature_data, 
                          signature_type, 
                          is_active, 
                          created_at
                        ) VALUES (?, ?, ?, 1, NOW())
                        ON DUPLICATE KEY UPDATE 
                          signature_data = VALUES(signature_data),
                          updated_at = NOW()";
        
        $signature_type = $user_role . '_proposal_approval';
        $signature_stmt = $connection->prepare($signature_sql);
        if($signature_stmt) {
            $signature_stmt->bind_param("iss", $user_id, $signature_data, $signature_type);
            $signature_stmt->execute();
            $signature_stmt->close();
        }
    }
    
    // Commit transaction
    if(!$connection->commit()) {
        throw new Exception('Failed to commit transaction');
    }
    
    // Clean output buffer and return success
    ob_end_clean();
    echo json_encode([
        'success' => true, 
        'message' => 'Proposal approved successfully',
        'new_status' => $new_status,
        'review_id' => $review_id
    ]);
    
} catch(Exception $e) {
    // Rollback on error
    if(isset($connection) && method_exists($connection, 'rollback')) {
        $connection->rollback();
    }
    
    // Clean buffer and return error
    ob_end_clean();
    error_log('Approve Proposal Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit();
}

// Make sure no extra output
ob_end_flush();
?>