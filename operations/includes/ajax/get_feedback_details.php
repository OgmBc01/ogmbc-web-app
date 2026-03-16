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
    echo json_encode(['success' => false, 'message' => 'Invalid feedback ID']);
    exit;
}

$feedback_id = (int)$_GET['id'];

$query = "SELECT 
    cf.*,
    c.company_name,
    c.contact_name,
    c.contact_email,
    e.title as engagement_title,
    CONCAT(u.first_name, ' ', u.last_name) as validated_by_name
    FROM client_feedback cf
    JOIN clients c ON cf.client_id = c.client_id
    LEFT JOIN engagements e ON cf.engagement_id = e.engagement_id
    LEFT JOIN users u ON cf.validated_by = u.user_id
    WHERE cf.feedback_id = $feedback_id AND cf.employee_id = $user_id";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Feedback not found']);
    exit;
}

$feedback = mysqli_fetch_assoc($result);

// Set status
$status_class = 'warning';
$status_text = 'Pending';
$status_icon = 'clock';

if ($feedback['is_rejected']) {
    $status_class = 'danger';
    $status_text = 'Rejected';
    $status_icon = 'x-circle';
} elseif ($feedback['is_validated']) {
    $status_class = 'success';
    $status_text = 'Validated';
    $status_icon = 'check-circle';
}

ob_start();
?>

<div class="feedback-detail-view">
    <!-- Header -->
    <div class="d-flex align-items-center mb-4 p-3 rounded text-white bg-<?php echo $status_class; ?>">
        <i class="bi bi-<?php echo $status_icon; ?> fs-1 me-3"></i>
        <div>
            <h5 class="mb-1">Feedback from <?php echo htmlspecialchars($feedback['company_name']); ?></h5>
            <small>
                <i class="bi bi-calendar me-1"></i><?php echo date('F d, Y \a\t h:i A', strtotime($feedback['created_at'])); ?>
            </small>
        </div>
    </div>

    <!-- Rating -->
    <div class="mb-4">
        <h6 class="border-bottom pb-2">Rating</h6>
        <div class="d-flex align-items-center">
            <?php for($i = 1; $i <= 5; $i++): ?>
                <i class="bi bi-star<?php echo $i <= $feedback['rating'] ? '-fill' : ''; ?> text-warning fs-3 me-1"></i>
            <?php endfor; ?>
            <span class="ms-3 fw-bold"><?php echo $feedback['rating']; ?>/5</span>
        </div>
    </div>

    <!-- Feedback Text -->
    <div class="mb-4">
        <h6 class="border-bottom pb-2">Feedback</h6>
        <div class="bg-light p-3 rounded">
            <?php echo nl2br(htmlspecialchars($feedback['feedback_text'])); ?>
        </div>
    </div>

    <!-- Points -->
    <?php if ($feedback['points_awarded'] > 0): ?>
    <div class="mb-4">
        <h6 class="border-bottom pb-2">Points Awarded</h6>
        <span class="badge bg-success fs-5">+<?php echo $feedback['points_awarded']; ?> points</span>
    </div>
    <?php endif; ?>

    <!-- Rejection Reason -->
    <?php if ($feedback['is_rejected'] && !empty($feedback['rejection_reason'])): ?>
    <div class="mb-4">
        <h6 class="border-bottom pb-2 text-danger">Rejection Reason</h6>
        <div class="bg-danger-soft p-3 rounded">
            <?php echo nl2br(htmlspecialchars($feedback['rejection_reason'])); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Engagement -->
    <?php if (!empty($feedback['engagement_title'])): ?>
    <div class="mb-4">
        <h6 class="border-bottom pb-2">Related Engagement</h6>
        <p><i class="bi bi-briefcase me-2"></i><?php echo htmlspecialchars($feedback['engagement_title']); ?></p>
    </div>
    <?php endif; ?>

    <!-- Validation Info -->
    <?php if ($feedback['validated_by_name']): ?>
    <div class="mb-4">
        <h6 class="border-bottom pb-2">Reviewed By</h6>
        <p><i class="bi bi-person-check me-2"></i><?php echo htmlspecialchars($feedback['validated_by_name']); ?></p>
        <small class="text-muted"><?php echo date('F d, Y H:i', strtotime($feedback['validated_at'] ?: $feedback['reviewed_at'])); ?></small>
    </div>
    <?php endif; ?>
</div>

<style>
.bg-danger-soft {
    background: rgba(220, 53, 69, 0.1);
}
</style>

<?php
$html = ob_get_clean();

echo json_encode([
    'success' => true,
    'html' => $html
]);

ob_end_flush();
?>