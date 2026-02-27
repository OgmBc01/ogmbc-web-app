<?php
include '../includes/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if (isset($_POST['doc_id'])) {
    $doc_id = intval($_POST['doc_id']);
    $user_id = $_SESSION['user_id'];
    
    // Get document details
    $sql = "SELECT file_path FROM client_documents WHERE doc_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $doc_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($document = $result->fetch_assoc()) {
        $file_path = $document['file_path'];
        
        // Delete from database
        $delete_sql = "DELETE FROM client_documents WHERE doc_id = ?";
        $delete_stmt = $connection->prepare($delete_sql);
        $delete_stmt->bind_param("i", $doc_id);
        
        if ($delete_stmt->execute()) {
            // Delete physical file
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            echo json_encode(['success' => true, 'message' => 'Document deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete document from database']);
        }
        $delete_stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Document not found']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>