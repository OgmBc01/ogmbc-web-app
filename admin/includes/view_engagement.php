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

// Fetch engagement details with all related info
$query = "SELECT 
            e.*,
            c.company_name,
            c.contact_name,
            c.contact_email,
            c.contact_mobile,
            c.country,
            s.service_name,
            s.service_category,
            r.base_points,
            r.penalty_type,
            r.penalty_value,
            r.penalty_unit,
            r.threshold_days,
            r.threshold_award,
            r.floor_points,
            r.rule_version,
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
    $_SESSION['error_message'] = "Engagement not found.";
    ob_end_clean();
    header("Location: engagements.php");
    exit();
}

$engagement = mysqli_fetch_assoc($result);

// Get status history
$history_query = "SELECT h.*, CONCAT(u.first_name, ' ', u.last_name) as changed_by_name
                  FROM engagement_status_history h
                  LEFT JOIN users u ON h.changed_by = u.user_id
                  WHERE h.engagement_id = $engagement_id
                  ORDER BY h.changed_at DESC";
$history_result = mysqli_query($connection, $history_query);

// Get evidence
$evidence_query = "SELECT ev.*, 
                   CONCAT(u.first_name, ' ', u.last_name) as uploaded_by_name,
                   CONCAT(val.first_name, ' ', val.last_name) as validated_by_name
                   FROM evidence ev
                   LEFT JOIN users u ON ev.uploaded_by = u.user_id
                   LEFT JOIN users val ON ev.validated_by = val.user_id
                   WHERE ev.engagement_id = $engagement_id
                   ORDER BY ev.uploaded_at DESC";
$evidence_result = mysqli_query($connection, $evidence_query);

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

