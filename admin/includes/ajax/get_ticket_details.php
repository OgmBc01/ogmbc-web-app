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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid ticket ID']);
    exit;
}

$ticket_id = (int)$_GET['id'];

// Get ticket details
$ticket_query = "SELECT t.*, 
                c.company_name, c.contact_name, c.contact_email,
                CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name
                FROM support_tickets t
                JOIN clients c ON t.client_id = c.client_id
                LEFT JOIN users u ON t.assigned_to = u.user_id
                WHERE t.ticket_id = $ticket_id";
$ticket_result = mysqli_query($connection, $ticket_query);

if (!$ticket_result || mysqli_num_rows($ticket_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Ticket not found']);
    exit;
}

$ticket = mysqli_fetch_assoc($ticket_result);

// Get recent replies (last 3)
$replies_query = "SELECT r.*, CONCAT(u.first_name, ' ', u.last_name) as user_name
                  FROM ticket_replies r
                  JOIN users u ON r.user_id = u.user_id
                  WHERE r.ticket_id = $ticket_id
                  ORDER BY r.created_at DESC
                  LIMIT 3";
$replies_result = mysqli_query($connection, $replies_query);

$replies = [];
while ($row = mysqli_fetch_assoc($replies_result)) {
    $replies[] = $row;
}

// Priority and status badge classes
$priority_class = 'secondary';
switch($ticket['priority']) {
    case 'urgent': $priority_class = 'danger'; break;
    case 'high': $priority_class = 'warning'; break;
    case 'medium': $priority_class = 'info'; break;
    case 'low': $priority_class = 'success'; break;
}

$status_class = 'secondary';
switch($ticket['status']) {
    case 'open': $status_class = 'warning'; break;
    case 'in_progress': $status_class = 'info'; break;
    case 'resolved': $status_class = 'success'; break;
    case 'closed': $status_class = 'dark'; break;
}

ob_start();
?>

<div class="ticket-quick-view">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h6 class="mb-1"><?php echo htmlspecialchars($ticket['subject']); ?></h6>
            <small class="text-muted">Client: <?php echo htmlspecialchars($ticket['company_name']); ?></small>
        </div>
        <span class="badge bg-<?php echo $priority_class; ?>"><?php echo ucfirst($ticket['priority']); ?></span>
    </div>

    <!-- Status & Assignment -->
    <div class="row mb-3">
        <div class="col-6">
            <small class="text-muted d-block">Status</small>
            <span class="badge bg-<?php echo $status_class; ?>"><?php echo str_replace('_', ' ', $ticket['status']); ?></span>
        </div>
        <div class="col-6">
            <small class="text-muted d-block">Assigned To</small>
            <?php if ($ticket['assigned_to_name']): ?>
                <span><?php echo htmlspecialchars($ticket['assigned_to_name']); ?></span>
            <?php else: ?>
                <span class="text-muted">Unassigned</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Original Message Preview -->
    <div class="mb-3">
        <small class="text-muted d-block mb-1">Original Message</small>
        <div class="message-preview">
            <?php echo nl2br(htmlspecialchars(substr($ticket['message'], 0, 200))); ?>
            <?php if (strlen($ticket['message']) > 200): ?>
                <span class="text-muted">...</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Replies -->
    <?php if (!empty($replies)): ?>
    <div class="mb-3">
        <small class="text-muted d-block mb-2">Recent Replies</small>
        <?php foreach($replies as $reply): ?>
            <div class="reply-preview mb-2">
                <strong><?php echo htmlspecialchars($reply['user_name']); ?></strong>
                <small class="text-muted ms-2"><?php echo date('M d, H:i', strtotime($reply['created_at'])); ?></small>
                <p class="mb-0 small"><?php echo htmlspecialchars(substr($reply['message'], 0, 100)); ?>...</p>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Quick Stats -->
    <div class="row g-2 text-center">
        <div class="col-4">
            <div class="stat-mini">
                <span class="stat-value"><?php echo count($replies); ?></span>
                <span class="stat-label">Replies</span>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-mini">
                <span class="stat-value"><?php echo date('M d', strtotime($ticket['created_at'])); ?></span>
                <span class="stat-label">Opened</span>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-mini">
                <span class="stat-value"><?php echo $ticket['assigned_to'] ? 'Yes' : 'No'; ?></span>
                <span class="stat-label">Assigned</span>
            </div>
        </div>
    </div>
</div>

<style>
.ticket-quick-view {
    font-size: 0.9rem;
}

.message-preview {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 10px;
    font-size: 0.85rem;
    max-height: 100px;
    overflow-y: auto;
}

.reply-preview {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 8px;
    border-left: 3px solid #dee2e6;
}

.stat-mini {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 8px;
    text-align: center;
}

.stat-value {
    display: block;
    font-weight: 600;
    color: #2c3e50;
}

.stat-label {
    font-size: 0.7rem;
    color: #6c757d;
}
</style>

<?php
$html = ob_get_clean();

echo json_encode([
    'success' => true,
    'html' => $html,
    'ticket' => $ticket
]);

ob_end_flush();
?>