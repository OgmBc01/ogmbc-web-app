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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    
    $username = mysqli_real_escape_string($connection, trim($_POST['username']));
    $first_name = mysqli_real_escape_string($connection, trim($_POST['first_name']));
    $last_name = mysqli_real_escape_string($connection, trim($_POST['last_name']));
    $user_email = mysqli_real_escape_string($connection, trim($_POST['user_email']));
    $role_id = !empty($_POST['role_id']) ? (int)$_POST['role_id'] : 'NULL';
    $type_id = !empty($_POST['type_id']) ? (int)$_POST['type_id'] : 'NULL';
    $user_status = mysqli_real_escape_string($connection, $_POST['user_status']);
    
    // Handle file upload
    $user_image = $_FILES['user_image']['name'];
    $user_image_temp = $_FILES['user_image']['tmp_name'];
    
    // Validation
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
            if (!empty($_POST['password'])) {
                if (strlen($_POST['password']) < 6) {
                    $message = "Password must be at least 6 characters long.";
                    $message_type = "danger";
                    ob_end_flush();
                    return;
                }
                if ($_POST['password'] !== $_POST['confirm_password']) {
                    $message = "Passwords do not match.";
                    $message_type = "danger";
                    ob_end_flush();
                    return;
                }
                $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
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
            } else {
                $update_query = "UPDATE users SET 
                                 username = '$username',
                                 first_name = '$first_name',
                                 last_name = '$last_name',
                                 user_email = '$user_email',
                                 user_image = '$user_image',
                                 role_id = $role_id_value,
                                 type_id = $type_id_value,
                                 user_status = '$user_status'
                                 WHERE user_id = $user_id";
            }
            
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

ob_end_flush();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit User</h5>
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

                    <div class="text-center mb-3">
                        <img src="../images/<?php echo $user['user_image'] ?: 'default.jpg'; ?>" 
                             class="rounded-circle" width="100" height="100" alt="User Image">
                    </div>

                    <form method="POST" action="" enctype="multipart/form-data" id="editUserForm">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username *</label>
                                <input type="text" id="username" name="username" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>" required>
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

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" id="password" name="password" class="form-control" minlength="6">
                                <div class="form-text">Leave empty to keep current password</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control">
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
        </div>
      </div>
      <div class="modal-footer justify-content-center border-0 pt-0">
        <a href="users.php" class="btn btn-success px-4">
          <i class="bi bi-list-ul me-2"></i>View All Users
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

<script>
document.getElementById('editUserForm')?.addEventListener('submit', function(e) {
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
    
    if (password || confirm) {
        if (password !== confirm) {
            e.preventDefault();
            alert('Passwords do not match!');
        }
        if (password.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters long!');
        }
    }
});
</script>