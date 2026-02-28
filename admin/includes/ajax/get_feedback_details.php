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

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid feedback ID']);
    exit;
}

$feedback_id = (int)$_GET['id'];

$query = "SELECT cf.*, 
          c.company_name,
          c.contact_name,
          c.contact_email,
          CONCAT(u.first_name, ' ', u.last_name) as employee_name,
          CONCAT(cr.first_name, ' ', cr.last_name) as created_by_name,
          CONCAT(v.first_name, ' ', v.last_name) as validated_by_name,
          e.title as engagement_title
          FROM client_feedback cf
          JOIN clients c ON cf.client_id = c.client_id
          LEFT JOIN users u ON cf.employee_id = u.user_id
          LEFT JOIN users cr ON cf.created_by = cr.user_id
          LEFT JOIN users v ON cf.validated_by = v.user_id
          LEFT JOIN engagements e ON cf.engagement_id = e.engagement_id
          WHERE cf.feedback_id = $feedback_id";

$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Feedback not found']);
    exit;
}

$feedback = mysqli_fetch_assoc($result);

ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <table class="table table-sm table-borderless">
                <tr>
                    <th width="150">Client:</th>
                    <td><strong><?php echo htmlspecialchars($feedback['company_name']); ?></strong></td>
                </tr>
                <?php if ($feedback['contact_name']): ?>
                <tr>
                    <th>Contact:</th>
                    <td><?php echo htmlspecialchars($feedback['contact_name']); ?> (<?php echo htmlspecialchars($feedback['contact_email']); ?>)</td>
                </tr>
                <?php endif; ?>
                
                <tr>
                    <th>Employee:</th>
                    <td><?php echo htmlspecialchars($feedback['employee_name'] ?? 'N/A'); ?></td>
                </tr>
                
                <?php if ($feedback['engagement_title']): ?>
                <tr>
                    <th>Related Engagement:</th>
                    <td><?php echo htmlspecialchars($feedback['engagement_title']); ?></td>
                </tr>
                <?php endif; ?>
                
                <tr>
                    <th>Feedback:</th>
                    <td>
                        <div class="bg-light p-3 rounded">
                            <?php echo nl2br(htmlspecialchars($feedback['feedback_text'])); ?>
                        </div>
                    </td>
                </tr>
                
                <tr>
                    <th>Type:</th>
                    <td>
                        <?php if ($feedback['is_positive']): ?>
                            <span class="badge bg-success">Positive (+50 points)</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Neutral/Negative</span>
                        <?php endif; ?>
                    </td>
                </tr>
                
                <tr>
                    <th>Status:</th>
                    <td>
                        <?php if ($feedback['is_validated']): ?>
                            <span class="badge bg-success">Validated</span>
                            <br><small>by <?php echo htmlspecialchars($feedback['validated_by_name']); ?> on <?php echo date('M d, Y', strtotime($feedback['validated_at'])); ?></small>
                        <?php else: ?>
                            <span class="badge bg-warning">Pending Validation</span>
                        <?php endif; ?>
                    </td>
                </tr>
                
                <?php if ($feedback['evidence_file']): ?>
                <tr>
                    <th>Evidence:</th>
                    <td>
                        <a href="../uploads/feedback_evidence/<?php echo $feedback['evidence_file']; ?>" target="_blank" class="btn btn-sm btn-info">
                            <i class="bi bi-file-earmark"></i> View Evidence
                        </a>
                    </td>
                </tr>
                <?php endif; ?>
                
                <tr>
                    <th>Created:</th>
                    <td>
                        <?php echo date('M d, Y H:i', strtotime($feedback['created_at'])); ?>
                        <br><small>by <?php echo htmlspecialchars($feedback['created_by_name']); ?></small>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<?php
$html = ob_get_clean();
echo json_encode(['success' => true, 'html' => $html]);

ob_end_flush();
?>