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
$query = "SELECT e.*, c.company_name, s.service_name, s.service_category
          FROM engagements e
          JOIN clients c ON e.client_id = c.client_id
          JOIN service_types s ON e.service_id = s.service_id
          WHERE e.engagement_id = $engagement_id AND e.assigned_to = $user_id";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<script>window.location.href = 'engagements.php';</script>";
    exit();
}

$engagement = mysqli_fetch_assoc($result);

// Check ONLY the LAST uploaded evidence status
$last_evidence_query = "SELECT status FROM evidence WHERE engagement_id = $engagement_id ORDER BY evidence_id DESC LIMIT 1";
$last_evidence_result = mysqli_query($connection, $last_evidence_query);
$last_evidence_status = ($last_evidence_result && mysqli_num_rows($last_evidence_result) > 0)
    ? mysqli_fetch_assoc($last_evidence_result)['status']
    : null;

$has_evidence = ($last_evidence_status !== null);
$last_evidence_approved = ($last_evidence_status === 'APPROVED');
$last_evidence_rejected = ($last_evidence_status === 'REJECTED');

// Define allowed status transitions
$allowed_transitions = [];
if ($engagement['status'] == 'SUBMITTED') {
    $allowed_transitions = ['AWAITING_REVIEW']; // No CLOSED for employees
} elseif ($engagement['status'] == 'CLOSED') {
    $allowed_transitions = [];
} elseif ($last_evidence_approved && $has_evidence) {
    $allowed_transitions = ['SUBMITTED', 'IN_PROGRESS'];
} elseif ($engagement['status'] == 'ASSIGNED') {
    $allowed_transitions = ['IN_PROGRESS'];
} elseif ($engagement['status'] == 'IN_PROGRESS') {
    $allowed_transitions = ['AWAITING_REVIEW', 'ASSIGNED'];
} elseif ($engagement['status'] == 'AWAITING_REVIEW') {
    $allowed_transitions = ['IN_PROGRESS'];
}

$message = '';
$message_type = '';
$showSuccessModal = false;
$new_status = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    
    $new_status = mysqli_real_escape_string($connection, $_POST['new_status']);
    $notes = mysqli_real_escape_string($connection, trim($_POST['notes'] ?? ''));
    
    // Handle optional file upload for SUBMIT
    $additional_file = null;
    if ($new_status == 'SUBMITTED' && isset($_FILES['additional_file']) && $_FILES['additional_file']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['additional_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $upload_dir = "../uploads/engagement_submissions/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $new_filename = "submission_" . time() . "_" . rand(1000, 9999) . ".{$ext}";
        $target = $upload_dir . $new_filename;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $additional_file = $new_filename;
        }
    }
    
    // Validate transition
    if (!in_array($new_status, $allowed_transitions)) {
        $message = "Invalid status transition from {$engagement['status']} to $new_status.";
        $message_type = "danger";
    } else {
        // Update status
        $update_query = "UPDATE engagements SET status = '$new_status'";
        if ($additional_file) {
            $update_query .= ", submission_file = '$additional_file', submitted_at = NOW()";
        }
        $update_query .= " WHERE engagement_id = $engagement_id";
        
        if (mysqli_query($connection, $update_query)) {
            // Add to status history
            $history_query = "INSERT INTO engagement_status_history 
                             (engagement_id, old_status, new_status, changed_by, notes)
                             VALUES ($engagement_id, '{$engagement['status']}', '$new_status', $user_id, '$notes')";
            mysqli_query($connection, $history_query);
            
            $showSuccessModal = true;
        } else {
            $message = "Error updating status: " . mysqli_error($connection);
            $message_type = "danger";
        }
    }
}

ob_end_flush();
?>

