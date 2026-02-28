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

// Get selected year and quarter
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$selected_quarter = isset($_GET['quarter']) ? (int)$_GET['quarter'] : ceil(date('n') / 3);

// Calculate month range for quarter
$months = [
    1 => [1, 2, 3],   // Q1: Jan-Mar
    2 => [4, 5, 6],   // Q2: Apr-Jun
    3 => [7, 8, 9],   // Q3: Jul-Sep
    4 => [10, 11, 12] // Q4: Oct-Dec
];

$quarter_months = $months[$selected_quarter];
$month_names = array_map(function($m) {
    return date('M', mktime(0, 0, 0, $m, 1));
}, $quarter_months);

// Handle payout approval
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_payout']) && ($user_role == 'CEO_GM' || $user_role == 'ADMIN_STAFF')) {
    $employee_id = (int)$_POST['employee_id'];
    $total_cashable = (int)$_POST['total_cashable'];
    $cash_amount = $total_cashable; // 1 AED per point
    
    // Check if payout already exists
    $check_query = "SELECT payout_id FROM quarterly_payouts 
                    WHERE employee_id = $employee_id AND year = $selected_year AND quarter = $selected_quarter";
    $check_result = mysqli_query($connection, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Update existing
        $update_query = "UPDATE quarterly_payouts 
                        SET total_cashable_points = $total_cashable, 
                            cash_amount = $cash_amount,
                            status = 'APPROVED',
                            approved_by = {$_SESSION['user_id']},
                            approved_at = NOW()
                        WHERE employee_id = $employee_id AND year = $selected_year AND quarter = $selected_quarter";
        mysqli_query($connection, $update_query);
    } else {
        // Insert new
        $insert_query = "INSERT INTO quarterly_payouts 
                        (employee_id, year, quarter, total_cashable_points, cash_amount, status, approved_by, approved_at)
                        VALUES ($employee_id, $selected_year, $selected_quarter, $total_cashable, $cash_amount, 'APPROVED', {$_SESSION['user_id']}, NOW())";
        mysqli_query($connection, $insert_query);
    }
    
    $_SESSION['success_message'] = "Payout approved successfully!";
    header("Location: points_ledger.php?source=quarterly_payout&year=$selected_year&quarter=$selected_quarter");
    exit();
}

