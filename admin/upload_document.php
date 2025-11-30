<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include dirname(__DIR__) . '/includes/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Enable detailed error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log the start of the script
error_log("=== UPLOAD DOCUMENT SCRIPT STARTED ===");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Log all received data
    error_log("POST data received: " . print_r($_POST, true));
    error_log("FILES data received: " . print_r($_FILES, true));
    
    // Check if all required fields are present
    if (!isset($_POST['client_id']) || !isset($_POST['document_title']) || !isset($_POST['document_type'])) {
        $response = ['success' => false, 'message' => 'Missing required fields'];
        error_log("Missing fields: " . json_encode($response));
        echo json_encode($response);
        exit();
    }
    
    $client_id = intval($_POST['client_id']);
    $document_title = trim($_POST['document_title']);
    $document_type = $_POST['document_type'];
    $user_id = $_SESSION['user_id'];
    
    error_log("Processing upload - Client: $client_id, Title: $document_title, Type: $document_type, User: $user_id");

    // Validate inputs
    if (empty($document_title) || $client_id <= 0) {
        $response = ['success' => false, 'message' => 'All fields are required and valid'];
        error_log("Validation failed: " . json_encode($response));
        echo json_encode($response);
        exit();
    }
    
    // Check if file was uploaded
    if (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] === UPLOAD_ERR_NO_FILE) {
        $response = ['success' => false, 'message' => 'No file was selected'];
        error_log("No file uploaded: " . json_encode($response));
        echo json_encode($response);
        exit();
    }
    
    $file = $_FILES['document_file'];
    error_log("File details - Name: {$file['name']}, Size: {$file['size']}, Error: {$file['error']}, Temp: {$file['tmp_name']}");
    
    // Check if file was uploaded successfully
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE => 'File too large (server limit)',
            UPLOAD_ERR_FORM_SIZE => 'File too large (form limit)',
            UPLOAD_ERR_PARTIAL => 'File upload was incomplete',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        $error_msg = $upload_errors[$file['error']] ?? 'Unknown upload error (Code: ' . $file['error'] . ')';
        $response = ['success' => false, 'message' => 'Upload error: ' . $error_msg];
        error_log("Upload error: " . $error_msg);
        echo json_encode($response);
        exit();
    }
    
    // File validation
    $allowed_types = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
    $max_size = 10 * 1024 * 1024; // 10MB
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    error_log("File extension: " . $file_extension);
    
    if (!in_array($file_extension, $allowed_types)) {
        $response = ['success' => false, 'message' => 'Invalid file type. Allowed: PDF, JPG, PNG, DOC, DOCX'];
        error_log("Invalid file type: " . $file_extension);
        echo json_encode($response);
        exit();
    }
    
    if ($file['size'] > $max_size) {
        $response = ['success' => false, 'message' => 'File too large. Maximum size: 10MB'];
        error_log("File too large: " . $file['size'] . " bytes");
        echo json_encode($response);
        exit();
    }
    
    // Define upload directory
    $upload_dir = dirname(__DIR__) . '/uploads/client_documents/';
    error_log("Upload directory: " . $upload_dir);
    
    // Ensure directory exists and is writable
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            $response = ['success' => false, 'message' => 'Failed to create upload directory'];
            error_log("Directory creation failed");
            echo json_encode($response);
            exit();
        }
        error_log("Directory created: " . $upload_dir);
    }
    
    if (!is_writable($upload_dir)) {
        $response = ['success' => false, 'message' => 'Upload directory is not writable'];
        error_log("Directory not writable: " . $upload_dir);
        echo json_encode($response);
        exit();
    }
    
    error_log("Directory check passed - exists: " . (file_exists($upload_dir) ? 'yes' : 'no') . ", writable: " . (is_writable($upload_dir) ? 'yes' : 'no'));
    
    // Generate unique filename
    $filename = "doc_" . time() . "_" . rand(1000, 9999) . "." . $file_extension;
    $file_path = $upload_dir . $filename;
    
    error_log("Generated filename: " . $filename);
    error_log("Full file path: " . $file_path);
    error_log("Temp file exists: " . (file_exists($file['tmp_name']) ? 'yes' : 'no'));
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        error_log("File moved successfully to: " . $file_path);
        error_log("File exists after move: " . (file_exists($file_path) ? 'yes' : 'no'));
        error_log("File size after move: " . filesize($file_path) . " bytes");
        
        // Prepare database path (relative for web access)
        $db_file_path = 'uploads/client_documents/' . $filename;
        
        // Save to database
        $sql = "INSERT INTO client_documents (client_id, document_title, document_type, file_path, uploaded_by) 
                VALUES (?, ?, ?, ?, ?)";
        
        error_log("SQL: " . $sql);
        error_log("Params: $client_id, $document_title, $document_type, $db_file_path, $user_id");
        
        $stmt = $connection->prepare($sql);
        
        if (!$stmt) {
            $error = $connection->error;
            unlink($file_path); // Clean up file
            $response = ['success' => false, 'message' => 'Database prepare failed: ' . $error];
            error_log("Database prepare failed: " . $error);
            echo json_encode($response);
            exit();
        }
        
        $stmt->bind_param("isssi", $client_id, $document_title, $document_type, $db_file_path, $user_id);
        
        if ($stmt->execute()) {
            $insert_id = $stmt->insert_id;
            error_log("Database insert successful! Insert ID: " . $insert_id);
            $response = ['success' => true, 'message' => 'Document uploaded successfully'];
            echo json_encode($response);
        } else {
            $error = $stmt->error;
            unlink($file_path); // Clean up file
            $response = ['success' => false, 'message' => 'Database insert failed: ' . $error];
            error_log("Database insert failed: " . $error);
            echo json_encode($response);
        }
        
        $stmt->close();
        
    } else {
        $last_error = error_get_last();
        error_log("File move failed! Error: " . ($last_error ? $last_error['message'] : 'Unknown error'));
        error_log("Temp file: " . $file['tmp_name']);
        error_log("Target: " . $file_path);
        
        $response = ['success' => false, 'message' => 'Failed to move uploaded file. Check server permissions.'];
        echo json_encode($response);
    }
    
} else {
    $response = ['success' => false, 'message' => 'Invalid request method. Expected POST.'];
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode($response);
}

error_log("=== UPLOAD DOCUMENT SCRIPT ENDED ===");
?>