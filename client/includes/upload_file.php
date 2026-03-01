<?php
ob_start();

// Ensure client_id is defined
if (!isset($client_id)) {
    $client_id = $_SESSION['client_id'] ?? 0;
}

if ($client_id <= 0) {
    echo '<div class="alert alert-danger">Invalid client ID</div>';
    return;
}
$engagement_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get engagements for dropdown
// Get engagements for dropdown - with error handling
$engagements_query = "SELECT engagement_id, title FROM engagements 
                      WHERE client_id = " . intval($client_id) . " 
                      ORDER BY created_at DESC";
$engagements_result = mysqli_query($connection, $engagements_query);
if (!$engagements_result) {
    $engagements_result = null;
}

// Initialize variables
$description = '';
$message = '';
$message_type = '';
$showSuccessModal = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_file'])) {
    
    $engagement_id = (int)$_POST['engagement_id'];
    $description = mysqli_real_escape_string($connection, trim($_POST['description'] ?? ''));
    
    // Handle file upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['file'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        
        // Validate file type
        $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'txt'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $message = "File type not allowed. Allowed types: " . implode(', ', $allowed);
            $message_type = "danger";
        } elseif ($file_size > 10 * 1024 * 1024) { // 10MB max
            $message = "File size too large. Maximum size: 10MB";
            $message_type = "danger";
        } else {
            // Create upload directory
            $upload_dir = "../uploads/client_files/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename
            $new_filename = "client_" . $client_id . "_" . time() . "_" . rand(1000, 9999) . "." . $ext;
            $target_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file_tmp, $target_path)) {
                // Save to database
                $engagement_value = $engagement_id > 0 ? $engagement_id : 'NULL';
                
                $insert_query = "INSERT INTO client_files 
                                (client_id, engagement_id, uploaded_by, file_name, file_path, file_size, file_type, description)
                                VALUES 
                                ($client_id, $engagement_value, 'client', '$file_name', '$new_filename', $file_size, '$ext', '$description')";
                
                if (mysqli_query($connection, $insert_query)) {
                    // Log activity
                    $log_query = "INSERT INTO client_activity_log 
                                 (client_id, activity_type, description, ip_address)
                                 VALUES 
                                 ($client_id, 'file_upload', 'Uploaded file: $file_name', '{$_SERVER['REMOTE_ADDR']}')";
                    mysqli_query($connection, $log_query);
                    
                    $showSuccessModal = true;
                } else {
                    $message = "Error saving file information: " . mysqli_error($connection);
                    $message_type = "danger";
                }
            } else {
                $message = "Error uploading file. Please try again.";
                $message_type = "danger";
            }
        }
    } else {
        $message = "Please select a file to upload.";
        $message_type = "danger";
    }
}

ob_end_flush();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-cloud-upload me-2"></i>Upload File</h5>
                    <a href="files.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Files
                    </a>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($message) && !$showSuccessModal): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="engagement_id" class="form-label">Related Engagement (Optional)</label>
                            <select class="form-control" id="engagement_id" name="engagement_id">
                                <option value="">General File</option>
                                <?php while($eng = mysqli_fetch_assoc($engagements_result)): ?>
                                    <option value="<?php echo $eng['engagement_id']; ?>" <?php echo ($engagement_id == $eng['engagement_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($eng['title']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label">Select File *</label>
                            <input type="file" class="form-control" id="file" name="file" required>
                            <div class="form-text">
                                Allowed types: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, TXT (Max: 10MB)
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description (Optional)</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($description); ?></textarea>
                        </div>

                        <div class="text-center">
                            <button type="submit" name="upload_file" class="btn btn-primary btn-lg">
                                <i class="bi bi-cloud-upload me-2"></i>Upload File
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($showSuccessModal): ?>
<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>File Uploaded!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-cloud-check text-success" style="font-size: 4rem;"></i>
                <h5 class="mt-3">Your file has been uploaded successfully!</h5>
            </div>
            <div class="modal-footer justify-content-center">
                <a href="files.php" class="btn btn-success px-4">View All Files</a>
                <a href="files.php?source=upload" class="btn btn-outline-success px-4">Upload Another</a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = new bootstrap.Modal(document.getElementById('successModal'));
        modal.show();
    });
</script>
<?php endif; ?>