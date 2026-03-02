<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$dept_name = $dept_code = $manager = $budget = $location = $description = '';
$message = '';
$message_type = '';

// Handle form submission
if (isset($_POST['submit_department'])) {
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
        // Check if department code already exists
        $check_query = "SELECT id FROM departments WHERE dept_code = '$dept_code'";
        $check_result = mysqli_query($connection, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $message = "Department code already exists. Please use a different code.";
            $message_type = "danger";
        } else {
            // Insert department
            $insert_query = "INSERT INTO departments (dept_name, dept_code, manager, budget, location, description) 
                             VALUES ('$dept_name', '$dept_code', '$manager', '$budget', '$location', '$description')";
            
            if (mysqli_query($connection, $insert_query)) {
                $_SESSION['success_message'] = "Department added successfully!";
                echo "<script>window.location.href = 'departments.php';</script>";
                exit();
            } else {
                $message = "Error adding department: " . mysqli_error($connection);
                $message_type = "danger";
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
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add New Department</h5>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="departmentForm">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="dept_name" class="form-label">Department Name *</label>
                                    <input type="text" id="dept_name" name="dept_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($dept_name); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="dept_code" class="form-label">Department Code *</label>
                                    <input type="text" id="dept_code" name="dept_code" class="form-control" 
                                           value="<?php echo htmlspecialchars($dept_code); ?>" 
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
                                            $selected = ($manager == $emp['employee_id']) ? 'selected' : '';
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
                                    <label for="budget" class="form-label">Annual Budget (AED) *</label>
                                    <input type="number" step="0.01" id="budget" name="budget" class="form-control" 
                                           value="<?php echo htmlspecialchars($budget); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" id="location" name="location" class="form-control" 
                                           value="<?php echo htmlspecialchars($location); ?>" 
                                           placeholder="Office location">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea id="description" name="description" class="form-control" rows="3"><?php echo htmlspecialchars($description); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="submit_department" class="btn btn-primary btn-lg me-2">
                                    <i class="bi bi-check-circle me-1"></i> Add Department
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
document.getElementById('departmentForm')?.addEventListener('submit', function(e) {
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