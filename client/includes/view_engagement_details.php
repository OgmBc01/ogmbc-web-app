<?php


$engagement_id = (int)$_GET['id'];
$client_id = $_SESSION['client_id'] ?? null;

// Fetch engagement details and verify client ownership
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
    r.points_within_deadline,
    r.points_tier_1,
    r.points_tier_2,
    r.points_tier_3,
    r.rule_version,
    CONCAT(assigned.first_name, ' ', assigned.last_name) as assigned_to_name,
    assigned.user_email as assigned_email,
    CONCAT(reviewer.first_name, ' ', reviewer.last_name) as reviewer_name,
    CONCAT(creator.first_name, ' ', creator.last_name) as created_by_name,
    DATEDIFF(COALESCE(e.approved_deadline, e.original_deadline), CURDATE()) as days_remaining
    FROM engagements e
    JOIN clients c ON e.client_id = c.client_id
    JOIN service_types s ON e.service_id = s.service_id
    JOIN service_point_rules r ON e.rule_version_id = r.rule_id
    LEFT JOIN users assigned ON e.assigned_to = assigned.user_id
    LEFT JOIN users reviewer ON e.reviewer_id = reviewer.user_id
    LEFT JOIN users creator ON e.created_by = creator.user_id
    WHERE e.engagement_id = $engagement_id AND e.client_id = $client_id";

$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<script>window.location.href = 'engagements.php';</script>";
    exit();
}


$engagement = mysqli_fetch_assoc($result);

// Fetch all documents for this engagement (assignment-specific)
$docs_query = "SELECT * FROM client_files WHERE engagement_id = $engagement_id ORDER BY uploaded_at DESC";
$docs_result = mysqli_query($connection, $docs_query);

// Fetch all documents uploaded by this client (across all their engagements)
$client_id = (int)$engagement['client_id'];
$client_docs_query = "SELECT * FROM client_files WHERE client_id = $client_id ORDER BY uploaded_at DESC";
$client_docs_result = mysqli_query($connection, $client_docs_query);

// Get evidence
$evidence_query = "SELECT * FROM evidence WHERE engagement_id = $engagement_id ORDER BY uploaded_at DESC";
$evidence_result = mysqli_query($connection, $evidence_query);

// Get comments
$comments_query = "SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) as user_name
                   FROM task_comments c
                   JOIN users u ON c.user_id = u.user_id
                   WHERE c.engagement_id = $engagement_id
                   ORDER BY c.created_at DESC";
$comments_result = mysqli_query($connection, $comments_query);

// Get deadline change requests
$requests_query = "SELECT r.*, 
                  CONCAT(ru.first_name, ' ', ru.last_name) as requested_by_name,
                  CONCAT(ru2.first_name, ' ', ru2.last_name) as reviewed_by_name
                  FROM deadline_change_requests r
                  LEFT JOIN users ru ON r.requested_by = ru.user_id
                  LEFT JOIN users ru2 ON r.reviewed_by = ru2.user_id
                  WHERE r.engagement_id = $engagement_id
                  ORDER BY r.created_at DESC";
$requests_result = mysqli_query($connection, $requests_query);

// Get status history
$history_query = "SELECT h.*, CONCAT(u.first_name, ' ', u.last_name) as changed_by_name
                  FROM engagement_status_history h
                  JOIN users u ON h.changed_by = u.user_id
                  WHERE h.engagement_id = $engagement_id
                  ORDER BY h.changed_at DESC";
$history_result = mysqli_query($connection, $history_query);

// Determine status color
$status_class = 'secondary';
$status_icon = 'bell';
switch($engagement['status']) {
    case 'ASSIGNED':
        $status_class = 'secondary';
        $status_icon = 'bell';
        break;
    case 'IN_PROGRESS':
        $status_class = 'primary';
        $status_icon = 'play-circle';
        break;
    case 'AWAITING_REVIEW':
        $status_class = 'warning';
        $status_icon = 'clock-history';
        break;
    case 'SUBMITTED':
        $status_class = 'success';
        $status_icon = 'check-circle';
        break;
    case 'CLOSED':
        $status_class = 'dark';
        $status_icon = 'check2-all';
        break;
}

$is_overdue = ($engagement['days_remaining'] < 0 && $engagement['status'] != 'CLOSED' && $engagement['status'] != 'SUBMITTED');
$overdue_days = abs($engagement['days_remaining']);
?>

