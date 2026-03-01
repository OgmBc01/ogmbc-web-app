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

// Get selected year filter
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$selected_employee = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : ($is_hr_admin ? '' : $user_id);

// Get statistics
$stats_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'APPROVED' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN cdp_type = 'CERTIFICATE' THEN 1 ELSE 0 END) as certificates,
                SUM(CASE WHEN cdp_type = 'COURSE' THEN 1 ELSE 0 END) as courses
                FROM cdp_records
                WHERE YEAR(created_at) = $selected_year";
if (!$is_hr_admin) {
    $stats_query .= " AND employee_id = $user_id";
}
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get employees for filter (HR only)
if ($is_hr_admin) {
    $employees_query = "SELECT u.user_id, u.first_name, u.last_name 
                       FROM users u
                       WHERE u.user_status = 'active'
                       ORDER BY u.first_name";
    $employees_result = mysqli_query($connection, $employees_query);
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">CDP Records</h1>
        <div>
            <a href="cdp_annual.php?source=add_cdp" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add CDP Record
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total</h5>
                    <h2><?php echo $stats['total'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Approved</h5>
                    <h2><?php echo $stats['approved'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-2">
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
                    <h5 class="card-title">Certificates</h5>
                    <h2><?php echo $stats['certificates'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Courses</h5>
                    <h2><?php echo $stats['courses'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <input type="hidden" name="tab" value="cdp">
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

    <!-- CDP Records Table -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-mortarboard me-2"></i>CDP Records</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr class="table-dark">
                            <th>Date</th>
                            <?php if ($is_hr_admin): ?><th>Employee</th><?php endif; ?>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Uplift</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php  
                        $where = ["YEAR(c.created_at) = $selected_year"];
                        
                        if (!$is_hr_admin) {
                            $where[] = "c.employee_id = $user_id";
                        } elseif (!empty($selected_employee)) {
                            $where[] = "c.employee_id = $selected_employee";
                        }
                        
                        $where_clause = implode(' AND ', $where);
                        
                        $query = "SELECT c.*, 
                                 CONCAT(u.first_name, ' ', u.last_name) as employee_name,
                                 CONCAT(a.first_name, ' ', a.last_name) as approved_by_name
                                 FROM cdp_records c
                                 JOIN users u ON c.employee_id = u.user_id
                                 LEFT JOIN users a ON c.approved_by = a.user_id
                                 WHERE $where_clause
                                 ORDER BY c.created_at DESC";
                        
                        $result = mysqli_query($connection, $query);
                        
                        if (!$result) {
                            echo "<tr><td colspan='7' class='text-center text-danger'>Error: " . mysqli_error($connection) . "</td></tr>";
                        } else if (mysqli_num_rows($result) == 0) {
                            echo "<tr><td colspan='7' class='text-center'>No CDP records found.</td></tr>";
                        } else {
                            while($cdp = mysqli_fetch_assoc($result)):
                                $type_class = 'secondary';
                                switch($cdp['cdp_type']) {
                                    case 'CERTIFICATE': $type_class = 'success'; break;
                                    case 'COURSE': $type_class = 'info'; break;
                                    case 'LOYALTY': $type_class = 'warning'; break;
                                    case 'BEHAVIOR': $type_class = 'primary'; break;
                                }
                                
                                $status_class = 'warning';
                                $status_text = 'Pending';
                                if ($cdp['status'] == 'APPROVED') {
                                    $status_class = 'success';
                                    $status_text = 'Approved';
                                } elseif ($cdp['status'] == 'REJECTED') {
                                    $status_class = 'danger';
                                    $status_text = 'Rejected';
                                }
                                ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($cdp['created_at'])); ?></td>
                                    <?php if ($is_hr_admin): ?>
                                    <td><?php echo htmlspecialchars($cdp['employee_name']); ?></td>
                                    <?php endif; ?>
                                    <td><span class="badge bg-<?php echo $type_class; ?>"><?php echo $cdp['cdp_type']; ?></span></td>
                                    <td><?php echo htmlspecialchars($cdp['title']); ?></td>
                                    <td>
                                        <?php if ($cdp['uplift_percentage']): ?>
                                            <span class="badge bg-success">+<?php echo $cdp['uplift_percentage']; ?>%</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                        <?php if ($cdp['status'] == 'APPROVED'): ?>
                                            <br><small>by <?php echo htmlspecialchars($cdp['approved_by_name']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewCDP(<?php echo $cdp['cdp_id']; ?>)" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        
                                        <?php if ($cdp['status'] == 'PENDING'): ?>
                                            <?php if ($is_hr_admin): ?>
                                                <a href="cdp_annual.php?approve_cdp=<?php echo $cdp['cdp_id']; ?>" class="btn btn-sm btn-success" title="Approve" onclick="return confirm('Approve this CDP record?')">
                                                    <i class="bi bi-check-lg"></i>
                                                </a>
                                                <a href="cdp_annual.php?reject_cdp=<?php echo $cdp['cdp_id']; ?>" class="btn btn-sm btn-danger" title="Reject" onclick="return confirm('Reject this CDP record?')">
                                                    <i class="bi bi-x-lg"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="cdp_annual.php?source=edit_cdp&id=<?php echo $cdp['cdp_id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
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