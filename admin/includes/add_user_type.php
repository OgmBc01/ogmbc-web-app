<?php
// Start output buffering at the VERY beginning
ob_start();

// AUTHENTICATION CHECK
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$type_name = $type_description = '';
$message = '';
$message_type = '';
$showSuccessModal = false;

// HANDLE FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_type'])) {
    $type_name = trim($_POST['type_name']);
    $type_description = trim($_POST['type_description']);
    if (empty($type_name)) {
        $message = "Type name is required.";
        $message_type = "danger";
    } else {
        $check_query = "SELECT type_id FROM user_types WHERE type_name = ?";
        $check_stmt = $connection->prepare($check_query);
        $check_stmt->bind_param("s", $type_name);
        $check_stmt->execute();
        $check_stmt->store_result();
        if ($check_stmt->num_rows > 0) {
            $message = "Type name already exists. Please use a different name.";
            $message_type = "danger";
        } else {
            $insert_query = "INSERT INTO user_types (type_name, type_description) VALUES (?, ?)";
            $insert_stmt = $connection->prepare($insert_query);
            $insert_stmt->bind_param("ss", $type_name, $type_description);
            if ($insert_stmt->execute()) {
                $showSuccessModal = true;
                $type_name = $type_description = '';
                $message = '';
                $message_type = '';
            } else {
                $message = "Error adding type: " . $connection->error;
                $message_type = "danger";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}

ob_end_flush();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add New User Type</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($message) && !$showSuccessModal): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="typeForm">
                        <div class="mb-3">
                            <label for="type_name" class="form-label">Type Name *</label>
                            <input type="text" id="type_name" name="type_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($type_name); ?>" 
                                   placeholder="e.g., internal, client, partner" required>
                            <div class="form-text">Use lowercase letters, no spaces</div>
                        </div>
                        <div class="mb-3">
                            <label for="type_description" class="form-label">Description</label>
                            <textarea id="type_description" name="type_description" class="form-control" rows="3"><?php echo htmlspecialchars($type_description); ?></textarea>
                            <div class="form-text">Brief description of this user type</div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="submit_type" class="btn btn-success btn-lg me-2">
                                    <i class="bi bi-check-circle me-1"></i> Add Type
                                </button>
                                <a href="user_roles.php" class="btn btn-outline-secondary btn-lg">
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

<?php if (isset($showSuccessModal) && $showSuccessModal): ?>
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
          <h5 class="mt-3">User Type Added Successfully!</h5>
          <p class="text-muted mb-0">The type "<?php echo htmlspecialchars($_POST['type_name'] ?? ''); ?>" has been created.</p>
        </div>
      </div>
      <div class="modal-footer justify-content-center border-0 pt-0">
        <a href="user_roles.php" class="btn btn-success px-4">
          <i class="bi bi-list-ul me-2"></i>View All Types
        </a>
        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
          <i class="bi bi-plus-circle me-2"></i>Add Another Type
        </button>
      </div>
    </div>
  </div>
</div>
<script>
  // Auto-show the modal when page loads
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
document.getElementById('typeForm')?.addEventListener('submit', function(e) {
    const typeName = document.getElementById('type_name').value;
    // Validate type name format (lowercase letters, numbers, underscores only)
    const nameRegex = /^[a-z0-9_]+$/;
    if (!nameRegex.test(typeName)) {
        e.preventDefault();
        alert('Type name must contain only lowercase letters, numbers, and underscores.');
        return;
    }
});
</script>