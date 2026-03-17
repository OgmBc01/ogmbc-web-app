<?php
// client/includes/view_engagement_details.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../includes/database.php';

if (!isset($_SESSION['client_id'])) {
    header('Location: ../login.php');
    exit();
}

$client_id = (int)$_SESSION['client_id'];
$engagement_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$engagement_id) {
    echo '<div class="alert alert-danger">Invalid engagement ID.</div>';
    exit();
}

// Handle new comment submission - MOVED TO TOP BEFORE ANY HTML OUTPUT
$comment_submitted = false;
$comment_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_submit'])) {
    if (isset($_POST['comment']) && trim($_POST['comment']) !== '') {
        $comment = mysqli_real_escape_string($connection, trim($_POST['comment']));
        $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        
        if ($user_id > 0 && $engagement_id > 0) {
            $insert = "INSERT INTO task_comments (engagement_id, user_id, comment, created_at) 
                       VALUES ($engagement_id, $user_id, '$comment', NOW())";
            if (mysqli_query($connection, $insert)) {
                $comment_submitted = true;
            } else {
                $comment_error = 'Failed to add comment: ' . mysqli_error($connection);
            }
        }
    } else {
        $comment_error = 'Comment cannot be empty';
    }
}

// Fetch engagement details
$query = "SELECT e.*, s.service_name, 
          CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name, 
          u.user_email as assigned_email 
          FROM engagements e 
          JOIN service_types s ON e.service_id = s.service_id 
          LEFT JOIN users u ON e.assigned_to = u.user_id 
          WHERE e.engagement_id = $engagement_id AND e.client_id = $client_id LIMIT 1";
$result = mysqli_query($connection, $query);
if (!$result || mysqli_num_rows($result) == 0) {
    echo '<div class="alert alert-danger">Engagement not found or access denied.</div>';
    exit();
}
$eng = mysqli_fetch_assoc($result);

// Comments (from task_comments)

$comments = [];
$comments_query = "SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) as user_name
                   FROM task_comments c
                   LEFT JOIN users u ON c.user_id = u.user_id
                   WHERE c.engagement_id = $engagement_id
                   ORDER BY c.created_at ASC";
$comments_result = mysqli_query($connection, $comments_query);
if ($comments_result) {
    while ($row = mysqli_fetch_assoc($comments_result)) {
        $comments[] = $row;
    }

}

// Deadline change requests
$deadlines = [];
$dl_query = "SELECT d.*, 
             CONCAT(u.first_name, ' ', u.last_name) as requested_by_name, 
             CONCAT(r.first_name, ' ', r.last_name) as reviewed_by_name 
             FROM deadline_change_requests d 
             LEFT JOIN users u ON d.requested_by = u.user_id 
             LEFT JOIN users r ON d.reviewed_by = r.user_id 
             WHERE d.engagement_id = $engagement_id 
             ORDER BY d.created_at DESC";
$dl_result = mysqli_query($connection, $dl_query);
if ($dl_result) {
    while ($row = mysqli_fetch_assoc($dl_result)) {
        $deadlines[] = $row;
    }
}

// Files uploaded (client_files)
$files = [];
$file_query = "SELECT * FROM client_files WHERE engagement_id = $engagement_id ORDER BY uploaded_at DESC";
$file_result = mysqli_query($connection, $file_query);
if ($file_result) {
    while ($row = mysqli_fetch_assoc($file_result)) {
        $files[] = $row;
    }
}

// Evidence
$evidence = [];
$ev_query = "SELECT e.*, CONCAT(u.first_name, ' ', u.last_name) as uploaded_by_name 
             FROM evidence e 
             LEFT JOIN users u ON e.uploaded_by = u.user_id 
             WHERE e.engagement_id = $engagement_id 
             ORDER BY e.uploaded_at DESC";
$ev_result = mysqli_query($connection, $ev_query);
if ($ev_result) {
    while ($row = mysqli_fetch_assoc($ev_result)) {
        $evidence[] = $row;
    }
}

