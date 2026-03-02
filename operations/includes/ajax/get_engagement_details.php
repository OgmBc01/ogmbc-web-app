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
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid engagement ID']);
    exit;
}

$engagement_id = (int)$_GET['id'];

// Verify engagement belongs to this user
$check_query = "SELECT engagement_id FROM engagements WHERE engagement_id = $engagement_id AND assigned_to = $user_id";
$check_result = mysqli_query($connection, $check_query);

if (!$check_result || mysqli_num_rows($check_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Engagement not found or access denied']);
    exit;
}

// Get engagement details with all related info
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
    DATEDIFF(COALESCE(e.approved_deadline, e.original_deadline), CURDATE()) as days_remaining
    FROM engagements e
    JOIN clients c ON e.client_id = c.client_id
    JOIN service_types s ON e.service_id = s.service_id
    JOIN service_point_rules r ON e.rule_version_id = r.rule_id
    LEFT JOIN users assigned ON e.assigned_to = assigned.user_id
    LEFT JOIN users reviewer ON e.reviewer_id = reviewer.user_id
    WHERE e.engagement_id = $engagement_id";

$result = mysqli_query($connection, $query);
$engagement = mysqli_fetch_assoc($result);

// Get evidence
$evidence_query = "SELECT * FROM evidence WHERE engagement_id = $engagement_id ORDER BY uploaded_at DESC";
$evidence_result = mysqli_query($connection, $evidence_query);
$evidence = [];
while ($row = mysqli_fetch_assoc($evidence_result)) {
    $evidence[] = $row;
}

// Get comments
$comments_query = "SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) as user_name
                   FROM task_comments c
                   JOIN users u ON c.user_id = u.user_id
                   WHERE c.engagement_id = $engagement_id
                   ORDER BY c.created_at DESC";
$comments_result = mysqli_query($connection, $comments_query);
$comments = [];
while ($row = mysqli_fetch_assoc($comments_result)) {
    $comments[] = $row;
}

// Get deadline change requests
$requests_query = "SELECT * FROM deadline_change_requests WHERE engagement_id = $engagement_id ORDER BY created_at DESC";
$requests_result = mysqli_query($connection, $requests_query);
$requests = [];
while ($row = mysqli_fetch_assoc($requests_result)) {
    $requests[] = $row;
}

ob_start();
?>

<!-- Engagement Details View -->
<div class="engagement-details">
    <!-- Header with Status -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h4 class="mb-2"><?php echo htmlspecialchars($engagement['title']); ?></h4>
            <div class="d-flex gap-2 flex-wrap">
                <span class="badge bg-<?php 
                    echo $engagement['status'] == 'CLOSED' ? 'dark' : 
                        ($engagement['status'] == 'SUBMITTED' ? 'success' : 
                        ($engagement['status'] == 'AWAITING_REVIEW' ? 'warning' : 
                        ($engagement['status'] == 'IN_PROGRESS' ? 'primary' : 'secondary'))); 
                ?> px-3 py-2">
                    <i class="bi bi-<?php 
                        echo $engagement['status'] == 'CLOSED' ? 'check2-all' : 
                            ($engagement['status'] == 'SUBMITTED' ? 'check-circle' : 
                            ($engagement['status'] == 'AWAITING_REVIEW' ? 'clock-history' : 
                            ($engagement['status'] == 'IN_PROGRESS' ? 'play-circle' : 'bell'))); 
                    ?> me-1"></i>
                    <?php echo str_replace('_', ' ', $engagement['status']); ?>
                </span>
                <span class="badge bg-info px-3 py-2">
                    <i class="bi bi-tag me-1"></i>
                    <?php echo htmlspecialchars($engagement['service_name']); ?>
                </span>
                <?php if ($engagement['days_remaining'] < 0 && $engagement['status'] != 'CLOSED'): ?>
                    <span class="badge bg-danger px-3 py-2">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <?php echo abs($engagement['days_remaining']); ?> days overdue
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <span class="text-muted">#<?php echo $engagement['engagement_id']; ?></span>
    </div>

    <!-- Client & Assignment Info -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="info-card">
                <h6 class="info-title"><i class="bi bi-building me-2"></i>Client Information</h6>
                <div class="info-content">
                    <p><strong><?php echo htmlspecialchars($engagement['company_name']); ?></strong></p>
                    <p class="mb-1"><i class="bi bi-person me-2 text-muted"></i><?php echo htmlspecialchars($engagement['contact_name']); ?></p>
                    <p class="mb-1"><i class="bi bi-envelope me-2 text-muted"></i><?php echo htmlspecialchars($engagement['contact_email']); ?></p>
                    <p class="mb-0"><i class="bi bi-telephone me-2 text-muted"></i><?php echo htmlspecialchars($engagement['contact_mobile']); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-card">
                <h6 class="info-title"><i class="bi bi-people me-2"></i>Assignment</h6>
                <div class="info-content">
                    <p><strong>Assigned to:</strong> <?php echo htmlspecialchars($engagement['assigned_to_name']); ?></p>
                    <p><strong>Reviewer:</strong> <?php echo $engagement['reviewer_name'] ? htmlspecialchars($engagement['reviewer_name']) : '<span class="text-muted">Not assigned</span>'; ?></p>
                    <p class="mb-0"><strong>Assigned on:</strong> <?php echo date('M d, Y', strtotime($engagement['assigned_at'])); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Dates & Deadlines -->
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
                    <?php echo $engagement['approved_deadline'] ? date('M d, Y', strtotime($engagement['approved_deadline'])) . ' (approved)' : date('M d, Y', strtotime($engagement['original_deadline'])); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Description -->
    <?php if (!empty($engagement['description'])): ?>
    <div class="info-card mb-4">
        <h6 class="info-title"><i class="bi bi-file-text me-2"></i>Description</h6>
        <div class="info-content">
            <?php echo nl2br(htmlspecialchars($engagement['description'])); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Points Information -->
    <div class="info-card mb-4">
        <h6 class="info-title"><i class="bi bi-trophy me-2"></i>Points Information (Version <?php echo $engagement['rule_version']; ?>)</h6>
        <div class="info-content">
            <div class="row text-center">
                <div class="col-3">
                    <div class="points-badge-card">
                        <span class="points-label">On Time</span>
                        <span class="points-value"><?php echo $engagement['points_within_deadline']; ?></span>
                    </div>
                </div>
                <div class="col-3">
                    <div class="points-badge-card">
                        <span class="points-label">5-15 Days</span>
                        <span class="points-value"><?php echo $engagement['points_tier_1']; ?></span>
                    </div>
                </div>
                <div class="col-3">
                    <div class="points-badge-card">
                        <span class="points-label">16-25 Days</span>
                        <span class="points-value"><?php echo $engagement['points_tier_2']; ?></span>
                    </div>
                </div>
                <div class="col-3">
                    <div class="points-badge-card">
                        <span class="points-label">>25 Days</span>
                        <span class="points-value"><?php echo $engagement['points_tier_3']; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Evidence Section -->
    <?php if (!empty($evidence)): ?>
    <div class="info-card mb-4">
        <h6 class="info-title"><i class="bi bi-file-earmark me-2"></i>Evidence Uploaded</h6>
        <div class="info-content">
            <div class="evidence-list">
                <?php foreach($evidence as $file): ?>
                <div class="evidence-item">
                    <i class="bi bi-file-earmark-<?php 
                        $ext = pathinfo($file['file_name'], PATHINFO_EXTENSION);
                        echo $ext == 'pdf' ? 'pdf' : ($ext == 'jpg' || $ext == 'png' ? 'image' : 'text');
                    ?> file-icon"></i>
                    <div class="file-info">
                        <span class="file-name"><?php echo htmlspecialchars($file['file_name']); ?></span>
                        <small class="text-muted">Uploaded on <?php echo date('M d, Y', strtotime($file['uploaded_at'])); ?></small>
                    </div>
                    <a href="../uploads/evidence/<?php echo $file['file_path']; ?>" class="btn btn-sm btn-outline-primary" download>
                        <i class="bi bi-download"></i>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Comments Section -->
    <div class="info-card">
        <h6 class="info-title"><i class="bi bi-chat me-2"></i>Comments</h6>
        <div class="info-content">
            <!-- Comment Form -->
            <form class="comment-form mb-3" onsubmit="addComment(event, <?php echo $engagement_id; ?>)">
                <div class="input-group">
                    <input type="text" class="form-control" id="commentText" placeholder="Add a comment..." required>
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-send"></i>
                    </button>
                </div>
            </form>
            
            <!-- Comments List -->
            <div class="comments-list">
                <?php if (!empty($comments)): ?>
                    <?php foreach($comments as $comment): ?>
                    <div class="comment-item">
                        <strong><?php echo htmlspecialchars($comment['user_name']); ?></strong>
                        <small class="text-muted"><?php echo date('M d, H:i', strtotime($comment['created_at'])); ?></small>
                        <p class="mb-0"><?php echo htmlspecialchars($comment['comment']); ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center">No comments yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.engagement-details {
    font-size: 0.95rem;
}

