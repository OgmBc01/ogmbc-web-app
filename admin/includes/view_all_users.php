<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get statistics
$total_users_query = "SELECT COUNT(*) as total FROM users";
$total_users_result = mysqli_query($connection, $total_users_query);
$total_users = mysqli_fetch_assoc($total_users_result)['total'];

$active_users_query = "SELECT COUNT(*) as total FROM users WHERE user_status = 'active'";
$active_users_result = mysqli_query($connection, $active_users_query);
$active_users = mysqli_fetch_assoc($active_users_result)['total'];

$role_stats_query = "SELECT r.role_name, COUNT(u.user_id) as count 
                     FROM user_roles r
                     LEFT JOIN users u ON r.role_id = u.role_id
                     GROUP BY r.role_id
                     ORDER BY r.role_level DESC";
$role_stats_result = mysqli_query($connection, $role_stats_query);

$type_stats_query = "SELECT t.type_name, COUNT(u.user_id) as count 
                     FROM user_types t
                     LEFT JOIN users u ON t.type_id = u.type_id
                     GROUP BY t.type_id
                     ORDER BY t.type_name";
$type_stats_result = mysqli_query($connection, $type_stats_query);
?>

<div class="container-fluid">

<?php
// Get current user's role for access control (same logic as sidebar.php)
$current_user_id = $_SESSION['user_id'] ?? 0;
$user_role_id = null;
$user_role_name = null;
if ($current_user_id > 0) {
    $role_query = "SELECT r.role_id, r.role_name FROM users u LEFT JOIN user_roles r ON u.role_id = r.role_id WHERE u.user_id = $current_user_id";
    $role_result = mysqli_query($connection, $role_query);
    if ($role_result && mysqli_num_rows($role_result) > 0) {
        $user_role = mysqli_fetch_assoc($role_result);
        $user_role_id = $user_role['role_id'];
        $user_role_name = $user_role['role_name'];
    }
}
$is_manager = ($user_role_id == 2 || strtolower($user_role_name) == 'manager');
?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">User Management</h1>
        <div>
            <a href="user_roles.php" class="btn btn-info me-2">
                <i class="bi bi-shield-lock"></i> Manage Roles & Types
            </a>
            <a href="users.php?source=add_user" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New User
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <h2><?php echo $total_users; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Active Users</h5>
                    <h2><?php echo $active_users; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Roles</h5>
                    <h2><?php echo mysqli_num_rows($role_stats_result); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Types</h5>
                    <h2><?php echo mysqli_num_rows($type_stats_result); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table with Filtering -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-people me-2"></i>All Users</h5>
        </div>
        <div class="card-body">
            <!-- Filter Form -->
            <form method="GET" class="row g-3 mb-4 align-items-end" style="background: #f8f9fa; border-radius: 8px; padding: 16px;">
                <div class="col-md-2">
                    <label for="filter_username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="filter_username" name="filter_username" value="<?php echo htmlspecialchars($_GET['filter_username'] ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <label for="filter_email" class="form-label">Email</label>
                    <input type="text" class="form-control" id="filter_email" name="filter_email" value="<?php echo htmlspecialchars($_GET['filter_email'] ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <label for="filter_role" class="form-label">Role</label>
                    <select class="form-select" id="filter_role" name="filter_role">
                        <option value="">All</option>
                        <?php mysqli_data_seek($role_stats_result, 0); while($role = mysqli_fetch_assoc($role_stats_result)): ?>
                        <option value="<?php echo $role['role_name']; ?>" <?php if(($_GET['filter_role'] ?? '') == $role['role_name']) echo 'selected'; ?>><?php echo htmlspecialchars($role['role_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filter_type" class="form-label">Type</label>
                    <select class="form-select" id="filter_type" name="filter_type">
                        <option value="">All</option>
                        <?php mysqli_data_seek($type_stats_result, 0); while($type = mysqli_fetch_assoc($type_stats_result)): ?>
                        <option value="<?php echo $type['type_name']; ?>" <?php if(($_GET['filter_type'] ?? '') == $type['type_name']) echo 'selected'; ?>><?php echo htmlspecialchars($type['type_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filter_status" class="form-label">Status</label>
                    <select class="form-select" id="filter_status" name="filter_status">
                        <option value="">All</option>
                        <option value="active" <?php if(($_GET['filter_status'] ?? '') == 'active') echo 'selected'; ?>>Active</option>
                        <option value="inactive" <?php if(($_GET['filter_status'] ?? '') == 'inactive') echo 'selected'; ?>>Inactive</option>
                        <option value="suspended" <?php if(($_GET['filter_status'] ?? '') == 'suspended') echo 'selected'; ?>>Suspended</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary mb-2"><i class="bi bi-funnel me-1"></i> Filter</button>
                    <a href="users.php" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i> Clear</a>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr class="table-dark">
                            <th>ID</th>
                            <th>Image</th>
                            <th>Username</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php  
                        // Build filter conditions
                        $where = [];
                        if (!empty($_GET['filter_username'])) {
                            $username = mysqli_real_escape_string($connection, $_GET['filter_username']);
                            $where[] = "u.username LIKE '%$username%'";
                        }
                        if (!empty($_GET['filter_email'])) {
                            $email = mysqli_real_escape_string($connection, $_GET['filter_email']);
                            $where[] = "u.user_email LIKE '%$email%'";
                        }
                        if (!empty($_GET['filter_role'])) {
                            $role = mysqli_real_escape_string($connection, $_GET['filter_role']);
                            $where[] = "r.role_name = '$role'";
                        }
                        if (!empty($_GET['filter_type'])) {
                            $type = mysqli_real_escape_string($connection, $_GET['filter_type']);
                            $where[] = "t.type_name = '$type'";
                        }
                        if (!empty($_GET['filter_status'])) {
                            $status = mysqli_real_escape_string($connection, $_GET['filter_status']);
                            $where[] = "u.user_status = '$status'";
                        }
                        $where_sql = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';
                        $query = "SELECT u.*, 
                                  r.role_name, r.role_level,
                                  t.type_name
                                  FROM users u
                                  LEFT JOIN user_roles r ON u.role_id = r.role_id
                                  LEFT JOIN user_types t ON u.type_id = t.type_id
                                  $where_sql
                                  ORDER BY u.user_id DESC";
                        $result = mysqli_query($connection, $query);
                        if (!$result) {
                            echo "<tr><td colspan='10' class='text-center text-danger'>Error: " . mysqli_error($connection) . "</td></tr>";
                        } else if (mysqli_num_rows($result) == 0) {
                            echo "<tr><td colspan='10' class='text-center'>No users found. <a href='users.php?source=add_user'>Add your first user</a></td></tr>";
                        } else {
                            while($user = mysqli_fetch_assoc($result)) {
                                // Set badge color based on role level
                                $role_class = 'secondary';
                                if ($user['role_level'] >= 90) {
                                    $role_class = 'danger';
                                } elseif ($user['role_level'] >= 70) {
                                    $role_class = 'warning';
                                } elseif ($user['role_level'] >= 50) {
                                    $role_class = 'info';
                                }
                                $status_class = $user['user_status'] == 'active' ? 'success' : 'warning';
                                ?>
                                <tr data-user-id="<?php echo $user['user_id']; ?>">
                                    <td><?php echo $user['user_id']; ?></td>
                                    <td>
                                        <img src="../images/<?php echo $user['user_image'] ?: 'default.jpg'; ?>" 
                                             class="rounded-circle" width="40" height="40" alt="User">
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['user_email']); ?></td>
                                    <td>
                                        <?php if ($user['role_name']): ?>
                                            <span class="badge bg-<?php echo $role_class; ?>">
                                                <?php echo htmlspecialchars($user['role_name']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Not Assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['type_name']): ?>
                                            <span class="badge bg-success">
                                                <?php echo htmlspecialchars($user['type_name']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Not Assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-<?php echo $status_class; ?>"><?php echo $user['user_status']; ?></span></td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewUser(<?php echo $user['user_id']; ?>)" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php
                                        // Determine edit link based on type_name (case-insensitive)
                                        $edit_link = '';
                                        $type = isset($user['type_name']) ? strtolower(trim($user['type_name'])) : '';
                                        if ($type === 'client') {
                                            // Lookup client_id from clients table using user_id
                                            $client_id = null;
                                            $client_lookup = mysqli_query($connection, "SELECT client_id FROM clients WHERE user_id = " . intval($user['user_id']) . " LIMIT 1");
                                            if ($client_lookup && mysqli_num_rows($client_lookup) > 0) {
                                                $client_row = mysqli_fetch_assoc($client_lookup);
                                                $client_id = $client_row['client_id'];
                                            }
                                            if ($client_id) {
                                                $edit_link = 'clients.php?source=edit_client&id=' . $client_id;
                                            } else {
                                                $edit_link = '#'; // fallback if not found
                                            }
                                        } elseif ($type === 'operations') {
                                            // Lookup employee_id from employees table using user_id
                                            $employee_id = null;
                                            $employee_lookup = mysqli_query($connection, "SELECT employee_id FROM employees WHERE user_id = " . intval($user['user_id']) . " LIMIT 1");
                                            if ($employee_lookup && mysqli_num_rows($employee_lookup) > 0) {
                                                $employee_row = mysqli_fetch_assoc($employee_lookup);
                                                $employee_id = $employee_row['employee_id'];
                                            }
                                            if ($employee_id) {
                                                $edit_link = 'employees.php?source=edit_employee&id=' . $employee_id;
                                            } else {
                                                $edit_link = '#'; // fallback if not found
                                            }
                                        } elseif (in_array($type, ['partner', 'vendor', 'guest'])) {
                                            // For partner, vendor, or guest, always open edit_user.php
                                            $edit_link = 'users.php?source=edit_user&id=' . $user['user_id'];
                                        } else {
                                            // For any other user type, route through users.php switch
                                            $edit_link = 'users.php?source=edit_user&id=' . $user['user_id'];
                                        }
                                        ?>
                                        <?php if (!$is_manager): ?>
                                        <a href="<?php echo $edit_link; ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                            <?php if (!$is_manager): ?>
                                            <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>')" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <?php endif; ?>
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

<!-- User Details Modal with Dark Blue Theme -->
<div class="modal fade" id="userDetailsModal" tabindex="-1" aria-labelledby="userDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: #0a2240; color: #f1bf70; border-bottom: 1px solid #f1bf70;">
                <h5 class="modal-title" id="userDetailsModalLabel">
                    <i class="bi bi-person-badge me-2"></i>User Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="userDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading user details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <?php if (!$is_manager): ?>
                <a href="#" id="editUserBtn" class="btn btn-primary" style="background: #f1bf70; border-color: #f1bf70; color: #0a2240;">
                    <i class="bi bi-pencil me-1"></i>Edit User
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>