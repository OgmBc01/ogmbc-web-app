<?php
ob_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$employee_id = $points = $description = '';
$message = '';
$message_type = '';
$showSuccessModal = false;

// Fetch employees for dropdown
$employees_query = "SELECT user_id, first_name, last_name FROM users ORDER BY first_name";
$employees_result = mysqli_query($connection, $employees_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_adjustment'])) {
    
    $employee_id = (int)$_POST['employee_id'];
    $points = (int)$_POST['points'];
    $description = mysqli_real_escape_string($connection, trim($_POST['description']));
    
    if (empty($employee_id) || empty($points) || empty($description)) {
        $message = "Please fill in all required fields.";
        $message_type = "danger";
    } else {
        // Insert manual adjustment (simplified - no approval required for testing)
        $insert_query = "INSERT INTO points_ledger 
                        (employee_id, source_type, points, points_type, description, created_by)
                        VALUES 
                        ($employee_id, 'MANUAL_ADJUSTMENT', $points, 
                         '" . ($points >= 0 ? 'EARNED' : 'DEDUCTED') . "',
                         '$description', {$_SESSION['user_id']})";
        
        if (mysqli_query($connection, $insert_query)) {
            $showSuccessModal = true;
            
            // Clear form
            $employee_id = $points = $description = '';
        } else {
            $message = "Error creating adjustment: " . mysqli_error($connection);
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
                    <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Manual Point Adjustment</h5>
                    <a href="points_ledger.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Ledger
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
                            <label for="employee_id" class="form-label">Employee *</label>
                            <select id="employee_id" name="employee_id" class="form-control" required>
                                <option value="">Select Employee</option>
                                <?php while($emp = mysqli_fetch_assoc($employees_result)): ?>
                                    <option value="<?php echo $emp['user_id']; ?>" <?php echo ($employee_id == $emp['user_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="points" class="form-label">Points *</label>
                            <input type="number" id="points" name="points" class="form-control" 
                                   value="<?php echo $points; ?>" required>
                            <div class="form-text">Use positive values to add points, negative values to deduct.</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <input type="text" id="description" name="description" class="form-control" 
                                   value="<?php echo htmlspecialchars($description); ?>" 
                                   placeholder="e.g., Test adjustment" required>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="submit_adjustment" class="btn btn-warning btn-lg me-2">
                                    <i class="bi bi-check-circle me-1"></i> Add Adjustment
                                </button>
                                <a href="points_ledger.php" class="btn btn-outline-secondary btn-lg">
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
                <h5 class="mt-3">Adjustment Added Successfully!</h5>
                <p class="text-muted mb-0">The point adjustment has been recorded.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <a href="points_ledger.php" class="btn btn-success px-4">View Ledger</a>
                <a href="points_ledger.php?source=manual_adjustment" class="btn btn-outline-success px-4">Add Another</a>
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