<?php
// Suppress PHP errors for AJAX endpoints
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and has appropriate permissions
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch departments for filter dropdown
$dept_sql = "SELECT id, dept_name FROM departments ORDER BY dept_name";
$dept_result = $connection->query($dept_sql);

// Fetch roles for filter dropdown
$role_sql = "SELECT role_id, role_name FROM user_roles ORDER BY role_name";
$role_result = $connection->query($role_sql);

// Fetch salary ranges for filter
$salary_sql = "SELECT 
                MIN(salary) as min_salary,
                MAX(salary) as max_salary,
                AVG(salary) as avg_salary
               FROM employees WHERE salary > 0";
$salary_result = $connection->query($salary_sql);
$salary_stats = $salary_result->fetch_assoc();

// Build the main query with filters applied
$where_conditions = ["(u.type_id = 1 OR t.type_name = 'operations')"];
$params = [];
$types = "";

// Apply filters if they exist in URL
if (isset($_GET['filter_submit']) || isset($_GET['search'])) {
    // Department filter
    if (!empty($_GET['department'])) {
        $where_conditions[] = "e.department_id = ?";
        $params[] = intval($_GET['department']);
        $types .= "i";
    }
    
    // Role filter
    if (!empty($_GET['role'])) {
        $where_conditions[] = "u.role_id = ?";
        $params[] = intval($_GET['role']);
        $types .= "i";
    }
    
    // Status filter
    if (!empty($_GET['status'])) {
        $where_conditions[] = "u.user_status = ?";
        $params[] = $_GET['status'];
        $types .= "s";
    }
    
    // Date range filters
    if (!empty($_GET['date_from'])) {
        $where_conditions[] = "u.created_at >= ?";
        $params[] = $_GET['date_from'] . " 00:00:00";
        $types .= "s";
    }
    
    if (!empty($_GET['date_to'])) {
        $where_conditions[] = "u.created_at <= ?";
        $params[] = $_GET['date_to'] . " 23:59:59";
        $types .= "s";
    }
    
    // Salary range filters
    if (!empty($_GET['salary_min'])) {
        $where_conditions[] = "e.salary >= ?";
        $params[] = floatval($_GET['salary_min']);
        $types .= "d";
    }
    
    if (!empty($_GET['salary_max'])) {
        $where_conditions[] = "e.salary <= ?";
        $params[] = floatval($_GET['salary_max']);
        $types .= "d";
    }
    
    // Search term
    if (!empty($_GET['search'])) {
        $search_term = "%" . $_GET['search'] . "%";
        $where_conditions[] = "(e.first_name LIKE ? OR e.last_name LIKE ? OR CONCAT(e.first_name, ' ', e.last_name) LIKE ? OR u.user_email LIKE ? OR u.username LIKE ?)";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= "sssss";
    }
}

$where_clause = implode(" AND ", $where_conditions);

// Base SQL query
$sql = "SELECT 
            e.*, 
            u.username,
            u.user_status,
            u.created_at as user_created_at,
            r.role_name,
            r.role_level,
            t.type_name,
            d.dept_name as department_name,
            d.dept_code as department_code
        FROM employees e
        INNER JOIN users u ON e.user_id = u.user_id
        LEFT JOIN user_roles r ON u.role_id = r.role_id
        LEFT JOIN user_types t ON u.type_id = t.type_id
        LEFT JOIN departments d ON e.department_id = d.id
        WHERE " . $where_clause . "
        ORDER BY e.first_name, e.last_name";

// Prepare and execute query with parameters
if (!empty($params)) {
    $stmt = $connection->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $connection->query($sql);
}

// Check for errors
if (!$result) {
    die("Query failed: " . $connection->error);
}

