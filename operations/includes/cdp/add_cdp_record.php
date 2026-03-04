<?php
ob_start();

// Initialize variables
$cdp_type = 'CERTIFICATE';
$title = '';
$description = '';
$effective_date = date('Y-m-d');
$message = '';
$message_type = '';
$showSuccessModal = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_cdp'])) {
    
    $cdp_type = mysqli_real_escape_string($connection, $_POST['cdp_type']);
    $title = mysqli_real_escape_string($connection, trim($_POST['title']));
    $description = mysqli_real_escape_string($connection, trim($_POST['description'] ?? ''));
    $effective_date = mysqli_real_escape_string($connection, $_POST['effective_date']);
    
    // Handle file upload
    $document_file = '';
    if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['document_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        
        if (in_array($ext, $allowed)) {
            $upload_dir = "../uploads/cdp_documents/";
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
    
    // Validation
    if (empty($title) || empty($cdp_type) || empty($effective_date)) {
        $message = "Please fill in all required fields.";
        $message_type = "danger";
    } else {
        // Determine uplift percentage based on type (default values)
        $uplift_percentage = null;
        switch($cdp_type) {
            case 'CERTIFICATE':
                $uplift_percentage = 18; // Default for Ops
                break;
            case 'COURSE':
                $uplift_percentage = 7;  // Default for Ops
                break;
            case 'LOYALTY':
                $uplift_percentage = 3;
                break;
            case 'BEHAVIOR':
                $uplift_percentage = 2;
                break;
        }
        
        // Insert CDP record
        $insert_query = "INSERT INTO cdp_records 
                        (employee_id, cdp_type, title, description, document_file, uplift_percentage, effective_date, created_by, status)
                        VALUES ($user_id, '$cdp_type', '$title', '$description', '$document_file', $uplift_percentage, '$effective_date', $user_id, 'PENDING')";
        
        if (mysqli_query($connection, $insert_query)) {
            $showSuccessModal = true;
            
            // Clear form
            $title = $description = '';
            $cdp_type = 'CERTIFICATE';
            $effective_date = date('Y-m-d');
        } else {
            $message = "Error saving CDP record: " . mysqli_error($connection);
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
                        <i class="bi bi-plus-circle me-2"></i>Add CDP Record
                    </h5>
                    <a href="cdp.php" class="btn btn-sm btn-outline-light">
                        <i class="bi bi-arrow-left me-1"></i>Back to CDP
                    </a>
                </div>
                <div class="card-body">
                    
                    <!-- Info Banner -->
                    <div class="info-banner mb-4">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <span>CDP records require HR approval. Uplift percentages will be applied to your annual performance.</span>
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
                                    <option value="CERTIFICATE" <?php echo $cdp_type == 'CERTIFICATE' ? 'selected' : ''; ?>>Certificate</option>
                                    <option value="COURSE" <?php echo $cdp_type == 'COURSE' ? 'selected' : ''; ?>>Course</option>
                                    <option value="LOYALTY" <?php echo $cdp_type == 'LOYALTY' ? 'selected' : ''; ?>>Loyalty</option>
                                    <option value="BEHAVIOR" <?php echo $cdp_type == 'BEHAVIOR' ? 'selected' : ''; ?>>Behavior</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Effective Date *</label>
                                <input type="date" name="effective_date" class="form-control" value="<?php echo $effective_date; ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($title); ?>" 
                                   placeholder="e.g., Certified Public Accountant, Leadership Course" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" 
                                      placeholder="Add details about this certification or course..."><?php echo htmlspecialchars($description); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Upload Document</label>
                                <input type="file" name="document_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                <small class="text-muted">Upload certificate or proof of completion (optional)</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="uplift-preview" id="upliftPreview">
                                    <span class="preview-label">Estimated Uplift:</span>
                                    <span class="preview-value" id="upliftValue">18%</span>
                                    <small class="text-muted">(subject to HR approval)</small>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" name="submit_cdp" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle me-2"></i>Submit CDP Record
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Uplift Guide Card -->
            <div class="uplift-guide-card mt-4">
                <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Uplift Guide</h6>
                <div class="row">
                    <div class="col-md-3">
                        <div class="guide-item">
                            <span class="badge bg-success">Certificates</span>
                            <span class="guide-value">+18%</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="guide-item">
                            <span class="badge bg-info">Courses</span>
                            <span class="guide-value">+7%</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="guide-item">
                            <span class="badge bg-warning">Loyalty</span>
                            <span class="guide-value">+3%</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="guide-item">
                            <span class="badge bg-primary">Behavior</span>
                            <span class="guide-value">+2%</span>
                        </div>
                    </div>
                </div>
                <small class="text-muted d-block mt-2">*Sales staff receive slightly different uplifts (Certificates: 15%, Courses: 5%)</small>
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
                <h5 class="mt-3">CDP Record Submitted!</h5>
                <p class="text-muted">Your record has been submitted for HR approval.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <a href="cdp.php" class="btn btn-success px-4">
                    <i class="bi bi-list-ul me-2"></i>View All Records
                </a>
                <a href="cdp.php?source=add" class="btn btn-outline-success px-4">
                    <i class="bi bi-plus-circle me-2"></i>Add Another
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
// Update uplift preview based on selected type
document.getElementById('cdp_type')?.addEventListener('change', function() {
    const upliftValue = document.getElementById('upliftValue');
    const type = this.value;
    
    switch(type) {
        case 'CERTIFICATE':
            upliftValue.textContent = '18%';
            break;
        case 'COURSE':
            upliftValue.textContent = '7%';
            break;
        case 'LOYALTY':
            upliftValue.textContent = '3%';
            break;
        case 'BEHAVIOR':
            upliftValue.textContent = '2%';
            break;
    }
});

// Form validation
document.getElementById('cdpForm')?.addEventListener('submit', function(e) {
    const fileInput = document.querySelector('input[name="document_file"]');
    const title = document.querySelector('input[name="title"]').value;
    
    if (!title.trim()) {
        e.preventDefault();
        alert('Please enter a title for your CDP record.');
    }
});
</script>

<style>
.info-banner {
    background: #e7f3ff;
    border-left: 4px solid #0d6efd;
    border-radius: 8px;
    padding: 12px 15px;
    display: flex;
    align-items: center;
}

.info-banner i {
    font-size: 1.2rem;
    color: #0d6efd;
}

.uplift-preview {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
    text-align: center;
}

.preview-label {
    display: block;
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 5px;
}

.preview-value {
    display: block;
    font-size: 2rem;
    font-weight: 700;
    color: #28a745;
    line-height: 1.2;
    margin-bottom: 5px;
}

.uplift-guide-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    border: 1px solid #eee;
}

.guide-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 8px 12px;
    margin-bottom: 8px;
}

.guide-value {
    font-weight: 600;
    color: #28a745;
}
</style>