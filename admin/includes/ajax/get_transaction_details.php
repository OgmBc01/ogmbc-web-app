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
    echo json_encode(['success' => false, 'message' => 'Invalid transaction ID']);
    exit;
}

$ledger_id = (int)$_GET['id'];

$query = "SELECT pl.*, 
          CONCAT(emp.first_name, ' ', emp.last_name) as employee_name,
          emp.user_email as employee_email,
          CONCAT(cre.first_name, ' ', cre.last_name) as created_by_name,
          CONCAT(app.first_name, ' ', app.last_name) as approved_by_name
          FROM points_ledger pl
          JOIN users emp ON pl.employee_id = emp.user_id
          LEFT JOIN users cre ON pl.created_by = cre.user_id
          LEFT JOIN users app ON pl.approved_by = app.user_id
          WHERE pl.ledger_id = $ledger_id";

$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Transaction not found']);
    exit;
}

$transaction = mysqli_fetch_assoc($result);

// Get source details based on source_type
$source_details = '';
if ($transaction['source_type'] == 'ENGAGEMENT' && $transaction['source_id']) {
    $source_query = "SELECT e.title, c.company_name 
                     FROM engagements e
                     JOIN clients c ON e.client_id = c.client_id
                     WHERE e.engagement_id = " . $transaction['source_id'];
    $source_result = mysqli_query($connection, $source_query);
    if (mysqli_num_rows($source_result) > 0) {
        $source = mysqli_fetch_assoc($source_result);
        $source_details = "Engagement: " . $source['title'] . " - " . $source['company_name'];
    }
}

ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <table class="table table-sm table-borderless">
                <tr>
                    <th width="150">Transaction ID:</th>
                    <td>#<?php echo $transaction['ledger_id']; ?></td>
                </tr>
                <tr>
                    <th>Employee:</th>
                    <td>
                        <strong><?php echo htmlspecialchars($transaction['employee_name']); ?></strong>
                        <br><small><?php echo htmlspecialchars($transaction['employee_email']); ?></small>
                    </td>
                </tr>
                <tr>
                    <th>Date:</th>
                    <td><?php echo date('F d, Y H:i:s', strtotime($transaction['created_at'])); ?></td>
                </tr>
                <tr>
                    <th>Source Type:</th>
                    <td>
                        <span class="badge bg-<?php 
                            echo $transaction['source_type'] == 'ENGAGEMENT' ? 'primary' : 
                                ($transaction['source_type'] == 'SALES_TARGET' ? 'success' : 
                                ($transaction['source_type'] == 'CLIENT_FEEDBACK' ? 'info' : 
                                ($transaction['source_type'] == 'MANUAL_ADJUSTMENT' ? 'warning' : 'secondary'))); 
                        ?>"><?php echo $transaction['source_type']; ?></span>
                        <?php if ($transaction['source_id']): ?>
                            <small class="text-muted ms-2">ID: <?php echo $transaction['source_id']; ?></small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if ($source_details): ?>
                <tr>
                    <th>Source Details:</th>
                    <td><?php echo htmlspecialchars($source_details); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th>Description:</th>
                    <td><?php echo htmlspecialchars($transaction['description'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <th>Points:</th>
                    <td>
                        <span class="badge bg-<?php echo $transaction['points'] >= 0 ? 'success' : 'danger'; ?> fs-6">
                            <?php echo ($transaction['points'] >= 0 ? '+' : '') . $transaction['points']; ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Points Type:</th>
                    <td><?php echo $transaction['points_type']; ?></td>
                </tr>
                <tr>
                    <th>Status:</th>
                    <td>
                        <?php if ($transaction['approved_by']): ?>
                            <span class="badge bg-success">Approved</span>
                            <br><small>by <?php echo htmlspecialchars($transaction['approved_by_name']); ?> on <?php echo date('M d, Y', strtotime($transaction['approved_at'])); ?></small>
                        <?php elseif ($transaction['requires_approval']): ?>
                            <span class="badge bg-warning">Pending Approval</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Auto-approved</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Created By:</th>
                    <td>
                        <?php echo htmlspecialchars($transaction['created_by_name']); ?>
                        <br><small><?php echo date('M d, Y H:i', strtotime($transaction['created_at'])); ?></small>
                    </td>
                </tr>
                <?php if ($transaction['calculation_data']): ?>
                <tr>
                    <th>Calculation Data:</th>
                    <td>
                        <pre class="bg-light p-2 rounded"><code><?php 
                            $calc = json_decode($transaction['calculation_data'], true);
                            print_r($calc);
                        ?></code></pre>
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

ob_end_flush();
?>