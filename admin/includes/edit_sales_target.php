<?php
ob_start();

// Check if user is logged in and has permission
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header("Location: ../login.php");
    exit();
}

// Check if user has permission
$user_role_query = "SELECT role_name FROM users u LEFT JOIN user_roles r ON u.role_id = r.role_id WHERE u.user_id = " . $_SESSION['user_id'];
$user_role_result = mysqli_query($connection, $user_role_query);
$user_role = mysqli_fetch_assoc($user_role_result)['role_name'] ?? '';

if ($user_role != 'CEO_GM' && $user_role != 'HR_ADMIN' && $user_role != 'ADMIN_STAFF') {
    $_SESSION['error_message'] = "You don't have permission to edit targets.";
    ob_end_clean();
    header("Location: sales_targets.php");
    exit();
}

// Get target ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid target ID.";
    ob_end_clean();
    header("Location: sales_targets.php");
    exit();
}

$target_id = (int)$_GET['id'];

// Fetch target details
$query = "SELECT st.*, CONCAT(u.first_name, ' ', u.last_name) as employee_name
          FROM sales_targets st
          JOIN users u ON st.employee_id = u.user_id
          WHERE st.target_id = $target_id";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['error_message'] = "Target not found.";
    ob_end_clean();
    header("Location: sales_targets.php");
    exit();
}

$target = mysqli_fetch_assoc($result);

// Check if target can be edited
if ($target['status'] != 'PENDING') {
    $_SESSION['error_message'] = "Only pending targets can be edited.";
    ob_end_clean();
    header("Location: sales_targets.php");
    exit();
}

// Initialize variables
$message = '';
$message_type = '';
$showSuccessModal = false;

// Get sales employees for dropdown
$sales_query = "SELECT u.user_id, u.first_name, u.last_name 
                FROM users u
                JOIN user_roles r ON u.role_id = r.role_id
                WHERE r.role_name IN ('SALES_STAFF')
                ORDER BY u.first_name";
$sales_result = mysqli_query($connection, $sales_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_target'])) {
    
    $employee_id = (int)$_POST['employee_id'];
    $target_value = floatval($_POST['target_value']);
    $year = (int)$_POST['year'];
    $month = (int)$_POST['month'];
    
    // Validation
    if (empty($employee_id) || $target_value <= 0) {
        $message = "Please fill in all required fields with valid values.";
        $message_type = "danger";
    } else {
        // Check if target already exists for another target
        $check_query = "SELECT target_id FROM sales_targets 
                       WHERE employee_id = $employee_id AND year = $year AND month = $month
                       AND target_id != $target_id";
        $check_result = mysqli_query($connection, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $message = "A target already exists for this employee and period.";
            $message_type = "danger";
        } else {
            // Update target
            $update_query = "UPDATE sales_targets SET 
                            employee_id = $employee_id,
                            year = $year,
                            month = $month,
                            target_value = $target_value
                            WHERE target_id = $target_id";
            
            if (mysqli_query($connection, $update_query)) {
                $showSuccessModal = true;
                
                // Refresh target data
                $refresh_query = "SELECT st.*, CONCAT(u.first_name, ' ', u.last_name) as employee_name
                                 FROM sales_targets st
                                 JOIN users u ON st.employee_id = u.user_id
                                 WHERE st.target_id = $target_id";
                $refresh_result = mysqli_query($connection, $refresh_query);
                $target = mysqli_fetch_assoc($refresh_result);
            } else {
                $message = "Error updating target: " . mysqli_error($connection);
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
                    <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Sales Target</h5>
                    <a href="sales_targets.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Targets
                    </a>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($message) && !$showSuccessModal): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="employee_id" class="form-label">Sales Person *</label>
                            <select id="employee_id" name="employee_id" class="form-control" required>
                                <option value="">Select Sales Person</option>
                                <?php while($sales = mysqli_fetch_assoc($sales_result)): ?>
                                    <option value="<?php echo $sales['user_id']; ?>" <?php echo ($target['employee_id'] == $sales['user_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($sales['first_name'] . ' ' . $sales['last_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="year" class="form-label">Year *</label>
                                <select id="year" name="year" class="form-control" required>
                                    <?php for($y = date('Y'); $y <= date('Y') + 1; $y++): ?>
                                        <option value="<?php echo $y; ?>" <?php echo ($target['year'] == $y) ? 'selected' : ''; ?>>
                                            <?php echo $y; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="month" class="form-label">Month *</label>
                                <select id="month" name="month" class="form-control" required>
                                    <?php for($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?php echo $m; ?>" <?php echo ($target['month'] == $m) ? 'selected' : ''; ?>>
                                            <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="target_value" class="form-label">Target Amount (AED) *</label>
                            <input type="number" step="0.01" min="0" id="target_value" name="target_value" 
                                   class="form-control" value="<?php echo $target['target_value']; ?>" required>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="update_target" class="btn btn-primary btn-lg me-2">
                                    <i class="bi bi-check-circle me-1"></i> Update Target
                                </button>
                                <a href="sales_targets.php" class="btn btn-outline-secondary btn-lg">
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
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Success!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-3">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h5 class="mt-3">Target Updated Successfully!</h5>
                <p class="text-muted mb-0">The sales target has been updated.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <a href="sales_targets.php" class="btn btn-success px-4">View All Targets</a>
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Continue Editing</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
    });
</script>
<?php endif; ?>