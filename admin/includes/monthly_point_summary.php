<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get current user's role
$user_role_query = "SELECT role_name FROM users u LEFT JOIN user_roles r ON u.role_id = r.role_id WHERE u.user_id = " . $_SESSION['user_id'];
$user_role_result = mysqli_query($connection, $user_role_query);
$user_role = mysqli_fetch_assoc($user_role_result)['role_name'] ?? '';

// Get selected year
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">Monthly Point Summary</h1>
        <div>
            <a href="points_ledger.php" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left"></i> Back to Ledger
            </a>
            <?php if ($user_role == 'CEO_GM' || $user_role == 'ADMIN_STAFF'): ?>
                <button class="btn btn-primary" onclick="exportToCSV()">
                    <i class="bi bi-download"></i> Export CSV
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Year Selector -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row">
                <input type="hidden" name="source" value="monthly_summary">
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

    <!-- Monthly Summary Table -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-calendar-month me-2"></i>Monthly Points - <?php echo $selected_year; ?></h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr class="table-dark">
                            <th>Employee</th>
                            <th>Department</th>
                            <?php for($m = 1; $m <= 12; $m++): ?>
                                <th class="text-center"><?php echo date('M', mktime(0, 0, 0, $m, 1)); ?></th>
                            <?php endfor; ?>
                            <th class="text-center">Total</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php  
                        // Get all employees who have points
                        $employees_query = "SELECT DISTINCT u.user_id, u.first_name, u.last_name, d.dept_name
                                           FROM points_ledger pl
                                           JOIN users u ON pl.employee_id = u.user_id
                                           LEFT JOIN employees e ON u.user_id = e.user_id
                                           LEFT JOIN departments d ON e.department_id = d.id
                                           WHERE YEAR(pl.created_at) = $selected_year
                                           ORDER BY u.first_name, u.last_name";
                        $employees_result = mysqli_query($connection, $employees_query);
                        
                        if (mysqli_num_rows($employees_result) == 0) {
                            echo "<tr><td colspan='15' class='text-center'>No data found for $selected_year</td></tr>";
                        } else {
                            while($emp = mysqli_fetch_assoc($employees_result)):
                                $employee_id = $emp['user_id'];
                                $year_total = 0;
                                $monthly_totals = [];
                                $monthly_cashable = [];
                                
                                // Get monthly summaries
                                for($m = 1; $m <= 12; $m++) {
                                    $summary_query = "SELECT total_points, cashable_points, is_closed 
                                                      FROM monthly_point_summary 
                                                      WHERE employee_id = $employee_id 
                                                      AND year = $selected_year 
                                                      AND month = $m";
                                    $summary_result = mysqli_query($connection, $summary_query);
                                    
                                    if (mysqli_num_rows($summary_result) > 0) {
                                        $summary = mysqli_fetch_assoc($summary_result);
                                        $monthly_totals[$m] = $summary['total_points'];
                                        $monthly_cashable[$m] = $summary['cashable_points'];
                                        $year_total += $summary['total_points'];
                                    } else {
                                        // Calculate on the fly if not closed
                                        $calc_query = "SELECT SUM(points) as total 
                                                      FROM points_ledger 
                                                      WHERE employee_id = $employee_id 
                                                      AND YEAR(created_at) = $selected_year 
                                                      AND MONTH(created_at) = $m
                                                      AND points_type = 'EARNED'";
                                        $calc_result = mysqli_query($connection, $calc_query);
                                        $calc = mysqli_fetch_assoc($calc_result);
                                        $monthly_totals[$m] = $calc['total'] ?? 0;
                                        $monthly_cashable[$m] = max(0, $monthly_totals[$m] - 1000);
                                        $year_total += $monthly_totals[$m];
                                    }
                                }
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($emp['dept_name'] ?? 'N/A'); ?></td>
                                    
                                    <?php for($m = 1; $m <= 12; $m++): ?>
                                        <td class="text-center">
                                            <?php if ($monthly_totals[$m] > 0): ?>
                                                <span class="d-block fw-bold"><?php echo number_format($monthly_totals[$m]); ?></span>
                                                <small class="text-success">(<?php echo number_format($monthly_cashable[$m]); ?> cashable)</small>
                                                <?php
                                                // Check if month is closed
                                                $closed_check = "SELECT is_closed FROM monthly_point_summary 
                                                                WHERE employee_id = $employee_id 
                                                                AND year = $selected_year 
                                                                AND month = $m";
                                                $closed_result = mysqli_query($connection, $closed_check);
                                                if (mysqli_num_rows($closed_result) > 0) {
                                                    $closed = mysqli_fetch_assoc($closed_result);
                                                    if ($closed['is_closed']) {
                                                        echo '<br><span class="badge bg-success" style="font-size: 0.6rem;">Closed</span>';
                                                    }
                                                }
                                                ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endfor; ?>
                                    
                                    <td class="text-center">
                                        <strong><?php echo number_format($year_total); ?></strong>
                                    </td>
                                    
                                    <td class="text-center">
                                        <a href="points_ledger.php?source=employee_wallet&id=<?php echo $employee_id; ?>&year=<?php echo $selected_year; ?>" class="btn btn-sm btn-info" title="View Details">
                                            <i class="bi bi-wallet2"></i>
                                        </a>
                                        
                                        <?php if ($user_role == 'CEO_GM' || $user_role == 'ADMIN_STAFF'): ?>
                                            <?php for($m = 1; $m <= 12; $m++): ?>
                                                <?php
                                                $month_closed = false;
                                                $closed_check = "SELECT is_closed FROM monthly_point_summary 
                                                                WHERE employee_id = $employee_id 
                                                                AND year = $selected_year 
                                                                AND month = $m";
                                                $closed_result = mysqli_query($connection, $closed_check);
                                                if (mysqli_num_rows($closed_result) > 0) {
                                                    $closed = mysqli_fetch_assoc($closed_result);
                                                    $month_closed = $closed['is_closed'];
                                                }
                                                
                                                if ($monthly_totals[$m] > 0 && !$month_closed):
                                                ?>
                                                <a href="points_ledger.php?close_month=1&year=<?php echo $selected_year; ?>&month=<?php echo $m; ?>&employee=<?php echo $employee_id; ?>" class="btn btn-sm btn-success" title="Close Month" onclick="return confirm('Close month <?php echo date('F', mktime(0,0,0,$m,1)); ?> for this employee?')">
                                                    <i class="bi bi-lock"></i>
                                                </a>
                                                <?php 
                                                break;
                                                endif; 
                                            endfor; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile;
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function exportToCSV() {
    window.location.href = 'includes/ajax/export_monthly_summary.php?year=<?php echo $selected_year; ?>';
}
</script>