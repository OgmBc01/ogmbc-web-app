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

// $is_hr_admin = ($user_role == 'hr_admin' || $user_role == 'ceo_gm' || $user_role == 'admin_staff');

// Get filter parameters
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : '';
$selected_employee = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : '';
$selected_status = isset($_GET['status']) ? $_GET['status'] : '';
$selected_type = isset($_GET['cdp_type']) ? $_GET['cdp_type'] : '';
$search_title = isset($_GET['title']) ? trim($_GET['title']) : '';

// Get statistics
$stats_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'APPROVED' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN cdp_type = 'CERTIFICATE' THEN 1 ELSE 0 END) as certificates,
                SUM(CASE WHEN cdp_type = 'COURSE' THEN 1 ELSE 0 END) as courses
                FROM cdp_records WHERE 1";
if (!empty($selected_year)) {
    $stats_query .= " AND YEAR(created_at) = $selected_year";
}
if (!empty($selected_employee)) {
    $stats_query .= " AND employee_id = $selected_employee";
}
if (!empty($selected_status)) {
    $stats_query .= " AND status = '" . mysqli_real_escape_string($connection, $selected_status) . "'";
}
if (!empty($selected_type)) {
    $stats_query .= " AND cdp_type = '" . mysqli_real_escape_string($connection, $selected_type) . "'";
}
if (!empty($search_title)) {
    $stats_query .= " AND title LIKE '%" . mysqli_real_escape_string($connection, $search_title) . "%'";
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
                <div class="col-md-2">
                    <label for="year" class="form-label">Year</label>
                    <select id="year" name="year" class="form-control">
                        <option value="">All Years</option>
                        <?php for($y = date('Y'); $y >= 2024; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo ($selected_year == $y) ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="APPROVED" <?php echo ($selected_status == 'APPROVED') ? 'selected' : ''; ?>>Approved</option>
                        <option value="PENDING" <?php echo ($selected_status == 'PENDING') ? 'selected' : ''; ?>>Pending</option>
                        <option value="REJECTED" <?php echo ($selected_status == 'REJECTED') ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="cdp_type" class="form-label">Type</label>
                    <select id="cdp_type" name="cdp_type" class="form-control">
                        <option value="">All Types</option>
                        <option value="CERTIFICATE" <?php echo ($selected_type == 'CERTIFICATE') ? 'selected' : ''; ?>>Certificate</option>
                        <option value="COURSE" <?php echo ($selected_type == 'COURSE') ? 'selected' : ''; ?>>Course</option>
                        <option value="LOYALTY" <?php echo ($selected_type == 'LOYALTY') ? 'selected' : ''; ?>>Loyalty</option>
                        <option value="BEHAVIOR" <?php echo ($selected_type == 'BEHAVIOR') ? 'selected' : ''; ?>>Behavior</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($search_title); ?>" placeholder="Search by title...">
                </div>
                <?php if ($is_hr_admin): ?>
                <div class="col-md-3">
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
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="cdp_annual.php?tab=cdp" class="btn btn-outline-secondary w-100">Clear</a>
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
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Uplift</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php  
                        // Pagination setup
                        $per_page = 20;
                        $page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
                        $offset = ($page - 1) * $per_page;
                        $where = ["1=1"];
                        if (!empty($selected_year)) {
                            $where[] = "YEAR(c.created_at) = $selected_year";
                        }
                        if (!empty($selected_employee)) {
                            $where[] = "c.employee_id = $selected_employee";
                        }
                        if (!empty($selected_status)) {
                            $where[] = "c.status = '" . mysqli_real_escape_string($connection, $selected_status) . "'";
                        }
                        if (!empty($selected_type)) {
                            $where[] = "c.cdp_type = '" . mysqli_real_escape_string($connection, $selected_type) . "'";
                        }
                        if (!empty($search_title)) {
                            $where[] = "c.title LIKE '%" . mysqli_real_escape_string($connection, $search_title) . "%'";
                        }
                        $where_clause = implode(' AND ', $where);
                        // Count total filtered records for pagination
                        $count_query = "SELECT COUNT(*) as total FROM cdp_records c JOIN users u ON c.employee_id = u.user_id LEFT JOIN users a ON c.approved_by = a.user_id WHERE $where_clause";
                        $count_result = mysqli_query($connection, $count_query);
                        $total_records = ($count_result && mysqli_num_rows($count_result) > 0) ? (int)mysqli_fetch_assoc($count_result)['total'] : 0;
                        $total_pages = ceil($total_records / $per_page);
                        $query = "SELECT c.*, 
                                 CONCAT(u.first_name, ' ', u.last_name) as employee_name,
                                 CONCAT(a.first_name, ' ', a.last_name) as approved_by_name
                                 FROM cdp_records c
                                 JOIN users u ON c.employee_id = u.user_id
                                 LEFT JOIN users a ON c.approved_by = a.user_id
                                 WHERE $where_clause
                                 ORDER BY c.created_at DESC
                                 LIMIT $offset, $per_page";
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
                                    <td><?php echo htmlspecialchars($cdp['employee_name']); ?></td>
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
                                            <a href="cdp_annual.php?approve_cdp=<?php echo $cdp['cdp_id']; ?>" class="btn btn-sm btn-success" title="Approve" onclick="return confirm('Approve this CDP record?')">
                                                <i class="bi bi-check-lg"></i>
                                            </a>
                                            <button class="btn btn-sm btn-danger" onclick="rejectWithNotes(<?php echo $cdp['cdp_id']; ?>)" title="Reject with Notes">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
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
    <!-- Pagination Controls -->
    <?php if ($total_pages > 1): ?>
    <nav aria-label="CDP pagination">
        <ul class="pagination justify-content-center my-3">
            <li class="page-item<?php if ($page <= 1) echo ' disabled'; ?>">
                <a class="page-link" href="?<?php 
                    $params = $_GET; $params['page'] = $page - 1; echo http_build_query($params); 
                ?>" tabindex="-1">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item<?php if ($i == $page) echo ' active'; ?>">
                    <a class="page-link" href="?<?php $params = $_GET; $params['page'] = $i; echo http_build_query($params); ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item<?php if ($page >= $total_pages) echo ' disabled'; ?>">
                <a class="page-link" href="?<?php 
                    $params = $_GET; $params['page'] = $page + 1; echo http_build_query($params); 
                ?>">Next</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>