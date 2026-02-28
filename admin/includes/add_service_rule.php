<?php
ob_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header("Location: ../login.php");
    exit();
}

// Get service ID from URL
$service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;

if ($service_id <= 0) {
    $_SESSION['error_message'] = "Invalid service ID.";
    ob_end_clean();
    header("Location: services.php");
    exit();
}

// Fetch service details
$service_query = "SELECT * FROM service_types WHERE service_id = $service_id";
$service_result = mysqli_query($connection, $service_query);

if (!$service_result || mysqli_num_rows($service_result) == 0) {
    $_SESSION['error_message'] = "Service not found.";
    ob_end_clean();
    header("Location: services.php");
    exit();
}

$service = mysqli_fetch_assoc($service_result);

// Get next rule version
$version_query = "SELECT MAX(rule_version) as max_version FROM service_point_rules WHERE service_id = $service_id";
$version_result = mysqli_query($connection, $version_query);
$version_row = mysqli_fetch_assoc($version_result);
$next_version = ($version_row['max_version'] ?? 0) + 1;

// Initialize variables
$base_points = 100;
$penalty_type = 'linear';
$penalty_value = 25;
$penalty_unit = '10days';
$threshold_days = '';
$threshold_award = '';
$floor_points = 0;
$is_active = 1;
$message = '';
$message_type = '';
$showSuccessModal = false;
$new_rule_id = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_rule'])) {
    
    $base_points = (int)$_POST['base_points'];
    $penalty_type = mysqli_real_escape_string($connection, $_POST['penalty_type']);
    $penalty_value = !empty($_POST['penalty_value']) ? (int)$_POST['penalty_value'] : null;
    $penalty_unit = mysqli_real_escape_string($connection, $_POST['penalty_unit'] ?? 'day');
    $threshold_days = !empty($_POST['threshold_days']) ? (int)$_POST['threshold_days'] : null;
    $threshold_award = !empty($_POST['threshold_award']) ? (int)$_POST['threshold_award'] : null;
    $floor_points = (int)$_POST['floor_points'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $effective_date = mysqli_real_escape_string($connection, $_POST['effective_date']);
    $created_by = $_SESSION['user_id'];
    
    // Validation
    if ($base_points <= 0) {
        $message = "Base points must be greater than 0.";
        $message_type = "danger";
    } elseif (empty($effective_date)) {
        $message = "Effective date is required.";
        $message_type = "danger";
    } else {
        
        // Insert rule
        $insert_query = "INSERT INTO service_point_rules 
                        (service_id, rule_version, base_points, penalty_type, penalty_value, 
                         penalty_unit, threshold_days, threshold_award, floor_points, 
                         is_active, created_by, effective_date) 
                        VALUES ($service_id, $next_version, $base_points, '$penalty_type', 
                                " . ($penalty_value ? $penalty_value : 'NULL') . ", 
                                '$penalty_unit', 
                                " . ($threshold_days ? $threshold_days : 'NULL') . ", 
                                " . ($threshold_award ? $threshold_award : 'NULL') . ", 
                                $floor_points, $is_active, $created_by, '$effective_date')";
        
        if (mysqli_query($connection, $insert_query)) {
            $new_rule_id = mysqli_insert_id($connection);
            $showSuccessModal = true;
            
            // Clear form data
            $base_points = 100;
            $penalty_type = 'linear';
            $penalty_value = 25;
            $penalty_unit = '10days';
            $threshold_days = '';
            $threshold_award = '';
            $floor_points = 0;
            $is_active = 1;
            
            // Update next version
            $next_version++;
        } else {
            $message = "Error adding rule: " . mysqli_error($connection);
            $message_type = "danger";
        }
    }
}

