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

// Fetch all documents for this engagement (assignment-specific)
$docs_query = "SELECT * FROM client_files WHERE engagement_id = $engagement_id ORDER BY uploaded_at DESC";
$docs_result = mysqli_query($connection, $docs_query);

// Fetch all documents uploaded by this client (across all their engagements)
$client_id = (int)$engagement['client_id'];
$client_docs_query = "SELECT * FROM client_files WHERE client_id = $client_id ORDER BY uploaded_at DESC";
$client_docs_result = mysqli_query($connection, $client_docs_query);

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
            <div class="alert <?php
                if ($engagement['status'] == 'CLOSED') echo 'alert-success';
                elseif ($engagement['status'] == 'SUBMITTED') echo 'alert-info';
                elseif ($engagement['status'] == 'AWAITING_REVIEW') echo 'alert-warning';
                elseif ($engagement['status'] == 'REJECTED' || $engagement['status'] == 'EVIDENCE_REJECTED') echo 'alert-danger evidence-rejected-banner';
                elseif ($engagement['status'] == 'EVIDENCE_APPROVED') echo 'alert-success evidence-approved-banner';
                else echo 'alert-primary';
            ?> d-flex justify-content-between align-items-center">
                <div>
                    <strong>Status:</strong> 
                    <span class="status-badge 
                        <?php if ($engagement['status'] == 'EVIDENCE_APPROVED') echo 'evidence-approved'; ?>
                        <?php if ($engagement['status'] == 'EVIDENCE_REJECTED') echo 'evidence-rejected'; ?>
                    ">
                        <?php echo str_replace('_', ' ', $engagement['status']); ?>
                    </span>
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

            <?php if ($engagement['is_recurring']): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-link-45deg me-2"></i>Recurring Chain</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-center flex-wrap">
                        <?php
                        // Get all engagements in this recurring chain
                        $chain_query = "SELECT engagement_id, title, start_date, status 
                                       FROM engagements 
                                       WHERE (engagement_id = {$engagement['engagement_id']} 
                                          OR parent_engagement_id = {$engagement['engagement_id']}
                                          OR engagement_id = (SELECT parent_engagement_id FROM engagements WHERE engagement_id = {$engagement['engagement_id']}))
                                       ORDER BY start_date ASC";
                        $chain_result = mysqli_query($connection, $chain_query);
                        $chain = [];
                        while ($c = mysqli_fetch_assoc($chain_result)) {
                            $chain[] = $c;
                        }
                        foreach ($chain as $index => $item):
                            $is_current = ($item['engagement_id'] == $engagement['engagement_id']);
                            $status_class = $is_current ? 'primary' : ($item['status'] == 'CLOSED' ? 'success' : 'secondary');
                        ?>
                            <div class="text-center mx-2">
                                <div class="chain-node bg-<?php echo $status_class; ?> text-white p-3 rounded-circle mb-2" 
                                     style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <?php echo $index + 1; ?>
                                </div>
                                <small class="d-block"><?php echo date('M Y', strtotime($item['start_date'])); ?></small>
                                <?php if ($is_current): ?>
                                    <span class="badge bg-primary">Current</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($index < count($chain) - 1): ?>
                                <i class="bi bi-arrow-right fs-4 mx-2"></i>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

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

            <!-- Comments Section -->
            <?php
            // Fetch comments for this engagement
            $comments_query = "SELECT c.*, 
                              CONCAT(u.first_name, ' ', u.last_name) as user_name,
                              u.user_image,
                              u.user_email
                              FROM task_comments c
                              LEFT JOIN users u ON c.user_id = u.user_id
                              WHERE c.engagement_id = $engagement_id
                              ORDER BY c.created_at DESC";
            $comments_result = mysqli_query($connection, $comments_query);
            // Handle new comment submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
                $comment_text = mysqli_real_escape_string($connection, trim($_POST['comment_text']));
                $user_id = (int)$_SESSION['user_id'];
                if (!empty($comment_text)) {
                    $insert_query = "INSERT INTO task_comments (engagement_id, user_id, comment, created_at) 
                                   VALUES ($engagement_id, $user_id, '$comment_text', NOW())";
                    if (mysqli_query($connection, $insert_query)) {
                        echo '<script>window.location.reload();</script>';
                        exit();
                    }
                }
            }
            ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Comments & Discussion</h6>
                    <span class="badge bg-primary"><?php echo mysqli_num_rows($comments_result); ?> comments</span>
                </div>
                <div class="card-body">
                    <!-- Add Comment Form -->
                    <form method="POST" action="" class="mb-4">
                        <div class="input-group">
                            <input type="text" name="comment_text" class="form-control" 
                                   placeholder="Type your comment here..." required maxlength="1000">
                            <button type="submit" name="submit_comment" class="btn btn-primary">
                                <i class="bi bi-send me-1"></i>Post Comment
                            </button>
                        </div>
                        <small class="text-muted mt-1"><i class="bi bi-info-circle"></i> Maximum 1000 characters</small>
                    </form>
                    <!-- Comments List -->
                    <?php if ($comments_result && mysqli_num_rows($comments_result) > 0): ?>
                        <div class="comments-timeline">
                            <?php while($comment = mysqli_fetch_assoc($comments_result)):
                                $is_admin = ($comment['user_id'] != $engagement['client_id']);
                                $avatar_url = !empty($comment['user_image']) 
                                    ? '../images/' . $comment['user_image'] 
                                    : 'https://ui-avatars.com/api/?name=' . urlencode($comment['user_name'] ?? 'User') . '&background=f1bf70&color=0a2240&size=40';
                            ?>
                            <div class="comment-item mb-3">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <img src="<?php echo $avatar_url; ?>" 
                                             alt="<?php echo htmlspecialchars($comment['user_name'] ?? 'User'); ?>"
                                             class="rounded-circle" width="40" height="40"
                                             onerror="this.src='https://ui-avatars.com/api/?name=User&background=f1bf70&color=0a2240&size=40'">
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <div>
                                                <strong class="me-2"><?php echo htmlspecialchars($comment['user_name'] ?? 'Unknown User'); ?></strong>
                                                <?php if ($is_admin): ?>
                                                    <span class="badge bg-primary-soft text-primary" style="background: rgba(241,191,112,0.1);">Operations</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success-soft text-success" style="background: rgba(40,167,69,0.1);">Client</span>
                                                <?php endif; ?>
                                            </div>
                                            <small class="text-muted">
                                                <i class="bi bi-clock me-1"></i>
                                                <?php echo date('M d, Y H:i', strtotime($comment['created_at'])); ?>
                                            </small>
                                        </div>
                                        <div class="comment-content p-3 rounded" style="background: #f8f9fa;">
                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-chat display-4 text-muted"></i>
                            <p class="text-muted mt-2">No comments yet. Start the discussion!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
