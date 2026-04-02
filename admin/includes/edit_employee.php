<?php

// Check if user is logged in and has appropriate permissions
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$employee_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $user_email = $password = $first_name = $last_name = '';
$field_of_study = $qualification = $highest_graduation = $year_of_graduation = $current_image = $salary = '';
$department_id = '';
$user_type = '';
$message = '';
$message_type = '';
$show_success_modal = false;
$password_changed = false;

// Fetch departments for dropdown
$departments_query = "SELECT id, dept_name FROM departments ORDER BY dept_name";
$departments_result = $connection->query($departments_query);

// Fetch employee data if editing existing employee
if ($employee_id > 0) {
    $sql = "SELECT * FROM employees WHERE employee_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $employee = $result->fetch_assoc();
        $user_id = $employee['user_id'];
        $user_email = $employee['user_email'];
        $password = $employee['password'];
        $first_name = $employee['first_name'];
        $last_name = $employee['last_name'];
        $current_image = $employee['user_image'];
        $field_of_study = $employee['field_of_study'] ?? '';
        $qualification = $employee['qualification'] ?? '';
        $highest_graduation = $employee['highest_graduation'] ?? '';
        $year_of_graduation = $employee['year_of_graduation'] ?? '';
        $salary = $employee['salary'] ?? '';
        $department_id = $employee['department_id'] ?? '';
        $user_type = isset($employee['user_type']) ? $employee['user_type'] : '';
    } else {
        $message = "Employee not found.";
        $message_type = "danger";
    }
    $stmt->close();
}