.info-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 15px;
}

.info-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 12px;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 8px;
}

.info-content {
    color: #4a5a6e;
}

.date-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
    text-align: center;
    height: 100%;
}

.date-label {
    display: block;
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 5px;
}

.date-value {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
}

.points-badge-card {
    background: white;
    border-radius: 10px;
    padding: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.points-label {
    display: block;
    font-size: 0.75rem;
    color: #6c757d;
    margin-bottom: 5px;
}

.points-value {
    font-size: 1.3rem;
    font-weight: 700;
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
    gap: 15px;
    padding: 10px;
    background: white;
    border-radius: 8px;
    border: 1px solid #eee;
}

.evidence-item .file-icon {
    font-size: 1.5rem;
    color: #f1bf70;
}

.evidence-item .file-info {
    flex: 1;
}

.evidence-item .file-name {
    font-weight: 500;
    display: block;
    margin-bottom: 3px;
}

.comment-item {
    padding: 10px;
    border-bottom: 1px solid #dee2e6;
}

.comment-item:last-child {
    border-bottom: none;
}

.comment-form {
    margin-top: 10px;
}
</style>

<script>
function addComment(event, engagementId) {
    event.preventDefault();
    const commentText = document.getElementById('commentText').value;
    
    fetch('includes/ajax/add_comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'engagement_id=' + engagementId + '&comment=' + encodeURIComponent(commentText)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error adding comment: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error adding comment');
    });
}
</script>

<?php
$html = ob_get_clean();

echo json_encode([
    'success' => true,
    'html' => $html,
    'engagement' => $engagement
]);

ob_end_flush();
?>