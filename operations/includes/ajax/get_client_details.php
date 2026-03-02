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
    echo json_encode(['success' => false, 'message' => 'Invalid client ID']);
    exit;
}

$client_id = (int)$_GET['id'];

// Verify client is associated with this user
$check_query = "SELECT c.client_id 
                FROM clients c
                JOIN engagements e ON c.client_id = e.client_id
                WHERE c.client_id = $client_id AND e.assigned_to = $user_id
                LIMIT 1";
$check_result = mysqli_query($connection, $check_query);

if (!$check_result || mysqli_num_rows($check_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Client not found or access denied']);
    exit;
}

// Get client details
$query = "SELECT 
    c.*,
    COUNT(DISTINCT e.engagement_id) as total_engagements,
    SUM(CASE WHEN e.status NOT IN ('CLOSED', 'SUBMITTED') THEN 1 ELSE 0 END) as active_engagements,
    MAX(e.updated_at) as last_activity,
    (SELECT COUNT(*) FROM client_communications WHERE client_id = c.client_id) as total_comms,
    (SELECT COUNT(*) FROM client_files WHERE client_id = c.client_id) as total_files
    FROM clients c
    LEFT JOIN engagements e ON c.client_id = e.client_id
    WHERE c.client_id = $client_id
    GROUP BY c.client_id";

$result = mysqli_query($connection, $query);
$client = mysqli_fetch_assoc($result);

// Get recent engagements
$engagements_query = "SELECT 
    e.engagement_id,
    e.title,
    e.status,
    COALESCE(e.approved_deadline, e.original_deadline) as deadline,
    s.service_name
    FROM engagements e
    JOIN service_types s ON e.service_id = s.service_id
    WHERE e.client_id = $client_id AND e.assigned_to = $user_id
    ORDER BY e.created_at DESC
    LIMIT 3";
$engagements_result = mysqli_query($connection, $engagements_query);
$engagements = [];
while ($row = mysqli_fetch_assoc($engagements_result)) {
    $engagements[] = $row;
}

ob_start();
?>

<!-- Client Details View -->
<div class="client-details">
    <!-- Header with Company Name -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h4 class="mb-2"><?php echo htmlspecialchars($client['company_name']); ?></h4>
            <div class="d-flex gap-2 flex-wrap">
                <span class="badge bg-info">
                    <i class="bi bi-briefcase me-1"></i>
                    <?php echo $client['total_engagements']; ?> Total Engagements
                </span>
                <?php if ($client['active_engagements'] > 0): ?>
                <span class="badge bg-success">
                    <i class="bi bi-play-circle me-1"></i>
                    <?php echo $client['active_engagements']; ?> Active
                </span>
                <?php endif; ?>
                <span class="badge bg-secondary">
                    <i class="bi bi-chat me-1"></i>
                    <?php echo $client['total_comms']; ?> Communications
                </span>
            </div>
        </div>
        <span class="text-muted">Client ID: #<?php echo $client['client_id']; ?></span>
    </div>

    <!-- Quick Stats Row -->
    <div class="row mb-4">
        <div class="col-4">
            <div class="stat-mini-card">
                <span class="stat-mini-label">Country</span>
                <span class="stat-mini-value"><?php echo htmlspecialchars($client['country'] ?: 'N/A'); ?></span>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-mini-card">
                <span class="stat-mini-label">Trade License</span>
                <span class="stat-mini-value"><?php echo htmlspecialchars($client['trade_license_no'] ?: 'N/A'); ?></span>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-mini-card">
                <span class="stat-mini-label">Client Since</span>
                <span class="stat-mini-value"><?php echo date('M Y', strtotime($client['created_at'])); ?></span>
            </div>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="info-card mb-4">
        <h6 class="info-title"><i class="bi bi-person me-2"></i>Contact Information</h6>
        <div class="info-content">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Contact Name:</strong> <?php echo htmlspecialchars($client['contact_name']); ?></p>
                    <p><strong>Designation:</strong> <?php echo htmlspecialchars($client['contact_designation'] ?: 'N/A'); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Email:</strong> <a href="mailto:<?php echo $client['contact_email']; ?>"><?php echo $client['contact_email']; ?></a></p>
                    <p><strong>Mobile:</strong> <?php echo $client['contact_mobile']; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Address & Business Info -->
    <?php if (!empty($client['address']) || !empty($client['business_activity'])): ?>
    <div class="info-card mb-4">
        <h6 class="info-title"><i class="bi bi-building me-2"></i>Business Information</h6>
        <div class="info-content">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($client['address'] ?: 'N/A')); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Business Activity:</strong> <?php echo nl2br(htmlspecialchars($client['business_activity'] ?: 'N/A')); ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Engagements -->
    <?php if (!empty($engagements)): ?>
    <div class="info-card">
        <h6 class="info-title"><i class="bi bi-briefcase me-2"></i>Recent Engagements</h6>
        <div class="info-content">
            <?php foreach($engagements as $eng): 
                $status_class = $eng['status'] == 'CLOSED' ? 'dark' : 
                    ($eng['status'] == 'SUBMITTED' ? 'success' : 
                    ($eng['status'] == 'AWAITING_REVIEW' ? 'warning' : 'primary'));
            ?>
            <div class="recent-engagement-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?php echo htmlspecialchars($eng['title']); ?></strong>
                        <br>
                        <small class="text-muted"><?php echo htmlspecialchars($eng['service_name']); ?></small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-<?php echo $status_class; ?>"><?php echo $eng['status']; ?></span>
                        <br>
                        <small class="text-muted">Due: <?php echo date('M d', strtotime($eng['deadline'])); ?></small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.client-details {
    font-size: 0.95rem;
}

.stat-mini-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 10px;
    text-align: center;
}

.stat-mini-label {
    display: block;
    font-size: 0.7rem;
    color: #6c757d;
    margin-bottom: 3px;
}

.stat-mini-value {
    font-size: 1rem;
    font-weight: 600;
    color: #2c3e50;
}

.info-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
}

.info-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 12px;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 8px;
}

.recent-engagement-item {
    padding: 10px;
    border-bottom: 1px solid #dee2e6;
}

.recent-engagement-item:last-child {
    border-bottom: none;
}
</style>

<?php
$html = ob_get_clean();

echo json_encode([
    'success' => true,
    'html' => $html,
    'client' => $client
]);

ob_end_flush();
?>