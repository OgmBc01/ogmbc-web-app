<?php
ob_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header("Location: ../login.php");
    exit();
}

// Get engagement ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid engagement ID.";
    ob_end_clean();
    header("Location: engagements.php");
    exit();
}

$engagement_id = (int)$_GET['id'];
$message = '';
$message_type = '';
$showSuccessModal = false;

// Fetch engagement data
$query = "SELECT e.*, c.company_name, s.service_name,
          CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name
          FROM engagements e
          JOIN clients c ON e.client_id = c.client_id
          JOIN service_types s ON e.service_id = s.service_id
          LEFT JOIN users u ON e.assigned_to = u.user_id
          WHERE e.engagement_id = $engagement_id";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['error_message'] = "Engagement not found.";
    ob_end_clean();
    header("Location: engagements.php");
    exit();
}

$engagement = mysqli_fetch_assoc($result);

// Fetch existing evidence
$evidence_query = "SELECT * FROM evidence WHERE engagement_id = $engagement_id ORDER BY uploaded_at DESC";
$evidence_result = mysqli_query($connection, $evidence_query);

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_evidence'])) {
    
    if (isset($_FILES['evidence_file']) && $_FILES['evidence_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['evidence_file'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_type = $file['type'];
        
        // Validate file type
        $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx'];
        
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($file_type, $allowed_types) && !in_array($ext, $allowed_ext)) {
            $message = "File type not allowed. Allowed types: PDF, JPG, PNG, GIF, DOC, DOCX";
            $message_type = "danger";
        } elseif ($file_size > 10 * 1024 * 1024) { // 10MB max
            $message = "File size too large. Maximum size: 10MB";
            $message_type = "danger";
        } else {
            // Create upload directory if not exists
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
                                (engagement_id, file_name, file_path, file_size, mime_type, uploaded_by) 
                                VALUES 
                                ($engagement_id, '" . mysqli_real_escape_string($connection, $file_name) . "', 
                                 '$new_filename', $file_size, '$file_type', {$_SESSION['user_id']})";
                
                if (mysqli_query($connection, $insert_query)) {
                    $showSuccessModal = true;
                    
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
    } else {
        $message = "Please select a file to upload.";
        $message_type = "danger";
    }
}

// Handle evidence validation (for reviewers)
if (isset($_GET['validate']) && is_numeric($_GET['validate'])) {
    $evidence_id = (int)$_GET['validate'];
    
    // Check if user is reviewer
    if ($engagement['reviewer_id'] == $_SESSION['user_id'] || $_SESSION['user_role'] == 'CEO_GM' || $_SESSION['user_role'] == 'ADMIN_STAFF') {
        $update_query = "UPDATE evidence SET is_validated = 1, validated_by = {$_SESSION['user_id']}, validated_at = NOW() WHERE evidence_id = $evidence_id";
        mysqli_query($connection, $update_query);
        
        $_SESSION['success_message'] = "Evidence validated successfully!";
    } else {
        $_SESSION['error_message'] = "You are not authorized to validate this evidence.";
    }
    
    header("Location: engagements.php?source=upload_evidence&id=$engagement_id");
    exit();
}

ob_end_flush();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-upload me-2"></i>Upload Evidence for: <?php echo htmlspecialchars($engagement['title']); ?></h5>
                    <a href="engagements.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Engagements
                    </a>
                </div>
                <div class="card-body">
                    
                    <!-- Engagement Summary -->
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Client:</strong> <?php echo htmlspecialchars($engagement['company_name']); ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Service:</strong> <?php echo htmlspecialchars($engagement['service_name']); ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Assigned To:</strong> <?php echo htmlspecialchars($engagement['assigned_to_name']); ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Status:</strong> 
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
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <!-- Upload Form -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Upload New Evidence</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label for="evidence_file" class="form-label">Select File *</label>
                                        <input type="file" id="evidence_file" name="evidence_file" class="form-control" required>
                                        <div class="form-text">Allowed: PDF, JPG, PNG, GIF, DOC, DOCX (Max: 10MB)</div>
                                    </div>
                                    <div class="col-md-4 mb-3 d-flex align-items-end">
                                        <button type="submit" name="upload_evidence" class="btn btn-primary">
                                            <i class="bi bi-cloud-upload me-1"></i> Upload
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Existing Evidence List -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Uploaded Evidence</h6>
                        </div>
                        <div class="card-body">
                            <?php if ($evidence_result && mysqli_num_rows($evidence_result) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>File Name</th>
                                                <th>Uploaded By</th>
                                                <th>Date</th>
                                                <th>Size</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($evidence = mysqli_fetch_assoc($evidence_result)): ?>
                                            <tr>
                                                <td>
                                                    <i class="bi bi-file-earmark me-1"></i>
                                                    <?php echo htmlspecialchars($evidence['file_name']); ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $uploader_query = "SELECT CONCAT(first_name, ' ', last_name) as name FROM users WHERE user_id = {$evidence['uploaded_by']}";
                                                    $uploader_result = mysqli_query($connection, $uploader_query);
                                                    $uploader = mysqli_fetch_assoc($uploader_result);
                                                    echo htmlspecialchars($uploader['name']);
                                                    ?>
                                                </td>
                                                <td><?php echo date('M d, Y H:i', strtotime($evidence['uploaded_at'])); ?></td>
                                                <td><?php echo round($evidence['file_size'] / 1024, 2); ?> KB</td>
                                                <td>
                                                    <?php if ($evidence['is_validated']): ?>
                                                        <span class="badge bg-success">Validated</span>
                                                        <small class="text-muted d-block">by <?php echo $evidence['validated_by']; ?></small>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="../uploads/evidence/<?php echo $evidence['file_path']; ?>" class="btn btn-sm btn-info" target="_blank" title="Download">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                    
                                                    <?php if (!$evidence['is_validated'] && ($engagement['reviewer_id'] == $_SESSION['user_id'] || $_SESSION['user_role'] == 'CEO_GM' || $_SESSION['user_role'] == 'ADMIN_STAFF')): ?>
                                                        <a href="engagements.php?source=upload_evidence&id=<?php echo $engagement_id; ?>&validate=<?php echo $evidence['evidence_id']; ?>" class="btn btn-sm btn-success" title="Validate" onclick="return confirm('Mark this evidence as validated?')">
                                                            <i class="bi bi-check-lg"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center mb-0">No evidence uploaded yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($showSuccessModal): ?>
<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="successModalLabel">
          <i class="bi bi-check-circle-fill me-2"></i>Success!
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-center py-3">
          <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
          <h5 class="mt-3">Evidence Uploaded Successfully!</h5>
          <p class="text-muted mb-0">The file has been uploaded and attached to this engagement.</p>
        </div>
      </div>
      <div class="modal-footer justify-content-center border-0 pt-0">
        <a href="engagements.php" class="btn btn-success px-4">
          <i class="bi bi-list-ul me-2"></i>View All Engagements
        </a>
        <a href="engagements.php?source=upload_evidence&id=<?php echo $engagement_id; ?>" class="btn btn-outline-success px-4">
          <i class="bi bi-upload"></i>Upload More
        </a>
        <a href="engagements.php?source=view_engagement&id=<?php echo $engagement_id; ?>" class="btn btn-outline-primary px-4">
          <i class="bi bi-eye"></i>View Details
        </a>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var successModal = new bootstrap.Modal(document.getElementById('successModal'), {
      backdrop: 'static',
      keyboard: false
    });
    successModal.show();
  });
</script>
<?php endif; ?>