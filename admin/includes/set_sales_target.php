<?php
// Start output buffering at the VERY beginning
ob_start();

// Check if user is logged in and has permission
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    echo "<script>window.location.href = '../login.php';</script>";
    exit();
}

// Check if user has permission
$user_role_query = "SELECT role_name FROM users u LEFT JOIN user_roles r ON u.role_id = r.role_id WHERE u.user_id = " . $_SESSION['user_id'];
$user_role_result = mysqli_query($connection, $user_role_query);
$user_role = mysqli_fetch_assoc($user_role_result)['role_name'] ?? '';

// if ($user_role != 'CEO_GM' && $user_role != 'HR_ADMIN' && $user_role != 'ADMIN_STAFF') {
//     $_SESSION['error_message'] = "You don't have permission to set targets.";
//     ob_end_clean();
//     echo "<script>window.location.href = 'sales_targets.php';</script>";
//     exit();
// }

// Initialize variables
$employee_id = '';
$target_value = '';
$year = date('Y');
$month = date('m');
$message = '';
$message_type = '';
$showSuccessModal = false;
$new_target_id = null;

// Get sales employees for dropdown
$sales_query = "SELECT u.user_id, u.first_name, u.last_name 
                FROM users u
                JOIN user_roles r ON u.role_id = r.role_id
                WHERE r.role_name IN ('sales_staff')
                ORDER BY u.first_name";
$sales_result = mysqli_query($connection, $sales_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_target'])) {
    
    $employee_id = (int)$_POST['employee_id'];
    $target_value = floatval($_POST['target_value']);
    $year = (int)$_POST['year'];
    $month = (int)$_POST['month'];
    $created_by = $_SESSION['user_id'];
    
    // Validation
    if (empty($employee_id) || $target_value <= 0) {
        $message = "Please fill in all required fields with valid values.";
        $message_type = "danger";
    } else {
        // Check if target already exists for this employee/month/year
        $check_query = "SELECT target_id FROM sales_targets 
                       WHERE employee_id = $employee_id AND year = $year AND month = $month";
        $check_result = mysqli_query($connection, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $message = "A target already exists for this employee and period.";
            $message_type = "danger";
        } else {
            // Insert target
            $insert_query = "INSERT INTO sales_targets 
                            (employee_id, year, month, target_value, created_by, status)
                            VALUES ($employee_id, $year, $month, $target_value, $created_by, 'PENDING')";
            
            if (mysqli_query($connection, $insert_query)) {
                $new_target_id = mysqli_insert_id($connection);
                $showSuccessModal = true;
                
                // Clear form
                $employee_id = '';
                $target_value = '';
            } else {
                $message = "Error setting target: " . mysqli_error($connection);
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
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Set Sales Target</h5>
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

                    <form method="POST" action="" id="targetForm">
                        <div class="mb-3">
                            <label for="employee_id" class="form-label">Sales Person *</label>
                            <select id="employee_id" name="employee_id" class="form-control" required>
                                <option value="">Select Sales Person</option>
                                <?php 
                                if ($sales_result && mysqli_num_rows($sales_result) > 0) {
                                    mysqli_data_seek($sales_result, 0);
                                    while($emp = mysqli_fetch_assoc($sales_result)): 
                                ?>
                                    <option value="<?php echo $emp['user_id']; ?>" <?php echo ($employee_id == $emp['user_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
                                    </option>
                                <?php 
                                    endwhile;
                                } else {
                                    echo '<option value="">No sales staff found</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="year" class="form-label">Year *</label>
                                <select id="year" name="year" class="form-control" required>
                                    <?php for($y = date('Y'); $y <= date('Y') + 1; $y++): ?>
                                        <option value="<?php echo $y; ?>" <?php echo ($year == $y) ? 'selected' : ''; ?>>
                                            <?php echo $y; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="month" class="form-label">Month *</label>
                                <select id="month" name="month" class="form-control" required>
                                    <?php for($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?php echo $m; ?>" <?php echo ($month == $m) ? 'selected' : ''; ?>>
                                            <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="target_value" class="form-label">Target Amount (AED) *</label>
                            <input type="number" step="0.01" min="0" id="target_value" name="target_value" 
                                   class="form-control" value="<?php echo $target_value; ?>" required>
                            <div class="form-text">Monthly sales target in AED</div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="submit_target" class="btn btn-primary btn-lg me-2">
                                    <i class="bi bi-check-circle me-1"></i> Set Target
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

<?php if ($showSuccessModal && $new_target_id): ?>
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
                    <h5 class="mt-3">Sales Target Set Successfully!</h5>
                    <p class="text-muted mb-0">The target has been created and is pending achievement submission.</p>
                </div>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <a href="sales_targets.php" class="btn btn-success px-4">
                    <i class="bi bi-list-ul me-2"></i>View All Targets
                </a>
                <a href="sales_targets.php?source=set_target" class="btn btn-outline-success px-4">
                    <i class="bi bi-plus-circle me-2"></i>Set Another Target
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
// Form validation
document.getElementById('targetForm')?.addEventListener('submit', function(e) {
    const targetValue = parseFloat(document.getElementById('target_value').value);
    const employeeId = document.getElementById('employee_id').value;
    
    if (!employeeId) {
        e.preventDefault();
        alert('Please select a sales person.');
        return;
    }
    
    if (targetValue <= 0) {
        e.preventDefault();
        alert('Target amount must be greater than 0.');
        return;
    }
});
</script>