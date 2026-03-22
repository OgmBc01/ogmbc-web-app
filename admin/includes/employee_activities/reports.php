<?php
$employee_filter = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : '';
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'monthly';
$selected_month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Get employees for filter
$employees_query = "SELECT user_id, first_name, last_name FROM users WHERE user_status = 'active' AND type_id = 1 ORDER BY first_name";
$employees_result = mysqli_query($connection, $employees_query);

// Get available months with data
$months_query = "SELECT DISTINCT MONTH(activity_date) as month, YEAR(activity_date) as year 
                 FROM employee_activities 
                 UNION
                 SELECT DISTINCT MONTH(expense_date) as month, YEAR(expense_date) as year 
                 FROM employee_expenses
                 ORDER BY year DESC, month DESC";
$months_result = mysqli_query($connection, $months_query);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Employee Performance Reports</h4>
        <div class="btn-group">
            <a href="?tab=reports&report_type=weekly&employee_id=<?php echo $employee_filter; ?>" class="btn btn-outline-primary btn-sm <?php echo $report_type == 'weekly' ? 'active' : ''; ?>">Weekly</a>
            <a href="?tab=reports&report_type=monthly&employee_id=<?php echo $employee_filter; ?>" class="btn btn-outline-primary btn-sm <?php echo $report_type == 'monthly' ? 'active' : ''; ?>">Monthly</a>
            <a href="?tab=reports&report_type=custom&employee_id=<?php echo $employee_filter; ?>" class="btn btn-outline-primary btn-sm <?php echo $report_type == 'custom' ? 'active' : ''; ?>">Custom Range</a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <input type="hidden" name="tab" value="reports">
                <input type="hidden" name="report_type" value="<?php echo $report_type; ?>">
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
                <?php if($report_type == 'monthly'): ?>
                <div class="col-md-2">
                    <label class="form-label">Month</label>
                    <select name="month" class="form-select">
                        <?php for($m=1;$m<=12;$m++): ?>
                            <option value="<?php echo $m; ?>" <?php echo $selected_month == $m ? 'selected' : ''; ?>><?php echo date('F', mktime(0,0,0,$m,1)); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-select">
                        <?php for($y=date('Y');$y>=2024;$y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo $selected_year == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <?php elseif($report_type == 'custom'): ?>
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                </div>
                <?php else: ?>
                <div class="col-md-3">
                    <label class="form-label">Week Starting</label>
                    <input type="date" name="week_start" class="form-control" value="<?php echo isset($_GET['week_start']) ? $_GET['week_start'] : date('Y-m-d', strtotime('monday this week')); ?>">
                </div>
                <?php endif; ?>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Generate</button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="?tab=reports&report_type=<?php echo $report_type; ?>&export=1&employee_id=<?php echo $employee_filter; ?>&month=<?php echo $selected_month; ?>&year=<?php echo $selected_year; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
                       class="btn btn-success w-100">
                        <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Results -->
    <?php
    // Set date range based on report type
    if($report_type == 'monthly'):
        $start_date = date('Y-m-01', strtotime("$selected_year-$selected_month-01"));
        $end_date = date('Y-m-t', strtotime("$selected_year-$selected_month-01"));
    elseif($report_type == 'weekly'):
        $week_start = isset($_GET['week_start']) ? $_GET['week_start'] : date('Y-m-d', strtotime('monday this week'));
        $start_date = $week_start;
        $end_date = date('Y-m-d', strtotime('sunday this week', strtotime($week_start)));
    endif;

    // Get activities
    $where = ["activity_date BETWEEN '$start_date' AND '$end_date'"];
    if(!empty($employee_filter)) {
        $where[] = "employee_id = $employee_filter";
    }
    $where_clause = implode(' AND ', $where);
    $activities_query = "SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) as employee_name
                         FROM employee_activities a
                         JOIN users u ON a.employee_id = u.user_id
                         WHERE $where_clause
                         ORDER BY a.employee_id, a.activity_date";
    $activities_result = mysqli_query($connection, $activities_query);

    // Get tasks
    $tasks_query = "SELECT t.*, CONCAT(u.first_name, ' ', u.last_name) as employee_name, c.company_name
                    FROM employee_tasks t
                    JOIN users u ON t.employee_id = u.user_id
                    JOIN clients c ON t.client_id = c.client_id
                    WHERE (t.date_started BETWEEN '$start_date' AND '$end_date' 
                           OR t.updated_at BETWEEN '$start_date' AND '$end_date')
                    " . (!empty($employee_filter) ? "AND t.employee_id = $employee_filter" : "") . "
                    ORDER BY t.employee_id, t.updated_at DESC";
    $tasks_result = mysqli_query($connection, $tasks_query);

    // Get expenses
    $expenses_query = "SELECT e.*, CONCAT(u.first_name, ' ', u.last_name) as employee_name, c.company_name
                       FROM employee_expenses e
                       JOIN users u ON e.employee_id = u.user_id
                       LEFT JOIN clients c ON e.client_id = c.client_id
                       WHERE e.expense_date BETWEEN '$start_date' AND '$end_date'
                       " . (!empty($employee_filter) ? "AND e.employee_id = $employee_filter" : "") . "
                       ORDER BY e.employee_id, e.expense_date DESC";
    $expenses_result = mysqli_query($connection, $expenses_query);

    // Calculate totals
    $total_hours = 0; $total_tasks = 0; $total_expenses = 0;
    mysqli_data_seek($activities_result, 0);
    while($act = mysqli_fetch_assoc($activities_result)) { $total_hours += $act['hours_worked']; }
    $total_tasks = mysqli_num_rows($tasks_result);
    mysqli_data_seek($expenses_result, 0);
    while($exp = mysqli_fetch_assoc($expenses_result)) { $total_expenses += $exp['amount']; }
    ?>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="summary-card"><i class="bi bi-clock-history text-primary"></i><h3><?php echo $total_hours; ?> hrs</h3><p>Total Hours Worked</p></div></div>
        <div class="col-md-4"><div class="summary-card"><i class="bi bi-list-check text-success"></i><h3><?php echo $total_tasks; ?></h3><p>Tasks Processed</p></div></div>
        <div class="col-md-4"><div class="summary-card"><i class="bi bi-cash-stack text-warning"></i><h3>AED <?php echo number_format($total_expenses, 2); ?></h3><p>Total Expenses</p></div></div>
    </div>

    <!-- Employee Breakdown -->
    <div class="card shadow-sm mb-4">
        <div class="card-header dark-header">
            <h5 class="card-title"><i class="bi bi-people me-2"></i>Employee Breakdown</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr><th>Employee</th><th>Hours</th><th>Tasks</th><th>Expenses</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        // Group by employee
                        $employee_data = [];
                        mysqli_data_seek($activities_result, 0);
                        while($act = mysqli_fetch_assoc($activities_result)) {
                            $emp_id = $act['employee_id'];
                            if(!isset($employee_data[$emp_id])) {
                                $employee_data[$emp_id] = ['name' => $act['employee_name'], 'hours' => 0, 'tasks' => 0, 'expenses' => 0];
                            }
                            $employee_data[$emp_id]['hours'] += $act['hours_worked'];
                        }
                        mysqli_data_seek($tasks_result, 0);
                        while($task = mysqli_fetch_assoc($tasks_result)) {
                            $emp_id = $task['employee_id'];
                            if(isset($employee_data[$emp_id])) {
                                $employee_data[$emp_id]['tasks']++;
                            } else {
                                $employee_data[$emp_id] = ['name' => $task['employee_name'], 'hours' => 0, 'tasks' => 1, 'expenses' => 0];
                            }
                        }
                        mysqli_data_seek($expenses_result, 0);
                        while($exp = mysqli_fetch_assoc($expenses_result)) {
                            $emp_id = $exp['employee_id'];
                            if(isset($employee_data[$emp_id])) {
                                $employee_data[$emp_id]['expenses'] += $exp['amount'];
                            } else {
                                $employee_data[$emp_id] = ['name' => $exp['employee_name'], 'hours' => 0, 'tasks' => 0, 'expenses' => $exp['amount']];
                            }
                        }
                        foreach($employee_data as $emp):
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($emp['name']); ?></strong></td>
                            <td><?php echo $emp['hours']; ?> hrs</td>
                            <td><?php echo $emp['tasks']; ?></td>
                            <td>AED <?php echo number_format($emp['expenses'], 2); ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" onclick="viewEmployeeDetails(<?php echo array_search($emp, $employee_data); ?>)">
                                    <i class="bi bi-eye"></i> View Details
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Detailed Activities -->
    <div class="card shadow-sm mb-4">
        <div class="card-header dark-header">
            <h5 class="card-title"><i class="bi bi-calendar-check me-2"></i>Detailed Activities</h5>
        </div>
        <div class="card-body p-0">
            <?php if(mysqli_num_rows($activities_result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr><th>Date</th><th>Employee</th><th>Hours</th><th>Clients</th><th>Nature of Work</th> </tr>
                        </thead>
                        <tbody>
                            <?php while($act = mysqli_fetch_assoc($activities_result)): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($act['activity_date'])); ?></td>
                                <td><?php echo htmlspecialchars($act['employee_name']); ?></td>
                                <td><?php echo $act['hours_worked']; ?></td>
                                <td><?php echo htmlspecialchars($act['clients_attended'] ?: '-'); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($act['nature_of_work'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state"><p class="text-muted">No activities in this period.</p></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tasks Summary -->
    <div class="card shadow-sm mb-4">
        <div class="card-header dark-header">
            <h5 class="card-title"><i class="bi bi-list-check me-2"></i>Tasks Summary</h5>
        </div>
        <div class="card-body p-0">
            <?php if(mysqli_num_rows($tasks_result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light"><tr><th>Employee</th><th>Client</th><th>Job Type</th><th>Status</th><th>Remarks</th></tr></thead>
                        <tbody>
                            <?php while($task = mysqli_fetch_assoc($tasks_result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($task['employee_name']); ?></td>
                                <td><?php echo htmlspecialchars($task['company_name']); ?></td>
                                <td><?php echo htmlspecialchars($task['job_type']); ?></td>
                                <td><span class="badge bg-<?php echo $task['status'] == 'Completed' ? 'success' : 'primary'; ?>"><?php echo $task['status']; ?></span></td>
                                <td><?php echo htmlspecialchars(substr($task['remarks'] ?? '', 0, 80)); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state"><p class="text-muted">No tasks in this period.</p></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Expenses Summary -->
    <div class="card shadow-sm">
        <div class="card-header dark-header">
            <h5 class="card-title"><i class="bi bi-cash-stack me-2"></i>Expenses Summary</h5>
        </div>
        <div class="card-body p-0">
            <?php if(mysqli_num_rows($expenses_result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light"><tr><th>Date</th><th>Employee</th><th>Client</th><th>Type</th><th>Amount</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php while($exp = mysqli_fetch_assoc($expenses_result)): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($exp['expense_date'])); ?></td>
                                <td><?php echo htmlspecialchars($exp['employee_name']); ?></td>
                                <td><?php echo htmlspecialchars($exp['company_name'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($exp['expense_type']); ?></td>
                                <td class="text-success">AED <?php echo number_format($exp['amount'], 2); ?></td>
                                <td><span class="badge bg-<?php echo $exp['status'] == 'Approved' ? 'success' : ($exp['status'] == 'Rejected' ? 'danger' : 'warning'); ?>"><?php echo $exp['status']; ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state"><p class="text-muted">No expenses in this period.</p></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.summary-card { background: #f8f9fa; border-radius: 12px; padding: 20px; text-align: center; height: 100%; }
.summary-card i { font-size: 2rem; display: block; margin-bottom: 10px; }
.summary-card h3 { font-size: 1.5rem; font-weight: 700; margin-bottom: 5px; }
.summary-card p { margin: 0; color: #6c757d; font-size: 0.85rem; }
.dark-header { background: #1e293b; color: white; padding: 12px 20px; border-radius: 12px 12px 0 0; }
.empty-state { text-align: center; padding: 40px 20px; }
</style>