// Calculate deadline status
$deadline_date = $eng['approved_deadline'] ?: $eng['original_deadline'];
$days_remaining = floor((strtotime($deadline_date) - time()) / (60 * 60 * 24));
$deadline_class = 'success';
$deadline_text = 'On Track';
if ($days_remaining < 0) {
    $deadline_class = 'danger';
    $deadline_text = 'Overdue by ' . abs($days_remaining) . ' days';
} elseif ($days_remaining <= 3) {
    $deadline_class = 'warning';
    $deadline_text = 'Due in ' . $days_remaining . ' days';
}
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Engagement Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="engagements.php">My Engagements</a></li>
                    <li class="breadcrumb-item active">#<?php echo $engagement_id; ?></li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="engagements.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Engagements
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if ($comment_submitted): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>Comment added successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($comment_error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?php echo $comment_error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Engagement Header Card -->
    <div class="card shadow-sm mb-4" style="border-left: 4px solid #f1bf70;">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="mb-2" style="color: #0a2240;"><?php echo htmlspecialchars($eng['title']); ?></h4>
                    <div class="d-flex flex-wrap gap-3">
                        <span class="badge bg-<?php 
                            echo $eng['status'] == 'CLOSED' ? 'dark' : 
                                ($eng['status'] == 'SUBMITTED' ? 'success' : 
                                ($eng['status'] == 'AWAITING_REVIEW' ? 'warning' : 
                                ($eng['status'] == 'IN_PROGRESS' ? 'primary' : 'info'))); 
                        ?> px-3 py-2">Status: <?php echo $eng['status']; ?></span>
                        
                        <span class="badge bg-<?php echo $deadline_class; ?> px-3 py-2">
                            <i class="bi bi-clock me-1"></i><?php echo $deadline_text; ?>
                        </span>
                        
                        <span class="text-muted">
                            <i class="bi bi-calendar3 me-1"></i>Deadline: <?php echo date('M d, Y', strtotime($deadline_date)); ?>
                        </span>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="p-3 rounded" style="background: #f8f9fa;">
                        <small class="text-muted d-block">Service Type</small>
                        <strong class="fs-5" style="color: #0a2240;"><?php echo htmlspecialchars($eng['service_name']); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignment Info -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header" style="background: #0a2240; color: #f1bf70;">
                    <h6 class="mb-0"><i class="bi bi-person-badge me-2"></i>Assignment Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="120" class="text-muted">Assigned To:</td>
                            <td class="fw-bold"><?php echo htmlspecialchars($eng['assigned_to_name']); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Email:</td>
                            <td><a href="mailto:<?php echo $eng['assigned_email']; ?>" style="color: #f1bf70;"><?php echo $eng['assigned_email']; ?></a></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Assigned On:</td>
                            <td><?php echo date('M d, Y', strtotime($eng['assigned_at'])); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header" style="background: #0a2240; color: #f1bf70;">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Quick Info</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="120" class="text-muted">Start Date:</td>
                            <td><?php echo date('M d, Y', strtotime($eng['start_date'])); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Original Deadline:</td>
                            <td><?php echo date('M d, Y', strtotime($eng['original_deadline'])); ?></td>
                        </tr>
                        <?php if ($eng['approved_deadline']): ?>
                        <tr>
                            <td class="text-muted">Approved Deadline:</td>
                            <td class="text-success"><?php echo date('M d, Y', strtotime($eng['approved_deadline'])); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($eng['points_awarded']): ?>
                        <tr>
                            <td class="text-muted">Points Awarded:</td>
                            <td><span class="badge bg-success"><?php echo $eng['points_awarded']; ?> pts</span></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Description -->
    <?php if (!empty($eng['description'])): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header" style="background: #0a2240; color: #f1bf70;">
            <h6 class="mb-0"><i class="bi bi-file-text me-2"></i>Description</h6>
        </div>
        <div class="card-body">
            <p class="mb-0"><?php echo nl2br(htmlspecialchars($eng['description'])); ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Comments and Deadline Requests -->
    <div class="row g-4 mb-4">
        <!-- Comments -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center" style="background: #0a2240; color: #f1bf70;">
                    <h6 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Comments</h6>
                    <span class="badge bg-light text-dark"><?php echo count($comments); ?> total</span>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <!-- Add Comment Form - FIXED: Added proper action and method -->
                    <form id="addCommentForm" class="mb-3" method="post" action="">
                        <input type="hidden" name="comment_submit" value="1">
                        <div class="input-group">
                            <input type="text" name="comment" id="commentInput" class="form-control" placeholder="Add a comment..." required maxlength="1000">
                            <button class="btn btn-primary" type="submit" style="background: #f1bf70; border-color: #f1bf70; color: #0a2240;">
                                <i class="bi bi-send me-1"></i>Post
                            </button>
                        </div>
                    </form>
                    
                    <?php if (count($comments) > 0): ?>
                        <div class="timeline">
                            <?php foreach ($comments as $c): ?>
                            <div class="communication-item mb-3">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <div></div>
                                    <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($c['created_at'])); ?></small>
                                </div>
                                <p class="fw-bold text-primary mb-1" style="font-size: 1.08rem; color: #0a2240 !important;">
                                    <?php echo htmlspecialchars($c['user_name']); ?>
                                </p>
                                <div class="bg-light p-2 rounded mt-1" style="font-size: 1.13rem; color: #222; font-weight: 500;">
                                    <?php echo nl2br(htmlspecialchars($c['comment'])); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-chat display-4 text-muted"></i>
                            <p class="text-muted mt-2">No comments yet. Start the conversation!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Deadline Change Requests -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center" style="background: #0a2240; color: #f1bf70;">
                    <h6 class="mb-0"><i class="bi bi-calendar-plus me-2"></i>Deadline Change Requests</h6>
                    <span class="badge bg-light text-dark"><?php echo count($deadlines); ?> total</span>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <?php if (count($deadlines) > 0): ?>
                        <div class="timeline">
                            <?php foreach ($deadlines as $d): ?>
                            <div class="deadline-item mb-3 p-3 rounded" style="background: #f8f9fa;">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-<?php 
                                        echo $d['status'] == 'APPROVED' ? 'success' : 
                                            ($d['status'] == 'REJECTED' ? 'danger' : 'warning'); 
                                    ?>"><?php echo $d['status']; ?></span>
                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($d['created_at'])); ?></small>
                                </div>
                                <p class="mb-2">
                                    <strong>Requested Date:</strong> <?php echo date('M d, Y', strtotime($d['requested_date'])); ?>
                                </p>
                                <p class="small mb-1">
                                    <i class="bi bi-person me-1"></i>By <?php echo htmlspecialchars($d['requested_by_name']); ?>
                                </p>
                                <p class="small mb-2">
                                    <strong>Reason:</strong> <?php echo ucfirst($d['reason_code']); ?>
                                    <?php if (!empty($d['reason_notes'])): ?>
                                    <br><span class="text-muted">"<?php echo htmlspecialchars($d['reason_notes']); ?>"</span>
                                    <?php endif; ?>
                                </p>
                                <?php if ($d['reviewed_by_name']): ?>
                                <div class="border-top pt-2 mt-2 small text-muted">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Reviewed by <?php echo htmlspecialchars($d['reviewed_by_name']); ?> 
                                    on <?php echo date('M d, Y H:i', strtotime($d['reviewed_at'])); ?>
                                    <?php if (!empty($d['review_notes'])): ?>
                                    <br><span class="fst-italic">"<?php echo htmlspecialchars($d['review_notes']); ?>"</span>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-calendar-x display-4 text-muted"></i>
                            <p class="text-muted mt-2">No deadline change requests</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Files and Evidence -->
    <div class="row g-4">
        <!-- Client Files -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center" style="background: #0a2240; color: #f1bf70;">
                    <h6 class="mb-0"><i class="bi bi-files me-2"></i>Files Uploaded</h6>
                    <span class="badge bg-light text-dark"><?php echo count($files); ?> files</span>
                </div>
                <div class="card-body">
                    <?php if (count($files) > 0): ?>
                        <div class="list-group">
                            <?php foreach ($files as $f): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-file-earmark me-2" style="color: #f1bf70;"></i>
                                    <span><?php echo htmlspecialchars($f['file_name']); ?></span>
                                    <span class="badge bg-<?php echo $f['uploaded_by'] == 'client' ? 'primary' : 'secondary'; ?> ms-2">
                                        <?php echo ucfirst($f['uploaded_by']); ?>
                                    </span>
                                    <small class="text-muted d-block mt-1">
                                        <i class="bi bi-calendar3 me-1"></i><?php echo date('M d, Y H:i', strtotime($f['uploaded_at'])); ?>
                                    </small>
                                </div>
                                <a href="../../uploads/client_files/<?php echo rawurlencode($f['file_path']); ?>" 
                                   class="btn btn-sm" style="background: #f1bf70; color: #0a2240;" target="_blank">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-folder-x display-4 text-muted"></i>
                            <p class="text-muted mt-2">No files uploaded</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Evidence -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center" style="background: #0a2240; color: #f1bf70;">
                    <h6 class="mb-0"><i class="bi bi-file-earmark-check me-2"></i>Evidence Uploaded</h6>
                    <span class="badge bg-light text-dark"><?php echo count($evidence); ?> items</span>
                </div>
                <div class="card-body">
                    <?php if (count($evidence) > 0): ?>
                        <div class="list-group">
                            <?php foreach ($evidence as $ev): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-file-earmark-check me-2" style="color: #f1bf70;"></i>
                                    <span><?php echo htmlspecialchars($ev['file_name']); ?></span>
                                    <span class="badge bg-<?php 
                                        echo $ev['status'] == 'APPROVED' ? 'success' : 
                                            ($ev['status'] == 'REJECTED' ? 'danger' : 'warning'); 
                                    ?> ms-2"><?php echo ucfirst($ev['status']); ?></span>
                                    <small class="text-muted d-block mt-1">
                                        <i class="bi bi-person me-1"></i>By <?php echo htmlspecialchars($ev['uploaded_by_name']); ?>
                                        <br><i class="bi bi-calendar3 me-1"></i><?php echo date('M d, Y H:i', strtotime($ev['uploaded_at'])); ?>
                                    </small>
                                </div>
                                <a href="../../uploads/evidence/<?php echo rawurlencode($ev['file_path']); ?>" 
                                   class="btn btn-sm" style="background: #f1bf70; color: #0a2240;" target="_blank">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-file-earmark-x display-4 text-muted"></i>
                            <p class="text-muted mt-2">No evidence uploaded yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Prevent form resubmission on page refresh
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

