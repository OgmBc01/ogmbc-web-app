<?php
ob_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$service_name = $service_category = '';
$is_active = 1;
$message = '';
$message_type = '';
$showSuccessModal = false;
$new_service_id = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_service'])) {
    
    $service_name = mysqli_real_escape_string($connection, trim($_POST['service_name']));
    $service_category = mysqli_real_escape_string($connection, trim($_POST['service_category']));
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $created_by = $_SESSION['user_id'];
    
    // Validation
    if (empty($service_name) || empty($service_category)) {
        $message = "Please fill in all required fields.";
        $message_type = "danger";
    } else {
        
        // Check if service name already exists
        $check_query = "SELECT service_id FROM service_types WHERE service_name = '$service_name'";
        $check_result = mysqli_query($connection, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $message = "Service name already exists. Please use a different name.";
            $message_type = "danger";
        } else {
            
            // Insert service
            $insert_query = "INSERT INTO service_types (service_name, service_category, is_active, created_by) 
                             VALUES ('$service_name', '$service_category', $is_active, $created_by)";
            
            if (mysqli_query($connection, $insert_query)) {
                $new_service_id = mysqli_insert_id($connection);
                $showSuccessModal = true;
                
                // Clear form data
                $service_name = $service_category = '';
                $is_active = 1;
            } else {
                $message = "Error adding service: " . mysqli_error($connection);
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
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add New Service</h5>
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
                        <div class="mb-3">
                            <label for="service_name" class="form-label">Service Name *</label>
                            <input type="text" id="service_name" name="service_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($service_name); ?>" 
                                   placeholder="e.g., Monthly Bookkeeping, VAT Return Filing" required>
                        </div>

                        <div class="mb-3">
                            <label for="service_category" class="form-label">Service Category *</label>
                            <select id="service_category" name="service_category" class="form-control" required>
                                <option value="">Select Category</option>
                                <option value="bookkeeping" <?php echo ($service_category == 'bookkeeping') ? 'selected' : ''; ?>>Bookkeeping</option>
                                <option value="audit" <?php echo ($service_category == 'audit') ? 'selected' : ''; ?>>Audit</option>
                                <option value="tax" <?php echo ($service_category == 'tax') ? 'selected' : ''; ?>>Tax</option>
                                <option value="registration" <?php echo ($service_category == 'registration') ? 'selected' : ''; ?>>Registration</option>
                                <option value="setup" <?php echo ($service_category == 'setup') ? 'selected' : ''; ?>>Setup</option>
                                <option value="other" <?php echo ($service_category == 'other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?php echo $is_active ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                            <div class="form-text">Inactive services cannot be selected for new engagements</div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="submit_service" class="btn btn-primary btn-lg me-2">
                                    <i class="bi bi-check-circle me-1"></i> Add Service
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

<?php if ($showSuccessModal && $new_service_id): ?>
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
          <h5 class="mt-3">Service Added Successfully!</h5>
          <p class="text-muted mb-0">The service "<?php echo htmlspecialchars($service_name); ?>" has been created.</p>
        </div>
      </div>
      <div class="modal-footer justify-content-center border-0 pt-0">
        <a href="services.php" class="btn btn-success px-4">
          <i class="bi bi-list-ul me-2"></i>View All Services
        </a>
        <a href="services.php?source=add_service" class="btn btn-outline-success px-4">
          <i class="bi bi-plus-circle me-2"></i>Add Another Service
        </a>
        <a href="services.php?source=add_rule&service_id=<?php echo $new_service_id; ?>" class="btn btn-outline-primary px-4">
          <i class="bi bi-gear"></i>Add Point Rules
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