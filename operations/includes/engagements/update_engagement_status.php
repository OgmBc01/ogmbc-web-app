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
$query = "SELECT e.*, c.company_name, s.service_name
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

// Define allowed status transitions
$allowed_transitions = [
    'ASSIGNED' => ['IN_PROGRESS'],
    'IN_PROGRESS' => ['AWAITING_REVIEW', 'ASSIGNED'],
    'AWAITING_REVIEW' => ['SUBMITTED', 'IN_PROGRESS'],
    'SUBMITTED' => ['CLOSED', 'AWAITING_REVIEW'],
    'CLOSED' => []
];

$message = '';
$message_type = '';
$showSuccessModal = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    
    $new_status = mysqli_real_escape_string($connection, $_POST['new_status']);
    $notes = mysqli_real_escape_string($connection, trim($_POST['notes'] ?? ''));
    
    // Validate transition
    if (!in_array($new_status, $allowed_transitions[$engagement['status']] ?? [])) {
        $message = "Invalid status transition from {$engagement['status']} to $new_status.";
        $message_type = "danger";
    } else {
        // Check if evidence is required and uploaded
        if ($new_status == 'SUBMITTED' && $engagement['evidence_required']) {
            $evidence_check = "SELECT COUNT(*) as count FROM evidence WHERE engagement_id = $engagement_id";
            $evidence_result = mysqli_query($connection, $evidence_check);
            $evidence_count = mysqli_fetch_assoc($evidence_result)['count'];
            
            if ($evidence_count == 0) {
                $message = "Cannot submit engagement without uploading required evidence.";
                $message_type = "danger";
                ob_end_flush();
            }
        }
        
        if (empty($message)) {
            // Update status
            $update_query = "UPDATE engagements SET status = '$new_status' WHERE engagement_id = $engagement_id";
            
            if (mysqli_query($connection, $update_query)) {
                // Add to status history
                $history_query = "INSERT INTO engagement_status_history 
                                 (engagement_id, old_status, new_status, changed_by, notes)
                                 VALUES ($engagement_id, '{$engagement['status']}', '$new_status', $user_id, '$notes')";
                mysqli_query($connection, $history_query);
                
                // If status is CLOSED, calculate points
                if ($new_status == 'CLOSED') {
                    calculate_engagement_points($connection, $engagement_id);
                }
                
                $showSuccessModal = true;
            } else {
                $message = "Error updating status: " . mysqli_error($connection);
                $message_type = "danger";
            }
        }
    }
}

// Function to calculate points (simplified)
function calculate_engagement_points($connection, $engagement_id) {
    $query = "SELECT e.*, r.points_within_deadline, r.points_tier_1, r.points_tier_2, r.points_tier_3
              FROM engagements e
              JOIN service_point_rules r ON e.rule_version_id = r.rule_id
              WHERE e.engagement_id = $engagement_id";
    $result = mysqli_query($connection, $query);
    $data = mysqli_fetch_assoc($result);
    
    if (!$data) return;
    
    // Calculate delay days
    $completion_date = new DateTime();
    $deadline = new DateTime($data['approved_deadline'] ?? $data['original_deadline']);
    $delay_days = $completion_date > $deadline ? $completion_date->diff($deadline)->days : 0;
    
    // Determine points based on delay
    if ($delay_days == 0) {
        $points = $data['points_within_deadline'];
    } elseif ($delay_days >= 5 && $delay_days <= 15) {
        $points = $data['points_tier_1'];
    } elseif ($delay_days >= 16 && $delay_days <= 25) {
        $points = $data['points_tier_2'];
    } else {
        $points = $data['points_tier_3'];
    }
    
    // Update engagement with points
    $update = "UPDATE engagements SET 
               points_awarded = $points,
               delay_days = $delay_days,
               completion_date = CURDATE()
               WHERE engagement_id = $engagement_id";
    mysqli_query($connection, $update);
    
    // Add to points ledger
    $ledger = "INSERT INTO points_ledger 
               (employee_id, source_type, source_id, points, points_type, description, created_by)
               VALUES ({$data['assigned_to']}, 'ENGAGEMENT', $engagement_id, $points, 'EARNED', 
               'Points awarded for completing engagement: {$data['title']}', {$data['assigned_to']})";
    mysqli_query($connection, $ledger);
}

ob_end_flush();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
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
                            <span class="badge bg-<?php 
                                echo $engagement['status'] == 'IN_PROGRESS' ? 'primary' : 
                                    ($engagement['status'] == 'AWAITING_REVIEW' ? 'warning' : 'secondary'); 
                            ?>">Current: <?php echo $engagement['status']; ?></span>
                        </div>
                    </div>
                    
                    <?php if (!empty($message) && !$showSuccessModal): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="statusForm">
                        <div class="mb-3">
                            <label for="new_status" class="form-label">New Status</label>
                            <select class="form-select" id="new_status" name="new_status" required>
                                <option value="">Select Status</option>
                                <?php foreach ($allowed_transitions[$engagement['status']] ?? [] as $transition): ?>
                                    <option value="<?php echo $transition; ?>">
                                        <?php echo str_replace('_', ' ', $transition); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">
                                Current status: <strong><?php echo str_replace('_', ' ', $engagement['status']); ?></strong>
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="Add any notes about this status change..."></textarea>
                        </div>

                        <!-- Status-specific tips -->
                        <div class="status-tips mb-3">
                            <?php if ($engagement['status'] == 'IN_PROGRESS'): ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Moving to "Awaiting Review"? Make sure all required evidence is uploaded.
                                </div>
                            <?php elseif ($engagement['status'] == 'AWAITING_REVIEW'): ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    Once submitted, the engagement will be reviewed by your supervisor.
                                </div>
                            <?php elseif ($engagement['status'] == 'SUBMITTED'): ?>
                                <div class="alert alert-success">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Closing this engagement will award points based on completion time.
                                </div>
                            <?php endif; ?>
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

<!-- Pro Tip Card -->
<div class="row mt-4">
    <div class="col-12">
        <div class="pro-tip-card">
            <div class="row align-items-center">
                <div class="col-md-9">
                    <h6 class="text-white mb-2">
                        <i class="bi bi-lightbulb me-2"></i>
                        Status Update Tips
                    </h6>
                    <p class="text-white-50 small mb-md-0">
                        ✅ <strong>ASSIGNED → IN PROGRESS:</strong> Start working on the task.<br>
                        ✅ <strong>IN PROGRESS → AWAITING REVIEW:</strong> Ready for review, ensure evidence is uploaded.<br>
                        ✅ <strong>AWAITING REVIEW → SUBMITTED:</strong> Final submission, cannot be changed after this.<br>
                        ✅ <strong>SUBMITTED → CLOSED:</strong> Engagement complete, points will be awarded.
                    </p>
                </div>
                <div class="col-md-3 text-md-end">
                    <i class="bi bi-lightbulb display-4 text-white-50"></i>
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
                <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Status Updated!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h5 class="mt-3">Engagement Status Updated Successfully!</h5>
                <p class="text-muted">The engagement has been moved to <?php echo $new_status ?? 'new status'; ?>.</p>
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

.status-tips {
    font-size: 0.9rem;
}

.pro-tip-card {
    background: linear-gradient(135deg, #2c3e50 0%, #1a2634 100%);
    border-radius: 16px;
    padding: 20px;
    color: white;
}
</style>