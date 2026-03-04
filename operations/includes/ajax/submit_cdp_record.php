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

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$cdp_type = mysqli_real_escape_string($connection, $_POST['cdp_type'] ?? '');
$title = mysqli_real_escape_string($connection, trim($_POST['title'] ?? ''));
$description = mysqli_real_escape_string($connection, trim($_POST['description'] ?? ''));
$effective_date = mysqli_real_escape_string($connection, $_POST['effective_date'] ?? '');

// Handle file upload
$document_file = '';
if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['document_file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
    
    if (in_array($ext, $allowed)) {
        $upload_dir = "../../uploads/cdp_documents/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $new_filename = "cdp_" . $user_id . "_" . time() . "_" . rand(1000, 9999) . "." . $ext;
        $target_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $document_file = $new_filename;
        }
    }
}

// Determine uplift percentage
$uplift_percentage = null;
switch($cdp_type) {
    case 'CERTIFICATE':
        $uplift_percentage = 18;
        break;
    case 'COURSE':
        $uplift_percentage = 7;
        break;
    case 'LOYALTY':
        $uplift_percentage = 3;
        break;
    case 'BEHAVIOR':
        $uplift_percentage = 2;
        break;
}

// Validation
if (empty($title) || empty($cdp_type) || empty($effective_date)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit;
}

// Insert record
$insert_query = "INSERT INTO cdp_records 
                (employee_id, cdp_type, title, description, document_file, uplift_percentage, effective_date, created_by, status)
                VALUES ($user_id, '$cdp_type', '$title', '$description', '$document_file', $uplift_percentage, '$effective_date', $user_id, 'PENDING')";

if (mysqli_query($connection, $insert_query)) {
    echo json_encode([
        'success' => true, 
        'message' => 'CDP record submitted successfully',
        'record_id' => mysqli_insert_id($connection)
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($connection)]);
}

ob_end_flush();
?>