// Password strength validation function
function validatePasswordStrength($password) {
    $score = 0;
    $requirements = [];
    
    // Length check (minimum 8 characters)
    if (strlen($password) >= 8) {
        $score++;
    } else {
        $requirements[] = "Minimum 8 characters";
    }
    
    // Contains uppercase letter
    if (preg_match('/[A-Z]/', $password)) {
        $score++;
    } else {
        $requirements[] = "At least one uppercase letter";
    }
    
    // Contains lowercase letter
    if (preg_match('/[a-z]/', $password)) {
        $score++;
    } else {
        $requirements[] = "At least one lowercase letter";
    }
    
    // Contains number
    if (preg_match('/[0-9]/', $password)) {
        $score++;
    } else {
        $requirements[] = "At least one number";
    }
    
    // Contains special character
    if (preg_match('/[^a-zA-Z0-9]/', $password)) {
        $score++;
    } else {
        $requirements[] = "At least one special character (!@#$%^&*)";
    }
    
    // Determine strength level
    if ($score >= 5) {
        $strength = 'very_strong';
        $strength_text = 'Very Strong';
        $strength_color = 'success';
    } elseif ($score >= 4) {
        $strength = 'strong';
        $strength_text = 'Strong';
        $strength_color = 'primary';
    } elseif ($score >= 3) {
        $strength = 'medium';
        $strength_text = 'Medium';
        $strength_color = 'warning';
    } elseif ($score >= 2) {
        $strength = 'weak';
        $strength_text = 'Weak';
        $strength_color = 'danger';
    } else {
        $strength = 'very_weak';
        $strength_text = 'Very Weak';
        $strength_color = 'danger';
    }
    
    return [
        'score' => $score,
        'strength' => $strength,
        'strength_text' => $strength_text,
        'strength_color' => $strength_color,
        'requirements' => $requirements
    ];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_employee'])) {
    // Get form data
    $employee_id = intval($_POST['employee_id']);
    $user_id = trim($_POST['user_id']);
    $user_email = trim($_POST['user_email']);
    $new_password = trim($_POST['password']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $field_of_study = trim($_POST['field_of_study'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $highest_graduation = trim($_POST['highest_graduation'] ?? '');
    $year_of_graduation = !empty($_POST['year_of_graduation']) ? intval($_POST['year_of_graduation']) : null;
    $salary = !empty($_POST['salary']) ? floatval($_POST['salary']) : 0.00;
    $department_id = !empty($_POST['department_id']) ? intval($_POST['department_id']) : null;
    $user_type = trim($_POST['user_type'] ?? '');

    // Handle file upload
    $user_image = $current_image;
    if (isset($_FILES['user_image']) && $_FILES['user_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['user_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($ext, $allowed)) {
            $upload_dir = "../uploads/profiles/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $new_filename = "profile_" . time() . "_" . rand(1000, 9999) . ".{$ext}";
            $target = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $target)) {
                // Delete old image if it exists
                if (!empty($current_image) && file_exists($upload_dir . $current_image)) {
                    unlink($upload_dir . $current_image);
                }
                $user_image = $new_filename;
            }
        }
    }

    // Validate required fields
    if (empty($user_id) || empty($user_email) || empty($first_name) || empty($last_name) || empty($department_id)) {
        $message = "Please fill in all required fields including Department.";
        $message_type = "danger";
    } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = "danger";
    } else {
        // Check if password is being changed
        $password_changed = false;
        $hashed_password = $password; // Keep current password by default
        
        if (!empty($new_password)) {
            // Validate password strength
            $password_strength = validatePasswordStrength($new_password);
            
            if ($password_strength['score'] < 3) {
                $message = "Password is too weak. Please use a stronger password.<br>
                            <small>Requirements: " . implode(", ", $password_strength['requirements']) . "</small>";
                $message_type = "danger";
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $password_changed = true;
            }
        }
        
        if (empty($message)) {
            // Update database
            $sql = "UPDATE employees SET 
                    user_id = ?, 
                    user_email = ?, 
                    password = ?, 
                    first_name = ?, 
                    last_name = ?, 
                    user_image = ?, 
                    field_of_study = ?, 
                    qualification = ?, 
                    highest_graduation = ?, 
                    year_of_graduation = ?,
                    salary = ?,
                    department_id = ?,
                    user_type = ?
                    WHERE employee_id = ?";
            
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("isssssssssdiss", 
                $user_id, 
                $user_email, 
                $hashed_password, 
                $first_name, 
                $last_name, 
                $user_image, 
                $field_of_study, 
                $qualification, 
                $highest_graduation, 
                $year_of_graduation,
                $salary,
                $department_id,
                $user_type,
                $employee_id
            );

            if ($stmt->execute()) {
                $stmt->close();
                
                // Also update the users table if password was changed
                if ($password_changed) {
                    $update_user_sql = "UPDATE users SET 
                                        first_name = ?, 
                                        last_name = ?, 
                                        user_email = ?, 
                                        password = ?,
                                        user_image = ?
                                        WHERE user_id = ?";
                    $user_stmt = $connection->prepare($update_user_sql);
                    $user_stmt->bind_param("sssssi", 
                        $first_name, 
                        $last_name, 
                        $user_email, 
                        $hashed_password, 
                        $user_image, 
                        $user_id
                    );
                    $user_stmt->execute();
                    $user_stmt->close();
                } else {
                    // Update users table without changing password
                    $update_user_sql = "UPDATE users SET 
                                        first_name = ?, 
                                        last_name = ?, 
                                        user_email = ?, 
                                        user_image = ?
                                        WHERE user_id = ?";
                    $user_stmt = $connection->prepare($update_user_sql);
                    $user_stmt->bind_param("ssssi", 
                        $first_name, 
                        $last_name, 
                        $user_email, 
                        $user_image, 
                        $user_id
                    );
                    $user_stmt->execute();
                    $user_stmt->close();
                }
                
                // Refresh employee data
                $sql = "SELECT * FROM employees WHERE employee_id = ?";
                $stmt = $connection->prepare($sql);
                $stmt->bind_param("i", $employee_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $employee = $result->fetch_assoc();
                $stmt->close();
                
                // Update variables with new data
                $user_id = $employee['user_id'];
                $user_email = $employee['user_email'];
                $first_name = $employee['first_name'];
                $last_name = $employee['last_name'];
                $current_image = $employee['user_image'];
                $field_of_study = $employee['field_of_study'] ?? '';
                $qualification = $employee['qualification'] ?? '';
                $highest_graduation = $employee['highest_graduation'] ?? '';
                $year_of_graduation = $employee['year_of_graduation'] ?? '';
                $salary = $employee['salary'] ?? '';
                $department_id = $employee['department_id'] ?? '';
                $user_type = isset($employee['user_type']) ? $employee['user_type'] : '';
                
                // Show success modal
                $show_success_modal = true;
            } else {
                $message = "Failed to update employee. Error: " . $connection->error;
                $message_type = "danger";
                $stmt->close();
            }
        }
    }
}
?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Edit Employee</h1>
            <a href="employees.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Employees
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <div class="card-header" style="background: #0a2240; color: #f1bf70;">
                        <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Employee Information</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="" enctype="multipart/form-data" id="employeeForm">
                            <input type="hidden" name="employee_id" value="<?php echo $employee_id; ?>">
                            
                            <div class="row">
                                <!-- Left Column - Required Fields -->
                                <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="first_name" class="form-label"><i class="bi bi-person me-1"></i>First Name *</label>
                                    <input type="text" id="first_name" name="first_name" class="form-control" 
                                    value="<?php echo htmlspecialchars($first_name ?? ''); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="last_name" class="form-label"><i class="bi bi-person me-1"></i>Last Name *</label>
                                    <input type="text" id="last_name" name="last_name" class="form-control" 
                                    value="<?php echo htmlspecialchars($last_name ?? ''); ?>" required>
                                </div>
                                    <div class="mb-3">
                                        <label for="user_type" class="form-label"><i class="bi bi-person-lines-fill me-1"></i>User Type *</label>
                                        <select id="user_type" name="user_type" class="form-control" required>
                                            <option value="">Select User Type</option>
                                            <option value="operations" <?php echo ($user_type == 'operations') ? 'selected' : ''; ?>>Operations</option>
                                            <option value="admin" <?php echo ($user_type == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                            <option value="manager" <?php echo ($user_type == 'manager') ? 'selected' : ''; ?>>Manager</option>
                                            <option value="hr" <?php echo ($user_type == 'hr') ? 'selected' : ''; ?>>HR</option>
                                            <option value="other" <?php echo ($user_type == 'other') ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="department_id" class="form-label"><i class="bi bi-building me-1"></i>Department *</label>
                                        <select id="department_id" name="department_id" class="form-control" required>
                                            <option value="">Select Department</option>
                                            <?php
                                            if ($departments_result && $departments_result->num_rows > 0) {
                                                $departments_result->data_seek(0);
                                                while ($dept = $departments_result->fetch_assoc()) {
                                                    $selected = ($department_id == $dept['id']) ? 'selected' : '';
                                                    echo "<option value='" . $dept['id'] . "' $selected>" . 
                                                         htmlspecialchars($dept['dept_name'] ?? '') . "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="user_id" class="form-label"><i class="bi bi-person-badge me-1"></i>User ID *</label>
                                        <input type="number" id="user_id" name="user_id" class="form-control" 
                                               value="<?php echo htmlspecialchars((string)($user_id ?? '')); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="user_email" class="form-label"><i class="bi bi-envelope me-1"></i>Email *</label>
                                        <input type="email" id="user_email" name="user_email" class="form-control" 
                                               value="<?php echo htmlspecialchars((string)($user_email ?? '')); ?>" required>
                                    </div>

                                    <!-- Password Section with Strength Meter -->
                                    <div class="mb-3">
                                        <label for="password" class="form-label"><i class="bi bi-lock me-1"></i>New Password</label>
                                        <div class="input-group">
                                            <input type="password" id="password" name="password" class="form-control" autocomplete="new-password" minlength="8">
                                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                        <div id="passwordStrength" class="mt-2" style="display: none;">
                                            <div class="strength-meter">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <small class="text-muted">Password Strength:</small>
                                                    <small id="strengthText" class="fw-bold"></small>
                                                </div>
                                                <div class="progress" style="height: 8px;">
                                                    <div id="strengthBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                                                </div>
                                                <div id="strengthRequirements" class="mt-2">
                                                    <small class="text-muted d-block mb-1">Password requirements:</small>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div id="reqLength" class="requirement-item">
                                                                <i class="bi bi-circle me-1"></i> Minimum 8 characters
                                                            </div>
                                                            <div id="reqUpper" class="requirement-item">
                                                                <i class="bi bi-circle me-1"></i> At least one uppercase letter
                                                            </div>
                                                            <div id="reqLower" class="requirement-item">
                                                                <i class="bi bi-circle me-1"></i> At least one lowercase letter
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div id="reqNumber" class="requirement-item">
                                                                <i class="bi bi-circle me-1"></i> At least one number
                                                            </div>
                                                            <div id="reqSpecial" class="requirement-item">
                                                                <i class="bi bi-circle me-1"></i> At least one special character
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-text">Leave empty to keep current password. If changing, password must be strong.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label"><i class="bi bi-lock me-1"></i>Confirm New Password</label>
                                        <div class="input-group">
                                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" autocomplete="new-password">
                                            <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                        <div id="passwordMatch" class="mt-1"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="salary" class="form-label"><i class="bi bi-cash me-1"></i>Salary ($)</label>
                                        <input type="number" step="0.01" id="salary" name="salary" class="form-control" 
                                               value="<?php echo htmlspecialchars((string)($salary ?? '0.00')); ?>">
                                    </div>
                                </div>

                                <!-- Right Column - Optional Fields & Image -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="user_image" class="form-label"><i class="bi bi-image me-1"></i>Profile Image</label>
                                        <input type="file" id="user_image" name="user_image" class="form-control" 
                                               accept="image/jpeg,image/png,image/gif">
                                        <div class="form-text">Accepted formats: JPG, PNG, GIF. Leave empty to keep current image.</div>
                                        <?php if (!empty($current_image)): ?>
                                        <div class="mt-2">
                                            <small>Current Image: </small>
                                            <?php
                                            $image_url = "";
                                            if (!empty($current_image) && file_exists("../uploads/profiles/" . $current_image)) {
                                                $image_url = "../uploads/profiles/" . $current_image;
                                            } else {
                                                $name = urlencode(($first_name ?? '') . '+' . ($last_name ?? ''));
                                                $image_url = "https://ui-avatars.com/api/?name=$name&background=f1bf70&color=0f172a&size=40";
                                            }
                                            ?>
                                            <img src="<?php echo $image_url; ?>" 
                                                 alt="Current Profile Image" 
                                                 class="rounded-circle ms-2" width="40" height="40"
                                                 onerror="this.src='https://ui-avatars.com/api/?name=Employee&background=f1bf70&color=0f172a&size=40'">
                                        </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-3">
                                        <label for="field_of_study" class="form-label"><i class="bi bi-book me-1"></i>Field of Study</label>
                                        <input type="text" id="field_of_study" name="field_of_study" class="form-control" 
                                               value="<?php echo htmlspecialchars($field_of_study ?? ''); ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="qualification" class="form-label"><i class="bi bi-award me-1"></i>Qualification</label>
                                        <input type="text" id="qualification" name="qualification" class="form-control" 
                                               value="<?php echo htmlspecialchars($qualification ?? ''); ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="highest_graduation" class="form-label"><i class="bi bi-mortarboard me-1"></i>Highest Graduation</label>
                                        <input type="text" id="highest_graduation" name="highest_graduation" class="form-control" 
                                               value="<?php echo htmlspecialchars($highest_graduation ?? ''); ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="year_of_graduation" class="form-label"><i class="bi bi-calendar me-1"></i>Year of Graduation</label>
                                        <input type="number" id="year_of_graduation" name="year_of_graduation" class="form-control" 
                                               value="<?php echo htmlspecialchars((string)($year_of_graduation ?? '')); ?>" 
                                               min="1900" max="<?php echo date('Y'); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12 text-center">
                                    <button type="submit" name="update_employee" class="btn btn-primary btn-lg me-2">
                                        <i class="bi bi-check-circle me-1"></i> Update Employee
                                    </button>
                                    <a href="employees.php" class="btn btn-outline-secondary btn-lg">
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
</div>

<!-- Success Modal -->
<?php if (isset($show_success_modal) && $show_success_modal): ?>
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Success!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h4 class="my-3">Employee Updated Successfully!</h4>
                <p class="text-muted">The employee "<?php echo htmlspecialchars(($first_name ?? '') . ' ' . ($last_name ?? '')); ?>" has been updated.</p>
                <?php if ($password_changed): ?>
                <div class="alert alert-info mt-3 mb-0">
                    <i class="bi bi-info-circle me-2"></i>Password has been updated.
                </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <a href="employees.php" class="btn btn-success px-4">
                    <i class="bi bi-list-ul me-2"></i>View All Employees
                </a>
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                    <i class="bi bi-pencil me-2"></i>Continue Editing
                </button>
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

<style>
    .form-label {
        font-weight: 500;
        color: #0f172a;
        margin-bottom: 0.5rem;
    }
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .card-header {
        background: linear-gradient(135deg, #f1bf70 0%, #e5b465 100%);
        color: #0f172a;
        font-weight: 600;
        border-radius: 12px 12px 0 0 !important;
        padding: 1rem 1.5rem;
    }
    .btn-primary {
        background: #f1bf70;
        border-color: #f1bf70;
        color: #0f172a;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        background: #e5b465;
        border-color: #e5b465;
        color: #0f172a;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    select.form-control {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%230f172a' class='bi bi-chevron-down' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 16px;
        padding-right: 2.5rem;
    }

    /* Password Strength Meter Styles */
    .strength-meter {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 12px;
        border: 1px solid #e0e0e0;
    }

    .requirement-item {
        font-size: 0.75rem;
        color: #6c757d;
        margin-bottom: 4px;
        transition: all 0.2s ease;
    }

    .requirement-item.met {
        color: #28a745;
    }

    .requirement-item.met i {
        color: #28a745;
    }

    .requirement-item i {
        font-size: 0.7rem;
    }

    #passwordMatch {
        font-size: 0.75rem;
        margin-top: 5px;
    }

    .match-success {
        color: #28a745;
    }

    .match-error {
        color: #dc3545;
    }

    .progress-bar {
        transition: width 0.3s ease, background-color 0.3s ease;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    const strengthDiv = document.getElementById('passwordStrength');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    const passwordMatchDiv = document.getElementById('passwordMatch');
    
    // Requirement elements
    const reqLength = document.getElementById('reqLength');
    const reqUpper = document.getElementById('reqUpper');
    const reqLower = document.getElementById('reqLower');
    const reqNumber = document.getElementById('reqNumber');
    const reqSpecial = document.getElementById('reqSpecial');
    
    // Password strength validation function
    function validatePasswordStrength(password) {
        let score = 0;
        const requirements = {
            length: password.length >= 8,
            upper: /[A-Z]/.test(password),
            lower: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[^a-zA-Z0-9]/.test(password)
        };
        
        // Count met requirements
        if (requirements.length) score++;
        if (requirements.upper) score++;
        if (requirements.lower) score++;
        if (requirements.number) score++;
        if (requirements.special) score++;
        
        // Update requirement indicators
        function updateRequirement(element, isMet, text) {
            if (isMet) {
                element.innerHTML = `<i class="bi bi-check-circle-fill me-1 text-success"></i> ${text}`;
                element.classList.add('met');
            } else {
                element.innerHTML = `<i class="bi bi-circle me-1"></i> ${text}`;
                element.classList.remove('met');
            }
        }
        
        updateRequirement(reqLength, requirements.length, 'Minimum 8 characters');
        updateRequirement(reqUpper, requirements.upper, 'At least one uppercase letter');
        updateRequirement(reqLower, requirements.lower, 'At least one lowercase letter');
        updateRequirement(reqNumber, requirements.number, 'At least one number');
        updateRequirement(reqSpecial, requirements.special, 'At least one special character');
        
        // Determine strength
        let strengthColor = '';
        let width = 0;
        let strengthTextValue = '';
        
        if (score >= 5) {
            strengthColor = 'success';
            width = 100;
            strengthTextValue = 'Very Strong';
        } else if (score >= 4) {
            strengthColor = 'primary';
            width = 80;
            strengthTextValue = 'Strong';
        } else if (score >= 3) {
            strengthColor = 'warning';
            width = 60;
            strengthTextValue = 'Medium';
        } else if (score >= 2) {
            strengthColor = 'danger';
            width = 40;
            strengthTextValue = 'Weak';
        } else {
            strengthColor = 'danger';
            width = 20;
            strengthTextValue = 'Very Weak';
        }
        
        // Update strength bar
        strengthBar.className = `progress-bar bg-${strengthColor}`;
        strengthBar.style.width = `${width}%`;
        strengthText.textContent = strengthTextValue;
        strengthText.className = `fw-bold text-${strengthColor}`;
        
        return { score, requirements };
    }
    
    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirm = confirmInput.value;
        
        if (confirm.length === 0) {
            passwordMatchDiv.innerHTML = '';
            return;
        }
        
        if (password === confirm) {
            passwordMatchDiv.innerHTML = '<i class="bi bi-check-circle-fill me-1 text-success"></i> Passwords match';
            passwordMatchDiv.className = 'match-success';
        } else {
            passwordMatchDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1 text-danger"></i> Passwords do not match';
            passwordMatchDiv.className = 'match-error';
        }
    }
    
    // Real-time password strength checking (only if password field is not empty)
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        
        if (password.length > 0) {
            strengthDiv.style.display = 'block';
            validatePasswordStrength(password);
        } else {
            strengthDiv.style.display = 'none';
        }
        
        checkPasswordMatch();
    });
    
    confirmInput.addEventListener('input', checkPasswordMatch);
    
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirm = document.getElementById('toggleConfirmPassword');
    
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('bi-eye');
            this.querySelector('i').classList.toggle('bi-eye-slash');
        });
    }
    
    if (toggleConfirm) {
        toggleConfirm.addEventListener('click', function() {
            const type = confirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('bi-eye');
            this.querySelector('i').classList.toggle('bi-eye-slash');
        });
    }
});

// Form submission validation
document.getElementById('employeeForm')?.addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    
    // Only validate if password is being changed (not empty)
    if (password.length > 0) {
        // Check password strength
        if (password.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long.');
            return false;
        }
        
        // Check if password meets strength requirements (score >= 3)
        let score = 0;
        if (password.length >= 8) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^a-zA-Z0-9]/.test(password)) score++;
        
        if (score < 3) {
            e.preventDefault();
            alert('Password is too weak. Please use a stronger password that includes:\n- Minimum 8 characters\n- Uppercase and lowercase letters\n- Numbers\n- Special characters');
            return false;
        }
        
        if (password !== confirm) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }
    }
    
    return true;
});
</script>