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
    echo json_encode(['success' => false, 'message' => 'Invalid CDP ID']);
    exit;
}

$cdp_id = (int)$_GET['id'];

$query = "SELECT c.*, 
          CONCAT(u.first_name, ' ', u.last_name) as employee_name,
          CONCAT(a.first_name, ' ', a.last_name) as approved_by_name
          FROM cdp_records c
          JOIN users u ON c.employee_id = u.user_id
          LEFT JOIN users a ON c.approved_by = a.user_id
          WHERE c.cdp_id = $cdp_id";

$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'CDP record not found']);
    exit;
}

$cdp = mysqli_fetch_assoc($result);

ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <table class="table table-sm table-borderless">
                <tr>
                    <th width="150">Employee:</th>
                    <td><strong><?php echo htmlspecialchars($cdp['employee_name']); ?></strong></td>
                </tr>
                <tr>
                    <th>Type:</th>
                    <td>
                        <span class="badge bg-<?php 
                            echo $cdp['cdp_type'] == 'CERTIFICATE' ? 'success' : 
                                ($cdp['cdp_type'] == 'COURSE' ? 'info' : 
                                ($cdp['cdp_type'] == 'LOYALTY' ? 'warning' : 'primary')); 
                        ?>"><?php echo $cdp['cdp_type']; ?></span>
                    </td>
                </tr>
                <tr>
                    <th>Title:</th>
                    <td><?php echo htmlspecialchars($cdp['title']); ?></td>
                </tr>
                <tr>
                    <th>Description:</th>
                    <td><?php echo nl2br(htmlspecialchars($cdp['description'] ?? 'N/A')); ?></td>
                </tr>
                <tr>
                    <th>Effective Date:</th>
                    <td><?php echo date('M d, Y', strtotime($cdp['effective_date'])); ?></td>
                </tr>
                <tr>
                    <th>Uplift:</th>
                    <td><span class="badge bg-success">+<?php echo $cdp['uplift_percentage']; ?>%</span></td>
                </tr>
                <tr>
                    <th>Status:</th>
                    <td>
                        <?php if ($cdp['status'] == 'APPROVED'): ?>
                            <span class="badge bg-success">Approved</span>
                            <br><small>by <?php echo htmlspecialchars($cdp['approved_by_name']); ?> on <?php echo date('M d, Y', strtotime($cdp['approved_at'])); ?></small>
                        <?php elseif ($cdp['status'] == 'REJECTED'): ?>
                            <span class="badge bg-danger">Rejected</span>
                            <br><small>by <?php echo htmlspecialchars($cdp['approved_by_name']); ?> on <?php echo date('M d, Y', strtotime($cdp['approved_at'])); ?></small>
                            <?php if (!empty($cdp['approval_notes'])): ?>
                                <div class="alert alert-danger mt-2">
                                    <strong>Rejection Reason:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($cdp['approval_notes'])); ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge bg-warning">Pending</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if ($cdp['document_file']): ?>
                <tr>
                    <th>Document:</th>
                    <td>
                        <a href="../uploads/cdp_documents/<?php echo $cdp['document_file']; ?>" target="_blank" class="btn btn-sm btn-info">
                            <i class="bi bi-file-earmark"></i> View Document
                        </a>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th>Created:</th>
                    <td><?php echo date('M d, Y H:i', strtotime($cdp['created_at'])); ?></td>
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