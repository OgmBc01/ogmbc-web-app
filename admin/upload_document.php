<?php
// Begin output buffering so accidental output doesn't break JSON
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include dirname(__DIR__) . '/includes/database.php';

// Turn on verbose errors for debugging (remove or lower in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log the start
error_log("=== UPLOAD DOCUMENT SCRIPT STARTED ===");

// Always return JSON
header('Content-Type: application/json');

// Helper to send JSON and exit (cleans buffer first)
function send_json_and_exit($payload) {
    // Capture and log any stray output
    $extra = ob_get_clean();
    if ($extra !== '') {
        error_log("Stray output detected (will not be sent to client): " . $extra);
    }
    echo json_encode($payload);
    error_log("=== UPLOAD DOCUMENT SCRIPT ENDED ===");
    exit;
}

// Check login
if (!isset($_SESSION['user_id'])) {
    send_json_and_exit(['success' => false, 'message' => 'Unauthorized access']);
}

$user_id = intval($_SESSION['user_id']);

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_and_exit(['success' => false, 'message' => 'Invalid request method. Use POST.']);
}

// Log incoming data
error_log("POST data received: " . print_r($_POST, true));
error_log("FILES data received: " . print_r($_FILES, true));

// Validate client_id
if (!isset($_POST['client_id']) || empty($_POST['client_id'])) {
    send_json_and_exit(['success' => false, 'message' => 'Client ID is missing']);
}
$client_id = intval($_POST['client_id']);
if ($client_id <= 0) {
    send_json_and_exit(['success' => false, 'message' => 'Invalid client ID']);
}

// Normalize inputs to arrays so code can handle single or multiple uploads
$titles = [];
$types = [];
$files = [];

// Case A: new multi-upload form with arrays: document_title[], document_type[], document_file[]
if (isset($_POST['document_title']) && is_array($_POST['document_title'])) {
    $titles = $_POST['document_title'];
} elseif (isset($_POST['document_title'])) {
    // single value
    $titles = [$_POST['document_title']];
}

if (isset($_POST['document_type']) && is_array($_POST['document_type'])) {
    $types = $_POST['document_type'];
} elseif (isset($_POST['document_type'])) {
    $types = [$_POST['document_type']];
}

// Files: handle both single-file and multi-file index structure
if (isset($_FILES['document_file'])) {
    // If single upload, $_FILES['document_file']['name'] is a string
    if (!is_array($_FILES['document_file']['name'])) {
        // convert to arrays
        $files = [
            'name' => [$_FILES['document_file']['name']],
            'type' => [$_FILES['document_file']['type']],
            'tmp_name' => [$_FILES['document_file']['tmp_name']],
            'error' => [$_FILES['document_file']['error']],
            'size' => [$_FILES['document_file']['size']],
        ];
    } else {
        // already arrays (multi)
        $files = $_FILES['document_file'];
    }
} else {
    // No uploaded files at all
    send_json_and_exit(['success' => false, 'message' => 'No files uploaded (document_file missing)']);
}

// Make sure titles and types arrays are at least as long as files
$totalFiles = count($files['name']);
error_log("Total files found: $totalFiles");

// If titles/types are shorter, pad with empty strings to keep indexes aligned
while (count($titles) < $totalFiles) $titles[] = '';
while (count($types) < $totalFiles) $types[] = 'other';

// Prepare results
$allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
$max_size = 10 * 1024 * 1024; // 10 MB - keep same as your previous rule
$upload_dir = dirname(__DIR__) . '/uploads/client_documents/';

if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        send_json_and_exit(['success' => false, 'message' => 'Failed to create upload directory']);
    }
}

if (!is_writable($upload_dir)) {
    send_json_and_exit(['success' => false, 'message' => 'Upload directory is not writable']);
}

$uploaded_count = 0;
$errors = [];
$inserted_ids = [];

for ($i = 0; $i < $totalFiles; $i++) {
    $title = isset($titles[$i]) ? trim($titles[$i]) : '';
    $type = isset($types[$i]) ? trim($types[$i]) : 'other';

    $origName = $files['name'][$i];
    $tmpName = $files['tmp_name'][$i];
    $fileError = $files['error'][$i];
    $fileSize = $files['size'][$i];

    // Skip empty file inputs
    if ($origName === '' || $fileError === UPLOAD_ERR_NO_FILE) {
        $errors[] = ($origName ?: "File #".($i+1)) . ": No file provided";
        continue;
    }

    error_log("Processing file index $i: $origName, size: $fileSize, error: $fileError");

    // Check upload error
    if ($fileError !== UPLOAD_ERR_OK) {
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE => 'File too large (server limit)',
            UPLOAD_ERR_FORM_SIZE => 'File too large (form limit)',
            UPLOAD_ERR_PARTIAL => 'File upload was incomplete',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        $msg = $upload_errors[$fileError] ?? 'Unknown upload error (Code: ' . $fileError . ')';
        $errors[] = "$origName: $msg";
        continue;
    }

    // Validate title
    if (empty($title)) {
        $errors[] = "$origName: Missing document title";
        continue;
    }

    // Validate extension
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_extensions)) {
        $errors[] = "$origName: Invalid file type ($ext)";
        continue;
    }

    // Validate size
    if ($fileSize > $max_size) {
        $errors[] = "$origName: File too large (max 10MB)";
        continue;
    }

    // Build unique filename
    $safeName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', pathinfo($origName, PATHINFO_FILENAME));
    $newFilename = "doc_" . $client_id . "_" . time() . "_" . rand(1000,9999) . "_" . $safeName . "." . $ext;
    $destination = $upload_dir . $newFilename;

    // Move uploaded file
    if (!move_uploaded_file($tmpName, $destination)) {
        $last_err = error_get_last();
        error_log("move_uploaded_file failed for $origName to $destination. last_err: " . print_r($last_err, true));
        $errors[] = "$origName: Failed to move uploaded file. Check server permissions.";
        continue;
    }

    // Prepare DB path relative to web root (same as original)
    $db_file_path = 'uploads/client_documents/' . $newFilename;

    // Insert into DB
    $sql = "INSERT INTO client_documents (client_id, document_title, document_type, file_path, uploaded_by) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($sql);
    if (!$stmt) {
        $db_err = $connection->error;
        // Clean up file if DB prepare fails
        if (file_exists($destination)) unlink($destination);
        $errors[] = "$origName: Database prepare failed: " . $db_err;
        error_log("DB prepare failed: " . $db_err);
        continue;
    }

    $stmt->bind_param("isssi", $client_id, $title, $type, $db_file_path, $user_id);
    if ($stmt->execute()) {
        $insert_id = $stmt->insert_id;
        $inserted_ids[] = $insert_id;
        $uploaded_count++;
        error_log("Inserted doc record ID: $insert_id for file $origName");
    } else {
        $stmt_err = $stmt->error;
        // Remove file if DB insert fails
        if (file_exists($destination)) unlink($destination);
        $errors[] = "$origName: Database insert failed: " . $stmt_err;
        error_log("DB insert failed: " . $stmt_err);
    }

    $stmt->close();
}

// Final response
$response = [
    'success' => $uploaded_count > 0,
    'uploaded' => $uploaded_count,
    'failed' => count($errors),
    'errors' => $errors,
    'inserted_ids' => $inserted_ids,
    'message' => $uploaded_count > 0 ? "$uploaded_count document(s) uploaded successfully." : "No documents uploaded."
];

send_json_and_exit($response);
