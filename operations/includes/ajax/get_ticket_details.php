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
    echo json_encode(['success' => false, 'message' => 'Invalid ticket ID']);
    exit;
}

$ticket_id = (int)$_GET['id'];

$query = "SELECT t.*, 
          c.company_name, c.contact_name
          FROM support_tickets t
          JOIN clients c ON t.client_id = c.client_id
          WHERE t.ticket_id = $ticket_id AND t.assigned_to = $user_id";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Ticket not found']);
    exit;
}

$ticket = mysqli_fetch_assoc($result);

// Set priority and status classes
$priority_class = 'secondary';
$priority_icon = 'record-circle';
switch($ticket['priority']) {
    case 'urgent':
        $priority_class = 'danger';
        $priority_icon = 'exclamation-triangle-fill';
        break;
    case 'high':
        $priority_class = 'warning';
        $priority_icon = 'exclamation-circle-fill';
        break;
    case 'medium':
        $priority_class = 'info';
        $priority_icon = 'info-circle-fill';
        break;
    case 'low':
        $priority_class = 'success';
        $priority_icon = 'check-circle-fill';
        break;
}

$status_class = 'secondary';
$status_icon = 'record-circle';
switch($ticket['status']) {
    case 'open':
        $status_class = 'warning';
        $status_icon = 'envelope-open';
        break;
    case 'in_progress':
        $status_class = 'info';
        $status_icon = 'arrow-repeat';
        break;
    case 'resolved':
        $status_class = 'success';
        $status_icon = 'check-circle';
        break;
    case 'closed':
        $status_class = 'dark';
        $status_icon = 'lock';
        break;
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

    <!-- Status & Info -->
    <div class="row mb-3">
        <div class="col-6">
            <small class="text-muted d-block">Status</small>
            <span class="badge bg-<?php echo $status_class; ?>">
                <i class="bi bi-<?php echo $status_icon; ?> me-1"></i>
                <?php echo str_replace('_', ' ', $ticket['status']); ?>
            </span>
        </div>
        <div class="col-6">
            <small class="text-muted d-block">Contact</small>
            <span><?php echo htmlspecialchars($ticket['contact_name']); ?></span>
        </div>
    </div>

    <!-- Message Preview -->
    <div class="mb-3">
        <small class="text-muted d-block mb-1">Message</small>
        <div class="message-preview">
            <?php echo nl2br(htmlspecialchars(substr($ticket['message'], 0, 200))); ?>
            <?php if (strlen($ticket['message']) > 200): ?>
                <span class="text-muted">...</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-2 text-center">
        <div class="col-4">
            <div class="stat-mini">
                <span class="stat-value"><?php echo date('M d', strtotime($ticket['created_at'])); ?></span>
                <span class="stat-label">Created</span>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-mini">
                <span class="stat-value"><?php echo date('M d', strtotime($ticket['updated_at'])); ?></span>
                <span class="stat-label">Updated</span>
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
.message-preview {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 10px;
    font-size: 0.85rem;
    max-height: 100px;
    overflow-y: auto;
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