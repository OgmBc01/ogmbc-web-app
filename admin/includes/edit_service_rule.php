<?php
ob_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header("Location: ../login.php");
    exit();
}

// Get rule ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid rule ID.";
    ob_end_clean();
    header("Location: services.php");
    exit();
}

$rule_id = (int)$_GET['id'];
$message = '';
$message_type = '';
$showSuccessModal = false;

// Fetch rule data with service info
$query = "SELECT r.*, s.service_name 
          FROM service_point_rules r
          JOIN service_types s ON r.service_id = s.service_id
          WHERE r.rule_id = $rule_id";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['error_message'] = "Rule not found.";
    ob_end_clean();
    header("Location: services.php");
    exit();
}

$rule = mysqli_fetch_assoc($result);
$service_id = $rule['service_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_rule'])) {
    
    $base_points = (int)$_POST['base_points'];
    $penalty_type = mysqli_real_escape_string($connection, $_POST['penalty_type']);
    $penalty_value = !empty($_POST['penalty_value']) ? (int)$_POST['penalty_value'] : null;
    $penalty_unit = mysqli_real_escape_string($connection, $_POST['penalty_unit'] ?? 'day');
    $threshold_days = !empty($_POST['threshold_days']) ? (int)$_POST['threshold_days'] : null;
    $threshold_award = !empty($_POST['threshold_award']) ? (int)$_POST['threshold_award'] : null;
    $floor_points = (int)$_POST['floor_points'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $effective_date = mysqli_real_escape_string($connection, $_POST['effective_date']);
    
    // Validation
    if ($base_points <= 0) {
        $message = "Base points must be greater than 0.";
        $message_type = "danger";
    } elseif (empty($effective_date)) {
        $message = "Effective date is required.";
        $message_type = "danger";
    } else {
        
        // Update rule
        $update_query = "UPDATE service_point_rules SET 
                         base_points = $base_points,
                         penalty_type = '$penalty_type',
                         penalty_value = " . ($penalty_value ? $penalty_value : 'NULL') . ",
                         penalty_unit = '$penalty_unit',
                         threshold_days = " . ($threshold_days ? $threshold_days : 'NULL') . ",
                         threshold_award = " . ($threshold_award ? $threshold_award : 'NULL') . ",
                         floor_points = $floor_points,
                         is_active = $is_active,
                         effective_date = '$effective_date'
                         WHERE rule_id = $rule_id";
        
        if (mysqli_query($connection, $update_query)) {
            $showSuccessModal = true;
            
            // Refresh rule data
            $refresh_query = "SELECT r.*, s.service_name 
                              FROM service_point_rules r
                              JOIN service_types s ON r.service_id = s.service_id
                              WHERE r.rule_id = $rule_id";
            $refresh_result = mysqli_query($connection, $refresh_query);
            $rule = mysqli_fetch_assoc($refresh_result);
            
            // Clear message
            $message = '';
            $message_type = '';
        } else {
            $message = "Error updating rule: " . mysqli_error($connection);
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
                    <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Point Rule for: <?php echo htmlspecialchars($rule['service_name']); ?> (v<?php echo $rule['rule_version']; ?>)</h5>
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

                    <form method="POST" action="" id="ruleForm">
                        <input type="hidden" name="rule_id" value="<?php echo $rule_id; ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="base_points" class="form-label">Base Points *</label>
                                <input type="number" id="base_points" name="base_points" class="form-control" 
                                       value="<?php echo $rule['base_points']; ?>" min="1" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="floor_points" class="form-label">Floor Points</label>
                                <input type="number" id="floor_points" name="floor_points" class="form-control" 
                                       value="<?php echo $rule['floor_points']; ?>" min="0">
                                <div class="form-text">Minimum points that can be awarded</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="penalty_type" class="form-label">Penalty Type *</label>
                                <select id="penalty_type" name="penalty_type" class="form-control" required>
                                    <option value="linear" <?php echo ($rule['penalty_type'] == 'linear') ? 'selected' : ''; ?>>Linear</option>
                                    <option value="threshold" <?php echo ($rule['penalty_type'] == 'threshold') ? 'selected' : ''; ?>>Threshold</option>
                                    <option value="fixed" <?php echo ($rule['penalty_type'] == 'fixed') ? 'selected' : ''; ?>>Fixed</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="penalty_value" class="form-label">Penalty Value</label>
                                <input type="number" id="penalty_value" name="penalty_value" class="form-control" 
                                       value="<?php echo $rule['penalty_value']; ?>">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="penalty_unit" class="form-label">Penalty Unit</label>
                                <select id="penalty_unit" name="penalty_unit" class="form-control">
                                    <option value="day" <?php echo ($rule['penalty_unit'] == 'day') ? 'selected' : ''; ?>>Per Day</option>
                                    <option value="5days" <?php echo ($rule['penalty_unit'] == '5days') ? 'selected' : ''; ?>>Per 5 Days</option>
                                    <option value="10days" <?php echo ($rule['penalty_unit'] == '10days') ? 'selected' : ''; ?>>Per 10 Days</option>
                                </select>
                            </div>
                        </div>

                        <div class="row threshold-fields" style="<?php echo ($rule['penalty_type'] != 'threshold') ? 'display: none;' : ''; ?>">
                            <div class="col-md-6 mb-3">
                                <label for="threshold_days" class="form-label">Threshold Days</label>
                                <input type="number" id="threshold_days" name="threshold_days" class="form-control" 
                                       value="<?php echo $rule['threshold_days']; ?>" min="1">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="threshold_award" class="form-label">Threshold Award Points</label>
                                <input type="number" id="threshold_award" name="threshold_award" class="form-control" 
                                       value="<?php echo $rule['threshold_award']; ?>" min="0">
                                <div class="form-text">Points awarded after threshold</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="effective_date" class="form-label">Effective Date *</label>
                                <input type="date" id="effective_date" name="effective_date" class="form-control" 
                                       value="<?php echo $rule['effective_date']; ?>" required>
                                <div class="form-text">Date from which this rule applies</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?php echo $rule['is_active'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="update_rule" class="btn btn-primary btn-lg me-2">
                                    <i class="bi bi-check-circle me-1"></i> Update Rule
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

<?php if ($showSuccessModal): ?>
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
          <h5 class="mt-3">Point Rule Updated Successfully!</h5>
          <p class="text-muted mb-0">Version v<?php echo $rule['rule_version']; ?> has been updated.</p>
        </div>
      </div>
      <div class="modal-footer justify-content-center border-0 pt-0">
        <a href="services.php" class="btn btn-success px-4">
          <i class="bi bi-list-ul me-2"></i>View All Services
        </a>
        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
          <i class="bi bi-pencil me-2"></i>Continue Editing
        </button>
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