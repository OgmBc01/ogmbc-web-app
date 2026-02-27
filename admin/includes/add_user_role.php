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
$role_name = $role_description = '';
$role_level = 1;
$message = '';
$message_type = '';
$showSuccessModal = false;

// HANDLE FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_role'])) {
    $role_name = trim($_POST['role_name']);
    $role_description = trim($_POST['role_description']);
    $role_level = (int) $_POST['role_level'];
    if (empty($role_name)) {
        $message = "Role name is required.";
        $message_type = "danger";
    } else {
        $check_query = "SELECT role_id FROM user_roles WHERE role_name = ?";
        $check_stmt = $connection->prepare($check_query);
        $check_stmt->bind_param("s", $role_name);
        $check_stmt->execute();
        $check_stmt->store_result();
        if ($check_stmt->num_rows > 0) {
            $message = "Role name already exists. Please use a different name.";
            $message_type = "danger";
        } else {
            $insert_query = "INSERT INTO user_roles (role_name, role_description, role_level) VALUES (?, ?, ?)";
            $insert_stmt = $connection->prepare($insert_query);
            $insert_stmt->bind_param("ssi", $role_name, $role_description, $role_level);
            if ($insert_stmt->execute()) {
                $showSuccessModal = true;
                $role_name = $role_description = '';
                $role_level = 1;
                $message = '';
                $message_type = '';
            } else {
                $message = "Error adding role: " . $connection->error;
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
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add New User Role</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($message) && !$showSuccessModal): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="roleForm">
                        <div class="mb-3">
                            <label for="role_name" class="form-label">Role Name *</label>
                            <input type="text" id="role_name" name="role_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($role_name); ?>" 
                                   placeholder="e.g., admin, manager, editor" required>
                            <div class="form-text">Use lowercase letters, no spaces</div>
                        </div>
                        <div class="mb-3">
                            <label for="role_level" class="form-label">Role Level *</label>
                            <input type="number" id="role_level" name="role_level" class="form-control" 
                                   value="<?php echo $role_level; ?>" min="1" max="100" required>
                            <div class="form-text">Higher level = more privileges (1-100)</div>
                        </div>
                        <div class="mb-3">
                            <label for="role_description" class="form-label">Description</label>
                            <textarea id="role_description" name="role_description" class="form-control" rows="3"><?php echo htmlspecialchars($role_description); ?></textarea>
                            <div class="form-text">Brief description of this role's responsibilities</div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="submit_role" class="btn btn-primary btn-lg me-2">
                                    <i class="bi bi-check-circle me-1"></i> Add Role
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
          <h5 class="mt-3">User Role Added Successfully!</h5>
          <p class="text-muted mb-0">The role "<?php echo htmlspecialchars($_POST['role_name'] ?? ''); ?>" has been created.</p>
        </div>
      </div>
      <div class="modal-footer justify-content-center border-0 pt-0">
        <a href="user_roles.php" class="btn btn-success px-4">
          <i class="bi bi-list-ul me-2"></i>View All Roles
        </a>
        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
          <i class="bi bi-plus-circle me-2"></i>Add Another Role
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
document.getElementById('roleForm')?.addEventListener('submit', function(e) {
    const roleName = document.getElementById('role_name').value;
    // Validate role name format (lowercase letters, numbers, underscores only)
    const nameRegex = /^[a-z0-9_]+$/;
    if (!nameRegex.test(roleName)) {
        e.preventDefault();
        alert('Role name must contain only lowercase letters, numbers, and underscores.');
        return;
    }
});
</script>