// Handle mark as paid
if (isset($_GET['mark_paid']) && ($user_role == 'CEO_GM' || $user_role == 'ADMIN_STAFF')) {
    $payout_id = (int)$_GET['mark_paid'];
    
    $update_query = "UPDATE quarterly_payouts 
                    SET status = 'PAID', paid_at = NOW()
                    WHERE payout_id = $payout_id";
    mysqli_query($connection, $update_query);
    
    $_SESSION['success_message'] = "Payout marked as paid!";
    header("Location: points_ledger.php?source=quarterly_payout&year=$selected_year&quarter=$selected_quarter");
    exit();
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">Quarterly Payout - Q<?php echo $selected_quarter; ?> <?php echo $selected_year; ?></h1>
        <div>
            <a href="points_ledger.php" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left"></i> Back to Ledger
            </a>
            <?php if ($user_role == 'CEO_GM' || $user_role == 'ADMIN_STAFF'): ?>
                <button class="btn btn-primary" onclick="exportToCSV()">
                    <i class="bi bi-download"></i> Export Payroll
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quarter Selector -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row">
                <input type="hidden" name="source" value="quarterly_payout">
                <div class="col-md-3">
                    <label for="year" class="form-label">Year</label>
                    <select id="year" name="year" class="form-control">
                        <?php for($y = date('Y'); $y >= 2024; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo ($selected_year == $y) ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="quarter" class="form-label">Quarter</label>
                    <select id="quarter" name="quarter" class="form-control">
                        <option value="1" <?php echo ($selected_quarter == 1) ? 'selected' : ''; ?>>Q1 (Jan-Mar)</option>
                        <option value="2" <?php echo ($selected_quarter == 2) ? 'selected' : ''; ?>>Q2 (Apr-Jun)</option>
                        <option value="3" <?php echo ($selected_quarter == 3) ? 'selected' : ''; ?>>Q3 (Jul-Sep)</option>
                        <option value="4" <?php echo ($selected_quarter == 4) ? 'selected' : ''; ?>>Q4 (Oct-Dec)</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">View</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payout Summary -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Quarterly Payout Summary</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr class="table-dark">
                            <th>Employee</th>
                            <th>Department</th>
                            <th class="text-center"><?php echo $month_names[0]; ?> Points</th>
                            <th class="text-center"><?php echo $month_names[1]; ?> Points</th>
                            <th class="text-center"><?php echo $month_names[2]; ?> Points</th>
                            <th class="text-center">Total Points</th>
                            <th class="text-center">Cashable (≥1000)</th>
                            <th class="text-center">Payout (AED)</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php  
                        // Get all employees with monthly summaries for this quarter
                        $employees_query = "SELECT DISTINCT u.user_id, u.first_name, u.last_name, d.dept_name
                                           FROM monthly_point_summary mps
                                           JOIN users u ON mps.employee_id = u.user_id
                                           LEFT JOIN employees e ON u.user_id = e.user_id
                                           LEFT JOIN departments d ON e.department_id = d.id
                                           WHERE mps.year = $selected_year AND mps.month IN (" . implode(',', $quarter_months) . ")
                                           ORDER BY u.first_name, u.last_name";
                        $employees_result = mysqli_query($connection, $employees_query);
                        
                        if (mysqli_num_rows($employees_result) == 0) {
                            echo "<tr><td colspan='10' class='text-center'>No data found for Q$selected_quarter $selected_year</td></tr>";
                        } else {
                            while($emp = mysqli_fetch_assoc($employees_result)):
                                $employee_id = $emp['user_id'];
                                $monthly_points = [];
                                $quarter_total = 0;
                                $quarter_cashable = 0;
                                
                                // Get points for each month in quarter
                                for($i = 0; $i < 3; $i++) {
                                    $month = $quarter_months[$i];
                                    $summary_query = "SELECT cashable_points FROM monthly_point_summary 
                                                      WHERE employee_id = $employee_id 
                                                      AND year = $selected_year 
                                                      AND month = $month";
                                    $summary_result = mysqli_query($connection, $summary_query);
                                    
                                    if (mysqli_num_rows($summary_result) > 0) {
                                        $summary = mysqli_fetch_assoc($summary_result);
                                        $monthly_points[$month] = $summary['cashable_points'];
                                        $quarter_cashable += $summary['cashable_points'];
                                    } else {
                                        // Calculate on the fly
                                        $calc_query = "SELECT SUM(points) as total 
                                                      FROM points_ledger 
                                                      WHERE employee_id = $employee_id 
                                                      AND YEAR(created_at) = $selected_year 
                                                      AND MONTH(created_at) = $month
                                                      AND points_type = 'EARNED'";
                                        $calc_result = mysqli_query($connection, $calc_query);
                                        $calc = mysqli_fetch_assoc($calc_result);
                                        $month_total = $calc['total'] ?? 0;
                                        $monthly_points[$month] = max(0, $month_total - 1000);
                                        $quarter_cashable += $monthly_points[$month];
                                    }
                                    $quarter_total += $monthly_points[$month];
                                }
                                
                                // Check if payout already exists
                                $payout_query = "SELECT * FROM quarterly_payouts 
                                                WHERE employee_id = $employee_id 
                                                AND year = $selected_year 
                                                AND quarter = $selected_quarter";
                                $payout_result = mysqli_query($connection, $payout_query);
                                $payout = mysqli_fetch_assoc($payout_result);
                                
                                $status = $payout['status'] ?? 'PENDING';
                                $status_class = 'secondary';
                                if ($status == 'APPROVED') $status_class = 'success';
                                if ($status == 'PAID') $status_class = 'primary';
                                if ($status == 'REJECTED') $status_class = 'danger';
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($emp['dept_name'] ?? 'N/A'); ?></td>
                                    
                                    <?php foreach($quarter_months as $month): ?>
                                        <td class="text-center">
                                            <?php if ($monthly_points[$month] > 0): ?>
                                                <?php echo number_format($monthly_points[$month]); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                    
                                    <td class="text-center"><strong><?php echo number_format($quarter_total); ?></strong></td>
                                    <td class="text-center"><strong class="text-success"><?php echo number_format($quarter_cashable); ?></strong></td>
                                    <td class="text-center"><strong>AED <?php echo number_format($quarter_cashable); ?></strong></td>
                                    
                                    <td class="text-center">
                                        <span class="badge bg-<?php echo $status_class; ?>"><?php echo $status; ?></span>
                                    </td>
                                    
                                    <td class="text-center">
                                        <?php if ($user_role == 'CEO_GM' || $user_role == 'ADMIN_STAFF'): ?>
                                            <?php if ($status == 'PENDING' && $quarter_cashable > 0): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="employee_id" value="<?php echo $employee_id; ?>">
                                                    <input type="hidden" name="total_cashable" value="<?php echo $quarter_cashable; ?>">
                                                    <button type="submit" name="approve_payout" class="btn btn-sm btn-success" title="Approve Payout" onclick="return confirm('Approve payout of AED <?php echo number_format($quarter_cashable); ?> for this employee?')">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <?php if ($status == 'APPROVED'): ?>
                                                <a href="points_ledger.php?source=quarterly_payout&mark_paid=<?php echo $payout['payout_id']; ?>&year=<?php echo $selected_year; ?>&quarter=<?php echo $selected_quarter; ?>" class="btn btn-sm btn-primary" title="Mark as Paid" onclick="return confirm('Mark this payout as paid?')">
                                                    <i class="bi bi-cash"></i>
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        
                                        <?php if ($status == 'PAID'): ?>
                                            <span class="badge bg-success">Paid</span>
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
    window.location.href = 'includes/ajax/export_quarterly_payout.php?year=<?php echo $selected_year; ?>&quarter=<?php echo $selected_quarter; ?>';
}
</script>