<?php
// Start output buffering
ob_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$is_hr_admin = false;

// Check if user is HR admin (for assigning to others)
$user_role_query = "SELECT role_name FROM users u LEFT JOIN user_roles r ON u.role_id = r.role_id WHERE u.user_id = $user_id";
$user_role_result = mysqli_query($connection, $user_role_query);
$user_role = mysqli_fetch_assoc($user_role_result)['role_name'] ?? '';
$is_hr_admin = ($user_role == 'hr_admin' || $user_role == 'ceo_gm' || $user_role == 'admin_staff');

// Initialize variables
$employee_id = $is_hr_admin ? '' : $user_id;
$cdp_type = 'CERTIFICATE';
$title = '';
$description = '';
$uplift_percentage = '';
$effective_date = date('Y-m-d');
$message = '';
$message_type = '';
$showSuccessModal = false;

// Get employees for dropdown (HR only)
if ($is_hr_admin) {
    $employees_query = "SELECT u.user_id, u.first_name, u.last_name 
                       FROM users u
                       WHERE u.user_status = 'active'
                       ORDER BY u.first_name";
    $employees_result = mysqli_query($connection, $employees_query);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_cdp'])) {
    
    $employee_id = (int)$_POST['employee_id'];
    $cdp_type = mysqli_real_escape_string($connection, $_POST['cdp_type']);
    $title = mysqli_real_escape_string($connection, trim($_POST['title']));
    $description = mysqli_real_escape_string($connection, trim($_POST['description'] ?? ''));
    $uplift_percentage = !empty($_POST['uplift_percentage']) ? floatval($_POST['uplift_percentage']) : 'NULL';
    $effective_date = mysqli_real_escape_string($connection, $_POST['effective_date']);
    
    // Handle file upload
    $document_file = '';
    if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['document_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        
        if (in_array($ext, $allowed)) {
            $upload_dir = "../uploads/cdp_documents/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $new_filename = "cdp_" . time() . "_" . rand(1000, 9999) . "." . $ext;
            $target_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                $document_file = $new_filename;
            }
        }
    }
    
    // Validation
    if (empty($employee_id) || empty($cdp_type) || empty($title) || empty($effective_date)) {
        $message = "Please fill in all required fields.";
        $message_type = "danger";
    } else {
        // Insert CDP record
        $uplift_value = ($uplift_percentage !== 'NULL') ? $uplift_percentage : 'NULL';
        
        $insert_query = "INSERT INTO cdp_records 
                        (employee_id, cdp_type, title, description, document_file, 
                         uplift_percentage, effective_date, created_by, status)
                        VALUES 
                        ($employee_id, '$cdp_type', '$title', '$description', '$document_file',
                         $uplift_value, '$effective_date', $user_id, 'PENDING')";
        
        if (mysqli_query($connection, $insert_query)) {
            $showSuccessModal = true;
            
            // Clear form
            $title = $description = '';
            $uplift_percentage = '';
            $cdp_type = 'CERTIFICATE';
        } else {
            $message = "Error saving CDP record: " . mysqli_error($connection);
            $message_type = "danger";
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
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add CDP Record</h5>
                    <a href="cdp_annual.php?tab=cdp" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to CDP Records
                    </a>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($message) && !$showSuccessModal): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data">
                        <?php if ($is_hr_admin): ?>
                        <div class="mb-3">
                            <label for="employee_id" class="form-label">Employee *</label>
                            <select id="employee_id" name="employee_id" class="form-control" required>
                                <option value="">Select Employee</option>
                                <?php while($emp = mysqli_fetch_assoc($employees_result)): ?>
                                    <option value="<?php echo $emp['user_id']; ?>" <?php echo ($employee_id == $emp['user_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <?php else: ?>
                        <input type="hidden" name="employee_id" value="<?php echo $user_id; ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cdp_type" class="form-label">CDP Type *</label>
                                <select id="cdp_type" name="cdp_type" class="form-control" required>
                                    <option value="CERTIFICATE" <?php echo ($cdp_type == 'CERTIFICATE') ? 'selected' : ''; ?>>Certificate</option>
                                    <option value="COURSE" <?php echo ($cdp_type == 'COURSE') ? 'selected' : ''; ?>>Course</option>
                                    <option value="LOYALTY" <?php echo ($cdp_type == 'LOYALTY') ? 'selected' : ''; ?>>Loyalty</option>
                                    <option value="BEHAVIOR" <?php echo ($cdp_type == 'BEHAVIOR') ? 'selected' : ''; ?>>Behavior</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="effective_date" class="form-label">Effective Date *</label>
                                <input type="date" id="effective_date" name="effective_date" class="form-control" 
                                       value="<?php echo $effective_date; ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" id="title" name="title" class="form-control" 
                                   value="<?php echo htmlspecialchars($title); ?>" 
                                   placeholder="e.g., Certified Public Accountant, Leadership Course" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="3"><?php echo htmlspecialchars($description); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="uplift_percentage" class="form-label">Uplift Percentage (%)</label>
                                <input type="number" step="0.1" min="0" max="100" id="uplift_percentage" name="uplift_percentage" 
                                       class="form-control" value="<?php echo $uplift_percentage; ?>">
                                <div class="form-text">
                                    <?php if ($cdp_type == 'CERTIFICATE'): ?>Ops: +18%, Sales: +15%<?php endif; ?>
                                    <?php if ($cdp_type == 'COURSE'): ?>Ops: +7%, Sales: +5%<?php endif; ?>
                                    <?php if ($cdp_type == 'LOYALTY'): ?>+3% for both<?php endif; ?>
                                    <?php if ($cdp_type == 'BEHAVIOR'): ?>+2% for both<?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="document_file" class="form-label">Document</label>
                                <input type="file" id="document_file" name="document_file" class="form-control">
                                <div class="form-text">Upload certificate or supporting document</div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="submit_cdp" class="btn btn-primary btn-lg me-2">
                                    <i class="bi bi-check-circle me-1"></i> Submit CDP Record
                                </button>
                                <a href="cdp_annual.php?tab=cdp" class="btn btn-outline-secondary btn-lg">
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
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Success!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-3">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h5 class="mt-3">CDP Record Submitted!</h5>
                <p class="text-muted mb-0">Your CDP record has been submitted for approval.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <a href="cdp_annual.php?tab=cdp" class="btn btn-success px-4">View All Records</a>
                <a href="cdp_annual.php?source=add_cdp" class="btn btn-outline-success px-4">Add Another</a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
    });
</script>
<?php endif; ?>