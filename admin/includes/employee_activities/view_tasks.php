<?php
$employee_filter = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$client_filter = isset($_GET['client_id']) ? (int)$_GET['client_id'] : '';

$where = ["1=1"];
if (!empty($employee_filter)) {
    $where[] = "t.employee_id = $employee_filter";
}
if (!empty($status_filter)) {
    $where[] = "t.status = '" . mysqli_real_escape_string($connection, $status_filter) . "'";
}
if (!empty($client_filter)) {
    $where[] = "t.client_id = $client_filter";
}
$where_clause = implode(' AND ', $where);

// Get employees for filter
$employees_query = "SELECT user_id, first_name, last_name FROM users WHERE user_status = 'active' AND type_id = 1 ORDER BY first_name";
$employees_result = mysqli_query($connection, $employees_query);

// Get clients for filter
$clients_query = "SELECT client_id, company_name FROM clients ORDER BY company_name";
$clients_result = mysqli_query($connection, $clients_query);

// Get tasks
$query = "SELECT t.*, 
          CONCAT(u.first_name, ' ', u.last_name) as employee_name,
          c.company_name
          FROM employee_tasks t
          JOIN users u ON t.employee_id = u.user_id
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
$result = mysqli_query($connection, $query);

// Get summary stats
$stats_query = "SELECT 
                COUNT(*) as total_tasks,
                SUM(CASE WHEN status = 'Work in progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'On Hold' THEN 1 ELSE 0 END) as on_hold
                FROM employee_tasks t
                WHERE $where_clause";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Employee Tasks</h4>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-2"><div class="stat-card-small bg-primary text-white"><div class="stat-icon"><i class="bi bi-list-check"></i></div><div class="stat-content"><h3 class="stat-value"><?php echo $stats['total_tasks'] ?? 0; ?></h3><p class="stat-label">Total</p></div></div></div>
        <div class="col-md-2"><div class="stat-card-small bg-info text-white"><div class="stat-icon"><i class="bi bi-play-circle"></i></div><div class="stat-content"><h3 class="stat-value"><?php echo $stats['in_progress'] ?? 0; ?></h3><p class="stat-label">In Progress</p></div></div></div>
        <div class="col-md-2"><div class="stat-card-small bg-warning text-white"><div class="stat-icon"><i class="bi bi-hourglass-split"></i></div><div class="stat-content"><h3 class="stat-value"><?php echo $stats['pending'] ?? 0; ?></h3><p class="stat-label">Pending</p></div></div></div>
        <div class="col-md-2"><div class="stat-card-small bg-secondary text-white"><div class="stat-icon"><i class="bi bi-pause-circle"></i></div><div class="stat-content"><h3 class="stat-value"><?php echo $stats['on_hold'] ?? 0; ?></h3><p class="stat-label">On Hold</p></div></div></div>
        <div class="col-md-2"><div class="stat-card-small bg-success text-white"><div class="stat-icon"><i class="bi bi-check-circle"></i></div><div class="stat-content"><h3 class="stat-value"><?php echo $stats['completed'] ?? 0; ?></h3><p class="stat-label">Completed</p></div></div></div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <input type="hidden" name="tab" value="tasks">
                <div class="col-md-3">
                    <label class="form-label">Employee</label>
                    <select name="employee_id" class="form-select">
                        <option value="">All Employees</option>
                        <?php while($emp = mysqli_fetch_assoc($employees_result)): ?>
                            <option value="<?php echo $emp['user_id']; ?>" <?php echo $employee_filter == $emp['user_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
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
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Employee</th>
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
                            <?php while($task = mysqli_fetch_assoc($result)):
                                $status_color = $task['status'] == 'Completed' ? 'success' : ($task['status'] == 'Work in progress' ? 'primary' : ($task['status'] == 'Pending' ? 'warning' : 'secondary'));
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($task['employee_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($task['company_name']); ?></td>
                                <td><?php echo htmlspecialchars($task['job_type']); ?></td>
                                <td><span class="badge bg-<?php echo $status_color; ?>"><?php echo $task['status']; ?></span></td>
                                <td><?php echo $task['date_started'] ? date('M d, Y', strtotime($task['date_started'])) : '-'; ?></td>
                                <td><?php echo $task['estimated_completion_date'] ? date('M d, Y', strtotime($task['estimated_completion_date'])) : '-'; ?></td>
                                <td><?php echo htmlspecialchars(substr($task['remarks'] ?? '', 0, 50)); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info" onclick="viewEmployeeDetails(<?php echo $task['employee_id']; ?>)">
                                        <i class="bi bi-eye"></i>
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
                    <p class="text-muted">No employee tasks match your criteria.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.stat-card-small {
    border-radius: 12px;
    padding: 12px;
    display: flex;
    align-items: center;
    gap: 12px;
    height: 100%;
}
.stat-card-small .stat-icon { width: 40px; height: 40px; border-radius: 8px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
.stat-card-small .stat-value { font-size: 1.2rem; font-weight: 600; margin-bottom: 2px; }
.stat-card-small .stat-label { font-size: 0.7rem; opacity: 0.9; margin: 0; }
.dark-header { background: #1e293b; color: white; padding: 12px 20px; border-radius: 12px 12px 0 0; }
.empty-state { text-align: center; padding: 60px 20px; }
</style>