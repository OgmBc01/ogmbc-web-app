<?php
$employee_filter = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');

$where = ["a.activity_date BETWEEN '$date_from' AND '$date_to'"];
if (!empty($employee_filter)) {
    $where[] = "a.employee_id = $employee_filter";
}
$where_clause = implode(' AND ', $where);

// Get all employees for filter
$employees_query = "SELECT user_id, first_name, last_name FROM users 
                    WHERE user_status = 'active' AND type_id = 1 
                    ORDER BY first_name";
$employees_result = mysqli_query($connection, $employees_query);

// Get activities with employee details
$query = "SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) as employee_name
          FROM employee_activities a
          JOIN users u ON a.employee_id = u.user_id
          WHERE $where_clause
          ORDER BY a.activity_date DESC, a.employee_id";
$result = mysqli_query($connection, $query);

// Get summary stats
$stats_query = "SELECT 
                COUNT(*) as total_activities,
                SUM(a.hours_worked) as total_hours,
                COUNT(DISTINCT a.employee_id) as active_employees
                FROM employee_activities a
                WHERE $where_clause";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Daily Activities</h4>
        <div class="btn-group">
            <a href="?tab=activities&report_type=excel&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&employee_id=<?php echo $employee_filter; ?>" 
               class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card stat-card-primary">
                <div class="stat-card-body d-flex align-items-center">
                    <div class="stat-icon"><i class="bi bi-calendar-check text-primary"></i></div>
                    <div class="stat-content ms-3"><h3 class="stat-value"><?php echo $stats['total_activities'] ?? 0; ?></h3><p class="stat-label">Total Activities</p></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-card-success">
                <div class="stat-card-body d-flex align-items-center">
                    <div class="stat-icon"><i class="bi bi-clock-history text-success"></i></div>
                    <div class="stat-content ms-3"><h3 class="stat-value"><?php echo $stats['total_hours'] ?? 0; ?> hrs</h3><p class="stat-label">Total Hours</p></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-card-info">
                <div class="stat-card-body d-flex align-items-center">
                    <div class="stat-icon"><i class="bi bi-people text-info"></i></div>
                    <div class="stat-content ms-3"><h3 class="stat-value"><?php echo $stats['active_employees'] ?? 0; ?></h3><p class="stat-label">Active Employees</p></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <input type="hidden" name="tab" value="activities">
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
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Activities Table -->
    <div class="card shadow-sm">
        <div class="card-header dark-header">
            <h5 class="card-title"><i class="bi bi-list-ul me-2"></i>Employee Activities</h5>
        </div>
        <div class="card-body p-0">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Employee</th>
                                <th>Hours</th>
                                <th>Clients</th>
                                <th>Location</th>
                                <th>Nature of Work</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($row['activity_date'])); ?></td>
                                <td><strong><?php echo htmlspecialchars($row['employee_name']); ?></strong></td>
                                <td><span class="badge bg-primary"><?php echo $row['hours_worked']; ?> hrs</span></td>
                                <td><?php echo htmlspecialchars($row['clients_attended'] ?: '-'); ?></td>
                                <td><span class="badge bg-secondary"><?php echo $row['work_location']; ?></span></td>
                                <td><?php echo nl2br(htmlspecialchars(substr($row['nature_of_work'], 0, 100))); ?><?php echo strlen($row['nature_of_work']) > 100 ? '...' : ''; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info" onclick="viewEmployeeDetails(<?php echo $row['employee_id']; ?>)">
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
                    <i class="bi bi-calendar-x display-1 text-muted"></i>
                    <h5 class="mt-3">No activities found</h5>
                    <p class="text-muted">No employee activities match your criteria.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.stat-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    border-left: 6px solid #e0e0e0;
    padding: 0;
    height: 100%;
}
.stat-card-primary { border-left-color: #667eea; }
.stat-card-success { border-left-color: #38c172; }
.stat-card-info { border-left-color: #17a2b8; }
.stat-card-body { padding: 18px 20px; display: flex; align-items: center; }
.stat-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; background: #f5f6fa; border-radius: 50%; }
.stat-value { font-size: 1.4rem; font-weight: 700; color: #222; }
.stat-label { font-size: 0.85rem; color: #666; }
.dark-header { background: #1e293b; color: white; padding: 12px 20px; border-radius: 12px 12px 0 0; }
.empty-state { text-align: center; padding: 60px 20px; }
</style>