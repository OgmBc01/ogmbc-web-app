<?php
// Start output buffering at the VERY beginning
ob_start();

/* ===============================
   AUTHENTICATION CHECK
=================================*/
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header("Location: ../login.php");
    exit();
}

/* ===============================
   VALIDATE ROLE ID
=================================*/
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid role ID.";
    ob_end_clean();
    header("Location: user_roles.php");
    exit();
}

$role_id = (int) $_GET['id'];
$message = '';
$message_type = '';
$showSuccessModal = false;

/* ===============================
   HANDLE FORM SUBMISSION
=================================*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {

    $role_name = trim($_POST['role_name']);
    $role_description = trim($_POST['role_description']);
    $role_level = (int) $_POST['role_level'];

    if (empty($role_name)) {
        $message = "Role name is required.";
        $message_type = "danger";
    } else {

        // Check duplicate role name (prepared statement)
        $check_stmt = $connection->prepare(
            "SELECT role_id FROM user_roles WHERE role_name = ? AND role_id != ?"
        );
        $check_stmt->bind_param("si", $role_name, $role_id);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $message = "Role name already exists. Please use a different name.";
            $message_type = "danger";
        } else {

            // Update role
            $update_stmt = $connection->prepare(
                "UPDATE user_roles 
                 SET role_name = ?, role_description = ?, role_level = ?
                 WHERE role_id = ?"
            );
            $update_stmt->bind_param(
                "ssii",
                $role_name,
                $role_description,
                $role_level,
                $role_id
            );

            if ($update_stmt->execute()) {
                $showSuccessModal = true;
                
                // Refresh role data after update
                $refresh_stmt = $connection->prepare("SELECT * FROM user_roles WHERE role_id = ?");
                $refresh_stmt->bind_param("i", $role_id);
                $refresh_stmt->execute();
                $refresh_result = $refresh_stmt->get_result();
                $role = $refresh_result->fetch_assoc();
                $refresh_stmt->close();
                
                // Clear any previous messages
                $message = '';
                $message_type = '';
            } else {
                $message = "Error updating role: " . $connection->error;
                $message_type = "danger";
            }

            $update_stmt->close();
        }

        $check_stmt->close();
    }
}

/* ===============================
   FETCH ROLE DATA (if not already fetched after update)
=================================*/
if (!isset($role)) {
    $stmt = $connection->prepare("SELECT * FROM user_roles WHERE role_id = ?");
    $stmt->bind_param("i", $role_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error_message'] = "Role not found.";
        ob_end_clean();
        header("Location: user_roles.php");
        exit();
    }

    $role = $result->fetch_assoc();
    $stmt->close();
}

ob_end_flush();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit User Role</h5>
                    <a href="user_roles.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Roles
                    </a>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($message) && !$showSuccessModal): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="editRoleForm">
                        <input type="hidden" name="role_id" value="<?php echo $role_id; ?>">
                        
                        <div class="mb-3">
                            <label for="role_name" class="form-label">Role Name *</label>
                            <input type="text" id="role_name" name="role_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($role['role_name']); ?>" 
                                   placeholder="e.g., admin, manager, editor" required>
                            <div class="form-text">Use lowercase letters, no spaces</div>
                        </div>

                        <div class="mb-3">
                            <label for="role_level" class="form-label">Role Level *</label>
                            <input type="number" id="role_level" name="role_level" class="form-control" 
                                   value="<?php echo $role['role_level']; ?>" min="1" max="100" required>
                            <div class="form-text">Higher level = more privileges (1-100)</div>
                        </div>

                        <div class="mb-3">
                            <label for="role_description" class="form-label">Description</label>
                            <textarea id="role_description" name="role_description" class="form-control" rows="4"><?php echo htmlspecialchars($role['role_description']); ?></textarea>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="update_role" class="btn btn-primary btn-lg me-2">
                                    <i class="bi bi-check-circle me-1"></i> Update Role
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
          <h5 class="mt-3">User Role Updated Successfully!</h5>
          <p class="text-muted mb-0">The role "<?php echo htmlspecialchars($role['role_name']); ?>" has been updated.</p>
        </div>
      </div>
      <div class="modal-footer justify-content-center border-0 pt-0">
        <a href="user_roles.php" class="btn btn-success px-4">
          <i class="bi bi-list-ul me-2"></i>View All Roles
        </a>
        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
          <i class="bi bi-pencil me-2"></i>Continue Editing
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
document.getElementById('editRoleForm')?.addEventListener('submit', function(e) {
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