<?php
ob_start();
$message = '';
$message_type = '';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // No redirect, just show modal
}

// Get department ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $message = "Invalid department ID.";
    $message_type = "danger";
} else {
    $dept_id = (int)$_GET['id'];
    // Fetch department data
    $query = "SELECT * FROM departments WHERE id = $dept_id";
    $result = mysqli_query($connection, $query);
    if (!$result || mysqli_num_rows($result) == 0) {
        $message = "Department not found.";
        $message_type = "danger";
    } else {
        $department = mysqli_fetch_assoc($result);
        // Handle form submission
        if (isset($_POST['update_department'])) {
            $dept_name = mysqli_real_escape_string($connection, $_POST['dept_name']);
            $dept_code = mysqli_real_escape_string($connection, $_POST['dept_code']);
            $manager = mysqli_real_escape_string($connection, $_POST['manager']);
            $budget = mysqli_real_escape_string($connection, $_POST['budget']);
            $location = mysqli_real_escape_string($connection, $_POST['location']);
            $description = mysqli_real_escape_string($connection, $_POST['description']);
            // Validate required fields
            if (empty($dept_name) || empty($dept_code) || empty($budget)) {
                $message = "Please fill in all required fields.";
                $message_type = "danger";
            } else {
                // Check if department code already exists (excluding current department)
                $check_query = "SELECT id FROM departments WHERE dept_code = '$dept_code' AND id != $dept_id";
                $check_result = mysqli_query($connection, $check_query);
                if (mysqli_num_rows($check_result) > 0) {
                    $message = "Department code already exists. Please use a different code.";
                    $message_type = "danger";
                } else {
                    // Update department
                    $update_query = "UPDATE departments SET 
                                     dept_name = '$dept_name',
                                     dept_code = '$dept_code',
                                     manager = '$manager',
                                     budget = '$budget',
                                     location = '$location',
                                     description = '$description'
                                     WHERE id = $dept_id";
                    if (mysqli_query($connection, $update_query)) {
                        $message = "Department updated successfully!";
                        $message_type = "success";
                        // Refetch updated department for form
                        $query = "SELECT * FROM departments WHERE id = $dept_id";
                        $result = mysqli_query($connection, $query);
                        $department = mysqli_fetch_assoc($result);
                    } else {
                        $message = "Error updating department: " . mysqli_error($connection);
                        $message_type = "danger";
                    }
                }
            }
        }
    }
}
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Department</h5>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($message) && $message_type !== 'success'): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($message) && $message_type === 'success'): ?>
                    <!-- Success Modal with Full Backdrop -->
                    <div class="modal-backdrop show" style="position:fixed;top:0;left:0;width:100vw;height:100vh;background-color:rgba(0,0,0,0.5);z-index:1050;"></div>
                    <div class="modal fade show" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-modal="true" style="display:block;z-index:1055;">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-success shadow-lg">
                                <div class="modal-header bg-success text-white border-success">
                                    <h5 class="modal-title" id="successModalLabel">
                                        <i class="bi bi-check-circle-fill me-2 fs-2"></i> Department Updated
                                    </h5>
                                </div>
                                <div class="modal-body text-center">
                                    <p class="fs-5 mb-3">Your changes have been saved successfully.</p>
                                    <div class="d-flex justify-content-center gap-3 mt-4">
                                        <a href="departments.php" class="btn btn-success btn-lg px-4">
                                            <i class="bi bi-list-ul me-1"></i> View All Departments
                                        </a>
                                        <button type="button" class="btn btn-outline-success btn-lg px-4" onclick="closeSuccessModal()">
                                            <i class="bi bi-pencil-square me-1"></i> Continue Editing
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        function closeSuccessModal() {
                            document.getElementById('successModal').style.display = 'none';
                            var backdrop = document.querySelector('.modal-backdrop.show');
                            if (backdrop) backdrop.style.display = 'none';
                            document.body.style.overflow = '';
                        }
                        document.body.style.overflow = 'hidden';
                        document.getElementById('successModal').addEventListener('click', function(e) {
                            if (e.target === this) {
                                closeSuccessModal();
                            }
                        });
                        window.closeSuccessModal = closeSuccessModal;
                    </script>
                    <?php endif; ?>

                    <form method="POST" action="" id="editDepartmentForm">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="dept_name" class="form-label">Department Name *</label>
                                    <input type="text" id="dept_name" name="dept_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($department['dept_name']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="dept_code" class="form-label">Department Code *</label>
                                    <input type="text" id="dept_code" name="dept_code" class="form-control" 
                                           value="<?php echo htmlspecialchars($department['dept_code']); ?>" 
                                           placeholder="e.g., HR, IT, FIN" maxlength="10" required>
                                    <div class="form-text">Unique identifier for the department</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="manager" class="form-label">Department Manager</label>
                                    <select id="manager" name="manager" class="form-control">
                                        <option value="">Select Manager</option>
                                        <?php
                                        // Get all employees to populate manager dropdown
                                        $employees_query = "SELECT employee_id, first_name, last_name FROM employees ORDER BY first_name";
                                        $employees_result = mysqli_query($connection, $employees_query);
                                        while ($emp = mysqli_fetch_assoc($employees_result)) {
                                            $selected = ($department['manager'] == $emp['employee_id']) ? 'selected' : '';
                                            echo "<option value='{$emp['employee_id']}' {$selected}>" . 
                                                 htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) . 
                                                 "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="budget" class="form-label">Annual Budget ($) *</label>
                                    <input type="number" step="0.01" id="budget" name="budget" class="form-control" 
                                           value="<?php echo htmlspecialchars($department['budget']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" id="location" name="location" class="form-control" 
                                           value="<?php echo htmlspecialchars($department['location']); ?>" 
                                           placeholder="Office location">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea id="description" name="description" class="form-control" rows="3"><?php echo htmlspecialchars($department['description']); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="update_department" class="btn btn-primary btn-lg me-2">
                                    <i class="bi bi-check-circle me-1"></i> Update Department
                                </button>
                                <a href="departments.php" class="btn btn-outline-secondary btn-lg">
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

<script>
// Form validation
document.getElementById('editDepartmentForm')?.addEventListener('submit', function(e) {
    const deptCode = document.getElementById('dept_code').value;
    const budget = parseFloat(document.getElementById('budget').value);
    
    // Validate department code format (alphanumeric)
    const codeRegex = /^[A-Za-z0-9]{2,10}$/;
    if (!codeRegex.test(deptCode)) {
        e.preventDefault();
        alert('Department code must be 2-10 alphanumeric characters.');
        return;
    }
    
    // Validate budget
    if (budget <= 0) {
        e.preventDefault();
        alert('Budget must be greater than 0.');
        return;
    }
});
</script>