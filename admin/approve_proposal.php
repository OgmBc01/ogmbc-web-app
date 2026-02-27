<?php
include '../includes/database.php';
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['manager', 'ceo', 'admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if (isset($_POST['client_id'])) {
    $client_id = intval($_POST['client_id']);
    $user_role = $_SESSION['user_role'];
    $user_id = $_SESSION['user_id'];
    
    // Update proposal based on user role
    if ($user_role === 'manager') {
        $update_sql = "UPDATE proposals SET 
                      manager_approved = 1, 
                      manager_approved_by = ?, 
                      manager_approved_at = NOW(),
                      manager_signature = ?,
                      status = 'under_ceo_review'
                      WHERE client_id = ? 
                      AND proposal_id = (SELECT proposal_id FROM proposals WHERE client_id = ? ORDER BY created_at DESC LIMIT 1)";
        
        $update_stmt = $connection->prepare($update_sql);
        $manager_signature = $_POST['manager_signature'] ?? '';
        $update_stmt->bind_param("isii", $user_id, $manager_signature, $client_id, $client_id);
        
        // Update client status
        $client_update_sql = "UPDATE clients SET client_status = 'Under CEO Review' WHERE client_id = ?";
    } 
    elseif ($user_role === 'ceo') {
        $update_sql = "UPDATE proposals SET 
                      ceo_approved = 1, 
                      ceo_approved_by = ?, 
                      ceo_approved_at = NOW(),
                      ceo_signature = ?,
                      company_stamp = ?,
                      status = 'approved'
                      WHERE client_id = ? 
                      AND proposal_id = (SELECT proposal_id FROM proposals WHERE client_id = ? ORDER BY created_at DESC LIMIT 1)";
        
        $update_stmt = $connection->prepare($update_sql);
        $ceo_signature = $_POST['ceo_signature'] ?? '';
        $company_stamp = $_POST['company_stamp'] ?? '';
        $update_stmt->bind_param("isiii", $user_id, $ceo_signature, $company_stamp, $client_id, $client_id);
        
        // Update client status
        $client_update_sql = "UPDATE clients SET client_status = 'Final Proposal Ready' WHERE client_id = ?";
    }
    
    if ($update_stmt->execute()) {
        // Update client status
        $client_stmt = $connection->prepare($client_update_sql);
        $client_stmt->bind_param("i", $client_id);
        $client_stmt->execute();
        $client_stmt->close();
        
        // Add note
        $note_sql = "INSERT INTO client_notes (client_id, user_id, note_type, note_content) 
                    VALUES (?, ?, 'status_change', ?)";
        $note_stmt = $connection->prepare($note_sql);
        $note_content = "Proposal approved by " . ucfirst($user_role);
        $note_stmt->bind_param("iis", $client_id, $user_id, $note_content);
        $note_stmt->execute();
        $note_stmt->close();
        
        echo json_encode(['success' => true, 'message' => 'Proposal approved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to approve proposal']);
    }
    
    $update_stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>