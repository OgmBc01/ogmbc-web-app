<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get employee ID from URL
$employee_id = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user_id'];
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Fetch employee details
$emp_query = "SELECT u.*, d.dept_name 
              FROM users u
              LEFT JOIN employees e ON u.user_id = e.user_id
              LEFT JOIN departments d ON e.department_id = d.id
              WHERE u.user_id = $employee_id";
$emp_result = mysqli_query($connection, $emp_query);
$employee = mysqli_fetch_assoc($emp_result);

if (!$employee) {
    $_SESSION['error_message'] = "Employee not found.";
    header("Location: points_ledger.php");
    exit();
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Employee Wallet</h1>
            <h5 class="text-muted"><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?> (<?php echo htmlspecialchars($employee['dept_name'] ?? 'No Department'); ?>)</h5>
        </div>
        <div>
            <a href="points_ledger.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Ledger
            </a>
        </div>
    </div>

    <!-- Year Selector -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row">
                <input type="hidden" name="source" value="employee_wallet">
                <input type="hidden" name="id" value="<?php echo $employee_id; ?>">
                <div class="col-md-3">
                    <label for="year" class="form-label">Select Year</label>
                    <select id="year" name="year" class="form-control" onchange="this.form.submit()">
                        <?php for($y = date('Y'); $y >= 2024; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo ($selected_year == $y) ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Wallet Summary Cards -->
    <div class="row mb-4">
        <?php
        // Calculate YTD totals
        $ytd_query = "SELECT 
                      SUM(CASE WHEN points_type = 'EARNED' THEN points ELSE 0 END) as total_earned,
                      SUM(CASE WHEN points_type = 'ADJUSTMENT' THEN points ELSE 0 END) as total_adjustments
                      FROM points_ledger 
                      WHERE employee_id = $employee_id 
                      AND YEAR(created_at) = $selected_year";
        $ytd_result = mysqli_query($connection, $ytd_query);
        $ytd = mysqli_fetch_assoc($ytd_result);
        $ytd_total = ($ytd['total_earned'] ?? 0) + ($ytd['total_adjustments'] ?? 0);
        
        // Get current month
        $current_month = date('m');
        $current_month_query = "SELECT SUM(points) as month_total 
                               FROM points_ledger 
                               WHERE employee_id = $employee_id 
                               AND YEAR(created_at) = $selected_year 
                               AND MONTH(created_at) = $current_month
                               AND points_type = 'EARNED'";
        $current_month_result = mysqli_query($connection, $current_month_query);
        $current_month_total = mysqli_fetch_assoc($current_month_result)['month_total'] ?? 0;
        $current_month_cashable = max(0, $current_month_total - 1000);
        
        // Get cashable YTD
        $cashable_ytd_query = "SELECT SUM(cashable_points) as total_cashable 
                               FROM monthly_point_summary 
                               WHERE employee_id = $employee_id 
                               AND year = $selected_year";
        $cashable_ytd_result = mysqli_query($connection, $cashable_ytd_query);
        $cashable_ytd = mysqli_fetch_assoc($cashable_ytd_result)['total_cashable'] ?? 0;
        ?>
        
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">YTD Total Points</h5>
                    <h2><?php echo number_format($ytd_total); ?></h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">YTD Cashable</h5>
                    <h2><?php echo number_format($cashable_ytd); ?></h2>
                    <small>AED <?php echo number_format($cashable_ytd); ?></small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">This Month</h5>
                    <h2><?php echo number_format($current_month_total); ?></h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Month Cashable</h5>
                    <h2><?php echo number_format($current_month_cashable); ?></h2>
                    <small>AED <?php echo number_format($current_month_cashable); ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Breakdown -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Monthly Breakdown - <?php echo $selected_year; ?></h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-center">Total Points</th>
                            <th class="text-center">Cashable Points</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for($m = 1; $m <= 12; $m++): 
                            $summary_query = "SELECT * FROM monthly_point_summary 
                                            WHERE employee_id = $employee_id 
                                            AND year = $selected_year 
                                            AND month = $m";
                            $summary_result = mysqli_query($connection, $summary_query);
                            
                            if (mysqli_num_rows($summary_result) > 0) {
                                $summary = mysqli_fetch_assoc($summary_result);
                                $total = $summary['total_points'];
                                $cashable = $summary['cashable_points'];
                                $is_closed = $summary['is_closed'];
                            } else {
                                // Calculate on the fly
                                $calc_query = "SELECT SUM(points) as total 
                                              FROM points_ledger 
                                              WHERE employee_id = $employee_id 
                                              AND YEAR(created_at) = $selected_year 
                                              AND MONTH(created_at) = $m
                                              AND points_type = 'EARNED'";
                                $calc_result = mysqli_query($connection, $calc_query);
                                $calc = mysqli_fetch_assoc($calc_result);
                                $total = $calc['total'] ?? 0;
                                $cashable = max(0, $total - 1000);
                                $is_closed = false;
                            }
                            
                            if ($total > 0):
                        ?>
                        <tr>
                            <td><strong><?php echo date('F', mktime(0, 0, 0, $m, 1)); ?></strong></td>
                            <td class="text-center"><?php echo number_format($total); ?></td>
                            <td class="text-center"><span class="text-success"><?php echo number_format($cashable); ?></span></td>
                            <td class="text-center">
                                <?php if ($is_closed): ?>
                                    <span class="badge bg-success">Closed</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Open</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Transactions</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th class="text-end">Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $trans_query = "SELECT pl.*, 
                                       CONCAT(au.first_name, ' ', au.last_name) as created_by_name
                                       FROM points_ledger pl
                                       LEFT JOIN users au ON pl.created_by = au.user_id
                                       WHERE pl.employee_id = $employee_id
                                       AND YEAR(pl.created_at) = $selected_year
                                       ORDER BY pl.created_at DESC
                                       LIMIT 20";
                        $trans_result = mysqli_query($connection, $trans_query);
                        
                        if (mysqli_num_rows($trans_result) > 0):
                            while($trans = mysqli_fetch_assoc($trans_result)):
                                $type_class = 'secondary';
                                switch($trans['source_type']) {
                                    case 'ENGAGEMENT': $type_class = 'primary'; break;
                                    case 'SALES_TARGET': $type_class = 'success'; break;
                                    case 'CLIENT_FEEDBACK': $type_class = 'info'; break;
                                    case 'MANUAL_ADJUSTMENT': $type_class = 'warning'; break;
                                }
                        ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($trans['created_at'])); ?></td>
                            <td><span class="badge bg-<?php echo $type_class; ?>"><?php echo $trans['source_type']; ?></span></td>
                            <td><?php echo htmlspecialchars($trans['description'] ?? ''); ?></td>
                            <td class="text-end <?php echo $trans['points'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <strong><?php echo ($trans['points'] >= 0 ? '+' : '') . $trans['points']; ?></strong>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No transactions found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get employee ID from URL
$employee_id = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user_id'];
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Fetch employee details
$emp_query = "SELECT u.*, d.dept_name 
              FROM users u
              LEFT JOIN employees e ON u.user_id = e.user_id
              LEFT JOIN departments d ON e.department_id = d.id
              WHERE u.user_id = $employee_id";
$emp_result = mysqli_query($connection, $emp_query);
$employee = mysqli_fetch_assoc($emp_result);

if (!$employee) {
    $_SESSION['error_message'] = "Employee not found.";
    header("Location: points_ledger.php");
    exit();
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Employee Wallet</h1>
            <h5 class="text-muted"><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?> (<?php echo htmlspecialchars($employee['dept_name'] ?? 'No Department'); ?>)</h5>
        </div>
        <div>
            <a href="points_ledger.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Ledger
            </a>
        </div>
    </div>

    <!-- Year Selector -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row">
                <input type="hidden" name="source" value="employee_wallet">
                <input type="hidden" name="id" value="<?php echo $employee_id; ?>">
                <div class="col-md-3">
                    <label for="year" class="form-label">Select Year</label>
                    <select id="year" name="year" class="form-control" onchange="this.form.submit()">
                        <?php for($y = date('Y'); $y >= 2024; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo ($selected_year == $y) ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Wallet Summary Cards -->
    <div class="row mb-4">
        <?php
        // Calculate YTD totals
        $ytd_query = "SELECT 
                      SUM(CASE WHEN points_type = 'EARNED' THEN points ELSE 0 END) as total_earned,
                      SUM(CASE WHEN points_type = 'ADJUSTMENT' THEN points ELSE 0 END) as total_adjustments
                      FROM points_ledger 
                      WHERE employee_id = $employee_id 
                      AND YEAR(created_at) = $selected_year";
        $ytd_result = mysqli_query($connection, $ytd_query);
        $ytd = mysqli_fetch_assoc($ytd_result);
        $ytd_total = ($ytd['total_earned'] ?? 0) + ($ytd['total_adjustments'] ?? 0);
        
        // Get current month
        $current_month = date('m');
        $current_month_query = "SELECT SUM(points) as month_total 
                               FROM points_ledger 
                               WHERE employee_id = $employee_id 
                               AND YEAR(created_at) = $selected_year 
                               AND MONTH(created_at) = $current_month
                               AND points_type = 'EARNED'";
        $current_month_result = mysqli_query($connection, $current_month_query);
        $current_month_total = mysqli_fetch_assoc($current_month_result)['month_total'] ?? 0;
        $current_month_cashable = max(0, $current_month_total - 1000);
        
        // Get cashable YTD
        $cashable_ytd_query = "SELECT SUM(cashable_points) as total_cashable 
                               FROM monthly_point_summary 
                               WHERE employee_id = $employee_id 
                               AND year = $selected_year";
        $cashable_ytd_result = mysqli_query($connection, $cashable_ytd_query);
        $cashable_ytd = mysqli_fetch_assoc($cashable_ytd_result)['total_cashable'] ?? 0;
        ?>
        
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">YTD Total Points</h5>
                    <h2><?php echo number_format($ytd_total); ?></h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">YTD Cashable</h5>
                    <h2><?php echo number_format($cashable_ytd); ?></h2>
                    <small>AED <?php echo number_format($cashable_ytd); ?></small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">This Month</h5>
                    <h2><?php echo number_format($current_month_total); ?></h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Month Cashable</h5>
                    <h2><?php echo number_format($current_month_cashable); ?></h2>
                    <small>AED <?php echo number_format($current_month_cashable); ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Breakdown -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Monthly Breakdown - <?php echo $selected_year; ?></h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-center">Total Points</th>
                            <th class="text-center">Cashable Points</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for($m = 1; $m <= 12; $m++): 
                            $summary_query = "SELECT * FROM monthly_point_summary 
                                            WHERE employee_id = $employee_id 
                                            AND year = $selected_year 
                                            AND month = $m";
                            $summary_result = mysqli_query($connection, $summary_query);
                            
                            if (mysqli_num_rows($summary_result) > 0) {
                                $summary = mysqli_fetch_assoc($summary_result);
                                $total = $summary['total_points'];
                                $cashable = $summary['cashable_points'];
                                $is_closed = $summary['is_closed'];
                            } else {
                                // Calculate on the fly
                                $calc_query = "SELECT SUM(points) as total 
                                              FROM points_ledger 
                                              WHERE employee_id = $employee_id 
                                              AND YEAR(created_at) = $selected_year 
                                              AND MONTH(created_at) = $m
                                              AND points_type = 'EARNED'";
                                $calc_result = mysqli_query($connection, $calc_query);
                                $calc = mysqli_fetch_assoc($calc_result);
                                $total = $calc['total'] ?? 0;
                                $cashable = max(0, $total - 1000);
                                $is_closed = false;
                            }
                            
                            if ($total > 0):
                        ?>
                        <tr>
                            <td><strong><?php echo date('F', mktime(0, 0, 0, $m, 1)); ?></strong></td>
                            <td class="text-center"><?php echo number_format($total); ?></td>
                            <td class="text-center"><span class="text-success"><?php echo number_format($cashable); ?></span></td>
                            <td class="text-center">
                                <?php if ($is_closed): ?>
                                    <span class="badge bg-success">Closed</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Open</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Transactions</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th class="text-end">Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $trans_query = "SELECT pl.*, 
                                       CONCAT(au.first_name, ' ', au.last_name) as created_by_name
                                       FROM points_ledger pl
                                       LEFT JOIN users au ON pl.created_by = au.user_id
                                       WHERE pl.employee_id = $employee_id
                                       AND YEAR(pl.created_at) = $selected_year
                                       ORDER BY pl.created_at DESC
                                       LIMIT 20";
                        $trans_result = mysqli_query($connection, $trans_query);
                        
                        if (mysqli_num_rows($trans_result) > 0):
                            while($trans = mysqli_fetch_assoc($trans_result)):
                                $type_class = 'secondary';
                                switch($trans['source_type']) {
                                    case 'ENGAGEMENT': $type_class = 'primary'; break;
                                    case 'SALES_TARGET': $type_class = 'success'; break;
                                    case 'CLIENT_FEEDBACK': $type_class = 'info'; break;
                                    case 'MANUAL_ADJUSTMENT': $type_class = 'warning'; break;
                                }
                        ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($trans['created_at'])); ?></td>
                            <td><span class="badge bg-<?php echo $type_class; ?>"><?php echo $trans['source_type']; ?></span></td>
                            <td><?php echo htmlspecialchars($trans['description'] ?? ''); ?></td>
                            <td class="text-end <?php echo $trans['points'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <strong><?php echo ($trans['points'] >= 0 ? '+' : '') . $trans['points']; ?></strong>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No transactions found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>