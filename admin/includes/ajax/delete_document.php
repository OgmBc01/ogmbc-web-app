<?php

session_start();
include '../../../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['document_id'])) {
    $document_id = intval($_POST['document_id']);
    
    // Get file path before deleting
    $query = "SELECT file_path FROM client_documents WHERE document_id = $document_id";
    $result = mysqli_query($connection, $query);
    $doc = mysqli_fetch_assoc($result);
    
    if ($doc) {
        // Delete physical file
        if (file_exists($doc['file_path'])) {
            unlink($doc['file_path']);
        }
        
        // Delete from database (cascade will handle related tables)
        $delete_query = "DELETE FROM client_documents WHERE document_id = $document_id";
        if (mysqli_query($connection, $delete_query)) {
            echo json_encode(['success' => true, 'message' => 'Document deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($connection)]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Document not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>