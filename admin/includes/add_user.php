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
    
    // Validation
    if (empty($username) || empty($first_name) || empty($last_name) || empty($user_email) || empty($password)) {
        $message = "Please fill in all required fields.";
        $message_type = "danger";
    } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = "danger";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
        $message_type = "danger";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = "danger";
    } else {
        
        // Check if username exists
        $check_username = "SELECT user_id FROM users WHERE username = '$username'";
        $username_result = mysqli_query($connection, $check_username);
        
        // Check if email exists
        $check_email = "SELECT user_id FROM users WHERE user_email = '$user_email'";
        $email_result = mysqli_query($connection, $check_email);
        
        if (mysqli_num_rows($username_result) > 0) {
            $message = "Username already exists. Please choose another.";
            $message_type = "danger";
        } elseif (mysqli_num_rows($email_result) > 0) {
            $message = "Email already exists. Please use another email.";
            $message_type = "danger";
        } else {
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
                // Only insert into employees table if user type is 'employee'
                $is_employee = false;
                $is_client = false;
                $type_name = '';
                if ($type_id !== 'NULL') {
                    // Fetch the type_name for the selected type_id
                    $type_check_query = "SELECT type_name FROM user_types WHERE type_id = $type_id LIMIT 1";
                    $type_check_result = mysqli_query($connection, $type_check_query);
                    if ($type_check_result && $type_row = mysqli_fetch_assoc($type_check_result)) {
                        $type_name = strtolower($type_row['type_name']);
                        if ($type_name === 'employee') {
                            $is_employee = true;
                        } else if ($type_name === 'client') {
                            $is_client = true;
                        }
                    }
                }
                if ($is_employee) {
                    $emp_insert_query = "INSERT INTO employees (user_id, user_email, password, first_name, last_name, user_image, department_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NULL, NOW())";
                    $emp_stmt = $connection->prepare($emp_insert_query);
                    $emp_stmt->bind_param("isssss", $new_user_id, $user_email, $hashed_password, $first_name, $last_name, $user_image);
                    $emp_stmt->execute();
                    $emp_stmt->close();
                }
                // Insert into clients table if user type is 'client'
                if ($is_client) {
                    $contact_name = trim($first_name . ' ' . $last_name);
                    $company_name = $contact_name; // or use a placeholder
                    $country = 'UAE'; // or use a relevant default
                    $contact_mobile = '0000000000'; // placeholder, should be updated later
                    $client_password = $hashed_password;
                    $contact_email = $user_email;
                    // Insert required NOT NULL columns, let others default to NULL/default
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
                // No redirect
            } else {
                $message = "Error adding user: " . mysqli_error($connection);
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
                                <input type="text" id="username" name="username" class="form-control" 
                                       value="<?php echo htmlspecialchars($username); ?>" required>
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

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" id="password" name="password" class="form-control" 
                                       minlength="6" required>
                                <div class="form-text">Minimum 6 characters</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password *</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
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

<script>
document.getElementById('userForm')?.addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    const username = document.getElementById('username').value;
    
    // Validate username format (alphanumeric and underscore only)
    const usernameRegex = /^[a-zA-Z0-9_]+$/;
    if (!usernameRegex.test(username)) {
        e.preventDefault();
        alert('Username can only contain letters, numbers, and underscores.');
        return;
    }
    
    if (password !== confirm) {
        e.preventDefault();
        alert('Passwords do not match!');
    }
});
</script>