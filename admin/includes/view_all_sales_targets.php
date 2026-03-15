<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get current user's role and ID
$user_id = $_SESSION['user_id'];
$user_role_query = "SELECT role_name FROM users u LEFT JOIN user_roles r ON u.role_id = r.role_id WHERE u.user_id = $user_id";
$user_role_result = mysqli_query($connection, $user_role_query);
$user_role = mysqli_fetch_assoc($user_role_result)['role_name'] ?? '';

// Get selected filters
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$selected_month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$selected_employee = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : '';

// Get statistics
$stats_query = "SELECT 
                COUNT(*) as total_targets,
                SUM(CASE WHEN status = 'VALIDATED' THEN 1 ELSE 0 END) as validated,
                SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) as pending,
                COALESCE(SUM(points_awarded), 0) as total_points
                FROM sales_targets
                WHERE year = $selected_year";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get sales employees for filter
$sales_query = "SELECT u.user_id, u.first_name, u.last_name 
                FROM users u
                JOIN user_roles r ON u.role_id = r.role_id
                WHERE r.role_name IN ('SALES_STAFF', 'CEO_GM', 'ADMIN_STAFF')
                ORDER BY u.first_name";
$sales_result = mysqli_query($connection, $sales_query);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">Sales Targets</h1>
        <div>
            <a href="sales_targets.php?source=set_target" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Set New Target
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Targets</h5>
                    <h2><?php echo $stats['total_targets'] ?? 0; ?></h2>
                    <small>Year <?php echo $selected_year; ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Validated</h5>
                    <h2><?php echo $stats['validated'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Pending</h5>
                    <h2><?php echo $stats['pending'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Points</h5>
                    <h2><?php echo number_format($stats['total_points'] ?? 0); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
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
                    <label for="month" class="form-label">Month</label>
                    <select id="month" name="month" class="form-control">
                        <option value="">All Months</option>
                        <?php for($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo $m; ?>" <?php echo ($selected_month == $m) ? 'selected' : ''; ?>>
                                <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="employee_id" class="form-label">Sales Person</label>
                    <select id="employee_id" name="employee_id" class="form-control">
                        <option value="">All Sales Staff</option>
                        <?php while($sales = mysqli_fetch_assoc($sales_result)): ?>
                            <option value="<?php echo $sales['user_id']; ?>" <?php echo ($selected_employee == $sales['user_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sales['first_name'] . ' ' . $sales['last_name']); ?>
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

    <!-- Targets Table -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-bullseye me-2"></i>Sales Targets</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr class="table-dark">
                            <th>Period</th>
                            <th>Sales Person</th>
                            <th>Target (AED)</th>
                            <th>Actual (AED)</th>
                            <th>Achievement</th>
                            <th>Points</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php  
                        // Build query
                        $where = ["st.year = $selected_year"];
                        if (!empty($selected_month)) {
                            $where[] = "st.month = $selected_month";
                        }
                        if (!empty($selected_employee)) {
                            $where[] = "st.employee_id = $selected_employee";
                        }
                        
                        // If sales staff, only show their own targets
                        if ($user_role == 'SALES_STAFF') {
                            $where[] = "st.employee_id = $user_id";
                        }
                        
                        $where_clause = implode(' AND ', $where);
                        
                        $query = "SELECT st.*, 
                                 CONCAT(u.first_name, ' ', u.last_name) as employee_name
                                 FROM sales_targets st
                                 JOIN users u ON st.employee_id = u.user_id
                                 WHERE $where_clause
                                 ORDER BY st.year DESC, st.month DESC, u.first_name";
                        
                        $result = mysqli_query($connection, $query);
                        
                        if (!$result) {
                            echo "<tr><td colspan='8' class='text-center text-danger'>Error: " . mysqli_error($connection) . "</td></tr>";
                        } else if (mysqli_num_rows($result) == 0) {
                            echo "<tr><td colspan='8' class='text-center'>No targets found for the selected period.</td></tr>";
                        } else {
                            while($target = mysqli_fetch_assoc($result)):
                                $achievement = $target['attainment_percentage'] ? number_format($target['attainment_percentage'], 1) . '%' : '-';
                                
                                // Status badge
                                $status_class = 'secondary';
                                $status_text = 'Not Set';
                                if ($target['status'] == 'SUBMITTED') {
                                    $status_class = 'info';
                                    $status_text = 'Submitted';
                                } elseif ($target['status'] == 'VALIDATED') {
                                    $status_class = 'success';
                                    $status_text = 'Validated';
                                } elseif ($target['status'] == 'REJECTED') {
                                    $status_class = 'danger';
                                    $status_text = 'Rejected';
                                }
                                ?>
                                <tr>
                                    <td><?php echo date('F Y', mktime(0, 0, 0, $target['month'], 1, $target['year'])); ?></td>
                                    <td><strong><?php echo htmlspecialchars($target['employee_name']); ?></strong></td>
                                    <td>AED <?php echo number_format($target['target_value'], 2); ?></td>
                                    <td>
                                        <?php if ($target['actual_value']): ?>
                                            AED <?php echo number_format($target['actual_value'], 2); ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($target['attainment_percentage']): ?>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-<?php echo $target['attainment_percentage'] >= 100 ? 'success' : ($target['attainment_percentage'] >= 75 ? 'info' : ($target['attainment_percentage'] >= 50 ? 'warning' : 'danger')); ?>" 
                                                     style="width: <?php echo min($target['attainment_percentage'], 100); ?>%">
                                                    <?php echo $achievement; ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($target['points_awarded']): ?>
                                            <span class="badge bg-success"><?php echo $target['points_awarded']; ?> pts</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-<?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewTarget(<?php echo $target['target_id']; ?>)" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        
                                        <?php if ($user_role == 'SALES_STAFF' && $target['status'] == 'PENDING'): ?>
                                            <a href="sales_targets.php?source=submit_achievement&id=<?php echo $target['target_id']; ?>" class="btn btn-sm btn-success" title="Submit Achievement">
                                                <i class="bi bi-upload"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($user_role == 'CEO_GM' || $user_role == 'HR_ADMIN' || $user_role == 'ADMIN_STAFF'): ?>
                                            <?php if ($target['status'] == 'PENDING'): ?>
                                                <a href="sales_targets.php?source=edit_target&id=<?php echo $target['target_id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if ($target['status'] == 'SUBMITTED'): ?>
                                                <a href="sales_targets.php?validate=<?php echo $target['target_id']; ?>" class="btn btn-sm btn-success" title="Validate & Award Points" onclick="return confirm('Validate this achievement and award points?')">
                                                    <i class="bi bi-check-lg"></i>
                                                </a>
                                                <a href="sales_targets.php?source=edit_target&id=<?php echo $target['target_id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if ($target['status'] == 'PENDING'): ?>
                                                <a href="sales_targets.php?delete=<?php echo $target['target_id']; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            <?php endif; ?>
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