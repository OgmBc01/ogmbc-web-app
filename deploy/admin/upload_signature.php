<?php
include '../includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $signature_data = $_POST['signature_data'] ?? '';
    $signature_type = 'digital'; // Can be 'digital' or 'upload'
    
    if (empty($signature_data)) {
        echo json_encode(['success' => false, 'message' => 'No signature data provided']);
        exit();
    }
    
    // Check if user already has a signature
    $check_sql = "SELECT signature_id FROM signatures WHERE user_id = ? AND is_active = 1";
    $check_stmt = $connection->prepare($check_sql);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Update existing signature
        $update_sql = "UPDATE signatures SET signature_data = ?, updated_at = NOW() WHERE user_id = ? AND is_active = 1";
        $update_stmt = $connection->prepare($update_sql);
        $update_stmt->bind_param("si", $signature_data, $user_id);
        
        if ($update_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Signature updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update signature']);
        }
        $update_stmt->close();
    } else {
        // Insert new signature
        $insert_sql = "INSERT INTO signatures (user_id, signature_data, signature_type) VALUES (?, ?, ?)";
        $insert_stmt = $connection->prepare($insert_sql);
        $insert_stmt->bind_param("iss", $user_id, $signature_data, $signature_type);
        
        if ($insert_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Signature saved successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save signature']);
        }
        $insert_stmt->close();
    }
    
    $check_stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>