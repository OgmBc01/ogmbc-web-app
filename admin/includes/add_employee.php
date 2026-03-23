<?php

// Check if user is logged in and has appropriate permissions
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$user_id = $user_email = $password = $first_name = $last_name = '';
$field_of_study = $qualification = $highest_graduation = $year_of_graduation = $salary = '';
$department_id = '';
$message = '';
$message_type = '';

// Fetch departments for dropdown - Note: departments table uses 'id' as primary key
$departments_query = "SELECT id, dept_name FROM departments ORDER BY dept_name";
$departments_result = $connection->query($departments_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_employee'])) {
    // Get form data
    $user_email = trim($_POST['user_email']);
    $password = trim($_POST['password']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $field_of_study = trim($_POST['field_of_study'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $highest_graduation = trim($_POST['highest_graduation'] ?? '');
    $year_of_graduation = !empty($_POST['year_of_graduation']) ? intval($_POST['year_of_graduation']) : null;
    $salary = !empty($_POST['salary']) ? floatval($_POST['salary']) : 0.00;
    $department_id = !empty($_POST['department_id']) ? intval($_POST['department_id']) : null;

    // Handle file upload
    $user_image = '';
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
                $user_image = $new_filename;
            }
        }
    }
    
    // Password strength validation
    $password_strength = validatePasswordStrength($password);

    // Validate required fields
    if (empty($user_email) || empty($password) || empty($first_name) || empty($last_name) || empty($department_id)) {
        $message = "Please fill in all required fields including Department.";
        $message_type = "danger";
    } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = "danger";
    } elseif ($password_strength['score'] < 3) {
        $message = "Password is too weak. Please use a stronger password.<br>
                    <small>Requirements: " . implode(", ", $password_strength['requirements']) . "</small>";
        $message_type = "danger";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // 1. Insert into users table first
        $user_insert_sql = "INSERT INTO users (first_name, last_name, user_image, user_email, password) VALUES (?, ?, ?, ?, ?)";
        $user_stmt = $connection->prepare($user_insert_sql);
        $user_stmt->bind_param("sssss", $first_name, $last_name, $user_image, $user_email, $hashed_password);
        if ($user_stmt->execute()) {
            $new_user_id = $user_stmt->insert_id;
            $user_stmt->close();

            // 2. Insert into employees table using new user_id
            $sql = "INSERT INTO employees (user_id, user_email, password, first_name, last_name, user_image, 
                    field_of_study, qualification, highest_graduation, year_of_graduation, salary, department_id, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("issssssssidi", 
                $new_user_id, 
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
                $department_id
            );
            if ($stmt->execute()) {
                $new_employee_id = $stmt->insert_id;
                $stmt->close();
                // Show success modal
                $show_success_modal = true;
            } else {
                $message = "Failed to add employee. Error: " . $connection->error;
                $message_type = "danger";
                $stmt->close();
            }
        } else {
            $message = "Failed to add user. Error: " . $connection->error;
            $message_type = "danger";
            $user_stmt->close();
        }
    }
}

