<?php

// Check if user is logged in and has appropriate permissions
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$user_id = $user_email = $password = $first_name = $last_name = '';
$field_of_study = $qualification = $highest_graduation = $year_of_graduation = '';
$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $user_id = trim($_POST['user_id']);
    $user_email = trim($_POST['user_email']);
    $password = trim($_POST['password']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $field_of_study = trim($_POST['field_of_study'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $highest_graduation = trim($_POST['highest_graduation'] ?? '');
    $year_of_graduation = !empty($_POST['year_of_graduation']) ? intval($_POST['year_of_graduation']) : null;

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

    // Validate required fields
    if (empty($user_id) || empty($user_email) || empty($password) || empty($first_name) || empty($last_name)) {
        $message = "Please fill in all required fields.";
        $message_type = "error";
    } else {
        // Insert into database
        $sql = "INSERT INTO employees (user_id, user_email, password, first_name, last_name, user_image, 
                field_of_study, qualification, highest_graduation, year_of_graduation, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("issssssssi", $user_id, $user_email, $password, $first_name, $last_name, 
                         $user_image, $field_of_study, $qualification, $highest_graduation, $year_of_graduation);

        if ($stmt->execute()) {
            $stmt->close();
            
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
            $message = "Failed to add employee. Error: " . $connection->error;
            $message_type = "error";
            $stmt->close();
        }
    }
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
                        <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="row">
                                <!-- Left Column - Required Fields -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="user_id" class="form-label"><i class="bi bi-person-badge me-1"></i>User ID *</label>
                                        <input type="number" id="user_id" name="user_id" class="form-control" 
                                               value="<?php echo htmlspecialchars($user_id); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="user_email" class="form-label"><i class="bi bi-envelope me-1"></i>Email *</label>
                                        <input type="email" id="user_email" name="user_email" class="form-control" 
                                               value="<?php echo htmlspecialchars($user_email); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label"><i class="bi bi-lock me-1"></i>Password *</label>
                                        <input type="password" id="password" name="password" class="form-control" 
                                               value="<?php echo htmlspecialchars($password); ?>" required>
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
                                    <button type="submit" class="btn btn-primary btn-lg me-2">
                                        <i class="bi bi-check-circle me-1"></i> Add Employee
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary btn-lg">
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
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Success</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                <h4 class="my-3">Employee Added Successfully!</h4>
                <p>The new employee has been added to the database.</p>
            </div>
            <div class="modal-footer">
                <a href="employees.php" class="btn btn-secondary">View All Employees</a>
                <a href="add_employee.php" class="btn btn-success">Add Another Employee</a>
            </div>
        </div>
    </div>
</div>

<style>
    .form-label {
        font-weight: 500;
        color: #0f172a;
        margin-bottom: 0.5rem;
    }
    .card {
        border: none;
        border-radius: 12px;
    }
    .card-header {
        border-radius: 12px 12px 0 0 !important;
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
    }
</style>