// Get statistics for filtered results
// Add LEFT JOIN user_types t for stats query to match main query
$stats_sql = "SELECT 
                                COUNT(*) as total_employees,
                                SUM(CASE WHEN u.user_status = 'active' THEN 1 ELSE 0 END) as active_employees,
                                COUNT(DISTINCT r.role_name) as role_count,
                                COUNT(DISTINCT e.department_id) as department_count
                            FROM employees e
                            INNER JOIN users u ON e.user_id = u.user_id
                            LEFT JOIN user_roles r ON u.role_id = r.role_id
                            LEFT JOIN user_types t ON u.type_id = t.type_id
                            WHERE " . $where_clause;
              
if (!empty($params)) {
    $stmt_stats = $connection->prepare($stats_sql);
    $stmt_stats->bind_param($types, ...$params);
    $stmt_stats->execute();
    $stats_result = $stmt_stats->get_result();
    $stats = $stats_result->fetch_assoc();
} else {
    $stats_result = $connection->query($stats_sql);
    $stats = $stats_result->fetch_assoc();
}
?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Employee Management</h1>
            <div>
                <a href="user_roles.php" class="btn btn-info me-2">
                    <i class="bi bi-shield-lock"></i> Roles & Types
                </a>
                <a href="employees.php?source=add_employee" class="btn btn-primary">
                    <i class="bi bi-person-plus"></i> Add New Employee
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Employees</h5>
                        <h2><?php echo $stats['total_employees'] ?? 0; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Active Employees</h5>
                        <h2><?php echo $stats['active_employees'] ?? 0; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Roles Used</h5>
                        <h2><?php echo $stats['role_count'] ?? 0; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Departments</h5>
                        <h2><?php echo $stats['department_count'] ?? 0; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="card shadow-sm mb-4" style="border: 1px solid rgba(10, 34, 64, 0.1);">
            <div class="card-header" style="background: #0a2240; color: #f1bf70;">
                <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter Employees</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <input type="hidden" name="source" value="view_all">
                    <input type="hidden" name="filter_submit" value="1">
                    
                    <!-- Search Bar -->
                    <div class="col-12 mb-3">
                        <div class="input-group">
                            <span class="input-group-text" style="background: #f1bf70; color: #0a2240; border: none;">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" id="search" name="search" 
                                   placeholder="Search by name, email, or username..." 
                                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                                   style="border-left: none;">
                            <?php if (!empty($_GET['search'])): ?>
                            <a href="employees.php" class="btn btn-outline-secondary" type="button">
                                <i class="bi bi-x-lg"></i> Clear
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Filter Grid -->
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Department</label>
                        <select class="form-select" name="department">
                            <option value="">All Departments</option>
                            <?php 
                            if ($dept_result && $dept_result->num_rows > 0) {
                                $dept_result->data_seek(0);
                                while($dept = $dept_result->fetch_assoc()): 
                                    $selected = (isset($_GET['department']) && $_GET['department'] == $dept['id']) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $dept['id']; ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($dept['dept_name']); ?>
                                </option>
                            <?php 
                                endwhile;
                            } 
                            ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Role</label>
                        <select class="form-select" name="role">
                            <option value="">All Roles</option>
                            <?php 
                            if ($role_result && $role_result->num_rows > 0) {
                                $role_result->data_seek(0);
                                while($role = $role_result->fetch_assoc()): 
                                    $selected = (isset($_GET['role']) && $_GET['role'] == $role['role_id']) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $role['role_id']; ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($role['role_name']); ?>
                                </option>
                            <?php 
                                endwhile;
                            } 
                            ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Status</label>
                        <select class="form-select" name="status">
                            <option value="">All Statuses</option>
                            <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            <option value="suspended" <?php echo (isset($_GET['status']) && $_GET['status'] == 'suspended') ? 'selected' : ''; ?>>Suspended</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Date From</label>
                        <input type="date" class="form-control" name="date_from" 
                               value="<?php echo htmlspecialchars($_GET['date_from'] ?? ''); ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Date To</label>
                        <input type="date" class="form-control" name="date_to" 
                               value="<?php echo htmlspecialchars($_GET['date_to'] ?? ''); ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Salary Min ($)</label>
                        <input type="number" class="form-control" name="salary_min" step="100" min="0"
                               value="<?php echo htmlspecialchars($_GET['salary_min'] ?? ''); ?>"
                               placeholder="<?php echo number_format($salary_stats['min_salary'] ?? 0, 2); ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Salary Max ($)</label>
                        <input type="number" class="form-control" name="salary_max" step="100" min="0"
                               value="<?php echo htmlspecialchars($_GET['salary_max'] ?? ''); ?>"
                               placeholder="<?php echo number_format($salary_stats['max_salary'] ?? 50000, 2); ?>">
                    </div>

                    <!-- Filter Action Buttons -->
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary me-2" style="background: #f1bf70; border-color: #f1bf70; color: #0a2240; font-weight: 600;">
                            <i class="bi bi-funnel me-2"></i>Apply Filters
                        </button>
                        <a href="employees.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Reset All
                        </a>
                        
                        <!-- Active Filters Display -->
                        <?php 
                        $active_filters = [];
                        if (!empty($_GET['department'])) $active_filters[] = 'Department';
                        if (!empty($_GET['role'])) $active_filters[] = 'Role';
                        if (!empty($_GET['status'])) $active_filters[] = 'Status';
                        if (!empty($_GET['date_from']) || !empty($_GET['date_to'])) $active_filters[] = 'Date Range';
                        if (!empty($_GET['salary_min']) || !empty($_GET['salary_max'])) $active_filters[] = 'Salary Range';
                        if (!empty($_GET['search'])) $active_filters[] = 'Search';
                        
                        if (!empty($active_filters)): 
                        ?>
                        <span class="ms-3 text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Active filters: <?php echo implode(', ', $active_filters); ?>
                            (<?php echo $result->num_rows; ?> results)
                        </span>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Employees Table -->
        <div class="card shadow-sm">
            <div class="card-header" style="background: #0a2240; color: #f1bf70;">
                <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Employee List 
                    <span class="badge bg-light text-dark ms-2"><?php echo $result->num_rows; ?> records</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="employeesTable">
                        <thead>
                            <tr class="table-dark">
                                <th width="50">#</th>
                                <th width="70">Image</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Username</th>
                                <th>Department</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Salary</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php 
                                $serial = 1;
                                while ($employee = $result->fetch_assoc()): 
                                    // Set badge color based on role level
                                    $role_class = 'secondary';
                                    if (isset($employee['role_level'])) {
                                        if ($employee['role_level'] >= 90) {
                                            $role_class = 'danger';
                                        } elseif ($employee['role_level'] >= 70) {
                                            $role_class = 'warning';
                                        } elseif ($employee['role_level'] >= 50) {
                                            $role_class = 'info';
                                        }
                                    }
                                    
                                    $status_class = ($employee['user_status'] ?? '') == 'active' ? 'success' : 'warning';
                                ?>
                                    <tr id="employee-row-<?php echo $employee['employee_id']; ?>">
                                        <td class="fw-bold"><?php echo $serial++; ?></td>
                                        <td>
                                            <?php
                                            $image_url = "";
                                            if (!empty($employee['user_image']) && file_exists("../uploads/profiles/" . $employee['user_image'])) {
                                                $image_url = "../uploads/profiles/" . $employee['user_image'];
                                            } else {
                                                $name = urlencode(($employee['first_name'] ?? '') . '+' . ($employee['last_name'] ?? ''));
                                                $image_url = "https://ui-avatars.com/api/?name=$name&background=f1bf70&color=0f172a&size=40";
                                            }
                                            ?>
                                            <img src="<?php echo $image_url; ?>" 
                                                 alt="<?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>"
                                                 class="rounded-circle" width="40" height="40"
                                                 onerror="this.src='https://ui-avatars.com/api/?name=Employee&background=f1bf70&color=0f172a&size=40'">
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></strong>
                                            <br><small class="text-muted">ID: <?php echo $employee['employee_id']; ?></small>
                                        </td>
                                        <td><a href="mailto:<?php echo htmlspecialchars($employee['user_email']); ?>"><?php echo htmlspecialchars($employee['user_email']); ?></a></td>
                                        <td><code><?php echo htmlspecialchars($employee['username']); ?></code></td>
                                        <td>
                                            <?php if (!empty($employee['department_name'])): ?>
                                                <span class="badge bg-secondary">
                                                    <?php echo htmlspecialchars($employee['department_name']); ?>
                                                    <?php if (!empty($employee['department_code'])): ?>
                                                        <small>(<?php echo $employee['department_code']; ?>)</small>
                                                    <?php endif; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Not Assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($employee['role_name'])): ?>
                                                <span class="badge bg-<?php echo $role_class; ?>">
                                                    <?php echo htmlspecialchars($employee['role_name']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Not Assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $status_class; ?>">
                                                <?php echo $employee['user_status'] ?? 'unknown'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold">$<?php echo number_format($employee['salary'] ?? 0, 2); ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-info view-employee-btn" 
                                                        onclick="viewEmployee(<?php echo $employee['employee_id']; ?>)" 
                                                        title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                              
                                                <a href='employees.php?source=edit_employee&id=<?php echo $employee['employee_id']; ?>'
                                                   class="btn btn-outline-warning"
                                                   title="Edit Employee">
                                                    <i class="bi bi-pencil text-dark"></i>
                                                </a>
                                                
                                                <button type="button" class="btn btn-outline-danger delete-employee-btn" 
                                                        onclick="showDeleteConfirmation(<?php echo $employee['employee_id']; ?>, '<?php echo htmlspecialchars(addslashes($employee['first_name'] . ' ' . $employee['last_name']), ENT_QUOTES); ?>')"
                                                        title="Delete Employee">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-people display-4 d-block mb-3"></i>
                                            <h5>No employees found</h5>
                                            <?php if (isset($_GET['filter_submit'])): ?>
                                                <p>No results match your filter criteria. Try adjusting your filters.</p>
                                                <a href="employees.php" class="btn btn-outline-primary mt-2">
                                                    <i class="bi bi-arrow-counterclockwise me-2 text-dark"></i>Clear All Filters
                                                </a>
                                            <?php else: ?>
                                                <p>Get started by adding your first employee.</p>
                                                <a href="employees.php?source=add_employee" class="btn btn-primary mt-2">
                                                    <i class="bi bi-person-plus"></i> Add Employee
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Employee Modal -->
<div class="modal fade" id="viewEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: #0a2240; color: #f1bf70;">
                <h5 class="modal-title"><i class="bi bi-person-badge me-2"></i>Employee Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="employeeDetails">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="editEmployeeBtn" class="btn" style="background: #f1bf70; border-color: #f1bf70; color: #0a2240;">Edit Employee</a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #0a2240; color: #f1bf70;">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteEmployeeName"></strong>?</p>
                <p class="text-danger"><small>This action cannot be undone. The employee record will be permanently removed.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete Employee</button>
            </div>
        </div>
    </div>
</div>

<!-- Toasts -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="successToast" class="toast align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle me-2"></i>
                <span id="toastMessage">Operation completed successfully!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="errorToast" class="toast align-items-center text-bg-danger border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <span id="errorToastMessage">An error occurred!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<style>
    /* Theme Colors */
    :root {
        --dark-blue: #0a2240;
        --gold: #f1bf70;
    }

    /* Card Headers */
    .card-header {
        background: var(--dark-blue) !important;
        color: var(--gold) !important;
        font-weight: 600;
    }

    /* Filter Section */
    .input-group-text {
        background: var(--gold);
        color: var(--dark-blue);
        border: none;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--gold);
        box-shadow: 0 0 0 0.2rem rgba(241, 191, 112, 0.25);
    }

    /* Table Styles */
    .table-dark {
        --bs-table-bg: var(--dark-blue) !important;
        --bs-table-color: white !important;
    }

    .table tbody tr:hover {
        background-color: rgba(241, 191, 112, 0.1);
    }

    /* Badge Styles */
    .badge {
        font-size: 0.85rem;
        padding: 0.4rem 0.6rem;
    }

    /* Button Styles */
    .btn-primary {
        background: var(--gold);
        border-color: var(--gold);
        color: var(--dark-blue);
        font-weight: 600;
    }

    .btn-primary:hover {
        background: #e5b465;
        border-color: #e5b465;
        color: var(--dark-blue);
    }

    .btn-group .btn {
        border-radius: 4px !important;
        margin: 0 2px;
    }

    /* Action Buttons */
    .btn-outline-info { color: #17a2b8; border-color: #17a2b8; }
    .btn-outline-info:hover { background: #17a2b8; color: white; }
    .btn-outline-warning { color: #ffc107; border-color: #ffc107; }
    .btn-outline-warning:hover { background: #ffc107; color: var(--dark-blue); }
    .btn-outline-danger { color: #dc3545; border-color: #dc3545; }
    .btn-outline-danger:hover { background: #dc3545; color: white; }

    /* Modal Styles */
    .modal-header {
        background: var(--dark-blue) !important;
        color: var(--gold) !important;
    }

    .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }

    /* Employee Details Image */
    .employee-details-img {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border: 3px solid var(--gold);
        border-radius: 50%;
    }

    /* Code Tag */
    code {
        background: #f8f9fa;
        padding: 2px 4px;
        border-radius: 4px;
        color: var(--dark-blue);
    }

    /* Active Filters Display */
    .text-muted i {
        color: var(--gold);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        
        .btn-group .btn {
            border-radius: 4px !important;
        }
    }
</style>

<script>
// Global variables
let currentDeleteEmployeeId = null;

// View Employee Details
function viewEmployee(employeeId) {
    if (!employeeId) {
        showError('Invalid employee ID');
        return;
    }
    
    document.getElementById('employeeDetails').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading employee details...</p>
        </div>
    `;
    
    const viewModal = new bootstrap.Modal(document.getElementById('viewEmployeeModal'));
    viewModal.show();
    
    fetch('includes/get_employee_details.php?id=' + employeeId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.employee) {
                const emp = data.employee;
                
                let imageUrl = "";
                if (emp.user_image && emp.user_image !== 'null' && emp.user_image !== '') {
                    imageUrl = '../uploads/profiles/' + emp.user_image;
                } else {
                    const name = encodeURIComponent((emp.first_name || '') + '+' + (emp.last_name || ''));
                    imageUrl = 'https://ui-avatars.com/api/?name=' + name + '&background=f1bf70&color=0f172a&size=120';
                }
                
                const roleBadge = emp.role_name ? 
                    `<span class="badge bg-info">${escapeHtml(emp.role_name)}</span>` : 
                    '<span class="badge bg-secondary">Not Assigned</span>';
                
                const statusBadge = emp.user_status == 'active' ? 
                    '<span class="badge bg-success">Active</span>' : 
                    '<span class="badge bg-warning">Inactive</span>';
                
                const departmentBadge = emp.department_name ? 
                    `<span class="badge bg-secondary">${escapeHtml(emp.department_name)}</span>` : 
                    '<span class="badge bg-secondary">Not Assigned</span>';
                
                const detailsHtml = `
                    <div class="row">
                        <div class="col-md-4 text-center mb-4 mb-md-0">
                            <img src="${imageUrl}" 
                                 alt="${escapeHtml(emp.first_name + ' ' + emp.last_name)}"
                                 class="employee-details-img mb-3"
                                 onerror="this.src='https://ui-avatars.com/api/?name=Employee&background=f1bf70&color=0f172a&size=120'">
                            <h4 class="mb-1">${escapeHtml(emp.first_name + ' ' + emp.last_name)}</h4>
                            <p class="text-muted">Employee ID: ${emp.employee_id}</p>
                            <p>${roleBadge} ${statusBadge}</p>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Email:</strong><br>
                                    <a href="mailto:${escapeHtml(emp.user_email)}">${escapeHtml(emp.user_email)}</a></p>
                                    
                                    <p><strong>Username:</strong><br>
                                    <code>${escapeHtml(emp.username)}</code></p>
                                    
                                    <p><strong>Department:</strong><br>
                                    ${departmentBadge}</p>
                                    
                                    <p><strong>Salary:</strong><br>
                                    $${emp.salary ? parseFloat(emp.salary).toFixed(2) : '0.00'}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Field of Study:</strong><br>
                                    ${emp.field_of_study ? escapeHtml(emp.field_of_study) : '<span class="text-muted">N/A</span>'}</p>
                                    
                                    <p><strong>Qualification:</strong><br>
                                    ${emp.qualification ? escapeHtml(emp.qualification) : '<span class="text-muted">N/A</span>'}</p>
                                    
                                    <p><strong>Highest Graduation:</strong><br>
                                    ${emp.highest_graduation ? escapeHtml(emp.highest_graduation) : '<span class="text-muted">N/A</span>'}</p>
                                    
                                    <p><strong>Year of Graduation:</strong><br>
                                    ${emp.year_of_graduation ? escapeHtml(emp.year_of_graduation) : '<span class="text-muted">N/A</span>'}</p>
                                </div>
                            </div>
                        </div>
                    </div>`;
                
                document.getElementById('employeeDetails').innerHTML = detailsHtml;
                document.getElementById('editEmployeeBtn').href = 'employees.php?source=edit_employee&id=' + employeeId;
            } else {
                document.getElementById('employeeDetails').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${data.message || 'Failed to load employee details'}
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('employeeDetails').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Error loading employee details. Please try again.
                </div>
            `;
            console.error('Error:', error);
        });
}

// Show Delete Confirmation
function showDeleteConfirmation(employeeId, employeeName) {
    if (!employeeId) {
        showError('Invalid employee ID');
        return;
    }
    
    currentDeleteEmployeeId = employeeId;
    document.getElementById('deleteEmployeeName').textContent = employeeName;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteModal.show();
}

// Delete Employee
function deleteEmployee() {
    if (!currentDeleteEmployeeId) {
        showError('No employee selected for deletion');
        return;
    }
    
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    const originalText = deleteBtn.innerHTML;
    
    deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    deleteBtn.disabled = true;
    
    fetch('includes/delete_employee.php?id=' + currentDeleteEmployeeId)
        .then(response => response.json())
        .then(data => {
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;
            
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
                modal.hide();
                
                const row = document.getElementById('employee-row-' + currentDeleteEmployeeId);
                if (row) {
                    row.style.opacity = '0';
                    row.style.transition = 'opacity 0.4s';
                    setTimeout(() => {
                        row.remove();
                        updateSerialNumbers();
                    }, 400);
                }
                
                showSuccess(data.message || 'Employee deleted successfully!');
                currentDeleteEmployeeId = null;
            } else {
                showError(data.message || 'Failed to delete employee');
            }
        })
        .catch(error => {
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;
            showError('Error deleting employee: ' + error.message);
            console.error('Error:', error);
        });
}

// Update serial numbers after deletion
function updateSerialNumbers() {
    const rows = document.querySelectorAll('#employeesTable tbody tr');
    rows.forEach((row, index) => {
        const serialCell = row.querySelector('td:first-child');
        if (serialCell) {
            serialCell.textContent = (index + 1);
            serialCell.classList.add('fw-bold');
        }
    });
}

// Helper function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Show success message
function showSuccess(message) {
    document.getElementById('toastMessage').textContent = message;
    const toast = new bootstrap.Toast(document.getElementById('successToast'));
    toast.show();
    
    setTimeout(() => toast.hide(), 5000);
}

// Show error message
function showError(message) {
    document.getElementById('errorToastMessage').textContent = message;
    const toast = new bootstrap.Toast(document.getElementById('errorToast'));
    toast.show();
    
    setTimeout(() => toast.hide(), 5000);
}

// Initialize event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Set up delete confirmation button
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', deleteEmployee);
    }
    
    // Handle enter key in search input
    const searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.querySelector('form').submit();
            }
        });
    }
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>