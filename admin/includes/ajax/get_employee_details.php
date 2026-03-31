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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid employee ID']);
    exit;
}

$employee_id = (int)$_GET['id'];

// Get employee details
$query = "SELECT u.user_id, u.first_name, u.last_name, u.user_email, u.user_role, u.user_status,
          r.role_name,
          t.type_name
          FROM users u
          LEFT JOIN user_roles r ON u.role_id = r.role_id
          LEFT JOIN user_types t ON u.type_id = t.type_id
          WHERE u.user_id = $employee_id";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Employee not found']);
    exit;
}

$employee = mysqli_fetch_assoc($result);

// Get recent activities
$activities_query = "SELECT * FROM employee_activities 
                     WHERE employee_id = $employee_id 
                     ORDER BY activity_date DESC 
                     LIMIT 5";
$activities_result = mysqli_query($connection, $activities_query);
$activities = [];
while ($row = mysqli_fetch_assoc($activities_result)) {
    $activities[] = $row;
}

// Get recent tasks
$tasks_query = "SELECT * FROM employee_tasks 
                WHERE employee_id = $employee_id 
                ORDER BY updated_at DESC 
                LIMIT 5";
$tasks_result = mysqli_query($connection, $tasks_query);
$tasks = [];
while ($row = mysqli_fetch_assoc($tasks_result)) {
    $tasks[] = $row;
}

// Get recent expenses
$expenses_query = "SELECT * FROM employee_expenses 
                   WHERE employee_id = $employee_id 
                   ORDER BY created_at DESC 
                   LIMIT 5";
$expenses_result = mysqli_query($connection, $expenses_query);
$expenses = [];
while ($row = mysqli_fetch_assoc($expenses_result)) {
    $expenses[] = $row;
}

ob_start();
?>

<div class="employee-details">
    <div class="text-center mb-4">
        <div class="avatar-circle mb-3">
            <i class="bi bi-person display-1"></i>
        </div>
        <h4><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></h4>
        <p class="text-muted"><?php echo htmlspecialchars($employee['user_email']); ?></p>
        <p>
            <span class="badge bg-info"><?php echo htmlspecialchars($employee['role_name'] ?? 'No Role'); ?></span>
            <span class="badge bg-secondary"><?php echo htmlspecialchars($employee['type_name'] ?? 'No Type'); ?></span>
            <span class="badge bg-<?php echo $employee['user_status'] == 'active' ? 'success' : 'danger'; ?>"><?php echo $employee['user_status']; ?></span>
        </p>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-mini">
                <span class="stat-value"><?php echo count($activities); ?></span>
                <span class="stat-label">Recent Activities</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-mini">
                <span class="stat-value"><?php echo count($tasks); ?></span>
                <span class="stat-label">Recent Tasks</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-mini">
                <span class="stat-value"><?php echo count($expenses); ?></span>
                <span class="stat-label">Recent Expenses</span>
            </div>
        </div>
    </div>

    <h6 class="mb-3">Recent Activities</h6>
    <?php if(count($activities) > 0): ?>
        <div class="list-group mb-4">
            <?php foreach($activities as $act): ?>
                <div class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <strong><?php echo date('M d, Y', strtotime($act['activity_date'])); ?></strong>
                        <span class="badge bg-primary"><?php echo $act['hours_worked']; ?> hrs</span>
                    </div>
                    <p class="mb-0 small text-muted"><?php echo htmlspecialchars(substr($act['nature_of_work'], 0, 100)); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-muted mb-4">No recent activities</p>
    <?php endif; ?>

    <h6 class="mb-3">Recent Tasks</h6>
    <?php if(count($tasks) > 0): ?>
        <div class="list-group mb-4">
            <?php foreach($tasks as $task): ?>
                <div class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <strong><?php echo htmlspecialchars($task['job_type'] ?: 'Task'); ?></strong>
                        <span class="badge bg-<?php echo $task['status'] == 'Completed' ? 'success' : ($task['status'] == 'Work in progress' ? 'primary' : 'warning'); ?>"><?php echo $task['status']; ?></span>
                    </div>
                    <p class="mb-0 small text-muted"><?php echo htmlspecialchars(substr($task['remarks'] ?? '', 0, 100)); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-muted mb-4">No recent tasks</p>
    <?php endif; ?>

    <h6 class="mb-3">Recent Expenses</h6>
    <?php if(count($expenses) > 0): ?>
        <div class="list-group">
            <?php foreach($expenses as $exp): ?>
                <div class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <span><?php echo date('M d, Y', strtotime($exp['expense_date'])); ?></span>
                        <span class="fw-bold text-success">AED <?php echo number_format($exp['amount'], 2); ?></span>
                    </div>
                    <p class="mb-0 small text-muted"><?php echo htmlspecialchars($exp['expense_type']); ?> - <?php echo htmlspecialchars($exp['description'] ?: 'No description'); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-muted">No recent expenses</p>
    <?php endif; ?>
</div>

<style>
.avatar-circle {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    color: white;
}
.stat-mini {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 12px;
    text-align: center;
}
.stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
}
.stat-label {
    font-size: 0.75rem;
    color: #6c757d;
}
</style>

<?php
$html = ob_get_clean();
echo json_encode(['success' => true, 'html' => $html]);

ob_end_flush();
?>