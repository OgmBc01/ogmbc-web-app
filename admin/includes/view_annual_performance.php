<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role_query = "SELECT role_name FROM users u LEFT JOIN user_roles r ON u.role_id = r.role_id WHERE u.user_id = $user_id";
$user_role_result = mysqli_query($connection, $user_role_query);
$user_role = mysqli_fetch_assoc($user_role_result)['role_name'] ?? '';

$is_hr_admin = ($user_role == 'hr_admin' || $user_role == 'ceo_gm' || $user_role == 'admin_staff');

// Get selected year
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$selected_employee = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : ($is_hr_admin ? '' : $user_id);

// Get employees for filter (HR only)
if ($is_hr_admin) {
    $employees_query = "SELECT u.user_id, u.first_name, u.last_name, d.dept_name
                       FROM users u
                       LEFT JOIN employees e ON u.user_id = e.user_id
                       LEFT JOIN departments d ON e.department_id = d.id
                       WHERE u.user_status = 'active'
                       ORDER BY u.first_name";
    $employees_result = mysqli_query($connection, $employees_query);
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">Annual Performance</h1>
        <div>
            <?php if ($is_hr_admin): ?>
                <a href="cdp_annual.php?tab=annual&annual_source=calculate&year=<?php echo $selected_year; ?>" class="btn btn-primary">
                    <i class="bi bi-calculator"></i> Calculate Performance
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <input type="hidden" name="tab" value="annual">
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
                
                <?php if ($is_hr_admin): ?>
                <div class="col-md-4">
                    <label for="employee_id" class="form-label">Employee</label>
                    <select id="employee_id" name="employee_id" class="form-control">
                        <option value="">All Employees</option>
                        <?php while($emp = mysqli_fetch_assoc($employees_result)): ?>
                            <option value="<?php echo $emp['user_id']; ?>" <?php echo ($selected_employee == $emp['user_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
                                <?php if ($emp['dept_name']): echo '(' . $emp['dept_name'] . ')'; endif; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Performance Table -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Annual Performance Summary - <?php echo $selected_year; ?></h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr class="table-dark">
                            <th>Employee</th>
                            <th>Department</th>
                            <th class="text-center">Total Points</th>
                            <th class="text-center">Base %</th>
                            <th class="text-center">CDP Uplift</th>
                            <th class="text-center">Final %</th>
                            <th class="text-center">Recommended Band</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php  
                        $where = ["ap.year = $selected_year"];
                        
                        if (!$is_hr_admin) {
                            $where[] = "ap.employee_id = $user_id";
                        } elseif (!empty($selected_employee)) {
                            $where[] = "ap.employee_id = $selected_employee";
                        }
                        
                        $where_clause = implode(' AND ', $where);
                        
                        $query = "SELECT ap.*, 
                                 CONCAT(u.first_name, ' ', u.last_name) as employee_name,
                                 d.dept_name,
                                 CONCAT(ab.first_name, ' ', ab.last_name) as approved_by_name
                                 FROM annual_performance ap
                                 JOIN users u ON ap.employee_id = u.user_id
                                 LEFT JOIN employees e ON u.user_id = e.user_id
                                 LEFT JOIN departments d ON e.department_id = d.id
                                 LEFT JOIN users ab ON ap.approved_by = ab.user_id
                                 WHERE $where_clause
                                 ORDER BY ap.final_percentage DESC";
                        
                        $result = mysqli_query($connection, $query);
                        
                        if (!$result) {
                            echo "<tr><td colspan='9' class='text-center text-danger'>Error: " . mysqli_error($connection) . "</td></tr>";
                        } else if (mysqli_num_rows($result) == 0) {
                            echo "<tr><td colspan='9' class='text-center'>No performance records found for $selected_year.</td></tr>";
                        } else {
                            while($perf = mysqli_fetch_assoc($result)):
                                $status_class = 'secondary';
                                $status_text = 'Draft';
                                if ($perf['status'] == 'PENDING_APPROVAL') {
                                    $status_class = 'warning';
                                    $status_text = 'Pending Approval';
                                } elseif ($perf['status'] == 'APPROVED') {
                                    $status_class = 'success';
                                    $status_text = 'Approved';
                                } elseif ($perf['status'] == 'LOCKED') {
                                    $status_class = 'dark';
                                    $status_text = 'Locked';
                                }
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($perf['employee_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($perf['dept_name'] ?? 'N/A'); ?></td>
                                    <td class="text-center"><?php echo number_format($perf['total_points']); ?></td>
                                    <td class="text-center"><?php echo number_format($perf['base_percentage'], 1); ?>%</td>
                                    <td class="text-center">
                                        <?php if ($perf['total_uplift'] > 0): ?>
                                            <span class="text-success">+<?php echo number_format($perf['total_uplift'], 1); ?>%</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <strong class="<?php echo $perf['final_percentage'] >= 90 ? 'text-success' : ($perf['final_percentage'] >= 75 ? 'text-primary' : 'text-warning'); ?>">
                                            <?php echo number_format($perf['final_percentage'], 1); ?>%
                                        </strong>
                                    </td>
                                    <td class="text-center"><?php echo htmlspecialchars($perf['recommended_band'] ?? 'N/A'); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                        <?php if ($perf['approved_by']): ?>
                                            <br><small>by <?php echo htmlspecialchars($perf['approved_by_name']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-info" onclick="viewPerformance(<?php echo $perf['performance_id']; ?>)" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        
                                        <?php if ($is_hr_admin && $perf['status'] == 'DRAFT'): ?>
                                            <a href="cdp_annual.php?tab=annual&annual_source=calculate&id=<?php echo $perf['performance_id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($is_hr_admin && $perf['status'] == 'PENDING_APPROVAL'): ?>
                                            <a href="cdp_annual.php?approve_performance=<?php echo $perf['performance_id']; ?>" class="btn btn-sm btn-success" title="Approve" onclick="return confirm('Approve this performance rating?')">
                                                <i class="bi bi-check-lg"></i>
                                            </a>
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