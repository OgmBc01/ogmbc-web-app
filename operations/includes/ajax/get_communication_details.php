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
    echo json_encode(['success' => false, 'message' => 'Invalid communication ID']);
    exit;
}

$comm_id = (int)$_GET['id'];

$query = "SELECT 
    cc.*,
    c.company_name,
    c.contact_name,
    c.contact_email,
    c.contact_mobile,
    e.title as engagement_title
    FROM client_communications cc
    JOIN clients c ON cc.client_id = c.client_id
    LEFT JOIN engagements e ON cc.engagement_id = e.engagement_id
    WHERE cc.comm_id = $comm_id AND cc.user_id = $user_id";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Communication not found']);
    exit;
}

$comm = mysqli_fetch_assoc($result);

// Set icon and color based on type
$icon = 'chat-dots';
$color = 'primary';
switch($comm['comm_type']) {
    case 'email':
        $icon = 'envelope';
        $color = 'info';
        break;
    case 'whatsapp':
        $icon = 'whatsapp';
        $color = 'success';
        break;
    case 'call':
        $icon = 'telephone';
        $color = 'warning';
        break;
    case 'meeting':
        $icon = 'people';
        $color = 'secondary';
        break;
    case 'note':
        $icon = 'sticky';
        $color = 'dark';
        break;
}

ob_start();
?>

<div class="communication-detail-view">
    <!-- Header -->
    <div class="d-flex align-items-center mb-4 p-3 rounded text-white" style="background-color: var(--bs-<?php echo $color; ?>);">
        <i class="bi bi-<?php echo $icon; ?> fs-1 me-3"></i>
        <div>
            <h5 class="mb-1"><?php echo ucfirst($comm['comm_type']); ?> Communication</h5>
            <small>
                <i class="bi bi-calendar me-1"></i><?php echo date('F d, Y \a\t h:i A', strtotime($comm['created_at'])); ?>
                <span class="ms-3">
                    <i class="bi bi-arrow-<?php echo $comm['direction'] == 'outgoing' ? 'right' : 'left'; ?> me-1"></i>
                    <?php echo ucfirst($comm['direction']); ?>
                </span>
            </small>
        </div>
    </div>

    <!-- Client Info -->
    <div class="mb-4">
        <h6 class="border-bottom pb-2">Client Information</h6>
        <p><strong><?php echo htmlspecialchars($comm['company_name']); ?></strong></p>
        <p class="mb-1"><i class="bi bi-person me-2"></i><?php echo htmlspecialchars($comm['contact_name']); ?></p>
        <p class="mb-1"><i class="bi bi-envelope me-2"></i><?php echo htmlspecialchars($comm['contact_email']); ?></p>
        <p class="mb-0"><i class="bi bi-telephone me-2"></i><?php echo htmlspecialchars($comm['contact_mobile']); ?></p>
    </div>

    <!-- Communication Details -->
    <?php if (!empty($comm['subject'])): ?>
    <div class="mb-3">
        <h6 class="border-bottom pb-2">Subject</h6>
        <p><?php echo htmlspecialchars($comm['subject']); ?></p>
    </div>
    <?php endif; ?>

    <div class="mb-3">
        <h6 class="border-bottom pb-2">Message / Notes</h6>
        <div class="bg-light p-3 rounded">
            <?php echo nl2br(htmlspecialchars($comm['message'] ?: 'No message provided.')); ?>
        </div>
    </div>

    <?php if (!empty($comm['engagement_title'])): ?>
    <div class="mb-3">
        <h6 class="border-bottom pb-2">Related Engagement</h6>
        <p><i class="bi bi-briefcase me-2"></i><?php echo htmlspecialchars($comm['engagement_title']); ?></p>
    </div>
    <?php endif; ?>
</div>

<?php
$html = ob_get_clean();

echo json_encode([
    'success' => true,
    'html' => $html
]);

ob_end_flush();
?>