<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

set_exception_handler(function($e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
});

set_error_handler(function($errno, $errstr) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $errstr]);
    exit;
});

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Fatal error: ' . $error['message']]);
        exit;
    }
});

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../includes/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid service ID']);
    exit;
}

$service_id = (int)$_GET['id'];

// Get service details
$service_query = "SELECT s.*, 
                  CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                  FROM service_types s
                  LEFT JOIN users u ON s.created_by = u.user_id
                  WHERE s.service_id = $service_id";
$service_result = mysqli_query($connection, $service_query);

if ($service_result && mysqli_num_rows($service_result) > 0) {
    $service = mysqli_fetch_assoc($service_result);
    
    // Get rules for this service
    $rules_query = "SELECT * FROM service_point_rules WHERE service_id = $service_id ORDER BY rule_version DESC";
    $rules_result = mysqli_query($connection, $rules_query);
    
    $rules = [];
    while ($rule = mysqli_fetch_assoc($rules_result)) {
        $rules[] = $rule;
    }
    
    // Clean up null values
    $service = array_map(function($value) {
        return $value === null ? '' : $value;
    }, $service);
    
    // Category colors mapping
    $category_colors = [
        'bookkeeping' => 'primary',
        'audit' => 'success',
        'tax' => 'warning',
        'registration' => 'info',
        'setup' => 'secondary',
        'other' => 'dark'
    ];
    
    $color = $category_colors[$service['service_category']] ?? 'secondary';
    
    // Build HTML response
    ob_start();
    ?>
    <div class="container-fluid">
        <!-- Header with status -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1"><?php echo htmlspecialchars($service['service_name']); ?></h4>
                <div>
                    <span class="badge bg-<?php echo $color; ?> me-2">
                        <i class="bi bi-tag me-1"></i><?php echo ucfirst(str_replace('_', ' ', $service['service_category'])); ?>
                    </span>
                    <span class="badge bg-<?php echo $service['is_active'] ? 'success' : 'secondary'; ?>">
                        <i class="bi bi-<?php echo $service['is_active'] ? 'check-circle' : 'slash-circle'; ?> me-1"></i>
                        <?php echo $service['is_active'] ? 'Active' : 'Inactive'; ?>
                    </span>
                </div>
            </div>
            <div class="text-muted">
                <small>
                    <i class="bi bi-calendar3 me-1"></i>Created: <?php echo date('M d, Y', strtotime($service['created_at'])); ?>
                    <?php if (!empty($service['created_by_name'])): ?>
                        <br><i class="bi bi-person me-1"></i>By: <?php echo htmlspecialchars($service['created_by_name']); ?>
                    <?php endif; ?>
                </small>
            </div>
        </div>

        <?php if (!empty($rules)): ?>
            <!-- Rules Table -->
            <h6 class="mb-3">
                <i class="bi bi-list-check me-2"></i>Point Rules (<?php echo count($rules); ?> versions)
            </h6>
            
            <?php foreach($rules as $index => $rule): ?>
                <div class="card mb-3 <?php echo $index === 0 ? 'border-primary' : ''; ?>">
                    <div class="card-header <?php echo $index === 0 ? 'bg-primary text-white' : 'bg-light'; ?> d-flex justify-content-between align-items-center">
                        <div>
                            <strong>
                                <i class="bi bi-tag me-2"></i>Version <?php echo $rule['rule_version']; ?>
                                <?php if ($index === 0): ?>
                                    <span class="badge bg-warning text-dark ms-2">Latest</span>
                                <?php endif; ?>
                            </strong>
                        </div>
                        <div>
                            <?php if ($rule['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                            <small class="ms-2 <?php echo $index === 0 ? 'text-white-50' : 'text-muted'; ?>">
                                <i class="bi bi-calendar"></i> Effective: <?php echo date('M d, Y', strtotime($rule['effective_date'])); ?>
                            </small>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Points Summary Cards -->
                            <div class="col-md-3 mb-2">
                                <div class="card bg-success bg-opacity-10 border-success">
                                    <div class="card-body p-3">
                                        <h6 class="card-title text-success mb-2">
                                            <i class="bi bi-check-circle-fill me-1"></i>Within Deadline
                                        </h6>
                                        <h3 class="mb-0"><?php echo $rule['points_within_deadline']; ?></h3>
                                        <small class="text-muted">points</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="card bg-warning bg-opacity-10 border-warning">
                                    <div class="card-body p-3">
                                        <h6 class="card-title text-warning mb-2">
                                            <i class="bi bi-clock-history me-1"></i>5-15 Days
                                        </h6>
                                        <h3 class="mb-0"><?php echo $rule['points_tier_1']; ?></h3>
                                        <small class="text-muted">points</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="card bg-orange bg-opacity-10 border-orange" style="border-color: #fd7e14;">
                                    <div class="card-body p-3">
                                        <h6 class="card-title text-orange mb-2" style="color: #fd7e14;">
                                            <i class="bi bi-hourglass-split me-1"></i>16-25 Days
                                        </h6>
                                        <h3 class="mb-0"><?php echo $rule['points_tier_2']; ?></h3>
                                        <small class="text-muted">points</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="card bg-danger bg-opacity-10 border-danger">
                                    <div class="card-body p-3">
                                        <h6 class="card-title text-danger mb-2">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>>25 Days
                                        </h6>
                                        <h3 class="mb-0"><?php echo $rule['points_tier_3']; ?></h3>
                                        <small class="text-muted">points</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Base Points Info -->
                        <div class="mt-2 text-muted small">
                            <i class="bi bi-info-circle me-1"></i>
                            Base points: <?php echo $rule['base_points']; ?> | 
                            Minimum award: <?php echo min($rule['points_tier_3'], $rule['points_within_deadline']); ?> points
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                No point rules defined for this service yet. 
                <a href="services.php?source=add_rule&service_id=<?php echo $service_id; ?>" class="alert-link">Add your first rule</a>.
            </div>
        <?php endif; ?>
    </div>
    <?php
    $html = ob_get_clean();
    
    echo json_encode([
        'success' => true,
        'service' => $service,
        'rules' => $rules,
        'html' => $html
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Service not found'
    ]);
}

// Free result sets
if ($service_result) mysqli_free_result($service_result);
if (isset($rules_result)) mysqli_free_result($rules_result);

ob_end_flush();
?>