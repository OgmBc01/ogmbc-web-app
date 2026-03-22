<?php
$user_id = $_SESSION['user_id'];
$selected_month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'weekly';

// Get available months with data
$months_query = "SELECT DISTINCT MONTH(activity_date) as month, YEAR(activity_date) as year 
                 FROM employee_activities WHERE employee_id = $user_id
                 UNION
                 SELECT DISTINCT MONTH(expense_date) as month, YEAR(expense_date) as year 
                 FROM employee_expenses WHERE employee_id = $user_id
                 ORDER BY year DESC, month DESC";
$months_result = mysqli_query($connection, $months_query);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Generate Reports</h4>
        <div class="btn-group">
            <a href="?report_type=weekly" class="btn btn-outline-primary btn-sm <?php echo $report_type == 'weekly' ? 'active' : ''; ?>">Weekly</a>
            <a href="?report_type=monthly" class="btn btn-outline-primary btn-sm <?php echo $report_type == 'monthly' ? 'active' : ''; ?>">Monthly</a>
            <a href="?report_type=custom" class="btn btn-outline-primary btn-sm <?php echo $report_type == 'custom' ? 'active' : ''; ?>">Custom Range</a>
        </div>
    </div>

    <!-- Weekly Report -->
    <?php if ($report_type == 'weekly'): 
        $week_start = isset($_GET['week_start']) ? $_GET['week_start'] : date('Y-m-d', strtotime('monday this week'));
        $week_end = date('Y-m-d', strtotime('sunday this week', strtotime($week_start)));
        
        // Get weekly data
        $activities_query = "SELECT * FROM employee_activities WHERE employee_id = $user_id AND activity_date BETWEEN '$week_start' AND '$week_end' ORDER BY activity_date";
        $activities_result = mysqli_query($connection, $activities_query);
        
        $tasks_query = "SELECT * FROM employee_tasks WHERE employee_id = $user_id AND (date_started BETWEEN '$week_start' AND '$week_end' OR updated_at BETWEEN '$week_start' AND '$week_end')";
        $tasks_result = mysqli_query($connection, $tasks_query);
        
        $expenses_query = "SELECT * FROM employee_expenses WHERE employee_id = $user_id AND expense_date BETWEEN '$week_start' AND '$week_end'";
        $expenses_result = mysqli_query($connection, $expenses_query);
        
        $total_hours = 0;
        while($act = mysqli_fetch_assoc($activities_result)) { $total_hours += $act['hours_worked']; }
        mysqli_data_seek($activities_result, 0);
    ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header dark-header d-flex justify-content-between align-items-center">
            <h5 class="card-title"><i class="bi bi-calendar-week me-2"></i>Weekly Report</h5>
            <div class="d-flex gap-2">
                <a href="?report_type=weekly&week_start=<?php echo date('Y-m-d', strtotime('-7 days', strtotime($week_start))); ?>" class="btn btn-sm btn-outline-light"><i class="bi bi-chevron-left"></i> Prev</a>
                <span class="px-3 py-1 bg-light text-dark rounded"><?php echo date('M d', strtotime($week_start)); ?> - <?php echo date('M d, Y', strtotime($week_end)); ?></span>
                <a href="?report_type=weekly&week_start=<?php echo date('Y-m-d', strtotime('+7 days', strtotime($week_start))); ?>" class="btn btn-sm btn-outline-light">Next <i class="bi bi-chevron-right"></i></a>
                <button onclick="exportReport('weekly', '<?php echo $week_start; ?>', '<?php echo $week_end; ?>')" class="btn btn-sm btn-success">
                    <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Summary Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4"><div class="summary-card"><i class="bi bi-clock-history text-primary"></i><h3><?php echo $total_hours; ?> hrs</h3><p>Total Hours</p></div></div>
                <div class="col-md-4"><div class="summary-card"><i class="bi bi-list-check text-success"></i><h3><?php echo mysqli_num_rows($tasks_result); ?></h3><p>Tasks Completed/Updated</p></div></div>
                <div class="col-md-4"><div class="summary-card"><i class="bi bi-cash-stack text-warning"></i><h3><?php 
                    $total_expenses = 0;
                    while($exp = mysqli_fetch_assoc($expenses_result)) { $total_expenses += $exp['amount']; }
                    echo 'AED ' . number_format($total_expenses, 2);
                    mysqli_data_seek($expenses_result, 0);
                ?></h3><p>Expenses</p></div></div>
            </div>
            
            <!-- Daily Activities Table -->
            <h6 class="mt-4 mb-3">Daily Activities</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr><th>Date</th><th>Day</th><th>Hours</th><th>Clients</th><th>Location</th><th>Nature of Work</th></tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($activities_result) > 0): ?>
                            <?php while($act = mysqli_fetch_assoc($activities_result)): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($act['activity_date'])); ?></td>
                                <td><?php echo date('l', strtotime($act['activity_date'])); ?></td>
                                <td><?php echo $act['hours_worked']; ?></td>
                                <td><?php echo htmlspecialchars($act['clients_attended']); ?></td>
                                <td><?php echo $act['work_location']; ?></td>
                                <td><?php echo nl2br(htmlspecialchars($act['nature_of_work'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center">No activities logged this week</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Tasks Table -->
            <h6 class="mt-4 mb-3">Tasks</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr><th>Client</th><th>Job Type</th><th>Status</th><th>Remarks</th><th>Updated</th></tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($tasks_result) > 0): ?>
                            <?php while($task = mysqli_fetch_assoc($tasks_result)): ?>
                            <tr>
                                <?php
                                $client_name = '';
                                $client_q = "SELECT company_name FROM clients WHERE client_id = " . $task['client_id'];
                                $client_r = mysqli_query($connection, $client_q);
                                if($client_r && mysqli_num_rows($client_r) > 0) $client_name = mysqli_fetch_assoc($client_r)['company_name'];
                                ?>
                                <td><?php echo htmlspecialchars($client_name); ?></td>
                                <td><?php echo htmlspecialchars($task['job_type']); ?></td>
                                <td><span class="badge bg-<?php echo $task['status'] == 'Completed' ? 'success' : ($task['status'] == 'Work in progress' ? 'primary' : 'warning'); ?>"><?php echo $task['status']; ?></span></td>
                                <td><?php echo htmlspecialchars($task['remarks']); ?></td>
                                <td><?php echo date('M d', strtotime($task['updated_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center">No tasks this week</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Monthly Report -->
    <?php if ($report_type == 'monthly'): 
        $start_date = date('Y-m-01', strtotime("$selected_year-$selected_month-01"));
        $end_date = date('Y-m-t', strtotime("$selected_year-$selected_month-01"));
        
        $activities_query = "SELECT * FROM employee_activities WHERE employee_id = $user_id AND activity_date BETWEEN '$start_date' AND '$end_date' ORDER BY activity_date";
        $activities_result = mysqli_query($connection, $activities_query);
        
        $tasks_query = "SELECT * FROM employee_tasks WHERE employee_id = $user_id AND (date_started BETWEEN '$start_date' AND '$end_date' OR updated_at BETWEEN '$start_date' AND '$end_date')";
        $tasks_result = mysqli_query($connection, $tasks_query);
        
        $expenses_query = "SELECT * FROM employee_expenses WHERE employee_id = $user_id AND expense_date BETWEEN '$start_date' AND '$end_date'";
        $expenses_result = mysqli_query($connection, $expenses_query);
        
        $total_hours = 0; $total_expenses = 0;
        while($act = mysqli_fetch_assoc($activities_result)) { $total_hours += $act['hours_worked']; }
        mysqli_data_seek($activities_result, 0);
        while($exp = mysqli_fetch_assoc($expenses_result)) { $total_expenses += $exp['amount']; }
        mysqli_data_seek($expenses_result, 0);
    ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header dark-header d-flex justify-content-between align-items-center">
            <h5 class="card-title"><i class="bi bi-calendar-month me-2"></i>Monthly Report - <?php echo date('F Y', strtotime($start_date)); ?></h5>
            <div class="d-flex gap-2">
                <select id="monthSelect" class="form-select form-select-sm w-auto" onchange="window.location.href='?report_type=monthly&month='+this.value+'&year=<?php echo $selected_year; ?>'">
                    <?php for($m=1;$m<=12;$m++): ?>
                        <option value="<?php echo $m; ?>" <?php echo $selected_month==$m?'selected':''; ?>><?php echo date('F', mktime(0,0,0,$m,1)); ?></option>
                    <?php endfor; ?>
                </select>
                <select id="yearSelect" class="form-select form-select-sm w-auto" onchange="window.location.href='?report_type=monthly&month=<?php echo $selected_month; ?>&year='+this.value">
                    <?php for($y=date('Y');$y>=2024;$y--): ?>
                        <option value="<?php echo $y; ?>" <?php echo $selected_year==$y?'selected':''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
                <button onclick="exportReport('monthly', '<?php echo $start_date; ?>', '<?php echo $end_date; ?>')" class="btn btn-sm btn-success">
                    <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-4"><div class="summary-card"><i class="bi bi-clock-history text-primary"></i><h3><?php echo $total_hours; ?> hrs</h3><p>Total Hours</p></div></div>
                <div class="col-md-4"><div class="summary-card"><i class="bi bi-list-check text-success"></i><h3><?php echo mysqli_num_rows($tasks_result); ?></h3><p>Tasks Updated</p></div></div>
                <div class="col-md-4"><div class="summary-card"><i class="bi bi-cash-stack text-warning"></i><h3>AED <?php echo number_format($total_expenses, 2); ?></h3><p>Expenses</p></div></div>
            </div>
            
            <!-- Daily Breakdown -->
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light"><tr><th>Date</th><th>Hours</th><th>Clients</th><th>Tasks Completed</th><th>Expenses</th></tr></thead>
                    <tbody>
                        <?php
                        $current_date = $start_date;
                        while(strtotime($current_date) <= strtotime($end_date)):
                            $day_hours = 0; $day_clients = []; $day_tasks = 0; $day_expenses = 0;
                            mysqli_data_seek($activities_result, 0);
                            while($act = mysqli_fetch_assoc($activities_result)):
                                if($act['activity_date'] == $current_date):
                                    $day_hours += $act['hours_worked'];
                                    if($act['clients_attended']) $day_clients[] = $act['clients_attended'];
                                endif;
                            endwhile;
                            mysqli_data_seek($tasks_result, 0);
                            while($task = mysqli_fetch_assoc($tasks_result)):
                                if($task['updated_at'] && date('Y-m-d', strtotime($task['updated_at'])) == $current_date):
                                    $day_tasks++;
                                endif;
                            endwhile;
                            mysqli_data_seek($expenses_result, 0);
                            while($exp = mysqli_fetch_assoc($expenses_result)):
                                if($exp['expense_date'] == $current_date):
                                    $day_expenses += $exp['amount'];
                                endif;
                            endwhile;
                            
                            if($day_hours > 0 || $day_tasks > 0 || $day_expenses > 0):
                        ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($current_date)); ?></td>
                            <td><?php echo $day_hours; ?></td>
                            <td><?php echo implode(', ', array_unique($day_clients)); ?></td>
                            <td><?php echo $day_tasks; ?></td>
                            <td><?php echo $day_expenses > 0 ? 'AED ' . number_format($day_expenses, 2) : '-'; ?></td>
                        </tr>
                        <?php endif; $current_date = date('Y-m-d', strtotime('+1 day', strtotime($current_date))); endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Custom Range Report -->
    <?php if ($report_type == 'custom'): 
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
        
        $activities_query = "SELECT * FROM employee_activities WHERE employee_id = $user_id AND activity_date BETWEEN '$start_date' AND '$end_date' ORDER BY activity_date";
        $activities_result = mysqli_query($connection, $activities_query);
        $tasks_query = "SELECT * FROM employee_tasks WHERE employee_id = $user_id AND (date_started BETWEEN '$start_date' AND '$end_date' OR updated_at BETWEEN '$start_date' AND '$end_date')";
        $tasks_result = mysqli_query($connection, $tasks_query);
        $expenses_query = "SELECT * FROM employee_expenses WHERE employee_id = $user_id AND expense_date BETWEEN '$start_date' AND '$end_date'";
        $expenses_result = mysqli_query($connection, $expenses_query);
        
        $total_hours = 0; $total_expenses = 0;
        while($act = mysqli_fetch_assoc($activities_result)) { $total_hours += $act['hours_worked']; }
        mysqli_data_seek($activities_result, 0);
        while($exp = mysqli_fetch_assoc($expenses_result)) { $total_expenses += $exp['amount']; }
        mysqli_data_seek($expenses_result, 0);
    ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header dark-header d-flex justify-content-between align-items-center">
            <h5 class="card-title"><i class="bi bi-calendar-range me-2"></i>Custom Range Report</h5>
            <button onclick="exportReport('custom', '<?php echo $start_date; ?>', '<?php echo $end_date; ?>')" class="btn btn-sm btn-success">
                <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
            </button>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="row g-3 mb-4">
                <input type="hidden" name="report_type" value="custom">
                <div class="col-md-4"><label class="form-label">From Date</label><input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>"></div>
                <div class="col-md-4"><label class="form-label">To Date</label><input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>"></div>
                <div class="col-md-4 d-flex align-items-end"><button type="submit" class="btn btn-primary w-100">Apply Range</button></div>
            </form>
            
            <div class="row g-3 mb-4">
                <div class="col-md-4"><div class="summary-card"><i class="bi bi-clock-history text-primary"></i><h3><?php echo $total_hours; ?> hrs</h3><p>Total Hours</p></div></div>
                <div class="col-md-4"><div class="summary-card"><i class="bi bi-list-check text-success"></i><h3><?php echo mysqli_num_rows($tasks_result); ?></h3><p>Tasks</p></div></div>
                <div class="col-md-4"><div class="summary-card"><i class="bi bi-cash-stack text-warning"></i><h3>AED <?php echo number_format($total_expenses, 2); ?></h3><p>Expenses</p></div></div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light"><tr><th>Date</th><th>Hours</th><th>Clients</th><th>Nature of Work</th></tr></thead>
                    <tbody>
                        <?php if(mysqli_num_rows($activities_result) > 0): ?>
                            <?php while($act = mysqli_fetch_assoc($activities_result)): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($act['activity_date'])); ?></td>
                                <td><?php echo $act['hours_worked']; ?></td>
                                <td><?php echo htmlspecialchars($act['clients_attended']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($act['nature_of_work'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center">No activities found in this range</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.summary-card { background: #f8f9fa; border-radius: 12px; padding: 15px; text-align: center; }
.summary-card i { font-size: 1.8rem; display: block; margin-bottom: 8px; }
.summary-card h3 { font-size: 1.5rem; font-weight: 700; margin-bottom: 5px; }
.summary-card p { margin: 0; color: #6c757d; font-size: 0.85rem; }
.dark-header { background: #1e293b; color: white; padding: 12px 20px; border-radius: 12px 12px 0 0; }
</style>