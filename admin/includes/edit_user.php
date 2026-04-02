<?php
ob_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header("Location: ../login.php");
    exit();
}

// Get user ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid user ID.";
    ob_end_clean();
    header("Location: users.php");
    exit();
}

$user_id = (int)$_GET['id'];
$message = '';
$message_type = '';
$showSuccessModal = false;

// Fetch roles for dropdown
$roles_query = "SELECT * FROM user_roles ORDER BY role_level DESC";
$roles_result = mysqli_query($connection, $roles_query);

// Fetch types for dropdown
$types_query = "SELECT * FROM user_types ORDER BY type_name";
$types_result = mysqli_query($connection, $types_query);

// Fetch user data
$stmt = $connection->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "User not found.";
    ob_end_clean();
    header("Location: users.php");
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    
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
    
    // Validation
    $first_name_check = mysqli_real_escape_string($connection, trim($first_name));
    $last_name_check = mysqli_real_escape_string($connection, trim($last_name));
    $dup_query = "SELECT user_id FROM users WHERE first_name = '$first_name_check' AND last_name = '$last_name_check' AND user_id != $user_id";
    $dup_result = mysqli_query($connection, $dup_query);
    if (mysqli_num_rows($dup_result) > 0) {
        $message = "A user with this first and last name already exists.";
        $message_type = "danger";
    }
    if (empty($username) || empty($first_name) || empty($last_name) || empty($user_email)) {
        $message = "Please fill in all required fields.";
        $message_type = "danger";
    } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = "danger";
    } else {
        
        // Check if username exists (excluding current user)
        $check_username = "SELECT user_id FROM users WHERE username = '$username' AND user_id != $user_id";
        $username_result = mysqli_query($connection, $check_username);
        
        // Check if email exists (excluding current user)
        $check_email = "SELECT user_id FROM users WHERE user_email = '$user_email' AND user_id != $user_id";
        $email_result = mysqli_query($connection, $check_email);
        
        if (mysqli_num_rows($username_result) > 0) {
            $message = "Username already exists. Please choose another.";
            $message_type = "danger";
        } elseif (mysqli_num_rows($email_result) > 0) {
            $message = "Email already exists. Please use another email.";
            $message_type = "danger";
        } else {
            // Check if password is being changed
            $password_changed = false;
            $hashed_password = $user['password']; // Keep current password by default
            
            if (!empty($password)) {
                // Validate password strength
                $password_strength = validatePasswordStrength($password);
                
                if ($password_strength['score'] < 3) {
                    $message = "Password is too weak. Please use a stronger password.<br>
                                <small>Requirements: " . implode(", ", $password_strength['requirements']) . "</small>";
                    $message_type = "danger";
                } elseif ($password !== $confirm_password) {
                    $message = "Passwords do not match.";
                    $message_type = "danger";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $password_changed = true;
                }
            }
            
            if (empty($message)) {
                // Upload new image if provided
                if (!empty($user_image)) {
                    $target_dir = "../images/";
                    $image_name = time() . '_' . basename($user_image);
                    $target_file = $target_dir . $image_name;
                    
                    if (move_uploaded_file($user_image_temp, $target_file)) {
                        // Delete old image if not default
                        if ($user['user_image'] && $user['user_image'] != 'default.jpg') {
                            @unlink($target_dir . $user['user_image']);
                        }
                        $user_image = $image_name;
                    } else {
                        $user_image = $user['user_image'];
                    }
                } else {
                    $user_image = $user['user_image'];
                }
                
                $role_id_value = ($role_id !== 'NULL') ? $role_id : 'NULL';
                $type_id_value = ($type_id !== 'NULL') ? $type_id : 'NULL';
                
                // Build query (password update optional)
                $update_query = "UPDATE users SET 
                                 username = '$username',
                                 first_name = '$first_name',
                                 last_name = '$last_name',
                                 user_email = '$user_email',
                                 password = '$hashed_password',
                                 user_image = '$user_image',
                                 role_id = $role_id_value,
                                 type_id = $type_id_value,
                                 user_status = '$user_status'
                                 WHERE user_id = $user_id";
                
                if (mysqli_query($connection, $update_query)) {
                    $showSuccessModal = true;
                    // Refresh user data after update
                    $refresh_stmt = $connection->prepare("SELECT * FROM users WHERE user_id = ?");
                    $refresh_stmt->bind_param("i", $user_id);
                    $refresh_stmt->execute();
                    $refresh_result = $refresh_stmt->get_result();
                    $user = $refresh_result->fetch_assoc();
                    $refresh_stmt->close();
                    // Clear any previous messages
                    $message = '';
                    $message_type = '';
                    // No redirect
                } else {
                    $message = "Error updating user: " . mysqli_error($connection);
                    $message_type = "danger";
                }
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
                <div class="card-header d-flex justify-content-between align-items-center" style="background: #0a2240; color: #f1bf70;">
                    <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit User</h5>
                    <a href="users.php" class="btn btn-outline-light btn-sm">
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

                    <div class="text-center mb-3">
                        <img src="../images/<?php echo $user['user_image'] ?: 'default.jpg'; ?>" 
                             class="rounded-circle" width="100" height="100" alt="User Image"
                             onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($user['first_name'] . '+' . $user['last_name']); ?>&background=f1bf70&color=0a2240&size=100'">
                    </div>

                    <form method="POST" action="" enctype="multipart/form-data" id="editUserForm">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username *</label>
                                <input type="text" id="username" name="username" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                <small class="text-muted">Letters, numbers, and underscores only</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="user_email" class="form-label">Email *</label>
                                <input type="email" id="user_email" name="user_email" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['user_email']); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" id="first_name" name="first_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>

                        <!-- Password Section with Strength Meter -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">New Password</label>
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
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <div class="input-group">
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" autocomplete="new-password">
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
                                        <?php echo ($user['role_id'] == $role['role_id']) ? 'selected' : ''; ?>>
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
                                        <?php echo ($user['type_id'] == $type['type_id']) ? 'selected' : ''; ?>>
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
                                    <option value="active" <?php echo ($user['user_status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($user['user_status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="suspended" <?php echo ($user['user_status'] == 'suspended') ? 'selected' : ''; ?>>Suspended</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="user_image" class="form-label">Profile Image</label>
                                <input type="file" id="user_image" name="user_image" class="form-control" accept="image/*">
                                <div class="form-text">Leave empty to keep current image</div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="update_user" class="btn btn-primary btn-lg me-2">
                                    <i class="bi bi-check-circle me-1"></i> Update User
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

<?php if ($showSuccessModal): ?>
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
          <h5 class="mt-3">User Updated Successfully!</h5>
          <p class="text-muted mb-0">The user "<?php echo htmlspecialchars($user['username']); ?>" has been updated.</p>
          <?php if (isset($password_changed) && $password_changed): ?>
          <div class="alert alert-info mt-3 mb-0">
            <i class="bi bi-info-circle me-2"></i>Password has been updated.
          </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="modal-footer justify-content-center border-0 pt-0">
        <a href="users.php" class="btn btn-success px-4">
          <i class="bi bi-list-ul me-2"></i>View All Users
        </a>
        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal" id="continueEditingBtn">
          <i class="bi bi-pencil me-2"></i>Continue Editing
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var modalElement = document.getElementById('successModal');
    if (modalElement) {
      var modalInstance = bootstrap.Modal.getInstance(modalElement);
      if (!modalInstance) {
        modalInstance = new bootstrap.Modal(modalElement, {
          backdrop: 'static',
          keyboard: false
        });
      }
      modalInstance.show();
      
      var continueBtn = document.getElementById('continueEditingBtn');
      if (continueBtn) {
        continueBtn.addEventListener('click', function() {
          modalInstance.hide();
          setTimeout(function() {
            var backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(function(backdrop) { backdrop.remove(); });
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
          }, 150);
        });
      }
    }
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

.progress-bar {
    transition: width 0.3s ease, background-color 0.3s ease;
}

.card-header {
    background: linear-gradient(135deg, #f1bf70 0%, #e5b465 100%);
    color: #0f172a;
    font-weight: 600;
}

.btn-primary {
    background: #f1bf70;
    border-color: #f1bf70;
    color: #0f172a;
    font-weight: 600;
}

.btn-primary:hover {
    background: #e5b465;
    border-color: #e5b465;
    color: #0f172a;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
    
    const reqLength = document.getElementById('reqLength');
    const reqUpper = document.getElementById('reqUpper');
    const reqLower = document.getElementById('reqLower');
    const reqNumber = document.getElementById('reqNumber');
    const reqSpecial = document.getElementById('reqSpecial');
    
    function validatePasswordStrength(password) {
        let score = 0;
        const requirements = {
            length: password.length >= 8,
            upper: /[A-Z]/.test(password),
            lower: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[^a-zA-Z0-9]/.test(password)
        };
        
        if (requirements.length) score++;
        if (requirements.upper) score++;
        if (requirements.lower) score++;
        if (requirements.number) score++;
        if (requirements.special) score++;
        
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
        
        strengthBar.className = `progress-bar bg-${strengthColor}`;
        strengthBar.style.width = `${width}%`;
        strengthText.textContent = strengthTextValue;
        strengthText.className = `fw-bold text-${strengthColor}`;
        
        return { score };
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
document.getElementById('editUserForm')?.addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    const username = document.getElementById('username').value;
    
    const usernameRegex = /^[a-zA-Z0-9_]+$/;
    if (!usernameRegex.test(username)) {
        e.preventDefault();
        alert('Username can only contain letters, numbers, and underscores.');
        return false;
    }
    
    if (password.length > 0 || confirm.length > 0) {
        if (password.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long.');
            return false;
        }
        
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