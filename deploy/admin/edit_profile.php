<?php
include 'includes/header.php';
include 'includes/nav.php';
include 'includes/sidebar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ Fetch user + employee data
$sql = "
    SELECT u.*, e.field_of_study, e.qualification, e.highest_graduation, e.year_of_graduation 
    FROM users u 
    LEFT JOIN employees e ON u.user_id = e.user_id 
    WHERE u.user_id = ?
";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$upload_dir = "../uploads/profiles/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// ✅ Handle update form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name  = trim($_POST['first_name']);
    $last_name   = trim($_POST['last_name']);
    $user_type   = trim($_POST['user_type']);

    $field_of_study      = $_POST['field_of_study'] ?? '';
    $qualification       = $_POST['qualification'] ?? '';
    $highest_graduation  = $_POST['highest_graduation'] ?? '';
    $year_of_graduation  = !empty($_POST['year_of_graduation']) ? intval($_POST['year_of_graduation']) : null;

    // ✅ Handle profile picture
    $user_image = $user['user_image'] ?? '';
    if (isset($_FILES['user_image']) && $_FILES['user_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['user_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($ext, $allowed)) {
            $new_filename = "profile_{$user_id}_" . time() . ".{$ext}";
            $target = $upload_dir . $new_filename;
            if (move_uploaded_file($file['tmp_name'], $target)) {
                if (!empty($user['user_image']) && file_exists($upload_dir . $user['user_image'])) {
                    unlink($upload_dir . $user['user_image']);
                }
                $user_image = $new_filename;
            }
        }
    }

    // ✅ Update users table
    $sql = "UPDATE users SET first_name = ?, last_name = ?, user_type = ?, user_image = ? WHERE user_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("ssssi", $first_name, $last_name, $user_type, $user_image, $user_id);

    if ($stmt->execute()) {
        $stmt->close();

        // ✅ Handle employee table
        if ($user_type === 'employee') {
            $check_sql = "SELECT user_id FROM employees WHERE user_id = ?";
            $check_stmt = $connection->prepare($check_sql);
            $check_stmt->bind_param("i", $user_id);
            $check_stmt->execute();
            $exists = $check_stmt->get_result()->num_rows > 0;
            $check_stmt->close();

            if ($exists) {
                $update_sql = "
                    UPDATE employees SET first_name=?, last_name=?, user_image=?, 
                        field_of_study=?, qualification=?, highest_graduation=?, year_of_graduation=? 
                    WHERE user_id=?
                ";
                $update_stmt = $connection->prepare($update_sql);
                $update_stmt->bind_param("sssssssi", $first_name, $last_name, $user_image,
                    $field_of_study, $qualification, $highest_graduation, $year_of_graduation, $user_id);
                $update_stmt->execute();
                $update_stmt->close();
            } else {
                $insert_sql = "
                    INSERT INTO employees (user_id, user_email, password, first_name, last_name, user_image,
                        field_of_study, qualification, highest_graduation, year_of_graduation, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";
                $insert_stmt = $connection->prepare($insert_sql);
                $insert_stmt->bind_param("issssssssis",
                    $user_id, $user['user_email'], $user['password'],
                    $first_name, $last_name, $user_image,
                    $field_of_study, $qualification, $highest_graduation, $year_of_graduation, $user['created_at']
                );
                $insert_stmt->execute();
                $insert_stmt->close();
            }
        } else {
            $delete_sql = "DELETE FROM employees WHERE user_id = ?";
            $delete_stmt = $connection->prepare($delete_sql);
            $delete_stmt->bind_param("i", $user_id);
            $delete_stmt->execute();
            $delete_stmt->close();
        }

        // ✅ Show modal immediately after success
        echo "
        <script>
            window.addEventListener('load', function() {
                var successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
            });
        </script>
        ";
    } else {
        echo "<p class='bg-danger text-white p-2'>Failed to update profile. Error: " . $connection->error . "</p>";
        $stmt->close();
    }
}

// ✅ Build profile image URL
$user_image_url = "";
if (!empty($user['user_image']) && file_exists($upload_dir . $user['user_image'])) {
    $user_image_url = $upload_dir . $user['user_image'];
} else {
    $name = ($user['first_name'] ?? '') . '+' . ($user['last_name'] ?? '');
    $name = empty(trim($name, '+')) ? "User" : $name;
    $user_image_url = "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=f1bf70&color=0f172a&size=200";
}
?>