<div class="container-fluid">
    <!-- Header with Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-2"><?php echo htmlspecialchars($engagement['title']); ?></h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="engagements.php">Engagements</a></li>
                    <li class="breadcrumb-item active">Engagement #<?php echo $engagement_id; ?></li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="engagements.php" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left me-1"></i>Back to List
            </a>
            <?php if ($engagement['status'] != 'CLOSED' && $engagement['status'] != 'SUBMITTED'): ?>
                <a href="engagements.php?source=update_status&id=<?php echo $engagement_id; ?>" class="btn btn-warning me-2">
                    <i class="bi bi-arrow-repeat me-1"></i>Update Status
                </a>
                <a href="engagements.php?source=upload_evidence&id=<?php echo $engagement_id; ?>" class="btn btn-success">
                    <i class="bi bi-cloud-upload me-1"></i>Upload Evidence
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Status Banner -->
    <div class="status-banner mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center">
                    <div class="status-icon me-3">
                        <i class="bi bi-<?php echo $status_icon; ?> text-<?php echo $status_class; ?>" style="font-size: 2.5rem;"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">
                            Status: <span class="badge bg-<?php echo $status_class; ?> px-3 py-2"><?php echo str_replace('_', ' ', $engagement['status']); ?></span>
                        </h5>
                        <?php if ($is_overdue): ?>
                            <p class="text-danger mb-0">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Overdue by <?php echo $overdue_days; ?> day<?php echo $overdue_days > 1 ? 's' : ''; ?>
                            </p>
                        <?php elseif ($engagement['days_remaining'] == 0 && $engagement['status'] != 'CLOSED'): ?>
                            <p class="text-warning mb-0">
                                <i class="bi bi-clock me-1"></i>
                                Due today
                            </p>
                        <?php elseif ($engagement['days_remaining'] > 0 && $engagement['status'] != 'CLOSED'): ?>
                            <p class="text-success mb-0">
                                <i class="bi bi-clock me-1"></i>
                                <?php echo $engagement['days_remaining']; ?> day<?php echo $engagement['days_remaining'] > 1 ? 's' : ''; ?> remaining
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php if ($engagement['points_awarded']): ?>
            <div class="col-md-4 text-md-end">
                <div class="points-awarded">
                    <span class="points-label">Points Awarded</span>
                    <span class="points-value"><?php echo $engagement['points_awarded']; ?></span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row">
        <!-- Left Column - Client & Assignment -->
        <div class="col-lg-4">
            <!-- Client Info Card -->
            <div class="info-card mb-4">
                <h6 class="info-title">
                    <i class="bi bi-building me-2"></i>Client Information
                </h6>
                <div class="info-content">
                    <p class="mb-2"><strong><?php echo htmlspecialchars($engagement['company_name']); ?></strong></p>
                    <p class="mb-2">
                        <i class="bi bi-person me-2 text-muted"></i>
                        <?php echo htmlspecialchars($engagement['contact_name']); ?>
                    </p>
                    <p class="mb-2">
                        <i class="bi bi-envelope me-2 text-muted"></i>
                        <a href="mailto:<?php echo $engagement['contact_email']; ?>"><?php echo $engagement['contact_email']; ?></a>
                    </p>
                    <p class="mb-2">
                        <i class="bi bi-telephone me-2 text-muted"></i>
                        <?php echo $engagement['contact_mobile']; ?>
                    </p>
                    <p class="mb-0">
                        <i class="bi bi-geo-alt me-2 text-muted"></i>
                        <?php echo htmlspecialchars($engagement['country']); ?>
                    </p>
                </div>
            </div>

            <!-- Assignment Card -->
            <div class="info-card mb-4">
                <h6 class="info-title">
                    <i class="bi bi-people me-2"></i>Assignment
                </h6>
                <div class="info-content">
                    <p class="mb-2"><strong>Assigned to:</strong> <?php echo htmlspecialchars($engagement['assigned_to_name']); ?></p>
                    <p class="mb-2"><strong>Reviewer:</strong> <?php echo $engagement['reviewer_name'] ? htmlspecialchars($engagement['reviewer_name']) : '<span class="text-muted">Not assigned</span>'; ?></p>
                    <p class="mb-2"><strong>Created by:</strong> <?php echo htmlspecialchars($engagement['created_by_name']); ?></p>
                    <p class="mb-0"><strong>Assigned on:</strong> <?php echo date('M d, Y', strtotime($engagement['assigned_at'])); ?></p>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="info-card">
                <h6 class="info-title">
                    <i class="bi bi-lightning-charge me-2"></i>Quick Actions
                </h6>
                <div class="d-grid gap-2">
                    <?php if ($engagement['status'] != 'CLOSED' && $engagement['status'] != 'SUBMITTED'): ?>
                        <a href="engagements.php?source=update_status&id=<?php echo $engagement_id; ?>" class="btn btn-outline-warning">
                            <i class="bi bi-arrow-repeat me-2"></i>Update Status
                        </a>
                        <a href="engagements.php?source=upload_evidence&id=<?php echo $engagement_id; ?>" class="btn btn-outline-success">
                            <i class="bi bi-cloud-upload me-2"></i>Upload Evidence
                        </a>
                        <a href="engagements.php?source=request_deadline&id=<?php echo $engagement_id; ?>" class="btn btn-outline-primary">
                            <i class="bi bi-calendar-plus me-2"></i>Request Deadline Change
                        </a>
                    <?php endif; ?>
                    <button class="btn btn-outline-secondary" onclick="scrollToComments()">
                        <i class="bi bi-chat me-2"></i>Add Comment
                    </button>
                </div>
            </div>
        </div>

        <!-- Right Column - Details & Activity -->
        <div class="col-lg-8">
            <!-- Dates Card -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="date-card">
                        <span class="date-label">Start Date</span>
                        <span class="date-value"><?php echo date('M d, Y', strtotime($engagement['start_date'])); ?></span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="date-card">
                        <span class="date-label">Original Deadline</span>
                        <span class="date-value"><?php echo date('M d, Y', strtotime($engagement['original_deadline'])); ?></span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="date-card">
                        <span class="date-label">Current Deadline</span>
                        <span class="date-value <?php echo $engagement['approved_deadline'] ? 'text-success' : ''; ?>">
                            <?php echo $engagement['approved_deadline'] ? date('M d, Y', strtotime($engagement['approved_deadline'])) . ' <i class="bi bi-check-circle-fill text-success" title="Approved change"></i>' : date('M d, Y', strtotime($engagement['original_deadline'])); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Service & Points Card -->
            <div class="info-card mb-4">
                <h6 class="info-title">
                    <i class="bi bi-trophy me-2"></i>Service & Points (Version <?php echo $engagement['rule_version']; ?>)
                </h6>
                <div class="info-content">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Service:</strong> <?php echo htmlspecialchars($engagement['service_name']); ?></p>
                            <p class="mb-0"><strong>Category:</strong> <span class="badge bg-info"><?php echo $engagement['service_category']; ?></span></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Base Points:</strong> <?php echo $engagement['base_points']; ?></p>
                            <p class="mb-0"><strong>Rule Version:</strong> v<?php echo $engagement['rule_version']; ?></p>
                        </div>
                    </div>
                    
                    <div class="points-grid">
                        <div class="points-tier">
                            <span class="tier-label">On Time</span>
                            <span class="tier-value"><?php echo $engagement['points_within_deadline']; ?></span>
                        </div>
                        <div class="points-tier">
                            <span class="tier-label">5-15 Days</span>
                            <span class="tier-value"><?php echo $engagement['points_tier_1']; ?></span>
                        </div>
                        <div class="points-tier">
                            <span class="tier-label">16-25 Days</span>
                            <span class="tier-value"><?php echo $engagement['points_tier_2']; ?></span>
                        </div>
                        <div class="points-tier">
                            <span class="tier-label">>25 Days</span>
                            <span class="tier-value"><?php echo $engagement['points_tier_3']; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <?php if (!empty($engagement['description'])): ?>
            <div class="info-card mb-4">
                <h6 class="info-title">
                    <i class="bi bi-file-text me-2"></i>Description
                </h6>
                <div class="info-content">
                    <?php echo nl2br(htmlspecialchars($engagement['description'])); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Assignment/Engagement Documents Section -->
            <div class="info-card mb-4">
                <h6 class="info-title">
                    <i class="bi bi-folder2-open me-2"></i>Documents for This Engagement
                </h6>
                <div class="info-content">
                    <?php if ($docs_result && mysqli_num_rows($docs_result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>File Name</th>
                                        <th>Description</th>
                                        <th>Uploaded By</th>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($doc = mysqli_fetch_assoc($docs_result)): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($doc['file_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($doc['description']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $doc['uploaded_by'] === 'client' ? 'primary' : 'secondary'; ?>">
                                                <?php echo ucfirst($doc['uploaded_by']); ?> 
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($doc['file_type']); ?></td>
                                        <td><?php echo $doc['file_size'] ? round($doc['file_size']/1024, 1) . ' KB' : '-'; ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($doc['uploaded_at'])); ?></td>
                                        <td>
                                            <a href="../../../../uploads/client_files/<?php echo rawurlencode($doc['file_path']); ?>" class="btn btn-outline-primary btn-sm" target="_blank">View</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center mb-0">No documents uploaded for this engagement.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- All Client Documents Section -->
            <div class="info-card mb-4">
                <h6 class="info-title">
                    <i class="bi bi-folder-symlink me-2"></i>All Documents Uploaded by This Client
                </h6>
                <div class="info-content">
                    <?php if ($client_docs_result && mysqli_num_rows($client_docs_result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>File Name</th>
                                        <th>Description</th>
                                        <th>Engagement</th>
                                        <th>Uploaded By</th>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($doc = mysqli_fetch_assoc($client_docs_result)): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($doc['file_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($doc['description']); ?></td>
                                        <td>
                                            <?php if ($doc['engagement_id']): ?>
                                                <a href="view_engagement_details.php?id=<?php echo (int)$doc['engagement_id']; ?>">#<?php echo (int)$doc['engagement_id']; ?></a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $doc['uploaded_by'] === 'client' ? 'primary' : 'secondary'; ?>">
                                                <?php echo ucfirst($doc['uploaded_by']); ?> 
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($doc['file_type']); ?></td>
                                        <td><?php echo $doc['file_size'] ? round($doc['file_size']/1024, 1) . ' KB' : '-'; ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($doc['uploaded_at'])); ?></td>
                                        <td>
                                            <a href="../../../../uploads/client_files/<?php echo rawurlencode($doc['file_path']); ?>" class="btn btn-outline-primary btn-sm" target="_blank">View</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center mb-0">No documents uploaded by this client.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Evidence Section -->
            <div class="info-card mb-4">
                <h6 class="info-title">
                    <i class="bi bi-file-earmark me-2"></i>Evidence
                    <a href="engagements.php?source=upload_evidence&id=<?php echo $engagement_id; ?>" class="btn btn-sm btn-success float-end">
                        <i class="bi bi-cloud-upload me-1"></i>Upload
                    </a>
                </h6>
                <div class="info-content">
                    <?php if ($evidence_result && mysqli_num_rows($evidence_result) > 0): ?>
                        <div class="evidence-list">
                            <?php while($file = mysqli_fetch_assoc($evidence_result)): ?>
                            <div class="evidence-item">
                                <?php
                                $ext = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
                                $icon = 'file-earmark';
                                if ($ext == 'pdf') $icon = 'file-earmark-pdf';
                                elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) $icon = 'file-earmark-image';
                                elseif (in_array($ext, ['doc', 'docx'])) $icon = 'file-earmark-word';
                                ?>
                                <i class="bi bi-<?php echo $icon; ?> file-icon"></i>
                                <div class="file-info">
                                    <span class="file-name"><?php echo htmlspecialchars($file['file_name']); ?></span>
                                    <small class="text-muted">
                                        Uploaded on <?php echo date('M d, Y H:i', strtotime($file['uploaded_at'])); ?>
                                    </small>
                                </div>
                                <a href="../uploads/evidence/<?php echo $file['file_path']; ?>" class="btn btn-sm btn-outline-primary" download>
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-3">No evidence uploaded yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Comments Section -->
            <div class="info-card mb-4" id="commentsSection">
                <h6 class="info-title">
                    <i class="bi bi-chat me-2"></i>Comments
                </h6>
                <div class="info-content">
                    <!-- Comment Form -->
                    <form class="comment-form mb-3" onsubmit="return addComment(event, <?php echo $engagement_id; ?>);">
                        <div class="input-group">
                            <input type="text" class="form-control" id="commentText" placeholder="Add a comment..." required>
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-send"></i>
                            </button>
                        </div>
                    </form>
                    
                    <!-- Comments List -->
                    <div class="comments-list" id="commentsList">
                        <?php if ($comments_result && mysqli_num_rows($comments_result) > 0): ?>
                            <?php while($comment = mysqli_fetch_assoc($comments_result)): ?>
                            <div class="comment-item">
                                <div class="d-flex justify-content-between">
                                    <strong><?php echo htmlspecialchars($comment['user_name']); ?></strong>
                                    <small class="text-muted"><?php echo date('M d, H:i', strtotime($comment['created_at'])); ?></small>
                                </div>
                                <p class="mb-0 mt-1"><?php echo htmlspecialchars($comment['comment']); ?></p>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-3">No comments yet. Start the conversation!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Deadline Change Requests -->
            <?php if ($requests_result && mysqli_num_rows($requests_result) > 0): ?>
            <div class="info-card mb-4">
                <h6 class="info-title">
                    <i class="bi bi-calendar-plus me-2"></i>Deadline Change Requests
                </h6>
                <div class="info-content">
                    <?php while($req = mysqli_fetch_assoc($requests_result)): 
                        $status_class = 'warning';
                        $status_text = 'Pending';
                        if ($req['status'] == 'APPROVED') {
                            $status_class = 'success';
                            $status_text = 'Approved';
                        } elseif ($req['status'] == 'REJECTED') {
                            $status_class = 'danger';
                            $status_text = 'Rejected';
                        }
                    ?>
                    <div class="request-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>Requested: <?php echo date('M d, Y', strtotime($req['requested_date'])); ?></strong>
                                <span class="badge bg-<?php echo $status_class; ?> ms-2"><?php echo $status_text; ?></span>
                            </div>
                            <small class="text-muted"><?php echo date('M d, Y', strtotime($req['created_at'])); ?></small>
                        </div>
                        <p class="mb-0 mt-2"><strong>Reason:</strong> <?php echo ucfirst($req['reason_code']); ?></p>
                        <?php if (!empty($req['reason_notes'])): ?>
                            <p class="mb-0 text-muted small mt-1"><?php echo htmlspecialchars($req['reason_notes']); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Status History -->
            <div class="info-card">
                <h6 class="info-title">
                    <i class="bi bi-clock-history me-2"></i>Status History
                </h6>
                <div class="info-content">
                    <?php if ($history_result && mysqli_num_rows($history_result) > 0): ?>
                        <div class="history-timeline">
                            <?php while($history = mysqli_fetch_assoc($history_result)): ?>
                            <div class="history-item">
                                <div class="history-badge">
                                    <i class="bi bi-record-circle"></i>
                                </div>
                                <div class="history-content">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>
                                                <?php echo $history['old_status'] ?: 'New'; ?> 
                                                <i class="bi bi-arrow-right mx-2"></i> 
                                                <?php echo $history['new_status']; ?>
                                            </strong>
                                        </div>
                                        <small class="text-muted"><?php echo date('M d, H:i', strtotime($history['changed_at'])); ?></small>
                                    </div>
                                    <p class="mb-0 small text-muted">
                                        by <?php echo htmlspecialchars($history['changed_by_name']); ?>
                                        <?php if (!empty($history['notes'])): ?>
                                            <br><i class="bi bi-chat me-1"></i><?php echo htmlspecialchars($history['notes']); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-3">No status history available.</p>
                    <?php endif; ?>
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
                        Engagement Tips
                    </h6>
                    <p class="text-white-50 small mb-md-0">
                        ✅ Update status regularly to keep track of progress<br>
                        ✅ Upload evidence immediately after completing tasks<br>
                        ✅ Request deadline extensions early if needed<br>
                        ✅ Add comments to communicate with team members
                    </p>
                </div>
                <div class="col-md-3 text-md-end">
                    <i class="bi bi-briefcase display-4 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.status-banner {
    background: #f8f9fa;
    border-radius: 16px;
    padding: 20px;
    border-left: 4px solid;
    border-left-color: <?php 
        echo $engagement['status'] == 'IN_PROGRESS' ? '#0d6efd' : 
            ($engagement['status'] == 'AWAITING_REVIEW' ? '#ffc107' : 
            ($engagement['status'] == 'SUBMITTED' ? '#198754' : 
            ($engagement['status'] == 'CLOSED' ? '#212529' : '#6c757d'))); 
    ?>;
}

.points-awarded {
    background: #d4edda;
    border-radius: 12px;
    padding: 10px 15px;
    display: inline-block;
}

.points-label {
    display: block;
    font-size: 0.8rem;
    color: #155724;
}

.points-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: #155724;
    line-height: 1.2;
}

.info-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 15px;
    height: fit-content;
}

.info-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #dee2e6;
}

.info-content {
    margin-bottom: 0;
}

.info-content p:last-child {
    margin-bottom: 0;
}

.date-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
    text-align: center;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.date-label {
    display: block;
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 5px;
}

.date-value {
    font-size: 1rem;
    font-weight: 600;
    color: #2c3e50;
}

.points-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-top: 15px;
}

