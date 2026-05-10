<?php
ob_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$username = $first_name = $last_name = $user_email = '';
$role_id = $type_id = '';
$user_status = 'active';
$message = '';
$message_type = '';
$showSuccessModal = false;
$new_user_id = null;

// Fetch roles for dropdown
$roles_query = "SELECT * FROM user_roles ORDER BY role_level DESC";
$roles_result = mysqli_query($connection, $roles_query);

// Fetch types for dropdown
$types_query = "SELECT * FROM user_types ORDER BY type_name";
$types_result = mysqli_query($connection, $types_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_user'])) {
    
    $username = mysqli_real_escape_string($connection, trim($_POST['username']));
    $first_name = mysqli_real_escape_string($connection, trim($_POST['first_name']));
    $last_name = mysqli_real_escape_string($connection, trim($_POST['last_name']));
    $user_email = mysqli_real_escape_string($connection, trim($_POST['user_email']));
    $role_id = !empty($_POST['role_id']) ? (int)$_POST['role_id'] : 'NULL';
    $type_id = !empty($_POST['type_id']) ? (int)$_POST['type_id'] : 'NULL';
    $user_status = mysqli_real_escape_string($connection, $_POST['user_status']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Handle file upload
    $user_image = $_FILES['user_image']['name'];
    $user_image_temp = $_FILES['user_image']['tmp_name'];
    
    // Password strength validation
    $password_strength = validatePasswordStrength($password);
    
    // Reset message
    $message = '';
    $message_type = '';
    $can_proceed = true;
    
    // ============================================
    // FIRST: Check for empty required fields
    // ============================================
    if (empty($username) || empty($first_name) || empty($last_name) || empty($user_email) || empty($password)) {
        $message = "Please fill in all required fields.";
        $message_type = "danger";
        $can_proceed = false;
    }
    
    // ============================================
    // SECOND: Validate username format
    // ============================================
    if ($can_proceed && !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $message = "Username can only contain letters, numbers, and underscores.";
        $message_type = "danger";
        $can_proceed = false;
    }
    
    // ============================================
    // THIRD: Validate email format
    // ============================================
    if ($can_proceed && !filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = "danger";
        $can_proceed = false;
    }
    
    // ============================================
    // FOURTH: Validate password strength
    // ============================================
    if ($can_proceed && $password_strength['score'] < 3) {
        $message = "Password is too weak. Please use a stronger password.<br>
                    <small>Requirements: " . implode(", ", $password_strength['requirements']) . "</small>";
        $message_type = "danger";
        $can_proceed = false;
    }
    
    // ============================================
    // FIFTH: Check if passwords match
    // ============================================
    if ($can_proceed && $password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = "danger";
        $can_proceed = false;
    }
    
    // ============================================
    // SIXTH: Check for duplicate username
    // ============================================
    if ($can_proceed) {
        $check_username = "SELECT user_id FROM users WHERE username = '$username'";
        $username_result = mysqli_query($connection, $check_username);
        if (mysqli_num_rows($username_result) > 0) {
            $message = "Username already exists. Please choose another.";
            $message_type = "danger";
            $can_proceed = false;
        }
    }
    
    // ============================================
    // SEVENTH: Check for duplicate email
    // ============================================
    if ($can_proceed) {
        $check_email = "SELECT user_id FROM users WHERE user_email = '$user_email'";
        $email_result = mysqli_query($connection, $check_email);
        if (mysqli_num_rows($email_result) > 0) {
            $message = "Email already exists. Please use another email.";
            $message_type = "danger";
            $can_proceed = false;
        }
    }
    
    // ============================================
    // EIGHTH: Check for duplicate first + last name
    // ============================================
    if ($can_proceed && !empty($first_name) && !empty($last_name)) {
        $dup_query = "SELECT user_id FROM users WHERE first_name = '$first_name' AND last_name = '$last_name'";
        $dup_result = mysqli_query($connection, $dup_query);
        if (mysqli_num_rows($dup_result) > 0) {
            $message = "A user with the name '$first_name $last_name' already exists. Please use a different name.";
            $message_type = "danger";
            $can_proceed = false;
        }
    }
    
    // ============================================
    // NINTH: All validations passed - proceed with insertion
    // ============================================
    if ($can_proceed) {
        // Upload image
        if (!empty($user_image)) {
            $target_dir = "../images/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $image_name = time() . '_' . basename($user_image);
            $target_file = $target_dir . $image_name;
            if (move_uploaded_file($user_image_temp, $target_file)) {
                $user_image = $image_name;
            } else {
                $user_image = '';
            }
        } else {
            $user_image = '';
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $role_id_value = ($role_id !== 'NULL') ? $role_id : 'NULL';
        $type_id_value = ($type_id !== 'NULL') ? $type_id : 'NULL';
        
        $insert_query = "INSERT INTO users (username, first_name, last_name, user_email, password, 
                          user_image, role_id, type_id, user_status) 
                         VALUES ('$username', '$first_name', '$last_name', '$user_email', '$hashed_password', 
                                 '$user_image', $role_id_value, $type_id_value, '$user_status')";
        
        if (mysqli_query($connection, $insert_query)) {
            $new_user_id = mysqli_insert_id($connection);
            
            // Only insert into employees table if user type is 'operations' (type_id=1)
            $is_operations = false;
            $is_client = false;
            $type_name = '';
            if ($type_id !== 'NULL') {
                // Fetch the type_name for the selected type_id
                $type_check_query = "SELECT type_name FROM user_types WHERE type_id = $type_id LIMIT 1";
                $type_check_result = mysqli_query($connection, $type_check_query);
                if ($type_check_result && $type_row = mysqli_fetch_assoc($type_check_result)) {
                    $type_name = strtolower($type_row['type_name']);
                    if ($type_name === 'operations' || $type_id == 1) {
                        $is_operations = true;
                    } else if ($type_name === 'client' || $type_id == 2) {
                        $is_client = true;
                    }
                }
            }
            
            if ($is_operations || $type_id == 8 || $type_name === 'sales') {
                $emp_insert_query = "INSERT INTO employees (user_id, user_email, password, first_name, last_name, user_image, department_id, created_at, user_type) VALUES (?, ?, ?, ?, ?, ?, NULL, NOW(), ?)";
                $emp_stmt = $connection->prepare($emp_insert_query);
                $user_type = ($is_operations ? 'operations' : 'sales');
                $emp_stmt->bind_param("issssss", $new_user_id, $user_email, $hashed_password, $first_name, $last_name, $user_image, $user_type);
                $emp_stmt->execute();
                $emp_stmt->close();
            }
            
            // Insert into clients table if user type is 'client'
            if ($is_client) {
                $contact_name = trim($first_name . ' ' . $last_name);
                $company_name = $contact_name;
                $country = 'UAE';
                $contact_mobile = '0000000000';
                $client_password = $hashed_password;
                $contact_email = $user_email;
                $client_insert_query = "INSERT INTO clients (user_id, company_name, country, contact_name, contact_mobile, contact_email, client_password, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
                $client_stmt = $connection->prepare($client_insert_query);
                $client_stmt->bind_param("issssss", $new_user_id, $company_name, $country, $contact_name, $contact_mobile, $contact_email, $client_password);
                $client_stmt->execute();
                $client_stmt->close();
            }
            
            $showSuccessModal = true;
            // Clear form data
            $username = $first_name = $last_name = $user_email = '';
            $role_id = $type_id = '';
            $user_status = 'active';
        } else {
            $message = "Error adding user: " . mysqli_error($connection);
            $message_type = "danger";
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

ob_end_flush();
?>

<!-- Rest of your HTML remains the same -->

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add New User</h5>
                    <a href="users.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Users
                    </a>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($message) && !$showSuccessModal): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data" id="userForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username *</label>
                                    <input type="text" id="username" name="username" class="form-control" value="" autocomplete="off" required>
                                <small class="text-muted">Letters, numbers, and underscores only</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="user_email" class="form-label">Email *</label>
                                <input type="email" id="user_email" name="user_email" class="form-control" 
                                       value="<?php echo htmlspecialchars($user_email); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" id="first_name" name="first_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($first_name); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($last_name); ?>" required>
                            </div>
                        </div>

                        <!-- Password Section with Strength Meter -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password *</label>
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
                                <div class="form-text">Password must be strong. See requirements above.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password *</label>
                                <div class="input-group">
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" value="" autocomplete="new-password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div id="passwordMatch" class="mt-1"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="role_id" class="form-label">User Role</label>
                                <select id="role_id" name="role_id" class="form-control">
                                    <option value="">Select Role</option>
                                    <?php 
                                    mysqli_data_seek($roles_result, 0);
                                    while($role = mysqli_fetch_assoc($roles_result)): 
                                    ?>
                                    <option value="<?php echo $role['role_id']; ?>" 
                                        <?php echo ($role_id == $role['role_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($role['role_name']); ?> (Level <?php echo $role['role_level']; ?>)
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="type_id" class="form-label">User Type</label>
                                <select id="type_id" name="type_id" class="form-control">
                                    <option value="">Select Type</option>
                                    <?php 
                                    mysqli_data_seek($types_result, 0);
                                    while($type = mysqli_fetch_assoc($types_result)): 
                                    ?>
                                    <option value="<?php echo $type['type_id']; ?>" 
                                        <?php echo ($type_id == $type['type_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($type['type_name']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="user_status" class="form-label">Status</label>
                                <select id="user_status" name="user_status" class="form-control">
                                    <option value="active" <?php echo ($user_status == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($user_status == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="suspended" <?php echo ($user_status == 'suspended') ? 'selected' : ''; ?>>Suspended</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="user_image" class="form-label">Profile Image</label>
                                <input type="file" id="user_image" name="user_image" class="form-control" accept="image/*">
                                <div class="form-text">Leave empty to use default image</div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="submit_user" class="btn btn-primary btn-lg me-2">
                                    <i class="bi bi-check-circle me-1"></i> Add User
                                </button>
                                <a href="users.php" class="btn btn-outline-secondary btn-lg">
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

<?php if ($showSuccessModal && $new_user_id): ?>
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
          <h5 class="mt-3">User Added Successfully!</h5>
          <p class="text-muted mb-0">The user "<?php echo htmlspecialchars($username); ?>" has been created.</p>
        </div>
      </div>
      <div class="modal-footer justify-content-center border-0 pt-0">
        <a href="users.php" class="btn btn-success px-4">
          <i class="bi bi-list-ul me-2"></i>View All Users
        </a>
        <a href="users.php?source=add_user" class="btn btn-outline-success px-4">
          <i class="bi bi-plus-circle me-2"></i>Add Another User
        </a>
        <a href="users.php?source=edit_user&id=<?php echo $new_user_id; ?>" class="btn btn-outline-primary px-4">
          <i class="bi bi-pencil me-2"></i>Edit This User
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
.progress-bar.very-weak { background-color: #dc3545; width: 20%; }
.progress-bar.weak { background-color: #fd7e14; width: 40%; }
.progress-bar.medium { background-color: #ffc107; width: 60%; }
.progress-bar.strong { background-color: #0d6efd; width: 80%; }
.progress-bar.very-strong { background-color: #198754; width: 100%; }
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
    
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('bi-eye');
        this.querySelector('i').classList.toggle('bi-eye-slash');
    });
    
    toggleConfirm.addEventListener('click', function() {
        const type = confirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('bi-eye');
        this.querySelector('i').classList.toggle('bi-eye-slash');
    });
});

// Form submission validation
document.getElementById('userForm')?.addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    const username = document.getElementById('username').value;
    
    // Validate username format (alphanumeric and underscore only)
    const usernameRegex = /^[a-zA-Z0-9_]+$/;
    if (!usernameRegex.test(username)) {
        e.preventDefault();
        alert('Username can only contain letters, numbers, and underscores.');
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
</script>