<!-- Simplified HTML (without checklist section) -->
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header dark-header">
                    <h5 class="card-title">
                        <i class="bi bi-arrow-repeat me-2"></i>Update Engagement Status
                    </h5>
                    <a href="engagements.php" class="btn btn-sm btn-outline-light">
                        <i class="bi bi-arrow-left me-1"></i>Back
                    </a>
                </div>
                <div class="card-body">
                    
                    <!-- Engagement Summary -->
                    <div class="engagement-summary mb-4">
                        <h6><?php echo htmlspecialchars($engagement['title']); ?></h6>
                        <div class="d-flex justify-content-between mt-2">
                            <span class="text-muted">Client: <?php echo htmlspecialchars($engagement['company_name']); ?></span>
                        </div>
                        
                        <!-- Last Evidence Status Indicator -->
                        <?php if ($has_evidence): ?>
                        <div class="mt-2">
                            <?php if ($last_evidence_approved): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Last Evidence: Approved ✓
                                </span>
                            <?php elseif ($last_evidence_rejected): ?>
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-circle me-1"></i>
                                    Last Evidence: Rejected ✗
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning">
                                    <i class="bi bi-clock me-1"></i>
                                    Last Evidence: Pending Review
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($message) && !$showSuccessModal): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="statusForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="new_status" class="form-label">New Status</label>
                            <select class="form-select" id="new_status" name="new_status" required>
                                <option value="">Select Status</option>
                                <?php foreach ($allowed_transitions as $transition): ?>
                                    <option value="<?php echo $transition; ?>">
                                        <?php 
                                        if ($transition == 'SUBMITTED') {
                                            echo 'SUBMIT';
                                        } else {
                                            echo str_replace('_', ' ', $transition);
                                        }
                                        ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">
                                Current status: <strong><?php echo str_replace('_', ' ', $engagement['status']); ?></strong>
                            </small>
                            
                            <?php if ($engagement['status'] == 'AWAITING_REVIEW' && !$last_evidence_approved): ?>
                                <div class="alert alert-warning mt-2">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    You cannot submit this engagement until the last evidence is approved. 
                                    <?php if ($last_evidence_rejected): ?>
                                        Please address the rejected evidence and upload a new version.
                                    <?php else: ?>
                                        Please wait for the last evidence to be approved before submitting.
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Optional File Upload for SUBMIT -->
                        <div id="fileUploadSection" style="display: none;">
                            <div class="mb-3">
                                <label for="additional_file" class="form-label">Additional Document (Optional)</label>
                                <input type="file" class="form-control" id="additional_file" name="additional_file">
                                <small class="text-muted">Upload any additional supporting documents for this submission. Max size: 10MB</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Status Change Notes (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="Add any notes about this status change..."></textarea>
                        </div>

                        <div class="text-center">
                            <button type="submit" name="update_status" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle me-2"></i>Update Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('new_status');
    const fileUploadSection = document.getElementById('fileUploadSection');
    
    function toggleSections() {
        if (statusSelect.value === 'SUBMITTED') {
            if (fileUploadSection) fileUploadSection.style.display = 'block';
        } else {
            if (fileUploadSection) fileUploadSection.style.display = 'none';
        }
    }
    
    if (statusSelect) {
        statusSelect.addEventListener('change', toggleSections);
        toggleSections();
    }
});
</script>

<?php if ($showSuccessModal): ?>
<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Status Updated!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h5 class="mt-3">Engagement Status Updated Successfully!</h5>
                <p class="text-muted">The engagement has been moved to <?php echo str_replace('_', ' ', $new_status ?? 'new status'); ?>.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <a href="engagements.php?source=view&id=<?php echo $engagement_id; ?>" class="btn btn-success px-4">
                    <i class="bi bi-eye me-2"></i>View Engagement
                </a>
                <a href="engagements.php" class="btn btn-outline-success px-4">
                    <i class="bi bi-list-ul me-2"></i>All Engagements
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

<style>
.engagement-summary {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
}

.dark-header {
    background: #1e293b;
    color: white;
}
</style>