<style>
/* Comments Section Styles */
.comments-timeline {
    max-height: 500px;
    overflow-y: auto;
    padding-right: 10px;
}

.comment-item {
    transition: transform 0.2s;
}

.comment-item:hover {
    transform: translateX(5px);
}

.comment-content {
    border-left: 3px solid #f1bf70;
    transition: all 0.2s;
}

.comment-content:hover {
    background: #ffffff !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

/* Custom badge backgrounds */
.bg-primary-soft {
    background: rgba(241, 191, 112, 0.1);
}
.bg-success-soft {
    background: rgba(40, 167, 69, 0.1);
}

/* Scrollbar styling */
.comments-timeline::-webkit-scrollbar {
    width: 6px;
}

.comments-timeline::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.comments-timeline::-webkit-scrollbar-thumb {
    background: #f1bf70;
    border-radius: 10px;
}

.comments-timeline::-webkit-scrollbar-thumb:hover {
    background: #e5b465;
}

/* Evidence status custom styles */
.evidence-approved-banner {
    background: #e6f9ed !important;
    color: #198754 !important;
    border-color: #b6f2d2 !important;
}
.evidence-rejected-banner {
    background: #fdeaea !important;
    color: #dc3545 !important;
    border-color: #f5bcbc !important;
}
.status-badge.evidence-approved {
    background: #198754;
    color: #fff;
    border-radius: 6px;
    padding: 2px 10px;
    margin-left: 4px;
    font-weight: 600;
    letter-spacing: 0.5px;
}
.status-badge.evidence-rejected {
    background: #dc3545;
    color: #fff;
    border-radius: 6px;
    padding: 2px 10px;
    margin-left: 4px;
    font-weight: 600;
    letter-spacing: 0.5px;
}
</style>

            <!-- Evidence Section -->
                        <!-- Assignment/Engagement Documents Section -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="bi bi-folder2-open me-2"></i>Documents for This Engagement</h6>
                            </div>
                            <div class="card-body">
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
                                                        <a href="../uploads/client_files/<?php echo rawurlencode($doc['file_path']); ?>" class="btn btn-outline-primary btn-sm" target="_blank">View</a>
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
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="bi bi-folder-symlink me-2"></i>All Documents Uploaded by This Client</h6>
                            </div>
                            <div class="card-body">
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
                                                            <a href="view_engagement.php?id=<?php echo (int)$doc['engagement_id']; ?>">#<?php echo (int)$doc['engagement_id']; ?></a>
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
                                                        <a href="../uploads/client_files/<?php echo rawurlencode($doc['file_path']); ?>" class="btn btn-outline-primary btn-sm" target="_blank">View</a>
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
                        <div class="row g-3">
                            <?php while($evidence = mysqli_fetch_assoc($evidence_result)): ?>
                            <div class="col-md-4 col-lg-3">
                                <div class="card h-100 shadow-sm border">
                                    <div class="card-body p-2 d-flex flex-column align-items-center justify-content-center">
                                        <?php
                                        $file_path = '../uploads/evidence/' . $evidence['file_path'];
                                        $file_ext = strtolower(pathinfo($evidence['file_name'], PATHINFO_EXTENSION));
                                        $is_image = in_array($file_ext, ['jpg','jpeg','png','gif','bmp','webp']);
                                        $is_pdf = ($file_ext === 'pdf');
                                        ?>
                                        <div class="mb-2" style="width:100%;height:120px;display:flex;align-items:center;justify-content:center;background:#f8f9fa;border-radius:6px;overflow:hidden;">
                                            <?php if ($is_image): ?>
                                                <img src="<?php echo $file_path; ?>" alt="Preview" style="max-width:100%;max-height:100%;object-fit:contain;">
                                            <?php elseif ($is_pdf): ?>
                                                <embed src="<?php echo $file_path; ?>#toolbar=0&navpanes=0&scrollbar=0" type="application/pdf" width="100%" height="100%" style="min-height:100px;max-height:120px;" />
                                            <?php else: ?>
                                                <i class="bi bi-file-earmark-text fs-1 text-secondary"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="w-100 text-truncate mb-1" title="<?php echo htmlspecialchars($evidence['file_name']); ?>">
                                            <strong><?php echo htmlspecialchars($evidence['file_name']); ?></strong>
                                        </div>
                                        <div class="small text-muted mb-1">By <?php echo htmlspecialchars($evidence['uploaded_by_name']); ?></div>
                                        <div class="small text-muted mb-2"><?php echo date('M d, Y H:i', strtotime($evidence['uploaded_at'])); ?></div>
                                        <div class="mb-2">
                                            <?php 
                                            $status = $evidence['status'] ?? 'PENDING';
                                            if ($status === 'APPROVED'): ?>
                                                <span class="badge bg-success">Approved</span>
                                            <?php elseif ($status === 'REJECTED'): ?>
                                                <span class="badge bg-danger">Rejected</span>
                                                <?php
                                                // Fetch latest rejection reason from evidence_approval_history
                                                $reason = '';
                                                $eid = (int)$evidence['evidence_id'];
                                                $hist_q = "SELECT reason FROM evidence_approval_history WHERE evidence_id = $eid AND action = 'REJECTED' ORDER BY reviewed_at DESC, history_id DESC LIMIT 1";
                                                $hist_r = mysqli_query($connection, $hist_q);
                                                if ($hist_r && $hist_row = mysqli_fetch_assoc($hist_r)) {
                                                    $reason = $hist_row['reason'];
                                                }
                                                if (!empty($reason)) {
                                                    $trunc = (mb_strlen($reason) > 60) ? mb_substr($reason, 0, 60) . '…' : $reason;
                                                    echo '<br><small class="text-muted" title="' . htmlspecialchars($reason) . '">Reason: ' . htmlspecialchars($trunc) . '</small>';
                                                }
                                                ?>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="d-flex gap-2 w-100 justify-content-center">
                                            <a href="<?php echo $file_path; ?>" class="btn btn-outline-primary btn-sm" target="_blank">Click to review</a>
                                            <?php if ($status === 'PENDING'): ?>
                                                <button class="btn btn-success btn-sm approve-evidence-btn" data-evidence-id="<?php echo $evidence['evidence_id']; ?>">Approve</button>
                                                <button class="btn btn-danger btn-sm reject-evidence-btn" data-evidence-id="<?php echo $evidence['evidence_id']; ?>">Reject</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
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
                                        <?php if ($request['status'] == 'PENDING'): ?>
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

            <!-- Admin Closure Section -->
            <?php 
            // Handle admin closure and points assignment
            if (isset($_POST['close_engagement']) && $engagement['status'] == 'SUBMITTED') {
                $points_awarded = intval($_POST['points_awarded']);
                $closure_notes = mysqli_real_escape_string($connection, trim($_POST['closure_notes'] ?? ''));
                
                // Update engagement
                $update_query = "UPDATE engagements SET 
                     status = 'CLOSED',
                     points_awarded = $points_awarded,
                     completion_date = NOW()
                     WHERE engagement_id = $engagement_id";
                
                if (mysqli_query($connection, $update_query)) {
                    // Add points to ledger
                    $ledger_query = "INSERT INTO points_ledger 
                        (employee_id, source_type, source_id, points, points_type, description, notes, awarded_by, created_by)
                        VALUES (
                            {$engagement['assigned_to']}, 
                            'ENGAGEMENT', 
                            $engagement_id, 
                            $points_awarded, 
                            'EARNED', 
                            'Points awarded for completing engagement: {$engagement['title']}', 
                            '" . mysqli_real_escape_string($connection, $closure_notes) . "',
                            {$_SESSION['user_id']},
                            {$_SESSION['user_id']}
                        )";
                    mysqli_query($connection, $ledger_query);
                    
                    // Add status history
                    $history_query = "INSERT INTO engagement_status_history 
                         (engagement_id, old_status, new_status, changed_by, notes)
                         VALUES ($engagement_id, 'SUBMITTED', 'CLOSED', {$_SESSION['user_id']}, 'Engagement closed by admin. Points awarded: $points_awarded')";
                    mysqli_query($connection, $history_query);
                    
                    echo '<script>window.location.reload();</script>';
                    exit();
                }
            }

            // Calculate suggested points based on deadline
            $deadline = strtotime($engagement['approved_deadline'] ?? $engagement['original_deadline']);
            $now = time();
            $delay_days = max(0, floor(($now - $deadline) / (60 * 60 * 24)));

            $suggested_points = $engagement['base_points'];
            if ($delay_days == 0) {
                $suggested_points = $engagement['points_within_deadline'] ?? $engagement['base_points'];
            } elseif ($delay_days >= 5 && $delay_days <= 15) {
                $suggested_points = $engagement['points_tier_1'] ?? $engagement['base_points'] * 0.7;
            } elseif ($delay_days >= 16 && $delay_days <= 25) {
                $suggested_points = $engagement['points_tier_2'] ?? $engagement['base_points'] * 0.5;
            } elseif ($delay_days > 25) {
                $suggested_points = $engagement['points_tier_3'] ?? $engagement['base_points'] * 0.3;
            }
            ?>

            <?php if ($engagement['status'] == 'SUBMITTED'): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header text-white">
                    <h6 class="mb-0"><i class="bi bi-check2-circle me-2"></i>Admin: Close Engagement & Award Points</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        This engagement has been submitted for review. Please review all evidence and documentation before closing.
                    </div>
                    
                    <!-- Evidence Summary -->
                    <?php 
                    $evidence_stats = ['total' => 0, 'approved_count' => 0, 'rejected_count' => 0];
                    $evidence_check = "SELECT COUNT(*) as total, 
                   SUM(CASE WHEN status = 'APPROVED' THEN 1 ELSE 0 END) as approved_count,
                   SUM(CASE WHEN status = 'REJECTED' THEN 1 ELSE 0 END) as rejected_count
                   FROM evidence WHERE engagement_id = $engagement_id";
                    $evidence_result = mysqli_query($connection, $evidence_check);
                    if ($evidence_result) $evidence_stats = mysqli_fetch_assoc($evidence_result);
                    ?>
                    <?php if ($evidence_stats['total'] > 0): ?>
                    <div class="mb-4">
                        <strong>Evidence Summary:</strong>
                        <div class="mt-2">
                            <span class="badge bg-success me-2">Approved: <?php echo $evidence_stats['approved_count']; ?></span>
                            <span class="badge bg-danger me-2">Rejected: <?php echo $evidence_stats['rejected_count']; ?></span>
                            <span class="badge bg-warning">Pending: <?php echo $evidence_stats['total'] - $evidence_stats['approved_count'] - $evidence_stats['rejected_count']; ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="closeEngagementForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="points_awarded" class="form-label">Points to Award</label>
                                    <input type="number" class="form-control" id="points_awarded" name="points_awarded" 
                                           value="<?php echo $suggested_points; ?>" min="0" required>
                                    <small class="text-muted">
                                        Base Points: <?php echo $engagement['base_points']; ?> | 
                                        On-Time: <?php echo $engagement['points_within_deadline'] ?? $engagement['base_points']; ?> |
                                        Delay: <?php echo $delay_days; ?> days
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="closure_notes" class="form-label">Closure Notes (Optional)</label>
                                    <textarea class="form-control" id="closure_notes" name="closure_notes" rows="2" 
                                              placeholder="Add any notes about this closure..."></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> Once closed, this engagement cannot be reopened. Points will be awarded to the employee.
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" name="close_engagement" class="btn btn-success btn-lg" 
                                    onclick="return confirm('Are you sure you want to close this engagement? Points will be awarded and the engagement cannot be reopened.')">
                                <i class="bi bi-check2-circle me-2"></i>Close Engagement & Award Points
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            <!-- END Admin Closure Section -->
        </div>
    </div>
</div>

<!-- Approve Success Modal -->
<div class="modal fade" id="approveSuccessModal" tabindex="-1" aria-labelledby="approveSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approveSuccessModalLabel">Evidence Approved</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="bi bi-check-circle-fill text-success fs-1 mb-2"></i>
                    <p class="mb-0">The evidence has been approved successfully.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Reason Modal -->
<div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-labelledby="rejectReasonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectReasonModalLabel">Reject Evidence</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectReasonForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rejectionReason" class="form-label">Please provide a reason for rejection:</label>
                        <textarea class="form-control" id="rejectionReason" name="reason" rows="3" required></textarea>
                        <input type="hidden" id="rejectEvidenceId" name="evidence_id" value="">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Submit Rejection</button>
                </div>
            </form>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Approve evidence
    document.querySelectorAll('.approve-evidence-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const evidenceId = this.getAttribute('data-evidence-id');
            if (!evidenceId) return;
            
            fetch('includes/ajax/evidence_review.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ evidence_id: evidenceId, action: 'APPROVED' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const modal = new bootstrap.Modal(document.getElementById('approveSuccessModal'));
                    modal.show();
                    setTimeout(() => { location.reload(); }, 1500);
                } else {
                    alert(data.message || 'Approval failed.');
                }
            })
            .catch(() => alert('Approval failed.'));
        });
    });

    // Reject evidence - show modal
    document.querySelectorAll('.reject-evidence-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const evidenceId = this.getAttribute('data-evidence-id');
            document.getElementById('rejectEvidenceId').value = evidenceId;
            document.getElementById('rejectionReason').value = '';
            
            const modal = new bootstrap.Modal(document.getElementById('rejectReasonModal'));
            modal.show();
        });
    });

    // Handle reject reason form submission
    const rejectForm = document.getElementById('rejectReasonForm');
    if (rejectForm) {
        rejectForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const evidenceId = document.getElementById('rejectEvidenceId').value;
            const reason = document.getElementById('rejectionReason').value.trim();
            
            if (!reason) {
                alert('Please provide a reason for rejection.');
                return;
            }
            
            fetch('includes/ajax/evidence_review.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ 
                    evidence_id: evidenceId, 
                    action: 'REJECTED', 
                    reason: reason 
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const modalEl = document.getElementById('rejectReasonModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    
                    // Add one-time event listener for modal hidden
                    const handler = function() {
                        alert('Evidence rejected.');
                        location.reload();
                        modalEl.removeEventListener('hidden.bs.modal', handler);
                    };
                    
                    modalEl.addEventListener('hidden.bs.modal', handler);
                    modal.hide();
                } else {
                    alert(data.message || 'Rejection failed.');
                }
            })
            .catch(() => alert('Rejection failed.'));
        });
    }
});
</script>