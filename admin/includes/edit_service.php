<?php
ob_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header("Location: ../login.php");
    exit();
}

// Get service ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid service ID.";
    ob_end_clean();
    header("Location: services.php");
    exit();
}

$service_id = (int)$_GET['id'];
$message = '';
$message_type = '';
$showSuccessModal = false;

// Fetch service data
$query = "SELECT * FROM service_types WHERE service_id = $service_id";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['error_message'] = "Service not found.";
    ob_end_clean();
    header("Location: services.php");
    exit();
}

$service = mysqli_fetch_assoc($result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_service'])) {
    
    $service_name = mysqli_real_escape_string($connection, trim($_POST['service_name']));
    $service_category = mysqli_real_escape_string($connection, trim($_POST['service_category']));
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    if (empty($service_name) || empty($service_category)) {
        $message = "Please fill in all required fields.";
        $message_type = "danger";
    } else {
        
        // Check if service name already exists (excluding current)
        $check_query = "SELECT service_id FROM service_types WHERE service_name = '$service_name' AND service_id != $service_id";
        $check_result = mysqli_query($connection, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $message = "Service name already exists. Please use a different name.";
            $message_type = "danger";
        } else {
            
            // Update service
            $update_query = "UPDATE service_types SET 
                             service_name = '$service_name',
                             service_category = '$service_category',
                             is_active = $is_active
                             WHERE service_id = $service_id";
            
            if (mysqli_query($connection, $update_query)) {
                $showSuccessModal = true;
                
                // Refresh service data
                $refresh_query = "SELECT * FROM service_types WHERE service_id = $service_id";
                $refresh_result = mysqli_query($connection, $refresh_query);
                $service = mysqli_fetch_assoc($refresh_result);
                
                // Clear message
                $message = '';
                $message_type = '';
            } else {
                $message = "Error updating service: " . mysqli_error($connection);
                $message_type = "danger";
            }
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
                    <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Service</h5>
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

                    <form method="POST" action="" id="serviceForm">
                        <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
                        
                        <div class="mb-3">
                            <label for="service_name" class="form-label">Service Name *</label>
                            <input type="text" id="service_name" name="service_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($service['service_name']); ?>" 
                                   placeholder="e.g., Monthly Bookkeeping, VAT Return Filing" required>
                        </div>

                        <div class="mb-3">
                            <label for="service_category" class="form-label">Service Category *</label>
                            <select id="service_category" name="service_category" class="form-control" required>
                                <option value="">Select Category</option>
                                <option value="bookkeeping" <?php echo ($service['service_category'] == 'bookkeeping') ? 'selected' : ''; ?>>Bookkeeping</option>
                                <option value="audit" <?php echo ($service['service_category'] == 'audit') ? 'selected' : ''; ?>>Audit</option>
                                <option value="tax" <?php echo ($service['service_category'] == 'tax') ? 'selected' : ''; ?>>Tax</option>
                                <option value="registration" <?php echo ($service['service_category'] == 'registration') ? 'selected' : ''; ?>>Registration</option>
                                <option value="setup" <?php echo ($service['service_category'] == 'setup') ? 'selected' : ''; ?>>Setup</option>
                                <option value="other" <?php echo ($service['service_category'] == 'other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?php echo $service['is_active'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                            <div class="form-text">Inactive services cannot be selected for new engagements</div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="update_service" class="btn btn-primary btn-lg me-2">
                                    <i class="bi bi-check-circle me-1"></i> Update Service
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
          <h5 class="mt-3">Service Updated Successfully!</h5>
          <p class="text-muted mb-0">The service "<?php echo htmlspecialchars($service['service_name']); ?>" has been updated.</p>
        </div>
      </div>
      <div class="modal-footer justify-content-center border-0 pt-0">
        <a href="services.php" class="btn btn-success px-4">
          <i class="bi bi-list-ul me-2"></i>View All Services
        </a>
        <a href="services.php?source=add_rule&service_id=<?php echo $service_id; ?>" class="btn btn-outline-primary px-4">
          <i class="bi bi-gear"></i>Manage Point Rules
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