ob_end_flush();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add Point Rule for: <?php echo htmlspecialchars($service['service_name']); ?></h5>
                    <a href="services.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Services
                    </a>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($message) && !$showSuccessModal): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Creating version <strong>v<?php echo $next_version; ?></strong> for this service.
                    </div>

                    <form method="POST" action="" id="ruleForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="base_points" class="form-label">Base Points *</label>
                                <input type="number" id="base_points" name="base_points" class="form-control" 
                                       value="<?php echo $base_points; ?>" min="1" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="floor_points" class="form-label">Floor Points</label>
                                <input type="number" id="floor_points" name="floor_points" class="form-control" 
                                       value="<?php echo $floor_points; ?>" min="0">
                                <div class="form-text">Minimum points that can be awarded</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="penalty_type" class="form-label">Penalty Type *</label>
                                <select id="penalty_type" name="penalty_type" class="form-control" required>
                                    <option value="linear" <?php echo ($penalty_type == 'linear') ? 'selected' : ''; ?>>Linear</option>
                                    <option value="threshold" <?php echo ($penalty_type == 'threshold') ? 'selected' : ''; ?>>Threshold</option>
                                    <option value="fixed" <?php echo ($penalty_type == 'fixed') ? 'selected' : ''; ?>>Fixed</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="penalty_value" class="form-label">Penalty Value</label>
                                <input type="number" id="penalty_value" name="penalty_value" class="form-control" 
                                       value="<?php echo $penalty_value; ?>">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="penalty_unit" class="form-label">Penalty Unit</label>
                                <select id="penalty_unit" name="penalty_unit" class="form-control">
                                    <option value="day" <?php echo ($penalty_unit == 'day') ? 'selected' : ''; ?>>Per Day</option>
                                    <option value="5days" <?php echo ($penalty_unit == '5days') ? 'selected' : ''; ?>>Per 5 Days</option>
                                    <option value="10days" <?php echo ($penalty_unit == '10days') ? 'selected' : ''; ?>>Per 10 Days</option>
                                </select>
                            </div>
                        </div>

                        <div class="row threshold-fields" style="<?php echo ($penalty_type != 'threshold') ? 'display: none;' : ''; ?>">
                            <div class="col-md-6 mb-3">
                                <label for="threshold_days" class="form-label">Threshold Days</label>
                                <input type="number" id="threshold_days" name="threshold_days" class="form-control" 
                                       value="<?php echo $threshold_days; ?>" min="1">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="threshold_award" class="form-label">Threshold Award Points</label>
                                <input type="number" id="threshold_award" name="threshold_award" class="form-control" 
                                       value="<?php echo $threshold_award; ?>" min="0">
                                <div class="form-text">Points awarded after threshold</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="effective_date" class="form-label">Effective Date *</label>
                                <input type="date" id="effective_date" name="effective_date" class="form-control" 
                                       value="<?php echo date('Y-m-d'); ?>" required>
                                <div class="form-text">Date from which this rule applies</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?php echo $is_active ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="submit_rule" class="btn btn-primary btn-lg me-2">
                                    <i class="bi bi-check-circle me-1"></i> Add Rule
                                </button>
                                <a href="services.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="bi bi-x-circle me-1"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($showSuccessModal && $new_rule_id): ?>
<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="successModalLabel">
          <i class="bi bi-check-circle-fill me-2"></i>Success!
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-center py-3">
          <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
          <h5 class="mt-3">Point Rule Added Successfully!</h5>
          <p class="text-muted mb-0">Version v<?php echo $next_version - 1; ?> for "<?php echo htmlspecialchars($service['service_name']); ?>" has been created.</p>
        </div>
      </div>
      <div class="modal-footer justify-content-center border-0 pt-0">
        <a href="services.php" class="btn btn-success px-4">
          <i class="bi bi-list-ul me-2"></i>View All Services
        </a>
        <a href="services.php?source=add_rule&service_id=<?php echo $service_id; ?>" class="btn btn-outline-success px-4">
          <i class="bi bi-plus-circle me-2"></i>Add Another Rule
        </a>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var successModal = new bootstrap.Modal(document.getElementById('successModal'), {
      backdrop: 'static',
      keyboard: false
    });
    successModal.show();
  });
</script>
<?php endif; ?>

<script>
document.getElementById('penalty_type')?.addEventListener('change', function() {
    const thresholdFields = document.querySelector('.threshold-fields');
    if (this.value === 'threshold') {
        thresholdFields.style.display = 'flex';
    } else {
        thresholdFields.style.display = 'none';
    }
});
</script>