.points-tier {
    background: white;
    border-radius: 8px;
    padding: 10px;
    text-align: center;
}

.tier-label {
    display: block;
    font-size: 0.7rem;
    color: #6c757d;
    margin-bottom: 5px;
}

.tier-value {
    font-size: 1.2rem;
    font-weight: 600;
    color: #f1bf70;
}

.evidence-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.evidence-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: white;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.evidence-item .file-icon {
    font-size: 1.3rem;
    color: #f1bf70;
}

.file-info {
    flex: 1;
}

.file-name {
    font-weight: 500;
    display: block;
    margin-bottom: 3px;
}

.comment-item {
    padding: 12px;
    border-bottom: 1px solid #dee2e6;
}

.comment-item:last-child {
    border-bottom: none;
}

.request-item {
    background: white;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    border: 1px solid #dee2e6;
}

.request-item:last-child {
    margin-bottom: 0;
}

.history-timeline {
    position: relative;
    padding-left: 20px;
}

.history-item {
    position: relative;
    margin-bottom: 15px;
    display: flex;
    gap: 15px;
}

.history-item:last-child {
    margin-bottom: 0;
}

.history-badge {
    position: absolute;
    left: -20px;
    width: 24px;
    height: 24px;
    background: white;
    border: 2px solid #f1bf70;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #f1bf70;
    z-index: 1;
}

