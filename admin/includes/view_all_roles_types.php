<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">User Roles & Types Management</h1>
    </div>

    <!-- User Roles Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>User Roles</h5>
                    <a href="user_roles.php?source=add_role" class="btn btn-light btn-sm">
                        <i class="bi bi-plus-circle"></i> Add New Role
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Role Name</th>
                                    <th>Description</th>
                                    <th>Level</th>
                                    <th>Users Count</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php  
                                $roles_query = "SELECT r.*, COUNT(u.user_id) as user_count 
                                                FROM user_roles r
                                                LEFT JOIN users u ON r.role_id = u.role_id
                                                GROUP BY r.role_id
                                                ORDER BY r.role_level DESC";
                                $roles_result = mysqli_query($connection, $roles_query);
                                
                                if (!$roles_result) {
                                    echo "<tr><td colspan='7' class='text-center text-danger'>Error: " . mysqli_error($connection) . "</td></tr>";
                                } else if (mysqli_num_rows($roles_result) == 0) {
                                    echo "<tr><td colspan='7' class='text-center'>No roles found. <a href='user_roles.php?source=add_role'>Add your first role</a></td></tr>";
                                } else {
                                    while($role = mysqli_fetch_assoc($roles_result)) {
                                        ?>
                                        <tr>
                                            <td><?php echo $role['role_id']; ?></td>
                                            <td><span class="badge bg-info"><?php echo htmlspecialchars($role['role_name']); ?></span></td>
                                            <td><?php echo htmlspecialchars($role['role_description'] ?: 'No description'); ?></td>
                                            <td><span class="badge bg-secondary"><?php echo $role['role_level']; ?></span></td>
                                            <td><span class="badge bg-primary"><?php echo $role['user_count']; ?></span></td>
                                            <td><?php echo date('M d, Y', strtotime($role['created_at'])); ?></td>
                                            <td>
                                                <a href="user_roles.php?source=edit_role&id=<?php echo $role['role_id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button class="btn btn-sm btn-danger" onclick="confirmDeleteRole(<?php echo $role['role_id']; ?>, '<?php echo htmlspecialchars($role['role_name'], ENT_QUOTES); ?>')" title="Delete" <?php echo ($role['user_count'] > 0) ? 'disabled' : ''; ?>>
                                                    <i class="bi bi-trash"></i>
                                                </button>
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
    </div>

    <!-- User Types Section -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-tags me-2"></i>User Types</h5>
                    <a href="user_roles.php?source=add_type" class="btn btn-light btn-sm">
                        <i class="bi bi-plus-circle"></i> Add New Type
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Type Name</th>
                                    <th>Description</th>
                                    <th>Users Count</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php  
                                $types_query = "SELECT t.*, COUNT(u.user_id) as user_count 
                                                FROM user_types t
                                                LEFT JOIN users u ON t.type_id = u.type_id
                                                GROUP BY t.type_id
                                                ORDER BY t.type_name";
                                $types_result = mysqli_query($connection, $types_query);
                                
                                if (!$types_result) {
                                    echo "<tr><td colspan='6' class='text-center text-danger'>Error: " . mysqli_error($connection) . "</td></tr>";
                                } else if (mysqli_num_rows($types_result) == 0) {
                                    echo "<tr><td colspan='6' class='text-center'>No types found. <a href='user_roles.php?source=add_type'>Add your first type</a></td></tr>";
                                } else {
                                    while($type = mysqli_fetch_assoc($types_result)) {
                                        ?>
                                        <tr>
                                            <td><?php echo $type['type_id']; ?></td>
                                            <td><span class="badge bg-success"><?php echo htmlspecialchars($type['type_name']); ?></span></td>
                                            <td><?php echo htmlspecialchars($type['type_description'] ?: 'No description'); ?></td>
                                            <td><span class="badge bg-primary"><?php echo $type['user_count']; ?></span></td>
                                            <td><?php echo date('M d, Y', strtotime($type['created_at'])); ?></td>
                                            <td>
                                                <a href="user_roles.php?source=edit_type&id=<?php echo $type['type_id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button class="btn btn-sm btn-danger" onclick="confirmDeleteType(<?php echo $type['type_id']; ?>, '<?php echo htmlspecialchars($type['type_name'], ENT_QUOTES); ?>')" title="Delete" <?php echo ($type['user_count'] > 0) ? 'disabled' : ''; ?>>
                                                    <i class="bi bi-trash"></i>
                                                </button>
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
    </div>
</div>