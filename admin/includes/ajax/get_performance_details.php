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
    echo json_encode(['success' => false, 'message' => 'Invalid performance ID']);
    exit;
}

$perf_id = (int)$_GET['id'];

$query = "SELECT ap.*, 
          CONCAT(u.first_name, ' ', u.last_name) as employee_name,
          CONCAT(ab.first_name, ' ', ab.last_name) as approved_by_name,
          d.dept_name
          FROM annual_performance ap
          JOIN users u ON ap.employee_id = u.user_id
          LEFT JOIN employees e ON u.user_id = e.user_id
          LEFT JOIN departments d ON e.department_id = d.id
          LEFT JOIN users ab ON ap.approved_by = ab.user_id
          WHERE ap.performance_id = $perf_id";

$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Performance record not found']);
    exit;
}

$perf = mysqli_fetch_assoc($result);

ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <table class="table table-sm table-borderless">
                <tr>
                    <th width="200">Employee:</th>
                    <td><strong><?php echo htmlspecialchars($perf['employee_name']); ?></strong></td>
                </tr>
                <tr>
                    <th>Department:</th>
                    <td><?php echo htmlspecialchars($perf['dept_name'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <th>Year:</th>
                    <td><?php echo $perf['year']; ?></td>
                </tr>
                <tr>
                    <th>Total Points:</th>
                    <td><?php echo number_format($perf['total_points']); ?></td>
                </tr>
                <tr>
                    <th>Base Percentage:</th>
                    <td><?php echo number_format($perf['base_percentage'], 1); ?>%</td>
                </tr>
                <tr>
                    <th>CDP Uplifts:</th>
                    <td>
                        <ul class="mb-0">
                            <?php if ($perf['cdp_uplift'] > 0): ?><li>Certificates: +<?php echo $perf['cdp_uplift']; ?>%</li><?php endif; ?>
                            <?php if ($perf['loyalty_uplift'] > 0): ?><li>Loyalty: +<?php echo $perf['loyalty_uplift']; ?>%</li><?php endif; ?>
                            <?php if ($perf['behavior_uplift'] > 0): ?><li>Behavior: +<?php echo $perf['behavior_uplift']; ?>%</li><?php endif; ?>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>Total Uplift:</th>
                    <td class="text-success">+<?php echo $perf['total_uplift']; ?>%</td>
                </tr>
                <tr>
                    <th class="fs-5">Final Percentage:</th>
                    <td class="fs-5"><strong><?php echo number_format($perf['final_percentage'], 1); ?>%</strong></td>
                </tr>
                <tr>
                    <th>Recommended Band:</th>
                    <td><?php echo $perf['recommended_band']; ?></td>
                </tr>
                <tr>
                    <th>Status:</th>
                    <td>
                        <?php if ($perf['status'] == 'APPROVED'): ?>
                            <span class="badge bg-success">Approved</span>
                            <br><small>by <?php echo htmlspecialchars($perf['approved_by_name']); ?> on <?php echo date('M d, Y', strtotime($perf['approved_at'])); ?></small>
                        <?php elseif ($perf['status'] == 'PENDING_APPROVAL'): ?>
                            <span class="badge bg-warning">Pending Approval</span>
                        <?php elseif ($perf['status'] == 'LOCKED'): ?>
                            <span class="badge bg-dark">Locked</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Draft</span>
                        <?php endif; ?>
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