<?php
include '../includes/database.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['manager', 'ceo', 'admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if (isset($_POST['client_id']) && isset($_POST['reject_reason'])) {
    $client_id = intval($_POST['client_id']);
    $user_role = $_SESSION['user_role'];
    $user_id = $_SESSION['user_id'];
    $reject_reason = trim($_POST['reject_reason']);
    
    if (empty($reject_reason)) {
        echo json_encode(['success' => false, 'message' => 'Rejection reason is required']);
        exit();
    }
    
    // Update proposal status
    $update_sql = "UPDATE proposals SET status = 'rejected' 
                  WHERE client_id = ? 
                  AND proposal_id = (SELECT proposal_id FROM proposals WHERE client_id = ? ORDER BY created_at DESC LIMIT 1)";
    $update_stmt = $connection->prepare($update_sql);
    $update_stmt->bind_param("ii", $client_id, $client_id);
    
    // Update client status based on who is rejecting
    if ($user_role === 'manager') {
        $client_status = 'Rejected by Manager';
    } elseif ($user_role === 'ceo') {
        $client_status = 'Rejected by CEO';
    }
    
    $client_update_sql = "UPDATE clients SET client_status = ? WHERE client_id = ?";
    $client_stmt = $connection->prepare($client_update_sql);
    $client_stmt->bind_param("si", $client_status, $client_id);
    
    if ($update_stmt->execute() && $client_stmt->execute()) {
        // Add rejection note
        $note_sql = "INSERT INTO client_notes (client_id, user_id, note_type, note_content) 
                    VALUES (?, ?, 'rejection_reason', ?)";
        $note_stmt = $connection->prepare($note_sql);
        $note_content = "Proposal rejected by " . ucfirst($user_role) . ". Reason: " . $reject_reason;
        $note_stmt->bind_param("iis", $client_id, $user_id, $note_content);
        $note_stmt->execute();
        $note_stmt->close();
        
        echo json_encode(['success' => true, 'message' => 'Proposal rejected successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to reject proposal']);
    }
    
    $update_stmt->close();
    $client_stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request or missing rejection reason']);
}
?>