<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../includes/database.php';

set_exception_handler(function($e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
});
set_error_handler(function($errno, $errstr) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $errstr]);
    exit;
});
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        echo json_encode(['success' => false, 'message' => 'Fatal error: ' . $error['message']]);
        exit;
    }
});

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid target ID']);
    exit;
}

$target_id = (int)$_GET['id'];

$query = "SELECT st.*, 
          CONCAT(u.first_name, ' ', u.last_name) as employee_name,
          u.user_email as employee_email,
          CONCAT(cr.first_name, ' ', cr.last_name) as created_by_name,
          CONCAT(vl.first_name, ' ', vl.last_name) as validated_by_name
          FROM sales_targets st
          JOIN users u ON st.employee_id = u.user_id
          LEFT JOIN users cr ON st.created_by = cr.user_id
          LEFT JOIN users vl ON st.validated_by = vl.user_id
          WHERE st.target_id = $target_id";

$result = mysqli_query($connection, $query);
if (!$result || mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Target not found']);
    exit;
}
$target = mysqli_fetch_assoc($result);

$attainment = $target['attainment_percentage'] ? number_format($target['attainment_percentage'], 1) . '%' : 'Not calculated';
$points_info = '';
if ($target['points_awarded']) {
    $points_info = $target['points_awarded'] . ' points';
} else if ($target['actual_value']) {
    $percentage = ($target['actual_value'] / $target['target_value']) * 100;
    if ($percentage >= 100) $points_info = '1000 points (≥100%)';
    elseif ($percentage >= 75) $points_info = '750 points (75-99%)';
    elseif ($percentage >= 50) $points_info = '500 points (50-74%)';
    else $points_info = '250 points (<50%)';
}

ob_start();
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <table class="table table-sm table-borderless">
                <tr>
                    <th width="150">Period:</th>
                    <td><strong><?php echo date('F Y', mktime(0,0,0,$target['month'],1,$target['year'])); ?></strong></td>
                </tr>
                <tr>
                    <th>Sales Person:</th>
                    <td>
                        <?php echo htmlspecialchars($target['employee_name']); ?>
                        <br><small><?php echo htmlspecialchars($target['employee_email']); ?></small>
                    </td>
                </tr>
                <tr>
                    <th>Target Amount:</th>
                    <td>AED <?php echo number_format($target['target_value'], 2); ?></td>
                </tr>
                <?php if ($target['actual_value']): ?>
                <tr>
                    <th>Actual Achievement:</th>
                    <td>AED <?php echo number_format($target['actual_value'], 2); ?></td>
                </tr>
                <tr>
                    <th>Achievement:</th>
                    <td>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-<?php echo $target['attainment_percentage'] >= 100 ? 'success' : ($target['attainment_percentage'] >= 75 ? 'info' : ($target['attainment_percentage'] >= 50 ? 'warning' : 'danger')); ?>" 
                                 style="width: <?php echo min($target['attainment_percentage'], 100); ?>%">
                                <?php echo $attainment; ?>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>Points:</th>
                    <td><span class="badge bg-success fs-6"><?php echo $points_info; ?></span></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th>Status:</th>
                    <td>
                        <?php
                        $status_class = 'secondary';
                        if ($target['status'] == 'SUBMITTED') $status_class = 'info';
                        if ($target['status'] == 'VALIDATED') $status_class = 'success';
                        if ($target['status'] == 'REJECTED') $status_class = 'danger';
                        ?>
                        <span class="badge bg-<?php echo $status_class; ?>"><?php echo $target['status']; ?></span>
                    </td>
                </tr>
                <?php if ($target['evidence_file']): ?>
                <tr>
                    <th>Evidence:</th>
                    <td>
                        <a href="../uploads/sales_evidence/<?php echo $target['evidence_file']; ?>" target="_blank" class="btn btn-sm btn-info">
                            <i class="bi bi-file-earmark"></i> View Evidence
                        </a>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th>Created By:</th>
                    <td>
                        <?php echo htmlspecialchars($target['created_by_name']); ?>
                        <br><small><?php echo date('M d, Y', strtotime($target['created_at'])); ?></small>
                    </td>
                </tr>
                <?php if ($target['validated_by']): ?>
                <tr>
                    <th>Validated By:</th>
                    <td>
                        <?php echo htmlspecialchars($target['validated_by_name']); ?>
                        <br><small><?php echo date('M d, Y', strtotime($target['validated_at'])); ?></small>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>
<?php
$html = ob_get_clean();
echo json_encode(['success' => true, 'html' => $html]);