ob_end_flush();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Header with Actions -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="page-title mb-1">Engagement Details</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="engagements.php">Engagements</a></li>
                            <li class="breadcrumb-item active">#<?php echo $engagement_id; ?> - <?php echo htmlspecialchars($engagement['title']); ?></li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="engagements.php" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                    <?php if ($engagement['status'] != 'CLOSED'): ?>
                        <a href="engagements.php?source=edit_engagement&id=<?php echo $engagement_id; ?>" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <a href="engagements.php?source=upload_evidence&id=<?php echo $engagement_id; ?>" class="btn btn-success">
                            <i class="bi bi-upload"></i> Upload Evidence
                        </a>
                        <a href="engagements.php?source=request_deadline_change&id=<?php echo $engagement_id; ?>" class="btn btn-primary">
                            <i class="bi bi-calendar-plus"></i> Request Deadline Change
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Status Banner -->
            <div class="alert alert-<?php 
                echo $engagement['status'] == 'CLOSED' ? 'success' : 
                    ($engagement['status'] == 'SUBMITTED' ? 'info' : 
                    ($engagement['status'] == 'AWAITING_REVIEW' ? 'warning' : 
                    ($engagement['status'] == 'REJECTED' ? 'danger' : 'primary'))); 
            ?> d-flex justify-content-between align-items-center">
                <div>
                    <strong>Status:</strong> <?php echo $engagement['status']; ?>
                    <?php if ($engagement['days_overdue'] > 0 && $engagement['status'] != 'CLOSED'): ?>
                        <span class="badge bg-danger ms-2">Overdue by <?php echo $engagement['days_overdue']; ?> days</span>
                    <?php endif; ?>
                </div>
                <?php if ($engagement['points_awarded'] !== null): ?>
                    <div>
                        <strong>Points Awarded:</strong> 
                        <span class="badge bg-success fs-6"><?php echo $engagement['points_awarded']; ?> points</span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Main Content Grid -->
            <div class="row">
                <!-- Left Column - Client & Assignment -->
                <div class="col-md-6">
                    <!-- Client Information Card -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bi bi-building me-2"></i>Client Information</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="120">Company:</th>
                                    <td><strong><?php echo htmlspecialchars($engagement['company_name']); ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Contact:</th>
                                    <td><?php echo htmlspecialchars($engagement['contact_name']); ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><a href="mailto:<?php echo htmlspecialchars($engagement['contact_email']); ?>"><?php echo htmlspecialchars($engagement['contact_email']); ?></a></td>
                                </tr>
                                <tr>
                                    <th>Phone:</th>
                                    <td><?php echo htmlspecialchars($engagement['contact_mobile']); ?></td>
                                </tr>
                                <tr>
                                    <th>Country:</th>
                                    <td><?php echo htmlspecialchars($engagement['country']); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Assignment Card -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bi bi-people me-2"></i>Assignment</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="120">Assigned To:</th>
                                    <td>
                                        <strong><?php echo htmlspecialchars($engagement['assigned_to_name']); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($engagement['assigned_email']); ?></small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Reviewer:</th>
                                    <td>
                                        <?php if ($engagement['reviewer_name']): ?>
                                            <?php echo htmlspecialchars($engagement['reviewer_name']); ?>
                                        <?php else: ?>
                                            <span class="text-muted">No reviewer assigned</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created By:</th>
                                    <td>
                                        <?php echo htmlspecialchars($engagement['created_by_name']); ?>
                                        <br><small class="text-muted"><?php echo date('M d, Y H:i', strtotime($engagement['created_at'])); ?></small>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Dates & Service -->
                <div class="col-md-6">
                    <!-- Dates Card -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bi bi-calendar me-2"></i>Dates & Deadlines</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="140">Start Date:</th>
                                    <td><?php echo date('M d, Y', strtotime($engagement['start_date'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Original Deadline:</th>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($engagement['original_deadline'])); ?>
                                        <?php if ($engagement['approved_deadline']): ?>
                                            <span class="badge bg-warning ms-2">Changed</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php if ($engagement['approved_deadline']): ?>
                                <tr>
                                    <th>Approved Deadline:</th>
                                    <td class="text-success">
                                        <strong><?php echo date('M d, Y', strtotime($engagement['approved_deadline'])); ?></strong>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($engagement['completion_date']): ?>
                                <tr>
                                    <th>Completed On:</th>
                                    <td><?php echo date('M d, Y', strtotime($engagement['completion_date'])); ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>

                    <!-- Service & Points Card -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bi bi-trophy me-2"></i>Service & Points Configuration</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="140">Service:</th>
                                    <td><?php echo htmlspecialchars($engagement['service_name']); ?></td>
                                </tr>
                                <tr>
                                    <th>Category:</th>
                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($engagement['service_category']); ?></span></td>
                                </tr>
                                <tr>
                                    <th>Rule Version:</th>
                                    <td>v<?php echo $engagement['rule_version']; ?> (effective <?php echo date('M d, Y', strtotime($engagement['rule_effective_date'])); ?>)</td>
                                </tr>
                                <tr>
                                    <th>Base Points:</th>
                                    <td><span class="badge bg-primary"><?php echo $engagement['base_points']; ?></span></td>
                                </tr>
                                <tr>
                                    <th>Penalty Rule:</th>
                                    <td>
                                        <?php 
                                        if ($engagement['penalty_type'] == 'linear') {
                                            echo "-{$engagement['penalty_value']} per {$engagement['penalty_unit']}";
                                        } elseif ($engagement['penalty_type'] == 'threshold') {
                                            echo "After {$engagement['threshold_days']} days: {$engagement['threshold_award']} pts";
                                        } else {
                                            echo "Fixed penalty";
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Floor Points:</th>
                                    <td><?php echo $engagement['floor_points']; ?></td>
                                </tr>
                                <tr>
                                    <th>Evidence Required:</th>
                                    <td>
                                        <?php if ($engagement['evidence_required']): ?>
                                            <span class="badge bg-success">Yes</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description Card -->
            <?php if (!empty($engagement['description'])): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-file-text me-2"></i>Description</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($engagement['description'])); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Evidence Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-file-earmark me-2"></i>Evidence Uploaded</h6>
                    <?php if ($engagement['status'] != 'CLOSED'): ?>
                        <a href="engagements.php?source=upload_evidence&id=<?php echo $engagement_id; ?>" class="btn btn-sm btn-success">
                            <i class="bi bi-upload"></i> Upload New
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if ($evidence_result && mysqli_num_rows($evidence_result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
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
                                        <td><?php echo htmlspecialchars($evidence['uploaded_by_name']); ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($evidence['uploaded_at'])); ?></td>
                                        <td><?php echo round($evidence['file_size'] / 1024, 2); ?> KB</td>
                                        <td>
                                            <?php if ($evidence['is_validated']): ?>
                                                <span class="badge bg-success">Validated</span>
                                                <br><small><?php echo htmlspecialchars($evidence['validated_by_name']); ?></small>
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

            <!-- Deadline Change Requests -->
            <?php if ($requests_result && mysqli_num_rows($requests_result) > 0): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-calendar-plus me-2"></i>Deadline Change Requests</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Requested Date</th>
                                    <th>Reason</th>
                                    <th>Requested By</th>
                                    <th>Status</th>
                                    <th>Reviewed By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($request = mysqli_fetch_assoc($requests_result)): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($request['requested_date'])); ?></td>
                                    <td>
                                        <strong><?php echo ucfirst($request['reason_code']); ?></strong>
                                        <?php if (!empty($request['reason_notes'])): ?>
                                            <br><small><?php echo htmlspecialchars($request['reason_notes']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($request['requested_by_name']); ?></td>
                                    <td>
                                        <?php
                                        $status_class = 'secondary';
                                        if ($request['status'] == 'APPROVED') $status_class = 'success';
                                        if ($request['status'] == 'REJECTED') $status_class = 'danger';
                                        if ($request['status'] == 'PENDING') $status_class = 'warning';
                                        ?>
                                        <span class="badge bg-<?php echo $status_class; ?>"><?php echo $request['status']; ?></span>
                                    </td>
                                    <td>
                                        <?php if ($request['reviewed_by_name']): ?>
                                            <?php echo htmlspecialchars($request['reviewed_by_name']); ?>
                                            <br><small><?php echo date('M d, Y', strtotime($request['reviewed_at'])); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($request['status'] == 'PENDING' && ($_SESSION['user_role'] == 'CEO_GM' || $_SESSION['user_role'] == 'ADMIN_STAFF')): ?>
                                            <a href="engagements.php?approve_request=<?php echo $request['request_id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Approve this deadline change?')">
                                                <i class="bi bi-check-lg"></i> Approve
                                            </a>
                                            <a href="engagements.php?reject_request=<?php echo $request['request_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Reject this deadline change?')">
                                                <i class="bi bi-x-lg"></i> Reject
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Status History -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Status History</h6>
                </div>
                <div class="card-body">
                    <?php if ($history_result && mysqli_num_rows($history_result) > 0): ?>
                        <div class="timeline">
                            <?php while($history = mysqli_fetch_assoc($history_result)): ?>
                            <div class="d-flex mb-3">
                                <div class="me-3 text-center" style="min-width: 60px;">
                                    <span class="badge bg-secondary"><?php echo date('H:i', strtotime($history['changed_at'])); ?></span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-<?php 
                                            echo $history['new_status'] == 'CLOSED' ? 'success' : 
                                                ($history['new_status'] == 'SUBMITTED' ? 'info' : 
                                                ($history['new_status'] == 'AWAITING_REVIEW' ? 'warning' : 
                                                ($history['new_status'] == 'REJECTED' ? 'danger' : 'primary'))); 
                                        ?>"><?php echo $history['new_status']; ?></span>
                                        
                                        <?php if ($history['old_status']): ?>
                                            <small class="text-muted ms-2">
                                                (was <?php echo $history['old_status']; ?>)
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted">
                                        by <?php echo htmlspecialchars($history['changed_by_name']); ?> 
                                        on <?php echo date('M d, Y', strtotime($history['changed_at'])); ?>
                                    </small>
                                    <?php if (!empty($history['notes'])): ?>
                                        <p class="mt-1 mb-0 text-muted"><?php echo htmlspecialchars($history['notes']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center mb-0">No status history available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 20px;
}
.timeline .d-flex {
    position: relative;
}
.timeline .d-flex:not(:last-child):before {
    content: '';
    position: absolute;
    left: -20px;
    top: 24px;
    bottom: -8px;
    width: 2px;
    background: #e9ecef;
}
.timeline .badge {
    position: relative;
    z-index: 1;
}
</style>