<?php
$user_id = $_SESSION['user_id'];
$status_filter = isset($_GET['task_status']) ? $_GET['task_status'] : '';
$client_filter = isset($_GET['client_id']) ? (int)$_GET['client_id'] : '';

$where = ["t.employee_id = $user_id"];
if (!empty($status_filter)) {
    $where[] = "t.status = '" . mysqli_real_escape_string($connection, $status_filter) . "'";
}
if (!empty($client_filter)) {
    $where[] = "t.client_id = $client_filter";
}
$where_clause = implode(' AND ', $where);

// Get statistics
$stats_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Work in progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'On Hold' THEN 1 ELSE 0 END) as on_hold
                FROM employee_tasks WHERE employee_id = $user_id";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get tasks
$tasks_query = "SELECT t.*, c.company_name 
                FROM employee_tasks t
                JOIN clients c ON t.client_id = c.client_id
                WHERE $where_clause
                ORDER BY 
                    CASE t.status
                        WHEN 'Work in progress' THEN 1
                        WHEN 'Pending' THEN 2
                        WHEN 'On Hold' THEN 3
                        WHEN 'Completed' THEN 4
                    END,
                    t.updated_at DESC";
$tasks_result = mysqli_query($connection, $tasks_query);

// Get clients for filter
$clients_query = "SELECT DISTINCT c.client_id, c.company_name 
                  FROM clients c
                  JOIN employee_tasks t ON c.client_id = t.client_id
                  WHERE t.employee_id = $user_id
                  ORDER BY c.company_name";
$clients_result = mysqli_query($connection, $clients_query);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">My Tasks</h4>
        <button class="btn btn-primary btn-sm" onclick="showAddTaskModal()">
            <i class="bi bi-plus-circle"></i> Add Task
        </button>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="stat-card-small bg-primary text-white">
                <div class="stat-icon"><i class="bi bi-list-check"></i></div>
                <div class="stat-content"><h3 class="stat-value"><?php echo $stats['total'] ?? 0; ?></h3><p class="stat-label">Total</p></div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card-small bg-info text-white">
                <div class="stat-icon"><i class="bi bi-play-circle"></i></div>
                <div class="stat-content"><h3 class="stat-value"><?php echo $stats['in_progress'] ?? 0; ?></h3><p class="stat-label">In Progress</p></div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card-small bg-warning text-white">
                <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-content"><h3 class="stat-value"><?php echo $stats['pending'] ?? 0; ?></h3><p class="stat-label">Pending</p></div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card-small bg-secondary text-white">
                <div class="stat-icon"><i class="bi bi-pause-circle"></i></div>
                <div class="stat-content"><h3 class="stat-value"><?php echo $stats['on_hold'] ?? 0; ?></h3><p class="stat-label">On Hold</p></div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card-small bg-success text-white">
                <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                <div class="stat-content"><h3 class="stat-value"><?php echo $stats['completed'] ?? 0; ?></h3><p class="stat-label">Completed</p></div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="task_status" class="form-select">
                        <option value="">All</option>
                        <option value="Work in progress" <?php echo $status_filter == 'Work in progress' ? 'selected' : ''; ?>>Work in progress</option>
                        <option value="Completed" <?php echo $status_filter == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="Pending" <?php echo $status_filter == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="On Hold" <?php echo $status_filter == 'On Hold' ? 'selected' : ''; ?>>On Hold</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Client</label>
                    <select name="client_id" class="form-select">
                        <option value="">All Clients</option>
                        <?php while($client = mysqli_fetch_assoc($clients_result)): ?>
                            <option value="<?php echo $client['client_id']; ?>" <?php echo $client_filter == $client['client_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($client['company_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tasks Table -->
    <div class="card shadow-sm">
        <div class="card-header dark-header">
            <h5 class="card-title"><i class="bi bi-list-ul me-2"></i>Task List</h5>
        </div>
        <div class="card-body p-0">
            <?php if (mysqli_num_rows($tasks_result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Client</th>
                                <th>Job Type</th>
                                <th>Status</th>
                                <th>Start Date</th>
                                <th>Est. Completion</th>
                                <th>Remarks</th>
                                <th>Actions</th>
                             </tr>
                        </thead>
                        <tbody>
                            <?php while($task = mysqli_fetch_assoc($tasks_result)):
                                $status_color = 'secondary';
                                if($task['status'] == 'Work in progress') $status_color = 'primary';
                                elseif($task['status'] == 'Completed') $status_color = 'success';
                                elseif($task['status'] == 'Pending') $status_color = 'warning';
                                elseif($task['status'] == 'On Hold') $status_color = 'secondary';
                            ?>
                             <tr>
                                <td><strong><?php echo htmlspecialchars($task['company_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($task['job_type']); ?></td>
                                <td><span class="badge bg-<?php echo $status_color; ?>"><?php echo $task['status']; ?></span></td>
                                <td><?php echo $task['date_started'] ? date('M d, Y', strtotime($task['date_started'])) : '-'; ?></td>
                                <td><?php echo $task['estimated_completion_date'] ? date('M d, Y', strtotime($task['estimated_completion_date'])) : '-'; ?></td>
                                <td><?php echo htmlspecialchars(substr($task['remarks'] ?? '', 0, 50)); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info" onclick="showAddTaskModal(<?php echo $task['task_id']; ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="updateTaskStatus(<?php echo $task['task_id']; ?>)">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </td>
                             </tr>
                            <?php endwhile; ?>
                        </tbody>
                     </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-list-check display-1 text-muted"></i>
                    <h5 class="mt-3">No tasks found</h5>
                    <p class="text-muted">Add your first task to track your work.</p>
                    <button class="btn btn-primary mt-2" onclick="showAddTaskModal()"><i class="bi bi-plus-circle me-2"></i>Add Task</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.stat-card-small {
    border-radius: 18px;
    padding: 22px 18px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: center;
    box-shadow: 0 4px 18px rgba(30, 41, 59, 0.10), 0 1.5px 4px rgba(30,41,59,0.08);
    transition: transform 0.12s;
    min-height: 110px;
    position: relative;
    overflow: hidden;
}
.stat-card-small:hover {
    transform: translateY(-2px) scale(1.03);
    box-shadow: 0 8px 24px rgba(30, 41, 59, 0.16), 0 2px 8px rgba(30,41,59,0.10);
}
.stat-card-small .stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: rgba(255,255,255,0.18);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.7rem;
    margin-bottom: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.stat-card-small .stat-value {
    font-size: 2.1rem;
    font-weight: 700;
    margin-bottom: 2px;
    color: #fff;
    letter-spacing: 0.5px;
}
.stat-card-small .stat-label {
    font-size: 1rem;
    opacity: 0.93;
    margin: 0;
    color: #f3f4f6;
    font-weight: 500;
    letter-spacing: 0.2px;
}
.dark-header { background: #1e293b; color: white; padding: 12px 20px; border-radius: 12px 12px 0 0; }
.empty-state { text-align: center; padding: 60px 20px; }
</style>

<script>
function updateTaskStatus(taskId) {
    let newStatus = prompt("Enter new status (Work in progress, Completed, Pending, On Hold):", "Work in progress");
    if (newStatus) {
        fetch('includes/ajax/update_task_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'task_id=' + taskId + '&status=' + encodeURIComponent(newStatus)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.message, 'danger');
            }
        });
    }
}
</script>