// Optional: Clear comment input after successful submission
<?php if ($comment_submitted): ?>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('commentInput').value = '';
});
<?php endif; ?>
</script>

<style>
/* Theme Colors */
:root {
    --dark-blue: #0a2240;
    --gold: #f1bf70;
}

/* Page Title */
.page-title {
    color: var(--dark-blue);
    font-weight: 600;
    margin-bottom: 0;
}

/* Breadcrumb */
.breadcrumb {
    background: transparent;
    padding: 0;
    margin-top: 8px;
}
.breadcrumb-item a {
    color: var(--gold);
    text-decoration: none;
}
.breadcrumb-item a:hover {
    text-decoration: underline;
}
.breadcrumb-item.active {
    color: #6c757d;
}

/* Cards */
.card {
    border: none;
    border-radius: 12px;
    overflow: hidden;
}
.card.shadow-sm {
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}
.card-header {
    border-bottom: 1px solid rgba(255,255,255,0.1);
    padding: 1rem 1.25rem;
}
.card-header h6 {
    font-weight: 600;
}

/* Timeline Items */
.communication-item, .deadline-item {
    position: relative;
    padding-left: 15px;
}
.communication-item:not(:last-child)::before,
.deadline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 0;
    top: 24px;
    bottom: -15px;
    width: 2px;
    background: var(--gold);
    opacity: 0.3;
}

