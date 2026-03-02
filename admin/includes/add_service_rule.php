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

// Initialize variables based on service type defaults
$base_points = 100;
$points_within_deadline = 100;
$points_tier_1 = 75;      // 5-15 days (75% of base)
$points_tier_2 = 50;      // 16-25 days (50% of base)
$points_tier_3 = 25;      // >25 days (25% of base)

// Service-specific defaults based on your table
$service_defaults = [
    'Monthly Bookkeeping' => ['base' => 100, 't1' => 75, 't2' => 50, 't3' => 25],
    'Backlog accounting' => ['base' => 150, 't1' => 113, 't2' => 75, 't3' => 38],
    'Monthly Internal Audit' => ['base' => 100, 't1' => 75, 't2' => 50, 't3' => 25],
    'Quarterly Internal audit' => ['base' => 350, 't1' => 263, 't2' => 175, 't3' => 88],
    'External Audit' => ['base' => 30, 't1' => 23, 't2' => 15, 't3' => 8],
    'Monthly CFO services' => ['base' => 100, 't1' => 75, 't2' => 50, 't3' => 25],
    'VAT & CT Return Filing' => ['base' => 25, 't1' => 19, 't2' => 13, 't3' => 6],
    'VAT & CT Registration' => ['base' => 30, 't1' => 23, 't2' => 15, 't3' => 8],
    'VAT & CT De-registration' => ['base' => 40, 't1' => 30, 't2' => 20, 't3' => 10],
    'gAML Registration' => ['base' => 25, 't1' => 19, 't2' => 13, 't3' => 6],
    'gAMl report' => ['base' => 75, 't1' => 56, 't2' => 38, 't3' => 19],
    'Free Zone Company Setup' => ['base' => 110, 't1' => 83, 't2' => 55, 't3' => 28],
    'Mainland Company Setup' => ['base' => 80, 't1' => 60, 't2' => 40, 't3' => 20],
    'Bank account opening' => ['base' => 80, 't1' => 60, 't2' => 40, 't3' => 20],
    'PRO Services' => ['base' => 30, 't1' => 23, 't2' => 15, 't3' => 8]
];

// Check if this service has predefined defaults
if (isset($service_defaults[$service['service_name']])) {
    $default = $service_defaults[$service['service_name']];
    $base_points = $default['base'];
    $points_within_deadline = $default['base'];
    $points_tier_1 = $default['t1'];
    $points_tier_2 = $default['t2'];
    $points_tier_3 = $default['t3'];
}

$is_active = 1;
$message = '';
$message_type = '';
$showSuccessModal = false;
$new_rule_id = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_rule'])) {
    
    $base_points = (int)$_POST['base_points'];
    $points_within_deadline = (int)$_POST['points_within_deadline'];
    $points_tier_1 = (int)$_POST['points_tier_1'];
    $points_tier_2 = (int)$_POST['points_tier_2'];
    $points_tier_3 = (int)$_POST['points_tier_3'];
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
                        (service_id, rule_version, base_points, points_within_deadline,
                         points_tier_1, points_tier_2, points_tier_3,
                         is_active, created_by, effective_date) 
                        VALUES ($service_id, $next_version, $base_points, $points_within_deadline,
                                $points_tier_1, $points_tier_2, $points_tier_3,
                                $is_active, $created_by, '$effective_date')";
        
        if (mysqli_query($connection, $insert_query)) {
            $new_rule_id = mysqli_insert_id($connection);
            $showSuccessModal = true;
            
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
        <div class="col-md-10">
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
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="base_points" class="form-label">Base Points *</label>
                                <input type="number" id="base_points" name="base_points" class="form-control" 
                                       value="<?php echo $base_points; ?>" min="1" required>
                            </div>
                            <div class="col-md-8">
                                <small class="text-muted d-block mt-4">Base points are the maximum possible points for this service.</small>
                            </div>
                        </div>

                        <h6 class="border-bottom pb-2 mb-3">Points by Completion Time</h6>
                        
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="points_within_deadline" class="form-label">Within Deadline</label>
                                <input type="number" id="points_within_deadline" name="points_within_deadline" class="form-control" 
                                       value="<?php echo $points_within_deadline; ?>" min="0" required>
                                <small class="text-muted">0 days late</small>
                            </div>
                            <div class="col-md-3">
                                <label for="points_tier_1" class="form-label">5-15 Days Late</label>
                                <input type="number" id="points_tier_1" name="points_tier_1" class="form-control" 
                                       value="<?php echo $points_tier_1; ?>" min="0" required>
                                <small class="text-muted">Tier 1</small>
                            </div>
                            <div class="col-md-3">
                                <label for="points_tier_2" class="form-label">16-25 Days Late</label>
                                <input type="number" id="points_tier_2" name="points_tier_2" class="form-control" 
                                       value="<?php echo $points_tier_2; ?>" min="0" required>
                                <small class="text-muted">Tier 2</small>
                            </div>
                            <div class="col-md-3">
                                <label for="points_tier_3" class="form-label">>25 Days Late</label>
                                <input type="number" id="points_tier_3" name="points_tier_3" class="form-control" 
                                       value="<?php echo $points_tier_3; ?>" min="0" required>
                                <small class="text-muted">Tier 3</small>
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

                        <!-- Preview Section -->
                        <div class="card bg-light mt-4">
                            <div class="card-body">
                                <h6 class="mb-3">Points Preview</h6>
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="p-2 bg-white rounded">
                                            <strong>Within Deadline</strong>
                                            <div class="h4 mt-2"><?php echo $points_within_deadline; ?> pts</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-2 bg-white rounded">
                                            <strong>5-15 Days</strong>
                                            <div class="h4 mt-2"><?php echo $points_tier_1; ?> pts</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-2 bg-white rounded">
                                            <strong>16-25 Days</strong>
                                            <div class="h4 mt-2"><?php echo $points_tier_2; ?> pts</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-2 bg-white rounded">
                                            <strong>>25 Days</strong>
                                            <div class="h4 mt-2"><?php echo $points_tier_3; ?> pts</div>
                                        </div>
                                    </div>
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
// Live preview update
document.getElementById('base_points').addEventListener('input', updatePreview);
document.getElementById('points_within_deadline').addEventListener('input', updatePreview);
document.getElementById('points_tier_1').addEventListener('input', updatePreview);
document.getElementById('points_tier_2').addEventListener('input', updatePreview);
document.getElementById('points_tier_3').addEventListener('input', updatePreview);

function updatePreview() {
    // Preview is updated automatically as fields change
    // The values will be submitted with the form
}
</script>