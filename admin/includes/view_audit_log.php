<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get filter parameters
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('-30 days'));
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');
$user_filter = isset($_GET['user_id']) ? (int)$_GET['user_id'] : '';
$table_filter = isset($_GET['table_name']) ? mysqli_real_escape_string($connection, $_GET['table_name']) : '';
$action_filter = isset($_GET['action']) ? mysqli_real_escape_string($connection, $_GET['action']) : '';

// Get users for filter dropdown
$users_query = "SELECT user_id, username, first_name, last_name FROM users ORDER BY username";
$users_result = mysqli_query($connection, $users_query);

// Get distinct tables for filter
$tables_query = "SELECT DISTINCT table_name FROM audit_log ORDER BY table_name";
$tables_result = mysqli_query($connection, $tables_query);

// Get distinct actions for filter
$actions_query = "SELECT DISTINCT action FROM audit_log ORDER BY action";
$actions_result = mysqli_query($connection, $actions_query);

// Get statistics
$stats_query = "SELECT 
                COUNT(*) as total_logs,
                COUNT(DISTINCT user_id) as active_users,
                COUNT(DISTINCT table_name) as tables_affected,
                SUM(CASE WHEN action = 'INSERT' THEN 1 ELSE 0 END) as inserts,
                SUM(CASE WHEN action = 'UPDATE' THEN 1 ELSE 0 END) as updates,
                SUM(CASE WHEN action = 'DELETE' THEN 1 ELSE 0 END) as deletes
                FROM audit_log
                WHERE DATE(created_at) BETWEEN '$date_from' AND '$date_to'";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">Audit Log</h1>
        <div>
            <button class="btn btn-success" onclick="exportLog()">
                <i class="bi bi-download"></i> Export CSV
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Logs</h5>
                    <h2><?php echo number_format($stats['total_logs'] ?? 0); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Active Users</h5>
                    <h2><?php echo $stats['active_users'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Tables</h5>
                    <h2><?php echo $stats['tables_affected'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Inserts</h5>
                    <h2><?php echo $stats['inserts'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h5 class="card-title">Updates</h5>
                    <h2><?php echo $stats['updates'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Deletes</h5>
                    <h2><?php echo $stats['deletes'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" id="date_from" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" id="date_to" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
                </div>
                <div class="col-md-2">
                    <label for="user_id" class="form-label">User</label>
                    <select id="user_id" name="user_id" class="form-control">
                        <option value="">All Users</option>
                        <?php while($user = mysqli_fetch_assoc($users_result)): ?>
                            <option value="<?php echo $user['user_id']; ?>" <?php echo ($user_filter == $user['user_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['first_name']); ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="table_name" class="form-label">Table</label>
                    <select id="table_name" name="table_name" class="form-control">
                        <option value="">All Tables</option>
                        <?php while($table = mysqli_fetch_assoc($tables_result)): ?>
                            <option value="<?php echo $table['table_name']; ?>" <?php echo ($table_filter == $table['table_name']) ? 'selected' : ''; ?>>
                                <?php echo $table['table_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="action" class="form-label">Action</label>
                    <select id="action" name="action" class="form-control">
                        <option value="">All Actions</option>
                        <?php while($action = mysqli_fetch_assoc($actions_result)): ?>
                            <option value="<?php echo $action['action']; ?>" <?php echo ($action_filter == $action['action']) ? 'selected' : ''; ?>>
                                <?php echo $action['action']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Audit Log Table -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>System Audit Trail</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr class="table-dark">
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Table</th>
                            <th>Record ID</th>
                            <th>Description</th>
                            <th>IP Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php  
                        // Build query with filters
                        $where = ["DATE(created_at) BETWEEN '$date_from' AND '$date_to'"];
                        
                        if (!empty($user_filter)) {
                            $where[] = "user_id = $user_filter";
                        }
                        if (!empty($table_filter)) {
                            $where[] = "table_name = '$table_filter'";
                        }
                        if (!empty($action_filter)) {
                            $where[] = "action = '$action_filter'";
                        }
                        
                        $where_clause = implode(' AND ', $where);
                        
                        $query = "SELECT * FROM audit_log 
                                  WHERE $where_clause 
                                  ORDER BY created_at DESC 
                                  LIMIT 1000";
                        
                        $result = mysqli_query($connection, $query);
                        
                        if (!$result) {
                            echo "<tr><td colspan='8' class='text-center text-danger'>Error: " . mysqli_error($connection) . "</td></tr>";
                        } else if (mysqli_num_rows($result) == 0) {
                            echo "<tr><td colspan='8' class='text-center'>No audit logs found for the selected criteria.</td></tr>";
                        } else {
                            while($log = mysqli_fetch_assoc($result)):
                                // Set badge color based on action
                                $action_class = 'secondary';
                                if ($log['action'] == 'INSERT') $action_class = 'success';
                                if ($log['action'] == 'UPDATE') $action_class = 'warning';
                                if ($log['action'] == 'DELETE') $action_class = 'danger';
                                if ($log['action'] == 'LOGIN') $action_class = 'info';
                                if ($log['action'] == 'LOGOUT') $action_class = 'secondary';
                                if ($log['action'] == 'EXPORT') $action_class = 'primary';
                                ?>
                                <tr>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($log['username']); ?></strong>
                                    </td>
                                    <td><span class="badge bg-<?php echo $action_class; ?>"><?php echo $log['action']; ?></span></td>
                                    <td><?php echo $log['table_name']; ?></td>
                                    <td>
                                        <?php if ($log['record_id']): ?>
                                            <code>#<?php echo $log['record_id']; ?></code>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars(substr($log['description'] ?? '', 0, 50)); ?>
                                        <?php if (strlen($log['description'] ?? '') > 50): ?>...<?php endif; ?>
                                    </td>
                                    <td><code><?php echo $log['ip_address'] ?? '-'; ?></code></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewLog(<?php echo $log['log_id']; ?>)" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile;
                        } ?>
                    </tbody>
                </table>
            </div>
            <div class="text-muted mt-2">
                <small>Showing last 1000 records. Use filters to narrow results.</small>
            </div>
        </div>
    </div>
</div>

<script>
function exportLog() {
    // Get current filter values
    const date_from = document.getElementById('date_from').value;
    const date_to = document.getElementById('date_to').value;
    const user_id = document.getElementById('user_id').value;
    const table_name = document.getElementById('table_name').value;
    const action = document.getElementById('action').value;
    
    // Build export URL
    let url = 'audit_log.php?export=1';
    if (date_from) url += '&date_from=' + date_from;
    if (date_to) url += '&date_to=' + date_to;
    if (user_id) url += '&user_id=' + user_id;
    if (table_name) url += '&table_name=' + table_name;
    if (action) url += '&action=' + action;
    
    window.location.href = url;
}
</script>