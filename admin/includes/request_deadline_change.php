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
          CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name,
          COALESCE(e.approved_deadline, e.original_deadline) as current_deadline
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

// Check if engagement is already closed
if ($engagement['status'] == 'CLOSED') {
    $_SESSION['error_message'] = "Cannot request deadline change for closed engagement.";
    ob_end_clean();
    header("Location: engagements.php");
    exit();
}

// Check for existing pending requests
$pending_query = "SELECT COUNT(*) as pending FROM deadline_change_requests 
                  WHERE engagement_id = $engagement_id AND status = 'PENDING'";
$pending_result = mysqli_query($connection, $pending_query);
$pending = mysqli_fetch_assoc($pending_result);

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
    } elseif ($pending['pending'] > 0) {
        $message = "There is already a pending deadline change request for this engagement.";
        $message_type = "danger";
    } else {
        
        // Insert request
        $insert_query = "INSERT INTO deadline_change_requests 
                        (engagement_id, requested_by, requested_date, reason_code, reason_notes, status) 
                        VALUES 
                        ($engagement_id, {$_SESSION['user_id']}, '$requested_date', '$reason_code', '$reason_notes', 'PENDING')";
        
        if (mysqli_query($connection, $insert_query)) {
            $showSuccessModal = true;
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
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-calendar-plus me-2"></i>Request Deadline Change</h5>
                    <a href="engagements.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Engagements
                    </a>
                </div>
                <div class="card-body">
                    
                    <!-- Engagement Summary -->
                    <div class="alert alert-info">
                        <p><strong>Engagement:</strong> <?php echo htmlspecialchars($engagement['title']); ?></p>
                        <p><strong>Client:</strong> <?php echo htmlspecialchars($engagement['company_name']); ?></p>
                        <p><strong>Current Deadline:</strong> <?php echo date('M d, Y', strtotime($engagement['current_deadline'])); ?></p>
                    </div>
                    
                    <?php if ($pending['pending'] > 0): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        There is already a pending deadline change request for this engagement.
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($message) && !$showSuccessModal): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <?php if ($pending['pending'] == 0): ?>
                    <form method="POST" action="" id="requestForm">
                        <div class="mb-3">
                            <label for="requested_date" class="form-label">Requested New Deadline *</label>
                            <input type="date" id="requested_date" name="requested_date" class="form-control" 
                                   min="<?php echo date('Y-m-d', strtotime('+1 day', strtotime($engagement['current_deadline']))); ?>" required>
                            <div class="form-text">Must be after current deadline</div>
                        </div>

                        <div class="mb-3">
                            <label for="reason_code" class="form-label">Reason *</label>
                            <select id="reason_code" name="reason_code" class="form-control" required>
                                <option value="">Select Reason</option>
                                <option value="workload">Workload / Capacity</option>
                                <option value="client_delay">Client Delay</option>
                                <option value="technical">Technical Issues</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="reason_notes" class="form-label">Additional Notes</label>
                            <textarea id="reason_notes" name="reason_notes" class="form-control" rows="3" placeholder="Please provide more details..."></textarea>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="submit_request" class="btn btn-primary btn-lg me-2">
                                    <i class="bi bi-send me-1"></i> Submit Request
                                </button>
                                <a href="engagements.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="bi bi-x-circle me-1"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                    <?php endif; ?>
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
          <i class="bi bi-check-circle-fill me-2"></i>Request Submitted!
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-center py-3">
          <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
          <h5 class="mt-3">Deadline Change Request Submitted</h5>
          <p class="text-muted mb-0">Your request has been sent for review. You will be notified once approved.</p>
        </div>
      </div>
      <div class="modal-footer justify-content-center border-0 pt-0">
        <a href="engagements.php" class="btn btn-success px-4">
          <i class="bi bi-list-ul me-2"></i>View All Engagements
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