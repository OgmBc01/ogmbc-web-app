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

// Define category colors for consistent theming
$category_colors = [
    'bookkeeping' => 'primary',
    'audit' => 'success',
    'tax' => 'warning',
    'registration' => 'info',
    'setup' => 'secondary',
    'other' => 'dark'
];
?>

<!-- Add custom CSS for tooltips and badges -->
<style>
.category-badge {
    font-size: 0.8rem;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-weight: 500;
}
.points-badge {
    font-size: 0.9rem;
    padding: 0.3rem 0.6rem;
    border-radius: 12px;
    background: #eaf5ff;
    color: #2c3e50;
    border-left: 3px solid;
}
.points-badge.within-deadline { border-left-color: #28a745; }
.points-badge.tier-1 { border-left-color: #ffc107; }
.points-badge.tier-2 { border-left-color: #fd7e14; }
.points-badge.tier-3 { border-left-color: #dc3545; }
    
/* Tooltip styles */
[data-tooltip] {
    position: relative;
    cursor: help;
}
[data-tooltip]:before {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    padding: 5px 10px;
    background: #2c3e50;
    color: white;
    font-size: 12px;
    white-space: nowrap;
    border-radius: 4px;
    display: none;
    z-index: 1000;
}
[data-tooltip]:hover:before {
    display: block;
}
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">
            <i class="bi bi-gear-wide-connected me-2"></i>Services Configuration
        </h1>
        <div>
            <a href="services.php?source=add_service" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Service
            </a>
        </div>
    </div>

    <!-- Statistics Cards with Icons -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-grid-3x3-gap-fill fs-1 me-3"></i>
                    <div>
                        <h6 class="card-title mb-1">Active Services</h6>
                        <h2 class="mb-0"><?php echo $total_services; ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-trophy-fill fs-1 me-3"></i>
                    <div>
                        <h6 class="card-title mb-1">Active Point Rules</h6>
                        <h2 class="mb-0"><?php echo $total_rules; ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-tags-fill fs-1 me-3"></i>
                    <div>
                        <h6 class="card-title mb-1">Service Categories</h6>
                        <h2 class="mb-0"><?php echo mysqli_num_rows($categories_result); ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Table with Rules -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white d-flex align-items-center">
            <i class="bi bi-grid me-2"></i>
            <h5 class="mb-0">Services & Point Rules</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Service Name</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Rules</th>
                            <th>Latest</th>
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
                            echo "<tr><td colspan='8' class='text-center py-5'>";
                            echo "<i class='bi bi-inbox fs-1 d-block text-muted mb-3'></i>";
                            echo "<h5>No services found</h5>";
                            echo "<p class='text-muted'>Get started by adding your first service.</p>";
                            echo "<a href='services.php?source=add_service' class='btn btn-primary mt-2'>";
                            echo "<i class='bi bi-plus-circle me-2'></i>Add New Service</a>";
                            echo "</td></tr>";
                        } else {
                            while($service = mysqli_fetch_assoc($result)):
                                $category_color = $category_colors[$service['service_category']] ?? 'secondary';
                                $status_class = $service['is_active'] ? 'success' : 'secondary';
                                $status_text = $service['is_active'] ? 'Active' : 'Inactive';
                                $rule_count = $service['rule_count'];
                                ?>
                                <tr id="service-row-<?php echo $service['service_id']; ?>" class="service-row">
                                    <td><span class="fw-bold text-muted">#<?php echo $service['service_id']; ?></span></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($service['service_name']); ?></strong>
                                        <?php if ($service['latest_version']): ?>
                                            <span class="badge bg-dark ms-2" data-tooltip="Latest version">v<?php echo $service['latest_version']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="category-badge badge bg-<?php echo $category_color; ?>">
                                            <i class="bi bi-tag me-1"></i>
                                            <?php echo ucfirst(str_replace('_', ' ', $service['service_category'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $status_class; ?> px-3 py-2">
                                            <i class="bi bi-<?php echo $service['is_active'] ? 'check-circle' : 'slash-circle'; ?> me-1"></i>
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $rule_count > 0 ? 'primary' : 'secondary'; ?> rounded-pill px-3">
                                            <i class="bi bi-<?php echo $rule_count > 0 ? 'list-check' : 'dash'; ?> me-1"></i>
                                            <?php echo $rule_count; ?> rule<?php echo $rule_count != 1 ? 's' : ''; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($service['latest_version']): ?>
                                            <span class="badge bg-dark">v<?php echo $service['latest_version']; ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="text-muted small" data-tooltip="<?php echo date('M d, Y H:i', strtotime($service['created_at'])); ?>">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            <?php echo date('M d, Y', strtotime($service['created_at'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-outline-info" onclick="viewService(<?php echo $service['service_id']; ?>)" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <a href="services.php?source=edit_service&id=<?php echo $service['service_id']; ?>" class="btn btn-outline-warning" title="Edit Service">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="services.php?source=add_rule&service_id=<?php echo $service['service_id']; ?>" class="btn btn-outline-success" title="Add Rule">
                                                <i class="bi bi-plus-circle"></i>
                                            </a>
                                            <button class="btn btn-outline-danger" onclick="confirmDelete(<?php echo $service['service_id']; ?>, '<?php echo htmlspecialchars($service['service_name'], ENT_QUOTES); ?>', 'service')" title="Delete Service" <?php echo ($rule_count > 0) ? 'disabled' : ''; ?>>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Rules for this service (collapsible) -->
                                <?php
                                $rules_query = "SELECT * FROM service_point_rules WHERE service_id = " . $service['service_id'] . " ORDER BY rule_version DESC";
                                $rules_result = mysqli_query($connection, $rules_query);
                                if (mysqli_num_rows($rules_result) > 0):
                                ?>
                                <tr class="rules-row">
                                    <td colspan="8" class="p-0 bg-light">
                                        <div class="p-3">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="bi bi-list-nested me-2 text-primary"></i>
                                                <h6 class="mb-0">Point Rules for <?php echo htmlspecialchars($service['service_name']); ?></h6>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered bg-white mb-0">
                                                    <thead class="table-secondary">
                                                        <tr class="text-center">
                                                            <th>Version</th>
                                                            <th data-tooltip="Base points before any penalties">Base</th>
                                                            <th data-tooltip="Points awarded when submitted within deadline">
                                                                <i class="bi bi-check-circle-fill text-success me-1"></i>Within Deadline
                                                            </th>
                                                            <th data-tooltip="Points awarded for 5-15 days delay (Tier 1)">
                                                                <i class="bi bi-clock-history text-warning me-1"></i>5-15 Days
                                                            </th>
                                                            <th data-tooltip="Points awarded for 16-25 days delay (Tier 2)">
                                                                <i class="bi bi-hourglass-split text-orange me-1"></i>16-25 Days
                                                            </th>
                                                            <th data-tooltip="Points awarded for more than 25 days delay (Tier 3)">
                                                                <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i>>25 Days
                                                            </th>
                                                            <th>Status</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php while($rule = mysqli_fetch_assoc($rules_result)): ?>
                                                        <tr class="text-center align-middle">
                                                            <td><span class="badge bg-dark">v<?php echo $rule['rule_version']; ?></span></td>
                                                            <td><span class="fw-bold"><?php echo $rule['base_points']; ?></span></td>
                                                            <td>
                                                                <span class="points-badge within-deadline">
                                                                    <?php echo $rule['points_within_deadline']; ?> pts
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="points-badge tier-1">
                                                                    <?php echo $rule['points_tier_1']; ?> pts
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="points-badge tier-2">
                                                                    <?php echo $rule['points_tier_2']; ?> pts
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="points-badge tier-3">
                                                                    <?php echo $rule['points_tier_3']; ?> pts
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-<?php echo $rule['is_active'] ? 'success' : 'secondary'; ?> px-3">
                                                                    <i class="bi bi-<?php echo $rule['is_active'] ? 'check' : 'x'; ?> me-1"></i>
                                                                    <?php echo $rule['is_active'] ? 'Active' : 'Inactive'; ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group btn-group-sm">
                                                                    <a href="services.php?source=edit_rule&id=<?php echo $rule['rule_id']; ?>" class="btn btn-outline-warning" title="Edit Rule">
                                                                        <i class="bi bi-pencil"></i>
                                                                    </a>
                                                                    <button class="btn btn-outline-danger" onclick="confirmDelete(<?php echo $rule['rule_id']; ?>, 'Rule v<?php echo $rule['rule_version']; ?>', 'rule')" title="Delete Rule">
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <?php endwhile; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            <?php endwhile;
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>