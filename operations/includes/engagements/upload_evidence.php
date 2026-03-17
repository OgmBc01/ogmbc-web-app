<?php
ob_start();

// Check authentication
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    echo "<script>window.location.href = '../login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Get engagement ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>window.location.href = 'engagements.php';</script>";
    exit();
}

$engagement_id = (int)$_GET['id'];

// Fetch engagement details and verify ownership
$query = "SELECT e.*, c.company_name
          FROM engagements e
          JOIN clients c ON e.client_id = c.client_id
          WHERE e.engagement_id = $engagement_id AND e.assigned_to = $user_id";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<script>window.location.href = 'engagements.php';</script>";
    exit();
}

$engagement = mysqli_fetch_assoc($result);

// Fetch existing evidence
$evidence_query = "SELECT * FROM evidence WHERE engagement_id = $engagement_id ORDER BY uploaded_at DESC";
$evidence_result = mysqli_query($connection, $evidence_query);

$message = '';
$message_type = '';
$showSuccessModal = false;

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_evidence'])) {
    
    if (!isset($_FILES['evidence_file']) || $_FILES['evidence_file']['error'] !== UPLOAD_ERR_OK) {
        $message = "Please select a file to upload.";
        $message_type = "danger";
    } else {
        $file = $_FILES['evidence_file'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_type = $file['type'];
        
        // Validate file type
        $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'application/msword', 
                         'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx'];
        
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($file_type, $allowed_types) && !in_array($ext, $allowed_ext)) {
            $message = "File type not allowed. Allowed: PDF, JPG, PNG, GIF, DOC, DOCX";
            $message_type = "danger";
        } elseif ($file_size > 10 * 1024 * 1024) { // 10MB max
            $message = "File size too large. Maximum size: 10MB";
            $message_type = "danger";
        } else {
            // Create upload directory
            $upload_dir = "../uploads/evidence/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename
            $new_filename = "evidence_" . $engagement_id . "_" . time() . "_" . rand(1000, 9999) . "." . $ext;
            $target_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file_tmp, $target_path)) {
                // Save to database
                $insert_query = "INSERT INTO evidence 
                                (engagement_id, file_name, file_path, uploaded_by)
                                VALUES 
                                ($engagement_id, '" . mysqli_real_escape_string($connection, $file_name) . "', 
                                 '$new_filename', $user_id)";
                
                if (mysqli_query($connection, $insert_query)) {
                    $showSuccessModal = true;
                    
                    // Add to activity log
                    $activity_query = "INSERT INTO user_activity_log 
                                      (user_id, activity_type, description, ip_address)
                                      VALUES ($user_id, 'evidence_upload', 'Uploaded evidence for engagement #$engagement_id', '{$_SERVER['REMOTE_ADDR']}')";
                    mysqli_query($connection, $activity_query);
                    
                    // Refresh evidence list
                    $evidence_result = mysqli_query($connection, $evidence_query);
                } else {
                    $message = "Error saving evidence record: " . mysqli_error($connection);
                    $message_type = "danger";
                }
            } else {
                $message = "Error uploading file.";
                $message_type = "danger";
            }
        }
    }
}

