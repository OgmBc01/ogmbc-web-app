<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/../../../includes/database.php';

set_exception_handler(function($e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit();
});
set_error_handler(function($errno, $errstr) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $errstr]);
    exit();
});
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        echo json_encode(['success' => false, 'message' => 'Fatal error: ' . $error['message']]);
        exit();
    }
});

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$target_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($target_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid target ID']);
    exit();
}

// Fetch target details
$query = "SELECT 
            st.*,
            CONCAT(u.first_name, ' ', u.last_name) as employee_name,
            u.user_email as employee_email,
            CONCAT(vl.first_name, ' ', vl.last_name) as validator_name,
            TIMESTAMPDIFF(DAY, st.created_at, st.validated_at) as validation_days
          FROM sales_targets st
          JOIN users u ON st.employee_id = u.user_id
          LEFT JOIN users vl ON st.validated_by = vl.user_id
          WHERE st.target_id = $target_id AND st.employee_id = {$_SESSION['user_id']}";

$result = mysqli_query($connection, $query);
$target = $result ? mysqli_fetch_assoc($result) : null;
if (!$target) {
    echo json_encode(['success' => false, 'message' => 'Target not found']);
    exit();
}

$period_name = date('F Y', mktime(0, 0, 0, $target['month'], 1, $target['year']));


$html = '<div class="table-responsive"><table class="table table-bordered">'
    . '<tr><th width="40%">Period</th><td><strong>' . $period_name . '</strong></td></tr>'
    . '<tr><th>Employee</th><td>' . htmlspecialchars($target['employee_name']) . '<br><small>' . htmlspecialchars($target['employee_email']) . '</small></td></tr>'
    . '<tr><th>Target Amount</th><td>₱' . number_format($target['target_value'], 2) . '</td></tr>'
    . '<tr><th>Actual Achievement</th><td>' . ($target['actual_value'] ? '₱' . number_format($target['actual_value'], 2) : '<span class="text-muted">Not yet submitted</span>') . '</td></tr>'
    . '<tr><th>Attainment Percentage</th><td>' . ($target['attainment_percentage'] ? number_format($target['attainment_percentage'], 2) . '%' : '<span class="text-muted">—</span>') . '</td></tr>'
    . '<tr><th>Points Awarded</th><td>' . ($target['points_awarded'] ? number_format($target['points_awarded']) . ' points' : '<span class="text-muted">—</span>') . '</td></tr>'
    . '<tr><th>Status</th><td><span class="badge bg-' . ($target['status'] == 'VALIDATED' ? 'success' : ($target['status'] == 'SUBMITTED' ? 'info' : ($target['status'] == 'PENDING' ? 'warning' : 'danger'))) . '">' . $target['status'] . '</span></td></tr>';

if ($target['status'] == 'VALIDATED' && $target['validated_at']) {
    $html .= '
        <tr>
            <th>Validated By</th>
            <td>' . htmlspecialchars($target['validator_name']) . '</td>
        </tr>
        <tr>
            <th>Validated At</th>
            <td>' . date('F d, Y h:i A', strtotime($target['validated_at'])) . '</td>
        </tr>';
    
    if ($target['validation_days']) {
        $html .= '
        <tr>
            <th>Processing Time</th>
            <td>' . $target['validation_days'] . ' days</td>
        </tr>';
    }
}

if ($target['validation_notes']) {
    $html .= '
        <tr>
            <th>Validation Notes</th>
            <td>' . nl2br(htmlspecialchars($target['validation_notes'])) . '</td>
        </tr>';
}

if ($target['evidence_file']) {
    $html .= '
        <tr>
            <th>Evidence File</th>
            <td><a href="../uploads/' . htmlspecialchars($target['evidence_file']) . '" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark-pdf"></i> View Document</a></td>
        </tr>';
}

$html .= '
        <tr>
            <th>Created At</th>
            <td>' . date('F d, Y h:i A', strtotime($target['created_at'])) . '</td>
        </tr>
        <tr>
            <th>Last Updated</th>
            <td>' . date('F d, Y h:i A', strtotime($target['updated_at'])) . '</td>
        </tr>
    </table>
</div>';

echo json_encode(['success' => true, 'html' => $html]);
?>