// Function to validate password strength
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
        $strength_icon = 'bi-shield-check';
    } elseif ($score >= 4) {
        $strength = 'strong';
        $strength_text = 'Strong';
        $strength_color = 'primary';
        $strength_icon = 'bi-shield-check';
    } elseif ($score >= 3) {
        $strength = 'medium';
        $strength_text = 'Medium';
        $strength_color = 'warning';
        $strength_icon = 'bi-shield-exclamation';
    } elseif ($score >= 2) {
        $strength = 'weak';
        $strength_text = 'Weak';
        $strength_color = 'danger';
        $strength_icon = 'bi-shield-x';
    } else {
        $strength = 'very_weak';
        $strength_text = 'Very Weak';
        $strength_color = 'danger';
        $strength_icon = 'bi-shield-x';
    }
    
    return [
        'score' => $score,
        'strength' => $strength,
        'strength_text' => $strength_text,
        'strength_color' => $strength_color,
        'strength_icon' => $strength_icon,
        'requirements' => $requirements
    ];
}
?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Add New Employee</h1>
            <a href="employees.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Employees
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Employee Information</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="" enctype="multipart/form-data" id="employeeForm">
                            <div class="row">
                                <!-- Left Column - Required Fields -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="department_id" class="form-label"><i class="bi bi-building me-1"></i>Department *</label>
                                        <select id="department_id" name="department_id" class="form-control" required>
                                            <option value="">Select Department</option>
                                            <?php
                                            if ($departments_result && $departments_result->num_rows > 0) {
                                                mysqli_data_seek($departments_result, 0);
                                                while ($dept = $departments_result->fetch_assoc()) {
                                                    $selected = ($department_id == $dept['id']) ? 'selected' : '';
                                                    echo "<option value='" . $dept['id'] . "' $selected>" . 
                                                         htmlspecialchars($dept['dept_name']) . "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="user_email" class="form-label"><i class="bi bi-envelope me-1"></i>Email *</label>
                                             <input type="email" id="user_email" name="user_email" class="form-control" value="" autocomplete="off" required>
                                    </div>

                                    <!-- Password Section with Strength Meter -->
                                    <div class="mb-3">
                                        <label for="password" class="form-label"><i class="bi bi-lock me-1"></i>Password *</label>
                                        <div class="input-group">
                                              <input type="password" id="password" name="password" class="form-control" value="" autocomplete="new-password" minlength="8" required>
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
                                    </div>

                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label"><i class="bi bi-lock-fill me-1"></i>Confirm Password *</label>
                                        <div class="input-group">
                                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" value="" autocomplete="new-password" required>
                                            <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                        <div id="passwordMatch" class="mt-1"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="first_name" class="form-label"><i class="bi bi-person me-1"></i>First Name *</label>
                                        <input type="text" id="first_name" name="first_name" class="form-control" 
                                               value="<?php echo htmlspecialchars($first_name); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="last_name" class="form-label"><i class="bi bi-person me-1"></i>Last Name *</label>
                                        <input type="text" id="last_name" name="last_name" class="form-control" 
                                               value="<?php echo htmlspecialchars($last_name); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="salary" class="form-label"><i class="bi bi-cash me-1"></i>Salary (AED)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">AED</span>
                                            <input type="number" step="0.01" id="salary" name="salary" class="form-control" 
                                                   value="<?php echo htmlspecialchars($salary ?: '0.00'); ?>">
                                        </div>
                                        <div class="form-text">Enter employee's monthly salary in AED</div>
                                    </div>
                                </div>

                                <!-- Right Column - Optional Fields & Image -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="user_image" class="form-label"><i class="bi bi-image me-1"></i>Profile Image</label>
                                        <input type="file" id="user_image" name="user_image" class="form-control" 
                                               accept="image/jpeg,image/png,image/gif">
                                        <div class="form-text">Accepted formats: JPG, PNG, GIF. Max size: 2MB</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="field_of_study" class="form-label"><i class="bi bi-book me-1"></i>Field of Study</label>
                                        <input type="text" id="field_of_study" name="field_of_study" class="form-control" 
                                               value="<?php echo htmlspecialchars($field_of_study); ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="qualification" class="form-label"><i class="bi bi-award me-1"></i>Qualification</label>
                                        <input type="text" id="qualification" name="qualification" class="form-control" 
                                               value="<?php echo htmlspecialchars($qualification); ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="highest_graduation" class="form-label"><i class="bi bi-mortarboard me-1"></i>Highest Graduation</label>
                                        <input type="text" id="highest_graduation" name="highest_graduation" class="form-control" 
                                               value="<?php echo htmlspecialchars($highest_graduation); ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="year_of_graduation" class="form-label"><i class="bi bi-calendar me-1"></i>Year of Graduation</label>
                                        <input type="number" id="year_of_graduation" name="year_of_graduation" class="form-control" 
                                               value="<?php echo htmlspecialchars($year_of_graduation); ?>" 
                                               min="1900" max="<?php echo date('Y'); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12 text-center">
                                    <button type="submit" name="add_employee" class="btn btn-primary btn-lg me-2">
                                        <i class="bi bi-check-circle me-1"></i> Add Employee
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary btn-lg" onclick="resetForm()">
                                        <i class="bi bi-x-circle me-1"></i> Reset
                                    </button>
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
                <h4 class="my-3">Employee Added Successfully!</h4>
                <p class="text-muted">The employee "<?php echo htmlspecialchars($first_name . ' ' . $last_name); ?>" has been added.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <a href="employees.php" class="btn btn-success px-4">
                    <i class="bi bi-list-ul me-2"></i>View All Employees
                </a>
                <a href="employees.php?source=add_employee" class="btn btn-outline-success px-4">
                    <i class="bi bi-plus-circle me-2"></i>Add Another Employee
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
    
    /* Progress bar animations */
    .progress-bar {
        transition: width 0.3s ease, background-color 0.3s ease;
    }
    
    /* Strength bar colors */
    .progress-bar.bg-success { background-color: #198754 !important; }
    .progress-bar.bg-primary { background-color: #0d6efd !important; }
    .progress-bar.bg-warning { background-color: #ffc107 !important; }
    .progress-bar.bg-danger { background-color: #dc3545 !important; }
    
    .input-group-text {
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        color: #0f172a;
        font-weight: 500;
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
        updateRequirement(reqLength, requirements.length, 'Minimum 8 characters');
        updateRequirement(reqUpper, requirements.upper, 'At least one uppercase letter');
        updateRequirement(reqLower, requirements.lower, 'At least one lowercase letter');
        updateRequirement(reqNumber, requirements.number, 'At least one number');
        updateRequirement(reqSpecial, requirements.special, 'At least one special character');
        
        // Determine strength
        let strength = '';
        let strengthTextValue = '';
        let strengthColor = '';
        let width = 0;
        
        if (score >= 5) {
            strength = 'very-strong';
            strengthTextValue = 'Very Strong';
            strengthColor = 'success';
            width = 100;
        } else if (score >= 4) {
            strength = 'strong';
            strengthTextValue = 'Strong';
            strengthColor = 'primary';
            width = 80;
        } else if (score >= 3) {
            strength = 'medium';
            strengthTextValue = 'Medium';
            strengthColor = 'warning';
            width = 60;
        } else if (score >= 2) {
            strength = 'weak';
            strengthTextValue = 'Weak';
            strengthColor = 'danger';
            width = 40;
        } else {
            strength = 'very-weak';
            strengthTextValue = 'Very Weak';
            strengthColor = 'danger';
            width = 20;
        }
        
        // Update strength bar
        strengthBar.className = `progress-bar bg-${strengthColor}`;
        strengthBar.style.width = `${width}%`;
        strengthText.textContent = strengthTextValue;
        strengthText.className = `fw-bold text-${strengthColor}`;
        
        return { score, strength, requirements };
    }
    
    function updateRequirement(element, isMet, text) {
        if (isMet) {
            element.innerHTML = `<i class="bi bi-check-circle-fill me-1 text-success"></i> ${text}`;
            element.classList.add('met');
        } else {
            element.innerHTML = `<i class="bi bi-circle me-1"></i> ${text}`;
            element.classList.remove('met');
        }
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
    
    // Real-time password strength checking
    if (passwordInput) {
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
    }
    
    if (confirmInput) {
        confirmInput.addEventListener('input', checkPasswordMatch);
    }
    
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
    const email = document.getElementById('user_email').value;
    const department = document.getElementById('department_id').value;
    const firstName = document.getElementById('first_name').value;
    const lastName = document.getElementById('last_name').value;
    
    // Validate required fields
    if (!firstName || !lastName || !email || !department || !password) {
        e.preventDefault();
        alert('Please fill in all required fields.');
        return false;
    }
    
    // Validate email format
    const emailRegex = /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('Please enter a valid email address.');
        return false;
    }
    
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
    
    return true;
});

function resetForm() {
    if (confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
        document.getElementById('employeeForm').reset();
        // Reset password strength display
        const strengthDiv = document.getElementById('passwordStrength');
        if (strengthDiv) strengthDiv.style.display = 'none';
        const passwordMatchDiv = document.getElementById('passwordMatch');
        if (passwordMatchDiv) passwordMatchDiv.innerHTML = '';
    }
}
</script>