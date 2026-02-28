<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

set_exception_handler(function($e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
});

set_error_handler(function($errno, $errstr) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $errstr]);
    exit;
});

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Fatal error: ' . $error['message']]);
        exit;
    }
});

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../includes/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid engagement ID']);
    exit;
}

$engagement_id = (int)$_GET['id'];

// Get engagement details with all related info
$query = "SELECT 
            e.*,
            c.company_name,
            c.contact_name,
            c.contact_email,
            c.contact_mobile,
            s.service_name,
            s.service_category,
            r.base_points,
            r.penalty_type,
            r.penalty_value,
            r.penalty_unit,
            r.threshold_days,
            r.threshold_award,
            r.floor_points,
            r.effective_date as rule_effective_date,
            CONCAT(assigned.first_name, ' ', assigned.last_name) as assigned_to_name,
            assigned.user_email as assigned_email,
            CONCAT(reviewer.first_name, ' ', reviewer.last_name) as reviewer_name,
            CONCAT(creator.first_name, ' ', creator.last_name) as created_by_name,
            COALESCE(e.approved_deadline, e.original_deadline) as current_deadline,
            DATEDIFF(CURDATE(), COALESCE(e.approved_deadline, e.original_deadline)) as days_overdue
          FROM engagements e
          JOIN clients c ON e.client_id = c.client_id
          JOIN service_types s ON e.service_id = s.service_id
          JOIN service_point_rules r ON e.rule_version_id = r.rule_id
          LEFT JOIN users assigned ON e.assigned_to = assigned.user_id
          LEFT JOIN users reviewer ON e.reviewer_id = reviewer.user_id
          LEFT JOIN users creator ON e.created_by = creator.user_id
          WHERE e.engagement_id = $engagement_id";

$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Engagement not found']);
    exit;
}

$engagement = mysqli_fetch_assoc($result);

// Get status history
$history_query = "SELECT h.*, CONCAT(u.first_name, ' ', u.last_name) as changed_by_name
                  FROM engagement_status_history h
                  LEFT JOIN users u ON h.changed_by = u.user_id
                  WHERE h.engagement_id = $engagement_id
                  ORDER BY h.changed_at DESC";
$history_result = mysqli_query($connection, $history_query);

$history = [];
while ($row = mysqli_fetch_assoc($history_result)) {
    $history[] = $row;
}

// Get evidence
$evidence_query = "SELECT ev.*, CONCAT(u.first_name, ' ', u.last_name) as uploaded_by_name,
                   CONCAT(val.first_name, ' ', val.last_name) as validated_by_name
                   FROM evidence ev
                   LEFT JOIN users u ON ev.uploaded_by = u.user_id
                   LEFT JOIN users val ON ev.validated_by = val.user_id
                   WHERE ev.engagement_id = $engagement_id
                   ORDER BY ev.uploaded_at DESC";
$evidence_result = mysqli_query($connection, $evidence_query);

$evidence = [];
while ($row = mysqli_fetch_assoc($evidence_result)) {
    $evidence[] = $row;
}

// Get deadline change requests
$requests_query = "SELECT dcr.*, 
                   CONCAT(req.first_name, ' ', req.last_name) as requested_by_name,
                   CONCAT(rev.first_name, ' ', rev.last_name) as reviewed_by_name
                   FROM deadline_change_requests dcr
                   LEFT JOIN users req ON dcr.requested_by = req.user_id
                   LEFT JOIN users rev ON dcr.reviewed_by = rev.user_id
                   WHERE dcr.engagement_id = $engagement_id
                   ORDER BY dcr.created_at DESC";
$requests_result = mysqli_query($connection, $requests_query);

$requests = [];
while ($row = mysqli_fetch_assoc($requests_result)) {
    $requests[] = $row;
}

// Build HTML response
ob_start();
?>

