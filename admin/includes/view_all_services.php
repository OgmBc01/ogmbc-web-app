<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get statistics
$total_services_query = "SELECT COUNT(*) as total FROM service_types WHERE is_active = 1";
$total_services_result = mysqli_query($connection, $total_services_query);
$total_services = mysqli_fetch_assoc($total_services_result)['total'];

$total_rules_query = "SELECT COUNT(*) as total FROM service_point_rules WHERE is_active = 1";
$total_rules_result = mysqli_query($connection, $total_rules_query);
$total_rules = mysqli_fetch_assoc($total_rules_result)['total'];

$categories_query = "SELECT DISTINCT service_category, COUNT(*) as count FROM service_types GROUP BY service_category";
$categories_result = mysqli_query($connection, $categories_query);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">Services Configuration</h1>
        <div>
            <a href="services.php?source=add_service" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Service
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Active Services</h5>
                    <h2><?php echo $total_services; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Active Point Rules</h5>
                    <h2><?php echo $total_rules; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Service Categories</h5>
                    <h2><?php echo mysqli_num_rows($categories_result); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Table with Rules -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-grid me-2"></i>Services & Point Rules</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr class="table-dark">
                            <th>ID</th>
                            <th>Service Name</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Active Rules</th>
                            <th>Latest Version</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php  
                        $query = "SELECT s.*, 
                                 COUNT(r.rule_id) as rule_count,
                                 MAX(r.rule_version) as latest_version
                                 FROM service_types s
                                 LEFT JOIN service_point_rules r ON s.service_id = r.service_id
                                 GROUP BY s.service_id
                                 ORDER BY s.service_name";
                        
                        $result = mysqli_query($connection, $query);
                        
                        if (!$result) {
                            echo "<tr><td colspan='8' class='text-center text-danger'>Error: " . mysqli_error($connection) . "</td></tr>";
                        } else if (mysqli_num_rows($result) == 0) {
                            echo "<tr><td colspan='8' class='text-center'>No services found. <a href='services.php?source=add_service'>Add your first service</a></td></tr>";
                        } else {
                            while($service = mysqli_fetch_assoc($result)) {
                                $status_class = $service['is_active'] ? 'success' : 'secondary';
                                $status_text = $service['is_active'] ? 'Active' : 'Inactive';
                                ?>
                                <tr id="service-row-<?php echo $service['service_id']; ?>">
                                    <td><?php echo $service['service_id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($service['service_name']); ?></strong></td>
                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($service['service_category']); ?></span></td>
                                    <td><span class="badge bg-<?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                                    <td><span class="badge bg-primary"><?php echo $service['rule_count']; ?></span></td>
                                    <td><?php echo $service['latest_version'] ? 'v' . $service['latest_version'] : 'No rules'; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($service['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewService(<?php echo $service['service_id']; ?>)" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="services.php?source=edit_service&id=<?php echo $service['service_id']; ?>" class="btn btn-sm btn-warning" title="Edit Service">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="services.php?source=add_rule&service_id=<?php echo $service['service_id']; ?>" class="btn btn-sm btn-success" title="Add Rule">
                                            <i class="bi bi-plus-circle"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $service['service_id']; ?>, '<?php echo htmlspecialchars($service['service_name'], ENT_QUOTES); ?>', 'service')" title="Delete Service" <?php echo ($service['rule_count'] > 0) ? 'disabled' : ''; ?>>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Display rules for this service -->
                                <?php
                                $rules_query = "SELECT * FROM service_point_rules WHERE service_id = " . $service['service_id'] . " ORDER BY rule_version DESC";
                                $rules_result = mysqli_query($connection, $rules_query);
                                if (mysqli_num_rows($rules_result) > 0):
                                ?>
                                <tr class="table-light">
                                    <td colspan="8" class="p-0">
                                        <div class="p-2 bg-light">
                                            <table class="table table-sm table-borderless mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Version</th>
                                                        <th>Base Points</th>
                                                        <th>Penalty Type</th>
                                                        <th>Penalty Value</th>
                                                        <th>Penalty Unit</th>
                                                        <th>Threshold</th>
                                                        <th>Floor</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while($rule = mysqli_fetch_assoc($rules_result)): ?>
                                                    <tr id="rule-row-<?php echo $rule['rule_id']; ?>">
                                                        <td>v<?php echo $rule['rule_version']; ?></td>
                                                        <td><?php echo $rule['base_points']; ?></td>
                                                        <td><?php echo ucfirst($rule['penalty_type']); ?></td>
                                                        <td><?php echo $rule['penalty_value'] ?? '-'; ?></td>
                                                        <td><?php echo $rule['penalty_unit'] ?? '-'; ?></td>
                                                        <td><?php echo $rule['threshold_days'] ? $rule['threshold_days'] . ' days' : '-'; ?></td>
                                                        <td><?php echo $rule['floor_points']; ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $rule['is_active'] ? 'success' : 'secondary'; ?>">
                                                                <?php echo $rule['is_active'] ? 'Active' : 'Inactive'; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <a href="services.php?source=edit_rule&id=<?php echo $rule['rule_id']; ?>" class="btn btn-sm btn-warning" title="Edit Rule">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $rule['rule_id']; ?>, 'Rule v<?php echo $rule['rule_version']; ?>', 'rule')" title="Delete Rule">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
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