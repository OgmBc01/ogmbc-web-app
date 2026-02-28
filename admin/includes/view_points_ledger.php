<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get filter parameters
$employee_filter = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : '';
$month_filter = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year_filter = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$type_filter = isset($_GET['type']) ? mysqli_real_escape_string($connection, $_GET['type']) : '';

// Get statistics
$stats_query = "SELECT 
                COUNT(*) as total_transactions,
                SUM(CASE WHEN points_type = 'EARNED' THEN points ELSE 0 END) as total_points_earned,
                SUM(CASE WHEN points_type = 'ADJUSTMENT' THEN points ELSE 0 END) as total_adjustments,
                COUNT(DISTINCT employee_id) as active_employees
                FROM points_ledger
                WHERE YEAR(created_at) = $year_filter AND MONTH(created_at) = $month_filter";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get employees for filter dropdown
$employees_query = "SELECT DISTINCT u.user_id, u.first_name, u.last_name 
                    FROM points_ledger pl
                    JOIN users u ON pl.employee_id = u.user_id
                    ORDER BY u.first_name";
$employees_result = mysqli_query($connection, $employees_query);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">Points Ledger</h1>
        <div>
            <?php if ($_SESSION['user_role'] == 'CEO_GM' || $_SESSION['user_role'] == 'ADMIN_STAFF'): ?>
                <a href="points_ledger.php?source=manual_adjustment" class="btn btn-warning me-2">
                    <i class="bi bi-pencil-square"></i> Manual Adjustment
                </a>
            <?php endif; ?>
            <a href="points_ledger.php?source=monthly_summary" class="btn btn-info me-2">
                <i class="bi bi-calendar-month"></i> Monthly Summary
            </a>
            <a href="points_ledger.php?source=quarterly_payout" class="btn btn-success">
                <i class="bi bi-cash-stack"></i> Quarterly Payout
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Transactions</h5>
                    <h2><?php echo $stats['total_transactions'] ?? 0; ?></h2>
                    <small>This month</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Points Earned</h5>
                    <h2><?php echo number_format($stats['total_points_earned'] ?? 0); ?></h2>
                    <small>This month</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Active Employees</h5>
                    <h2><?php echo $stats['active_employees'] ?? 0; ?></h2>
                    <small>This month</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Adjustments</h5>
                    <h2><?php echo number_format($stats['total_adjustments'] ?? 0); ?></h2>
                    <small>This month</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <input type="hidden" name="source" value="view_ledger">
                <div class="col-md-3">
                    <label for="employee_filter" class="form-label">Employee</label>
                    <select id="employee_filter" name="employee_id" class="form-control">
                        <option value="">All Employees</option>
                        <?php while($emp = mysqli_fetch_assoc($employees_result)): ?>
                            <option value="<?php echo $emp['user_id']; ?>" <?php echo ($employee_filter == $emp['user_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="month_filter" class="form-label">Month</label>
                    <select id="month_filter" name="month" class="form-control">
                        <?php for($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo $m; ?>" <?php echo ($month_filter == $m) ? 'selected' : ''; ?>>
                                <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="year_filter" class="form-label">Year</label>
                    <select id="year_filter" name="year" class="form-control">
                        <?php for($y = date('Y'); $y >= 2024; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo ($year_filter == $y) ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="type_filter" class="form-label">Transaction Type</label>
                    <select id="type_filter" name="type" class="form-control">
                        <option value="">All Types</option>
                        <option value="ENGAGEMENT" <?php echo ($type_filter == 'ENGAGEMENT') ? 'selected' : ''; ?>>Engagement</option>
                        <option value="SALES_TARGET" <?php echo ($type_filter == 'SALES_TARGET') ? 'selected' : ''; ?>>Sales Target</option>
                        <option value="CLIENT_FEEDBACK" <?php echo ($type_filter == 'CLIENT_FEEDBACK') ? 'selected' : ''; ?>>Client Feedback</option>
                        <option value="MANUAL_ADJUSTMENT" <?php echo ($type_filter == 'MANUAL_ADJUSTMENT') ? 'selected' : ''; ?>>Manual Adjustment</option>
                        <option value="CDP" <?php echo ($type_filter == 'CDP') ? 'selected' : ''; ?>>CDP</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-journal-bookmark-fill me-2"></i>Point Transactions</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr class="table-dark">
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Points</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php  
                        $where_conditions = ["YEAR(pl.created_at) = $year_filter", "MONTH(pl.created_at) = $month_filter"];
                        
                        if (!empty($employee_filter)) {
                            $where_conditions[] = "pl.employee_id = $employee_filter";
                        }
                        
                        if (!empty($type_filter)) {
                            $where_conditions[] = "pl.source_type = '$type_filter'";
                        }
                        
                        $where_clause = implode(' AND ', $where_conditions);
                        
                        $query = "SELECT pl.*, 
                                 CONCAT(u.first_name, ' ', u.last_name) as employee_name,
                                 CONCAT(au.first_name, ' ', au.last_name) as created_by_name
                                 FROM points_ledger pl
                                 JOIN users u ON pl.employee_id = u.user_id
                                 LEFT JOIN users au ON pl.created_by = au.user_id
                                 WHERE $where_clause
                                 ORDER BY pl.created_at DESC
                                 LIMIT 500";
                        
                        $result = mysqli_query($connection, $query);
                        
                        if (!$result) {
                            echo "<tr><td colspan='7' class='text-center text-danger'>Error: " . mysqli_error($connection) . "</td></tr>";
                        } else if (mysqli_num_rows($result) == 0) {
                            echo "<tr><td colspan='7' class='text-center'>No transactions found for the selected period.</td></tr>";
                        } else {
                            while($row = mysqli_fetch_assoc($result)) {
                                // Determine badge color based on source type
                                $type_class = 'secondary';
                                switch($row['source_type']) {
                                    case 'ENGAGEMENT':
                                        $type_class = 'primary';
                                        break;
                                    case 'SALES_TARGET':
                                        $type_class = 'success';
                                        break;
                                    case 'CLIENT_FEEDBACK':
                                        $type_class = 'info';
                                        break;
                                    case 'MANUAL_ADJUSTMENT':
                                        $type_class = 'warning';
                                        break;
                                    case 'CDP':
                                        $type_class = 'dark';
                                        break;
                                }
                                
                                // Points color
                                $points_class = $row['points'] >= 0 ? 'success' : 'danger';
                                $points_sign = $row['points'] >= 0 ? '+' : '';
                                ?>
                                <tr>
                                    <td><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['employee_name']); ?></strong></td>
                                    <td><span class="badge bg-<?php echo $type_class; ?>"><?php echo $row['source_type']; ?></span></td>
                                    <td>
                                        <?php echo htmlspecialchars($row['description'] ?? ''); ?>
                                        <?php if ($row['requires_approval'] && !$row['approved_by']): ?>
                                            <span class="badge bg-warning ms-2">Pending Approval</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-<?php echo $points_class; ?> fs-6"><?php echo $points_sign . $row['points']; ?></span></td>
                                    <td>
                                        <?php if ($row['approved_by']): ?>
                                            <span class="badge bg-success">Approved</span>
                                        <?php elseif ($row['requires_approval']): ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Auto</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewTransaction(<?php echo $row['ledger_id']; ?>)" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        
                                        <?php if ($row['requires_approval'] && !$row['approved_by'] && ($_SESSION['user_role'] == 'CEO_GM' || $_SESSION['user_role'] == 'ADMIN_STAFF')): ?>
                                            <a href="points_ledger.php?approve_adjustment=<?php echo $row['ledger_id']; ?>" class="btn btn-sm btn-success" title="Approve" onclick="return confirm('Approve this manual adjustment?')">
                                                <i class="bi bi-check-lg"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>