<div class="row">
    <div class="col-md-12">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4><?php echo htmlspecialchars($engagement['title']); ?></h4>
            <span class="badge bg-<?php 
                echo $engagement['status'] == 'CLOSED' ? 'dark' : 
                    ($engagement['status'] == 'SUBMITTED' ? 'success' : 
                    ($engagement['status'] == 'AWAITING_REVIEW' ? 'warning' : 
                    ($engagement['status'] == 'IN_PROGRESS' ? 'primary' : 'info'))); 
            ?> px-3 py-2"><?php echo $engagement['status']; ?></span>
        </div>
        
        <!-- Client Info -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-building me-2"></i>Client Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Company:</strong><br>
                        <?php echo htmlspecialchars($engagement['company_name']); ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Contact:</strong><br>
                        <?php echo htmlspecialchars($engagement['contact_name']); ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Email:</strong><br>
                        <a href="mailto:<?php echo htmlspecialchars($engagement['contact_email']); ?>">
                            <?php echo htmlspecialchars($engagement['contact_email']); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Assignment Info -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-people me-2"></i>Assignment</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Assigned To:</strong><br>
                        <?php echo htmlspecialchars($engagement['assigned_to_name']); ?>
                        <br><small><?php echo htmlspecialchars($engagement['assigned_email']); ?></small>
                    </div>
                    <div class="col-md-4">
                        <strong>Reviewer:</strong><br>
                        <?php echo $engagement['reviewer_name'] ? htmlspecialchars($engagement['reviewer_name']) : '<span class="text-muted">No reviewer</span>'; ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Created By:</strong><br>
                        <?php echo htmlspecialchars($engagement['created_by_name']); ?>
                        <br><small><?php echo date('M d, Y', strtotime($engagement['created_at'])); ?></small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Dates & Deadlines -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-calendar me-2"></i>Dates & Deadlines</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Start Date:</strong><br>
                        <?php echo date('M d, Y', strtotime($engagement['start_date'])); ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Original Deadline:</strong><br>
                        <?php echo date('M d, Y', strtotime($engagement['original_deadline'])); ?>
                    </div>
                    <?php if ($engagement['approved_deadline']): ?>
                    <div class="col-md-3">
                        <strong>Approved Deadline:</strong><br>
                        <span class="text-success"><?php echo date('M d, Y', strtotime($engagement['approved_deadline'])); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-3">
                        <strong>Status:</strong><br>
                        <?php if ($engagement['days_overdue'] > 0 && $engagement['status'] != 'CLOSED'): ?>
                            <span class="text-danger">Overdue by <?php echo $engagement['days_overdue']; ?> days</span>
                        <?php else: ?>
                            <span class="text-success">On track</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Service & Points Info -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-trophy me-2"></i>Service & Points</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Service:</strong><br>
                        <?php echo htmlspecialchars($engagement['service_name']); ?>
                        <br><small><?php echo htmlspecialchars($engagement['service_category']); ?></small>
                    </div>
                    <div class="col-md-2">
                        <strong>Base Points:</strong><br>
                        <span class="badge bg-primary fs-6"><?php echo $engagement['base_points']; ?></span>
                    </div>
                    <div class="col-md-3">
                        <strong>Penalty Rule:</strong><br>
                        <?php 
                        if ($engagement['penalty_type'] == 'linear') {
                            echo "-{$engagement['penalty_value']} per {$engagement['penalty_unit']}";
                        } elseif ($engagement['penalty_type'] == 'threshold') {
                            echo "After {$engagement['threshold_days']} days: {$engagement['threshold_award']} pts";
                        } else {
                            echo "Fixed penalty";
                        }
                        ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Floor Points:</strong><br>
                        <?php echo $engagement['floor_points']; ?>
                    </div>
                </div>
                <?php if ($engagement['points_awarded'] !== null): ?>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <strong>Points Awarded:</strong>
                        <span class="badge bg-success fs-5 ms-2"><?php echo $engagement['points_awarded']; ?> points</span>
                        <?php if ($engagement['delay_days'] > 0): ?>
                            <small class="text-muted ms-2">(<?php echo $engagement['delay_days']; ?> days delay)</small>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Evidence -->
        <?php if (!empty($evidence)): ?>
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-file-earmark me-2"></i>Evidence (<?php echo count($evidence); ?>)</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>File</th>
                                <th>Uploaded By</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($evidence as $ev): ?>
                            <tr>
                                <td>
                                    <a href="../uploads/evidence/<?php echo $ev['file_path']; ?>" target="_blank">
                                        <i class="bi bi-file-earmark"></i>
                                        <?php echo htmlspecialchars($ev['file_name']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($ev['uploaded_by_name']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($ev['uploaded_at'])); ?></td>
                                <td>
                                    <?php if ($ev['is_validated']): ?>
                                        <span class="badge bg-success">Validated</span>
                                        <small><?php echo htmlspecialchars($ev['validated_by_name']); ?></small>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Status History -->
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Status History</h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <?php foreach($history as $h): ?>
                    <div class="d-flex mb-2">
                        <div class="me-3">
                            <span class="badge bg-info"><?php echo date('H:i', strtotime($h['changed_at'])); ?></span>
                        </div>
                        <div>
                            <strong><?php echo $h['old_status'] ?: 'New'; ?></strong> 
                            <i class="bi bi-arrow-right"></i> 
                            <strong><?php echo $h['new_status']; ?></strong>
                            <br>
                            <small class="text-muted">
                                by <?php echo htmlspecialchars($h['changed_by_name']); ?> 
                                on <?php echo date('M d, Y', strtotime($h['changed_at'])); ?>
                            </small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$html = ob_get_clean();

echo json_encode(['success' => true, 'html' => $html]);

ob_end_flush();
?>