ob_end_flush();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <!-- Main Upload Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header dark-header">
                    <h5 class="card-title">
                        <i class="bi bi-cloud-upload me-2"></i>Upload Evidence
                    </h5>
                    <a href="engagements.php?source=view&id=<?php echo $engagement_id; ?>" class="btn btn-sm btn-outline-light">
                        <i class="bi bi-arrow-left me-1"></i>Back to Engagement
                    </a>
                </div>
                <div class="card-body">
                    
                    <!-- Engagement Summary -->
                    <div class="engagement-summary mb-4">
                        <div class="row">
                            <div class="col-md-8">
                                <h6><?php echo htmlspecialchars($engagement['title']); ?></h6>
                                <p class="text-muted mb-0">Client: <?php echo htmlspecialchars($engagement['company_name']); ?></p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <span class="badge bg-<?php 
                                    echo $engagement['status'] == 'CLOSED' ? 'dark' : 
                                        ($engagement['status'] == 'SUBMITTED' ? 'success' : 
                                        ($engagement['status'] == 'AWAITING_REVIEW' ? 'warning' : 'primary')); 
                                ?>"><?php echo $engagement['status']; ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($message) && !$showSuccessModal): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <!-- Upload Form -->
                    <div class="upload-area" id="uploadArea">
                        <?php if ($engagement['status'] === 'CLOSED'): ?>
                            <div class="alert alert-warning text-center my-4">
                                <i class="bi bi-lock-fill me-2"></i>
                                This engagement is <strong>closed</strong>. Uploading new evidence is no longer allowed.
                            </div>
                            <form method="POST" action="" enctype="multipart/form-data" id="uploadForm">
                                <div class="upload-box text-center p-5" id="dropZone" style="pointer-events: none; opacity: 0.6;">
                                    <i class="bi bi-cloud-arrow-up display-1 text-muted"></i>
                                    <h5 class="mt-3">Drag & Drop Files Here</h5>
                                    <p class="text-muted">or</p>
                                    <label for="evidence_file" class="btn btn-primary disabled" aria-disabled="true">
                                        <i class="bi bi-folder2-open me-2"></i>Browse Files
                                    </label>
                                    <input type="file" id="evidence_file" name="evidence_file" style="display: none;" disabled 
                                           accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx">
                                    <p class="text-muted small mt-3">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Max file size: 10MB | Allowed: PDF, JPG, PNG, GIF, DOC, DOCX
                                    </p>
                                    <div id="fileInfo" class="mt-3 text-start" style="display: none;"></div>
                                </div>
                                <div class="text-center mt-4">
                                    <button type="submit" name="upload_evidence" class="btn btn-success btn-lg" id="uploadBtn" disabled>
                                        <i class="bi bi-cloud-upload me-2"></i>Upload File
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <form method="POST" action="" enctype="multipart/form-data" id="uploadForm">
                                <div class="upload-box text-center p-5" id="dropZone">
                                    <i class="bi bi-cloud-arrow-up display-1 text-muted"></i>
                                    <h5 class="mt-3">Drag & Drop Files Here</h5>
                                    <p class="text-muted">or</p>
                                    <label for="evidence_file" class="btn btn-primary">
                                        <i class="bi bi-folder2-open me-2"></i>Browse Files
                                    </label>
                                    <input type="file" id="evidence_file" name="evidence_file" style="display: none;" 
                                           accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx">
                                    <p class="text-muted small mt-3">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Max file size: 10MB | Allowed: PDF, JPG, PNG, GIF, DOC, DOCX
                                    </p>
                                    <div id="fileInfo" class="mt-3 text-start" style="display: none;"></div>
                                </div>
                                <div class="text-center mt-4">
                                    <button type="submit" name="upload_evidence" class="btn btn-success btn-lg" id="uploadBtn" disabled>
                                        <i class="bi bi-cloud-upload me-2"></i>Upload File
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Uploaded Files List -->
            <?php if ($evidence_result && mysqli_num_rows($evidence_result) > 0): ?>
            <div class="card shadow-sm">
                <div class="card-header dark-header">
                    <h5 class="card-title">
                        <i class="bi bi-files me-2"></i>Uploaded Files (<?php echo mysqli_num_rows($evidence_result); ?>)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="files-list">
                        <?php while($file = mysqli_fetch_assoc($evidence_result)): ?>
                        <div class="file-item">
                            <div class="file-icon">
                                <?php
                                $ext = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
                                if ($ext == 'pdf') {
                                    echo '<i class="bi bi-file-earmark-pdf text-danger"></i>';
                                } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    echo '<i class="bi bi-file-earmark-image text-success"></i>';
                                } elseif (in_array($ext, ['doc', 'docx'])) {
                                    echo '<i class="bi bi-file-earmark-word text-primary"></i>';
                                } else {
                                    echo '<i class="bi bi-file-earmark-text text-secondary"></i>';
                                }
                                ?>
                            </div>
                            <div class="file-info">
                                <div class="file-name"><?php echo htmlspecialchars($file['file_name']); ?></div>
                                <div class="file-meta">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar me-1"></i><?php echo date('M d, Y H:i', strtotime($file['uploaded_at'])); ?>
                                    </small>
                                </div>
                            </div>
                            <div class="file-actions">
                                <a href="../uploads/evidence/<?php echo $file['file_path']; ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="../uploads/evidence/<?php echo $file['file_path']; ?>" class="btn btn-sm btn-outline-success" download title="Download">
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right Column - Tips -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header dark-header">
                    <h5 class="card-title">
                        <i class="bi bi-info-circle me-2"></i>Upload Guidelines
                    </h5>
                </div>
                <div class="card-body">
                    <div class="guidelines-list">
                        <div class="guideline-item">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span>Clear, legible documents only</span>
                        </div>
                        <div class="guideline-item">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span>PDF format preferred for reports</span>
                        </div>
                        <div class="guideline-item">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span>Images should be high resolution</span>
                        </div>
                        <div class="guideline-item">
                            <i class="bi bi-exclamation-circle-fill text-warning me-2"></i>
                            <span>Max file size: 10MB per file</span>
                        </div>
                        <div class="guideline-item">
                            <i class="bi bi-exclamation-circle-fill text-warning me-2"></i>
                            <span>Do not upload sensitive personal data</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pro Tip Card -->
            <div class="pro-tip-card">
                <h6 class="text-white mb-3">
                    <i class="bi bi-lightbulb me-2"></i>
                    Pro Tip
                </h6>
                <p class="text-white-50 small">
                    <?php if ($engagement['status'] == 'AWAITING_REVIEW'): ?>
                        ⚡ You're about to submit for review. Make sure all required documents are uploaded before proceeding.
                    <?php elseif ($engagement['status'] == 'IN_PROGRESS'): ?>
                        🚀 Upload evidence as you complete tasks, don't wait until the end!
                    <?php else: ?>
                        📁 Organize your files with clear names like "VAT_Return_Q1_2024.pdf" for easy reference.
                    <?php endif; ?>
                </p>
                <hr class="border-white-50">
                <p class="text-white-50 small mb-0">
                    <i class="bi bi-question-circle me-1"></i>
                    Need help? <a href="support.php?source=new&subject=Evidence Upload Help" class="text-white">Contact Support</a>
                </p>
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
                <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Upload Successful!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-cloud-check-fill text-success" style="font-size: 4rem;"></i>
                <h5 class="mt-3">File Uploaded Successfully!</h5>
                <p class="text-muted">Your evidence has been saved and attached to this engagement.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <a href="engagements.php?source=upload_evidence&id=<?php echo $engagement_id; ?>" class="btn btn-success px-4">
                    <i class="bi bi-cloud-upload me-2"></i>Upload More
                </a>
                <a href="engagements.php?source=view&id=<?php echo $engagement_id; ?>" class="btn btn-outline-success px-4">
                    <i class="bi bi-eye me-2"></i>View Engagement
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
// Drag and drop functionality
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('evidence_file');
const fileInfo = document.getElementById('fileInfo');
const uploadBtn = document.getElementById('uploadBtn');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, () => {
        dropZone.classList.add('dragover');
    });
});

