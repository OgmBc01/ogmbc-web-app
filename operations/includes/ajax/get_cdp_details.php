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
    echo json_encode(['success' => false, 'message' => 'Invalid CDP ID']);
    exit;
}

$cdp_id = (int)$_GET['id'];

// Get CDP details
$query = "SELECT c.*, 
          CONCAT(u.first_name, ' ', u.last_name) as approved_by_name
          FROM cdp_records c
          LEFT JOIN users u ON c.approved_by = u.user_id
          WHERE c.cdp_id = $cdp_id AND c.employee_id = $user_id";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'CDP record not found']);
    exit;
}

$cdp = mysqli_fetch_assoc($result);

// Get file info
$file_info = '';
if (!empty($cdp['document_file'])) {
    $file_path = "../uploads/cdp_documents/" . $cdp['document_file'];
    $file_size = file_exists($file_path) ? round(filesize($file_path) / 1024, 1) . ' KB' : 'File not found';
    $file_info = '<a href="' . $file_path . '" class="btn btn-sm btn-outline-primary" download><i class="bi bi-download me-1"></i>Download</a>';
}

// Determine type icon and color
$type_icon = '';
$type_color = '';
switch($cdp['cdp_type']) {
    case 'CERTIFICATE':
        $type_icon = 'patch-check';
        $type_color = 'success';
        break;
    case 'COURSE':
        $type_icon = 'book';
        $type_color = 'info';
        break;
    case 'LOYALTY':
        $type_icon = 'star';
        $type_color = 'warning';
        break;
    case 'BEHAVIOR':
        $type_icon = 'heart';
        $type_color = 'primary';
        break;
}

ob_start();
?>

<!-- CDP Details View -->
<div class="cdp-details">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h4 class="mb-2"><?php echo htmlspecialchars($cdp['title']); ?></h4>
            <div class="d-flex gap-2 flex-wrap">
                <span class="badge bg-<?php echo $type_color; ?>">
                    <i class="bi bi-<?php echo $type_icon; ?> me-1"></i>
                    <?php echo $cdp['cdp_type']; ?>
                </span>
                <?php if ($cdp['uplift_percentage']): ?>
                <span class="badge bg-success">
                    <i class="bi bi-percent me-1"></i>+<?php echo $cdp['uplift_percentage']; ?>% Uplift
                </span>
                <?php endif; ?>
            </div>
        </div>
        <span class="text-muted">Record #<?php echo $cdp['cdp_id']; ?></span>
    </div>

    <!-- Status Banner -->
    <div class="status-banner mb-4">
        <div class="d-flex align-items-center">
            <div class="status-icon me-3">
                <?php if ($cdp['status'] == 'APPROVED'): ?>
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 2rem;"></i>
                <?php elseif ($cdp['status'] == 'REJECTED'): ?>
                    <i class="bi bi-x-circle-fill text-danger" style="font-size: 2rem;"></i>
                <?php else: ?>
                    <i class="bi bi-clock-history-fill text-warning" style="font-size: 2rem;"></i>
                <?php endif; ?>
            </div>
            <div>
                <h5 class="mb-1">
                    Status: 
                    <span class="badge bg-<?php 
                        echo $cdp['status'] == 'APPROVED' ? 'success' : 
                            ($cdp['status'] == 'REJECTED' ? 'danger' : 'warning'); 
                    ?>"><?php echo $cdp['status']; ?></span>
                </h5>
                <?php if ($cdp['status'] == 'APPROVED' && $cdp['approved_by_name']): ?>
                    <p class="mb-0 text-muted">
                        Approved by <?php echo htmlspecialchars($cdp['approved_by_name']); ?> 
                        on <?php echo date('M d, Y', strtotime($cdp['approved_at'])); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="details-grid">
        <div class="detail-item">
            <span class="detail-label">Effective Date</span>
            <span class="detail-value"><?php echo date('F d, Y', strtotime($cdp['effective_date'])); ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Submitted On</span>
            <span class="detail-value"><?php echo date('F d, Y', strtotime($cdp['created_at'])); ?></span>
        </div>
        <?php if ($cdp['uplift_percentage']): ?>
        <div class="detail-item">
            <span class="detail-label">Uplift Percentage</span>
            <span class="detail-value text-success">+<?php echo $cdp['uplift_percentage']; ?>%</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Description -->
    <?php if (!empty($cdp['description'])): ?>
    <div class="detail-section">
        <h6 class="section-title"><i class="bi bi-file-text me-2"></i>Description</h6>
        <div class="section-content">
            <?php echo nl2br(htmlspecialchars($cdp['description'])); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Document -->
    <?php if (!empty($cdp['document_file'])): ?>
    <div class="detail-section">
        <h6 class="section-title"><i class="bi bi-file-earmark me-2"></i>Document</h6>
        <div class="section-content">
            <div class="file-preview">
                <i class="bi bi-file-earmark-text file-icon"></i>
                <span class="file-name"><?php echo basename($cdp['document_file']); ?></span>
                <?php echo $file_info; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Notes (for rejected) -->
    <?php if ($cdp['status'] == 'REJECTED' && !empty($cdp['approval_notes'])): ?>
    <div class="detail-section">
        <h6 class="section-title text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Rejection Notes</h6>
        <div class="section-content bg-danger-soft">
            <?php echo nl2br(htmlspecialchars($cdp['approval_notes'])); ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.cdp-details {
    font-size: 0.95rem;
}

.status-banner {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
    border-left: 4px solid;
}

.status-banner.bg-warning-soft { border-left-color: #ffc107; }
.status-banner.bg-success-soft { border-left-color: #28a745; }
.status-banner.bg-danger-soft { border-left-color: #dc3545; }

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 25px;
}

.detail-item {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 12px 15px;
}

.detail-label {
    display: block;
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 5px;
}

.detail-value {
    font-size: 1rem;
    font-weight: 600;
    color: #2c3e50;
}

.detail-section {
    margin-bottom: 25px;
}

.section-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 12px;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 8px;
}

.section-content {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
}

.file-preview {
    display: flex;
    align-items: center;
    gap: 15px;
}

.file-icon {
    font-size: 1.5rem;
    color: #f1bf70;
}

.file-name {
    flex: 1;
    font-family: monospace;
    word-break: break-all;
}

.bg-danger-soft {
    background: rgba(220, 53, 69, 0.1);
}
</style>

<?php
$html = ob_get_clean();

echo json_encode([
    'success' => true,
    'html' => $html,
    'cdp' => $cdp
]);

ob_end_flush();
?>