
<?php
ob_start();

// Set user_id from session
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>window.location.href = 'cdp.php';</script>";
    exit();
}

$cdp_id = (int)$_GET['id'];

// Fetch record and verify ownership
$query = "SELECT * FROM cdp_records WHERE cdp_id = $cdp_id AND employee_id = $user_id";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<script>window.location.href = 'cdp.php';</script>";
    exit();
}

$record = mysqli_fetch_assoc($result);

// Check if record can be edited (only pending)
if ($record['status'] != 'PENDING') {
    $_SESSION['error_message'] = "Only pending records can be edited.";
    echo "<script>window.location.href = 'cdp.php';</script>";
    exit();
}

$message = '';
$message_type = '';
$showSuccessModal = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cdp'])) {
    
    $cdp_type = mysqli_real_escape_string($connection, $_POST['cdp_type']);
    $title = mysqli_real_escape_string($connection, trim($_POST['title']));
    $description = mysqli_real_escape_string($connection, trim($_POST['description'] ?? ''));
    $effective_date = mysqli_real_escape_string($connection, $_POST['effective_date']);
    
    // Handle file upload
    $document_file = $record['document_file'];
    if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['document_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        
        if (in_array($ext, $allowed)) {
            $upload_dir = "../uploads/cdp_documents/";
            
            $new_filename = "cdp_" . $user_id . "_" . time() . "_" . rand(1000, 9999) . "." . $ext;
            $target_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                // Delete old file
                if (!empty($record['document_file']) && file_exists($upload_dir . $record['document_file'])) {
                    unlink($upload_dir . $record['document_file']);
                }
                $document_file = $new_filename;
            }
        }
    }
    
    // Determine uplift percentage based on type
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
        $message = "Please fill in all required fields.";
        $message_type = "danger";
    } else {
        // Update record
        $update_query = "UPDATE cdp_records SET 
                        cdp_type = '$cdp_type',
                        title = '$title',
                        description = '$description',
                        document_file = '$document_file',
                        uplift_percentage = $uplift_percentage,
                        effective_date = '$effective_date'
                        WHERE cdp_id = $cdp_id";
        
        if (mysqli_query($connection, $update_query)) {
            $showSuccessModal = true;
        } else {
            $message = "Error updating record: " . mysqli_error($connection);
            $message_type = "danger";
        }
    }
}

ob_end_flush();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header dark-header">
                    <h5 class="card-title">
                        <i class="bi bi-pencil me-2"></i>Edit CDP Record
                    </h5>
                    <a href="cdp.php" class="btn btn-sm btn-outline-light">
                        <i class="bi bi-arrow-left me-1"></i>Back to CDP
                    </a>
                </div>
                <div class="card-body">
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        You are editing a pending record. Changes will require re-approval.
                    </div>
                    
                    <?php if (!empty($message) && !$showSuccessModal): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data" id="cdpForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CDP Type *</label>
                                <select name="cdp_type" id="cdp_type" class="form-select" required>
                                    <option value="CERTIFICATE" <?php echo $record['cdp_type'] == 'CERTIFICATE' ? 'selected' : ''; ?>>Certificate</option>
                                    <option value="COURSE" <?php echo $record['cdp_type'] == 'COURSE' ? 'selected' : ''; ?>>Course</option>
                                    <option value="LOYALTY" <?php echo $record['cdp_type'] == 'LOYALTY' ? 'selected' : ''; ?>>Loyalty</option>
                                    <option value="BEHAVIOR" <?php echo $record['cdp_type'] == 'BEHAVIOR' ? 'selected' : ''; ?>>Behavior</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Effective Date *</label>
                                <input type="date" name="effective_date" class="form-control" value="<?php echo $record['effective_date']; ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($record['title']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($record['description']); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Upload New Document</label>
                                <input type="file" name="document_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                <small class="text-muted">Leave empty to keep current file</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="current-file">
                                    <i class="bi bi-file-earmark me-2"></i>
                                    <span>Current: <?php echo $record['document_file'] ? basename($record['document_file']) : 'No file'; ?></span>
                                </div>
                                <div class="uplift-preview mt-3">
                                    <span class="preview-label">Uplift Value:</span>
                                    <span class="preview-value" id="upliftValue">
                                        <?php 
                                        if ($record['uplift_percentage']) {
                                            echo '+' . $record['uplift_percentage'] . '%';
                                        } else {
                                            echo '—';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" name="update_cdp" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle me-2"></i>Update Record
                            </button>
                            <a href="cdp.php" class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </a>
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
                <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Success!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h5 class="mt-3">CDP Record Updated!</h5>
                <p class="text-muted">Your changes have been saved and will be reviewed.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <a href="cdp.php" class="btn btn-success px-4">
                    <i class="bi bi-list-ul me-2"></i>View All Records
                </a>
                <a href="cdp.php?source=view&id=<?php echo $cdp_id; ?>" class="btn btn-outline-success px-4">
                    <i class="bi bi-eye me-2"></i>View Record
                </a>
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

<script>
// Update uplift preview
document.getElementById('cdp_type')?.addEventListener('change', function() {
    const upliftValue = document.getElementById('upliftValue');
    const type = this.value;
    
    switch(type) {
        case 'CERTIFICATE':
            upliftValue.textContent = '+18%';
            break;
        case 'COURSE':
            upliftValue.textContent = '+7%';
            break;
        case 'LOYALTY':
            upliftValue.textContent = '+3%';
            break;
        case 'BEHAVIOR':
            upliftValue.textContent = '+2%';
            break;
    }
});
</script>

<style>
.current-file {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 10px;
    font-size: 0.9rem;
    border: 1px solid #dee2e6;
}

.uplift-preview {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 10px;
    text-align: center;
}

.preview-label {
    display: block;
    font-size: 0.8rem;
    color: #6c757d;
}

.preview-value {
    font-size: 1.2rem;
    font-weight: 600;
    color: #28a745;
}
</style>