.history-content {
    flex: 1;
    background: white;
    border-radius: 8px;
    padding: 12px;
    margin-left: 10px;
}

.pro-tip-card {
    background: linear-gradient(135deg, #2c3e50 0%, #1a2634 100%);
    border-radius: 16px;
    padding: 20px;
    color: white;
}

@media (max-width: 768px) {
    .points-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .status-banner .row {
        gap: 15px;
    }
    
    .points-awarded {
        text-align: left;
    }
}
</style>

<script>
function addComment(event, engagementId) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    const commentTextInput = document.getElementById('commentText');
    const commentText = commentTextInput.value.trim();
    
    if (!commentText) {
        alert('Please enter a comment');
        return false;
    }
    
    // Disable the submit button to prevent multiple submissions
    const submitBtn = event ? event.target.querySelector('button[type="submit"]') : document.querySelector('.comment-form button[type="submit"]');
    if (!submitBtn) return false;
    
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Sending...';
    
    fetch('includes/ajax/add_comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'engagement_id=' + engagementId + '&comment=' + encodeURIComponent(commentText)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Clear input
            commentTextInput.value = '';
            // Get the comments list container
            const commentsList = document.getElementById('commentsList');
            if (commentsList) {
                // Create new comment element
                const commentDiv = document.createElement('div');
                commentDiv.className = 'comment-item';
                commentDiv.style.opacity = '0';
                commentDiv.style.transform = 'translateY(-10px)';
                commentDiv.style.transition = 'all 0.3s ease';
                const userName = data.user_name || 'You';
                const dateString = data.created_at || new Date().toLocaleString('en-US', { month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' });
                commentDiv.innerHTML = `
                    <div class="d-flex justify-content-between">
                        <strong>${escapeHtml(userName)}</strong>
                        <small class="text-muted">${escapeHtml(dateString)}</small>
                    </div>
                    <p class="mb-0 mt-1">${escapeHtml(commentText)}</p>
                `;
                // Remove 'No comments yet' message if present
                const noComments = commentsList.querySelector('.text-muted.text-center');
                if (noComments && noComments.innerText.includes('No comments yet')) {
                    noComments.remove();
                }
                // Insert at the top of the list
                commentsList.insertBefore(commentDiv, commentsList.firstChild);
                // Animate the new comment
                setTimeout(() => {
                    commentDiv.style.opacity = '1';
                    commentDiv.style.transform = 'translateY(0)';
                }, 10);
            }
        } else {
            alert('Error adding comment: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding comment. Please try again.');
    })
    .finally(() => {
        // Re-enable the submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
    
    return false;
}

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function scrollToComments() {
    var commentsSection = document.getElementById('commentsSection');
    if (commentsSection) {
        commentsSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(function() {
            var commentInput = document.getElementById('commentText');
            if (commentInput) commentInput.focus();
        }, 400);
    }
}
</script>