<?php
ob_start();

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    echo "<script>window.location.href = '../login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current user data - ONLY columns that definitely exist
$query = "SELECT user_id, first_name, last_name, user_email, username, user_image, created_at 
          FROM users 
          WHERE user_id = $user_id";
$result = mysqli_query($connection, $query);

if (!$result) {
    die("Error fetching user: " . mysqli_error($connection));
}

$user = mysqli_fetch_assoc($result);

$message = '';
$message_type = '';
$showSuccessModal = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    
    $first_name = mysqli_real_escape_string($connection, trim($_POST['first_name']));
    $last_name = mysqli_real_escape_string($connection, trim($_POST['last_name']));
    $user_email = mysqli_real_escape_string($connection, trim($_POST['user_email']));
    $current_image = $user['user_image'];
    
    // Handle file upload
    $user_image = $current_image;
    if (isset($_FILES['user_image']) && $_FILES['user_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['user_image'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        
        // Get file extension
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($ext, $allowed)) {
            // Check file size (max 2MB)
            if ($file_size <= 2 * 1024 * 1024) {
                $upload_dir = "../uploads/profiles/";
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Generate unique filename
                $new_filename = "profile_" . $user_id . "_" . time() . "." . $ext;
                $target_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($file_tmp, $target_path)) {
                    // Delete old image if it exists and is not the default
                    if (!empty($current_image) && file_exists($upload_dir . $current_image)) {
                        unlink($upload_dir . $current_image);
                    }
                    $user_image = $new_filename;
                }
            }
        }
    }
    
    // Validate inputs
    $errors = [];
    
    if (empty($first_name)) {
        $errors[] = "First name is required.";
    }
    
    if (empty($last_name)) {
        $errors[] = "Last name is required.";
    }
    
    if (empty($user_email)) {
        $errors[] = "Email address is required.";
    } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    
    if (empty($errors)) {
        // Check if email already exists for another user
        $check_email = "SELECT user_id FROM users WHERE user_email = '$user_email' AND user_id != $user_id";
        $email_result = mysqli_query($connection, $check_email);
        
        if (mysqli_num_rows($email_result) > 0) {
            $message = "Email address already in use by another account.";
            $message_type = "danger";
        } else {
            // Update users table - ONLY columns that exist
            $update_user = "UPDATE users SET 
                           first_name = '$first_name',
                           last_name = '$last_name',
                           user_email = '$user_email',
                           user_image = '$user_image'
                           WHERE user_id = $user_id";
            
            if (mysqli_query($connection, $update_user)) {
                // Update session
                $_SESSION['first_name'] = $first_name;
                $_SESSION['user_image'] = $user_image;
                
                $showSuccessModal = true;
                
                // Refresh user data
                $refresh_query = "SELECT user_id, first_name, last_name, user_email, username, user_image 
                                 FROM users WHERE user_id = $user_id";
                $refresh_result = mysqli_query($connection, $refresh_query);
                $user = mysqli_fetch_assoc($refresh_result);
            } else {
                $message = "Error updating profile: " . mysqli_error($connection);
                $message_type = "danger";
            }
        }
    } else {
        $message = implode(" ", $errors);
        $message_type = "danger";
    }
}

ob_end_flush();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm" style="border-radius: 12px; border: none;">
                <div class="card-header" style="background: #1e293b; color: #fff; border-radius: 12px 12px 0 0; padding: 15px 20px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Profile</h5>
                        <a href="profile.php" class="btn btn-sm btn-light">
                            <i class="bi bi-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    
                    <?php if (!empty($message) && !$showSuccessModal): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data" id="profileForm">
                        <!-- Profile Image Upload -->
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <img src="<?php 
                                    if (!empty($user['user_image']) && file_exists('../uploads/profiles/' . $user['user_image'])) {
                                        echo '../uploads/profiles/' . $user['user_image'];
                                    } else {
                                        $name = urlencode(($user['first_name'] ?? 'User') . ' ' . ($user['last_name'] ?? ''));
                                        echo "https://ui-avatars.com/api/?name=$name&background=f1bf70&color=0f172a&size=150";
                                    }
                                ?>" alt="Profile" class="rounded-circle border border-3 border-primary" 
                                     style="width: 150px; height: 150px; object-fit: cover;" id="imagePreview">
                                <div class="mt-2">
                                    <label for="user_image" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-camera me-1"></i>Change Photo
                                    </label>
                                    <input type="file" id="user_image" name="user_image" accept="image/*" style="display: none;" onchange="previewImage(this)">
                                </div>
                                <small class="text-muted d-block mt-1">Max size: 2MB (JPG, PNG, GIF)</small>
                            </div>
                        </div>

                        <!-- Form Fields -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label fw-medium">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label fw-medium">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="user_email" class="form-label fw-medium">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="user_email" name="user_email" 
                                   value="<?php echo htmlspecialchars($user['user_email'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">Username</label>
                            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" readonly disabled>
                            <small class="text-muted">Username cannot be changed</small>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" name="update_profile" class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-check-circle me-2"></i>Save Changes
                            </button>
                            <a href="profile.php" class="btn btn-outline-secondary btn-lg px-5 ms-2">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($showSuccessModal): ?>
<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Success!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h5 class="mt-3">Profile Updated Successfully!</h5>
                <p class="text-muted mb-0">Your changes have been saved.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <a href="profile.php" class="btn btn-success px-4">
                    <i class="bi bi-person me-2"></i>View Profile
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
        var modal = new bootstrap.Modal(document.getElementById('successModal'));
        modal.show();
    });
</script>
<?php endif; ?>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        // Validate file size (2MB max)
        if (input.files[0].size > 2 * 1024 * 1024) {
            alert('File size must be less than 2MB');
            input.value = '';
            return;
        }
        
        // Validate file type
        const fileType = input.files[0].type;
        if (!fileType.match(/image\/(jpeg|jpg|png|gif)/)) {
            alert('Only JPG, PNG, and GIF images are allowed');
            input.value = '';
            return;
        }
        
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Form validation
document.getElementById('profileForm')?.addEventListener('submit', function(e) {
    const email = document.getElementById('user_email').value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('Please enter a valid email address.');
    }
});
</script>