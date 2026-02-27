<?php
// Start output buffering at the VERY beginning
ob_start();

// AUTHENTICATION CHECK
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header("Location: ../login.php");
    exit();
}

// VALIDATE TYPE ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid type ID.";
    ob_end_clean();
    header("Location: user_roles.php");
    exit();
}

$type_id = (int) $_GET['id'];
$message = '';
$message_type = '';
$showSuccessModal = false;

// HANDLE FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_type'])) {
    $type_name = trim($_POST['type_name']);
    $type_description = trim($_POST['type_description']);
    if (empty($type_name)) {
        $message = "Type name is required.";
        $message_type = "danger";
    } else {
        $check_query = "SELECT type_id FROM user_types WHERE type_name = ? AND type_id != ?";
        $check_stmt = $connection->prepare($check_query);
        $check_stmt->bind_param("si", $type_name, $type_id);
        $check_stmt->execute();
        $check_stmt->store_result();
        if ($check_stmt->num_rows > 0) {
            $message = "Type name already exists. Please use a different name.";
            $message_type = "danger";
        } else {
            $update_query = "UPDATE user_types SET type_name = ?, type_description = ? WHERE type_id = ?";
            $update_stmt = $connection->prepare($update_query);
            $update_stmt->bind_param("ssi", $type_name, $type_description, $type_id);
            if ($update_stmt->execute()) {
                $showSuccessModal = true;
                // Refresh type data after update
                $refresh_stmt = $connection->prepare("SELECT * FROM user_types WHERE type_id = ?");
                $refresh_stmt->bind_param("i", $type_id);
                $refresh_stmt->execute();
                $refresh_result = $refresh_stmt->get_result();
                $type = $refresh_result->fetch_assoc();
                $refresh_stmt->close();
                $message = '';
                $message_type = '';
            } else {
                $message = "Error updating type: " . $connection->error;
                $message_type = "danger";
            }
            $update_stmt->close();
        }
        $check_stmt->close();
    }
}

// FETCH TYPE DATA (if not already fetched after update)
if (!isset($type)) {
    $stmt = $connection->prepare("SELECT * FROM user_types WHERE type_id = ?");
    $stmt->bind_param("i", $type_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $_SESSION['error_message'] = "Type not found.";
        ob_end_clean();
        header("Location: user_roles.php");
        exit();
    }
    $type = $result->fetch_assoc();
    $stmt->close();
}

ob_end_flush();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit User Type</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($message) && !$showSuccessModal): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="editTypeForm">
                        <div class="mb-3">
                            <label for="type_name" class="form-label">Type Name *</label>
                            <input type="text" id="type_name" name="type_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($type['type_name']); ?>" 
                                   placeholder="e.g., internal, client, partner" required>
                            <div class="form-text">Use lowercase letters, no spaces</div>
                        </div>
                        <div class="mb-3">
                            <label for="type_description" class="form-label">Description</label>
                            <textarea id="type_description" name="type_description" class="form-control" rows="3"><?php echo htmlspecialchars($type['type_description']); ?></textarea>
                        </div>
                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="update_type" class="btn btn-success btn-lg me-2">
                                    <i class="bi bi-check-circle me-1"></i> Update Type
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
          <h5 class="mt-3">User Type Updated Successfully!</h5>
          <p class="text-muted mb-0">The type "<?php echo htmlspecialchars($type['type_name']); ?>" has been updated.</p>
        </div>
      </div>
      <div class="modal-footer justify-content-center border-0 pt-0">
        <a href="user_roles.php" class="btn btn-success px-4">
          <i class="bi bi-list-ul me-2"></i>View All Types
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
document.getElementById('editTypeForm')?.addEventListener('submit', function(e) {
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