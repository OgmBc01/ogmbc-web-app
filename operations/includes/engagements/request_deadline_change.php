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
$query = "SELECT e.*, c.company_name, s.service_name,
          COALESCE(e.approved_deadline, e.original_deadline) as current_deadline
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

// Check if engagement can be extended (not closed)
if ($engagement['status'] == 'CLOSED' || $engagement['status'] == 'SUBMITTED') {
    echo "<script>window.location.href = 'engagements.php?source=view&id=$engagement_id';</script>";
    exit();
}

// Check for existing pending requests
$pending_query = "SELECT COUNT(*) as pending FROM deadline_change_requests 
                  WHERE engagement_id = $engagement_id AND status = 'PENDING'";
$pending_result = mysqli_query($connection, $pending_query);
$pending = mysqli_fetch_assoc($pending_result)['pending'];

$message = '';
$message_type = '';
$showSuccessModal = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    
    $requested_date = mysqli_real_escape_string($connection, $_POST['requested_date']);
    $reason_code = mysqli_real_escape_string($connection, $_POST['reason_code']);
    $reason_notes = mysqli_real_escape_string($connection, trim($_POST['reason_notes'] ?? ''));
    
    // Validation
    if (empty($requested_date) || empty($reason_code)) {
        $message = "Please fill in all required fields.";
        $message_type = "danger";
    } elseif (strtotime($requested_date) <= strtotime($engagement['current_deadline'])) {
        $message = "Requested deadline must be after current deadline.";
        $message_type = "danger";
    } elseif ($pending > 0) {
        $message = "There is already a pending deadline change request for this engagement.";
        $message_type = "danger";
    } else {
        // Insert request
        $insert_query = "INSERT INTO deadline_change_requests 
                        (engagement_id, requested_by, requested_date, reason_code, reason_notes, status)
                        VALUES 
                        ($engagement_id, $user_id, '$requested_date', '$reason_code', '$reason_notes', 'PENDING')";
        
        if (mysqli_query($connection, $insert_query)) {
            $showSuccessModal = true;
            
            // Add to activity log
            $activity_query = "INSERT INTO user_activity_log 
                              (user_id, activity_type, description, ip_address)
                              VALUES ($user_id, 'deadline_request', 'Requested deadline extension for engagement #$engagement_id', '{$_SERVER['REMOTE_ADDR']}')";
            mysqli_query($connection, $activity_query);
        } else {
            $message = "Error submitting request: " . mysqli_error($connection);
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
                        <i class="bi bi-calendar-plus me-2"></i>Request Deadline Change
                    </h5>
                    <a href="engagements.php?source=view&id=<?php echo $engagement_id; ?>" class="btn btn-sm btn-outline-light">
                        <i class="bi bi-arrow-left me-1"></i>Back to Engagement
                    </a>
                </div>
                <div class="card-body">
                    
                    <!-- Engagement Summary -->
                    <div class="engagement-summary mb-4">
                        <h6><?php echo htmlspecialchars($engagement['title']); ?></h6>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <span class="text-muted">Client:</span>
                                <strong><?php echo htmlspecialchars($engagement['company_name']); ?></strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted">Current Deadline:</span>
                                <strong class="text-<?php 
                                    echo strtotime($engagement['current_deadline']) < time() ? 'danger' : 'success'; 
                                ?>">
                                    <?php echo date('M d, Y', strtotime($engagement['current_deadline'])); ?>
                                    <?php if (strtotime($engagement['current_deadline']) < time()): ?>
                                        <span class="badge bg-danger ms-2">Overdue</span>
                                    <?php endif; ?>
                                </strong>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($pending > 0): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        You already have a pending deadline change request for this engagement.
                        Please wait for it to be reviewed before submitting another.
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($message) && !$showSuccessModal): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <?php if ($pending == 0): ?>
                    <form method="POST" action="" id="requestForm">
                        <div class="mb-3">
                            <label for="requested_date" class="form-label">Requested New Deadline *</label>
                            <input type="date" class="form-control" id="requested_date" name="requested_date" 
                                   min="<?php echo date('Y-m-d', strtotime('+1 day', strtotime($engagement['current_deadline']))); ?>" 
                                   value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required>
                            <small class="text-muted">Must be after current deadline</small>
                        </div>

                        <div class="mb-3">
                            <label for="reason_code" class="form-label">Reason for Extension *</label>
                            <select class="form-select" id="reason_code" name="reason_code" required>
                                <option value="">Select a reason</option>
                                <option value="workload">High workload / Multiple priorities</option>
                                <option value="client_delay">Waiting for client information</option>
                                <option value="technical">Technical issues / System problems</option>
                                <option value="complexity">Task more complex than anticipated</option>
                                <option value="other">Other (please specify in notes)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="reason_notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="reason_notes" name="reason_notes" rows="4" 
                                      placeholder="Please provide more details about why you need the extension..."></textarea>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Note:</strong> Your request will be reviewed by a manager. You'll be notified once a decision is made.
                        </div>

                        <div class="text-center">
                            <button type="submit" name="submit_request" class="btn btn-primary btn-lg" <?php echo $pending > 0 ? 'disabled' : ''; ?>>
                                <i class="bi bi-send me-2"></i>Submit Request
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tips Card -->
            <div class="pro-tip-card mt-4">
                <div class="row align-items-center">
                    <div class="col-md-9">
                        <h6 class="text-white mb-2">
                            <i class="bi bi-lightbulb me-2"></i>
                            Before You Submit
                        </h6>
                        <ul class="text-white-50 small mb-md-0">
                            <li>✅ Make sure you've exhausted all options to meet the original deadline</li>
                            <li>✅ Provide clear, specific reasons for the extension</li>
                            <li>✅ Request a realistic new deadline - don't underestimate</li>
                            <li>✅ Communicate with your reviewer if you have one</li>
                        </ul>
                    </div>
                    <div class="col-md-3 text-md-end">
                        <i class="bi bi-clock-history display-4 text-white-50"></i>
                    </div>
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
                <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Request Submitted!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-send-check-fill text-success" style="font-size: 4rem;"></i>
                <h5 class="mt-3">Deadline Change Request Submitted</h5>
                <p class="text-muted">Your request has been sent for review. You'll be notified when it's approved.</p>
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

<script>
// Form validation
document.getElementById('requestForm')?.addEventListener('submit', function(e) {
    const requestedDate = document.getElementById('requested_date').value;
    const currentDeadline = '<?php echo $engagement['current_deadline']; ?>';
    
    if (new Date(requestedDate) <= new Date(currentDeadline)) {
        e.preventDefault();
        alert('Requested deadline must be after the current deadline.');
    }
});

// Show notes field for "other" reason
document.getElementById('reason_code')?.addEventListener('change', function() {
    const notesField = document.getElementById('reason_notes');
    if (this.value === 'other') {
        notesField.placeholder = 'Please explain why you need the extension...';
        notesField.focus();
    } else {
        notesField.placeholder = 'Please provide more details about why you need the extension...';
    }
});
</script>

<style>
.engagement-summary {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
}

.pro-tip-card {
    background: linear-gradient(135deg, #2c3e50 0%, #1a2634 100%);
    border-radius: 16px;
    padding: 20px;
    color: white;
}

.pro-tip-card ul {
    padding-left: 20px;
}
</style>