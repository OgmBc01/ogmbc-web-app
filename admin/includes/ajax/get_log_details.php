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
    echo json_encode(['success' => false, 'message' => 'Invalid log ID']);
    exit;
}

$log_id = (int)$_GET['id'];

$query = "SELECT * FROM audit_log WHERE log_id = $log_id";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Log entry not found']);
    exit;
}

$log = mysqli_fetch_assoc($result);

ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <table class="table table-sm table-borderless">
                <tr>
                    <th width="120">Log ID:</th>
                    <td><code>#<?php echo $log['log_id']; ?></code></td>
                </tr>
                <tr>
                    <th>Timestamp:</th>
                    <td><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></td>
                </tr>
                <tr>
                    <th>User:</th>
                    <td>
                        <strong><?php echo htmlspecialchars($log['username']); ?></strong> (ID: <?php echo $log['user_id']; ?>)
                    </td>
                </tr>
                <tr>
                    <th>Action:</th>
                    <td>
                        <span class="badge bg-<?php 
                            echo $log['action'] == 'INSERT' ? 'success' : 
                                ($log['action'] == 'UPDATE' ? 'warning' : 
                                ($log['action'] == 'DELETE' ? 'danger' : 
                                ($log['action'] == 'LOGIN' ? 'info' : 'secondary'))); 
                        ?>"><?php echo $log['action']; ?></span>
                    </td>
                </tr>
                <tr>
                    <th>Table:</th>
                    <td><?php echo $log['table_name']; ?></td>
                </tr>
                <tr>
                    <th>Record ID:</th>
                    <td><?php echo $log['record_id'] ? '<code>#' . $log['record_id'] . '</code>' : '-'; ?></td>
                </tr>
                <tr>
                    <th>Description:</th>
                    <td><?php echo nl2br(htmlspecialchars($log['description'] ?? '')); ?></td>
                </tr>
                <tr>
                    <th>IP Address:</th>
                    <td><code><?php echo $log['ip_address'] ?? '-'; ?></code></td>
                </tr>
                <tr>
                    <th>User Agent:</th>
                    <td><small><?php echo htmlspecialchars($log['user_agent'] ?? '-'); ?></small></td>
                </tr>
                <tr>
                    <th>Request URL:</th>
                    <td><small><?php echo htmlspecialchars($log['request_url'] ?? '-'); ?></small></td>
                </tr>
            </table>

            <?php if ($log['old_data'] && $log['old_data'] != 'null'): ?>
            <div class="mt-3">
                <h6>Old Data:</h6>
                <pre class="bg-light p-2 rounded"><code><?php 
                    $old = json_decode($log['old_data'], true);
                    print_r($old);
                ?></code></pre>
            </div>
            <?php endif; ?>

            <?php if ($log['new_data'] && $log['new_data'] != 'null'): ?>
            <div class="mt-3">
                <h6>New Data:</h6>
                <pre class="bg-light p-2 rounded"><code><?php 
                    $new = json_decode($log['new_data'], true);
                    print_r($new);
                ?></code></pre>
            </div>
            <?php endif; ?>

            <?php if ($log['changes'] && $log['changes'] != 'null'): ?>
            <div class="mt-3">
                <h6>Changes:</h6>
                <pre class="bg-light p-2 rounded"><code><?php 
                    $changes = json_decode($log['changes'], true);
                    print_r($changes);
                ?></code></pre>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$html = ob_get_clean();
echo json_encode(['success' => true, 'html' => $html]);

ob_end_flush();
?>