/* Badge Styles */
.badge {
    font-size: 0.85rem;
    padding: 0.4rem 0.6rem;
    font-weight: 500;
}

/* List Group */
.list-group-item {
    border: 1px solid rgba(0,0,0,0.05);
    margin-bottom: 8px;
    border-radius: 8px !important;
    transition: transform 0.2s;
}
.list-group-item:hover {
    transform: translateX(5px);
    background: #f8f9fa;
}

/* Buttons */
.btn-outline-secondary {
    border-color: var(--gold);
    color: var(--dark-blue);
}
.btn-outline-secondary:hover {
    background: var(--gold);
    border-color: var(--gold);
    color: var(--dark-blue);
}

/* Primary Button */
.btn-primary {
    background: var(--gold) !important;
    border-color: var(--gold) !important;
    color: var(--dark-blue) !important;
    font-weight: 600;
}
.btn-primary:hover {
    background: #e5b465 !important;
    border-color: #e5b465 !important;
}

/* Status Badges in Header */
.badge.bg-primary { background: var(--dark-blue) !important; }
.badge.bg-success { background: #28a745 !important; }
.badge.bg-warning { background: #ffc107 !important; color: var(--dark-blue); }
.badge.bg-danger { background: #dc3545 !important; }
.badge.bg-info { background: #17a2b8 !important; }

/* Links */
a {
    color: var(--gold);
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}

/* Responsive */
@media (max-width: 768px) {
    .page-title {
        font-size: 1.5rem;
    }
    .card-header {
        flex-direction: column;
        align-items: start !important;
        gap: 10px;
    }
}
</style>