<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <div class="card-header text-white">
                        <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Profile</h4>
                    </div>
                    <div class="card-body">
                        <!-- Success/Error Message -->
                        <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="" enctype="multipart/form-data">
                            <!-- Profile Picture Section - FIXED: Use correct image URL -->
                            <div class="row mb-4">
                                <div class="col-12 text-center">
                                    <div class="profile-picture-container position-relative mx-auto mb-3">
                                        <img src="<?php echo $user_image_url; ?>" 
                                            alt="User Profile" class="user-profile-image" id="profileImage"
                                            onerror="this.src='https://ui-avatars.com/api/?name=User&background=f1bf70&color=0f172a&size=200'">
                                        <div class="profile-overlay">
                                            <label for="profileUpload" class="btn btn-light btn-sm">
                                                <i class="bi bi-camera"></i> Change
                                            </label>
                                            <button type="button" class="btn btn-light btn-sm" onclick="removeProfileImage()">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                    <input type="file" id="profileUpload" name="user_image" accept="image/*" style="display: none;" onchange="previewImage(this)">
                                    <small class="text-muted">Click on the image to change or remove your profile picture</small>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label"><i class="bi bi-person me-1"></i>Username</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label"><i class="bi bi-envelope me-1"></i>Email</label>
                                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['user_email']); ?>" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label for="first_name" class="form-label"><i class="bi bi-person-circle me-1"></i>First Name *</label>
                                        <input type="text" id="first_name" name="first_name" class="form-control" 
                                            value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="last_name" class="form-label"><i class="bi bi-person-badge me-1"></i>Last Name *</label>
                                        <input type="text" id="last_name" name="last_name" class="form-control" 
                                            value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label"><i class="bi bi-shield me-1"></i>User Role</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['user_role']); ?>" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label for="user_type" class="form-label"><i class="bi bi-person-gear me-1"></i>User Type *</label>
                                        <select id="user_type" name="user_type" class="form-control" required onchange="toggleEmployeeFields()">
                                            <option value="client" <?php echo $user['user_type'] == 'client' ? 'selected' : ''; ?>>Client</option>
                                            <option value="employee" <?php echo $user['user_type'] == 'employee' ? 'selected' : ''; ?>>Employee</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label"><i class="bi bi-circle-fill me-1"></i>Account Status</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['user_status']); ?>" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Employee Fields (Initially hidden if not employee) -->
                            <div id="employeeFields" style="display: <?php echo $user['user_type'] === 'employee' ? 'block' : 'none'; ?>;">
                                <hr>
                                <h5 class="mb-3"><i class="bi bi-briefcase me-2"></i>Employee Information</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="field_of_study" class="form-label"><i class="bi bi-book me-1"></i>Field of Study</label>
                                            <input type="text" id="field_of_study" name="field_of_study" class="form-control" 
                                                value="<?php echo htmlspecialchars($user['field_of_study'] ?? ''); ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label for="qualification" class="form-label"><i class="bi bi-award me-1"></i>Qualification</label>
                                            <input type="text" id="qualification" name="qualification" class="form-control" 
                                                value="<?php echo htmlspecialchars($user['qualification'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="highest_graduation" class="form-label"><i class="bi bi-mortarboard me-1"></i>Highest Graduation</label>
                                            <input type="text" id="highest_graduation" name="highest_graduation" class="form-control" 
                                                value="<?php echo htmlspecialchars($user['highest_graduation'] ?? ''); ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label for="year_of_graduation" class="form-label"><i class="bi bi-calendar-event me-1"></i>Year of Graduation</label>
                                            <input type="number" id="year_of_graduation" name="year_of_graduation" class="form-control" 
                                                value="<?php echo htmlspecialchars($user['year_of_graduation'] ?? ''); ?>" min="1900" max="<?php echo date('Y'); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg me-2">
                                        <i class="bi bi-check-circle me-1"></i> Update Profile
                                    </button>
                                    <a href="profile.php" class="btn btn-secondary btn-lg">
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
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Success</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                <h4 class="my-3">Profile Updated Successfully!</h4>
                <p>Your profile information has been updated.</p>
            </div>
            <div class="modal-footer">
                <a href="profile.php" class="btn btn-success">Continue</a>
            </div>
        </div>
    </div>
</div>

<style>


</style>

<script>
    // ✅ Show/hide employee fields depending on user_type
    function toggleEmployeeFields() {
        const userType = document.getElementById('user_type').value;
        const employeeFields = document.getElementById('employeeFields');
        if (employeeFields) {
            employeeFields.style.display = (userType === 'employee') ? 'block' : 'none';
        }
    }

    // ✅ Preview selected image before upload
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('profileImage');
                if (img) {
                    img.src = e.target.result;
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // ✅ Reset profile image to default UI-Avatar
    function removeProfileImage() {
        const name = "<?php echo urlencode(($user['first_name'] ?? '') . '+' . ($user['last_name'] ?? '')); ?>";
        const defaultImage = "https://ui-avatars.com/api/?name=" + (name || "User") + "&background=f1bf70&color=0f172a&size=200";
        const img = document.getElementById('profileImage');
        if (img) {
            img.src = defaultImage;
        }
        const fileInput = document.getElementById('profileUpload');
        if (fileInput) {
            fileInput.value = '';
        }
    }

    // ✅ Initialize on load (so employee fields show correctly if already set)
    document.addEventListener('DOMContentLoaded', function() {
        toggleEmployeeFields();
    });
</script>


<?php
include 'includes/footer.php'
?>