['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, () => {
        dropZone.classList.remove('dragover');
    });
});

dropZone.addEventListener('drop', (e) => {
    const files = e.dataTransfer.files;
    fileInput.files = files;
    handleFileSelect(files[0]);
});

fileInput.addEventListener('change', (e) => {
    handleFileSelect(e.target.files[0]);
});

function handleFileSelect(file) {
    if (file) {
        const fileSize = (file.size / 1024 / 1024).toFixed(2);
        const fileExt = file.name.split('.').pop().toLowerCase();
        const allowedExts = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx'];
        
        if (!allowedExts.includes(fileExt)) {
            alert('File type not allowed. Please select a valid file type.');
            fileInput.value = '';
            uploadBtn.disabled = true;
            fileInfo.style.display = 'none';
            return;
        }
        
        if (file.size > 10 * 1024 * 1024) {
            alert('File size too large. Maximum size is 10MB.');
            fileInput.value = '';
            uploadBtn.disabled = true;
            fileInfo.style.display = 'none';
            return;
        }
        
        fileInfo.style.display = 'block';
        fileInfo.innerHTML = `
            <div class="alert alert-info">
                <i class="bi bi-file-earmark me-2"></i>
                <strong>${file.name}</strong> (${fileSize} MB)
            </div>
        `;
        uploadBtn.disabled = false;
    } else {
        uploadBtn.disabled = true;
        fileInfo.style.display = 'none';
    }
}

// Form submission validation
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    if (!fileInput.files || fileInput.files.length === 0) {
        e.preventDefault();
        alert('Please select a file to upload.');
    }
});
</script>

<style>
.engagement-summary {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
}

.upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 16px;
    transition: all 0.3s ease;
}

.upload-area.dragover {
    border-color: #f1bf70;
    background: #fff9f0;
}

.upload-box {
    cursor: pointer;
    transition: all 0.3s ease;
}

.upload-box:hover {
    background: #f8f9fa;
}

.guidelines-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.guideline-item {
    display: flex;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.guideline-item:last-child {
    border-bottom: none;
}

.files-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.file-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.file-item:hover {
    background: #e9ecef;
}

.file-icon {
    font-size: 1.8rem;
    min-width: 40px;
    text-align: center;
}

.file-info {
    flex: 1;
}

.file-name {
    font-weight: 500;
    margin-bottom: 4px;
}

.file-actions {
    display: flex;
    gap: 5px;
}

.pro-tip-card {
    background: linear-gradient(135deg, #2c3e50 0%, #1a2634 100%);
    border-radius: 16px;
    padding: 20px;
    color: white;
}

@media (max-width: 768px) {
    .file-item {
        flex-direction: column;
        text-align: center;
    }
    
    .file-actions {
        width: 100%;
        justify-content: center;
    }
}
</style>