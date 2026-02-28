<?php
// Start output buffering
ob_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header("Location: ../login.php");
    exit();
}

// Get current user's role
$user_role_query = "SELECT role_name FROM users u LEFT JOIN user_roles r ON u.role_id = r.role_id WHERE u.user_id = " . $_SESSION['user_id'];
$user_role_result = mysqli_query($connection, $user_role_query);
$user_role = mysqli_fetch_assoc($user_role_result)['role_name'] ?? '';

$can_edit = ($user_role == 'ceo_gm' || $user_role == 'hr_admin' || $user_role == 'admin_staff');

if (!$can_edit) {
    $_SESSION['error_message'] = "You don't have permission to edit feedback.";
    ob_end_clean();
    header("Location: client_feedback.php");
    exit();
}

// Get feedback ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid feedback ID.";
    ob_end_clean();
    header("Location: client_feedback.php");
    exit();
}

$feedback_id = (int)$_GET['id'];

// Fetch feedback details
$query = "SELECT cf.*, c.company_name 
          FROM client_feedback cf
          JOIN clients c ON cf.client_id = c.client_id
          WHERE cf.feedback_id = $feedback_id";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['error_message'] = "Feedback not found.";
    ob_end_clean();
    header("Location: client_feedback.php");
    exit();
}

$feedback = mysqli_fetch_assoc($result);

// Check if feedback can be edited
if ($feedback['is_validated']) {
    $_SESSION['error_message'] = "Validated feedback cannot be edited.";
    ob_end_clean();
    header("Location: client_feedback.php");
    exit();
}

// Initialize variables
$message = '';
$message_type = '';
$showSuccessModal = false;

// Get clients for dropdown
$clients_query = "SELECT client_id, company_name FROM clients ORDER BY company_name";
$clients_result = mysqli_query($connection, $clients_query);

// Get employees for dropdown
$employees_query = "SELECT u.user_id, u.first_name, u.last_name 
                   FROM users u
                   JOIN user_roles r ON u.role_id = r.role_id
                   WHERE r.role_name IN ('operations_staff', 'sales_staff', 'admin_staff')
                   ORDER BY u.first_name";
$employees_result = mysqli_query($connection, $employees_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_feedback'])) {
    
    $client_id = (int)$_POST['client_id'];
    $engagement_id = !empty($_POST['engagement_id']) ? (int)$_POST['engagement_id'] : 'NULL';
    $employee_id = (int)$_POST['employee_id'];
    $feedback_text = mysqli_real_escape_string($connection, trim($_POST['feedback_text']));
    $is_positive = isset($_POST['is_positive']) ? 1 : 0;
    
    // Handle file upload
    $evidence_file = $feedback['evidence_file'];
    if (isset($_FILES['evidence_file']) && $_FILES['evidence_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['evidence_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx', 'txt'];
        
        if (in_array($ext, $allowed)) {
            $upload_dir = "../uploads/feedback_evidence/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $new_filename = "feedback_" . time() . "_" . rand(1000, 9999) . "." . $ext;
            $target_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                // Delete old file if exists
                if ($feedback['evidence_file'] && file_exists($upload_dir . $feedback['evidence_file'])) {
                    unlink($upload_dir . $feedback['evidence_file']);
                }
                $evidence_file = $new_filename;
            }
        }
    }
    
    // Validation
    if (empty($client_id) || empty($employee_id) || empty($feedback_text)) {
        $message = "Please fill in all required fields.";
        $message_type = "danger";
    } else {
        // Update feedback
        $engagement_value = ($engagement_id !== 'NULL') ? $engagement_id : 'NULL';
        
        $update_query = "UPDATE client_feedback SET 
                        client_id = $client_id,
                        engagement_id = $engagement_value,
                        employee_id = $employee_id,
                        feedback_text = '$feedback_text',
                        is_positive = $is_positive,
                        evidence_file = '$evidence_file'
                        WHERE feedback_id = $feedback_id";
        
        if (mysqli_query($connection, $update_query)) {
            $showSuccessModal = true;
            
            // Refresh feedback data
            $refresh_query = "SELECT cf.*, c.company_name 
                             FROM client_feedback cf
                             JOIN clients c ON cf.client_id = c.client_id
                             WHERE cf.feedback_id = $feedback_id";
            $refresh_result = mysqli_query($connection, $refresh_query);
            $feedback = mysqli_fetch_assoc($refresh_result);
        } else {
            $message = "Error updating feedback: " . mysqli_error($connection);
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
                    <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Client Feedback</h5>
                    <a href="client_feedback.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Feedback
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
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="client_id" class="form-label">Client *</label>
                                <select id="client_id" name="client_id" class="form-control" required onchange="loadEngagements(<?php echo $feedback['engagement_id'] ?: 'null'; ?>)">
                                    <option value="">Select Client</option>
                                    <?php while($client = mysqli_fetch_assoc($clients_result)): ?>
                                        <option value="<?php echo $client['client_id']; ?>" <?php echo ($feedback['client_id'] == $client['client_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($client['company_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="employee_id" class="form-label">Employee *</label>
                                <select id="employee_id" name="employee_id" class="form-control" required>
                                    <option value="">Select Employee</option>
                                    <?php while($emp = mysqli_fetch_assoc($employees_result)): ?>
                                        <option value="<?php echo $emp['user_id']; ?>" <?php echo ($feedback['employee_id'] == $emp['user_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="engagement_id" class="form-label">Related Engagement (Optional)</label>
                            <select id="engagement_id" name="engagement_id" class="form-control">
                                <option value="">Select Engagement (Optional)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="feedback_text" class="form-label">Feedback Text *</label>
                            <textarea id="feedback_text" name="feedback_text" class="form-control" rows="4" required><?php echo htmlspecialchars($feedback['feedback_text']); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_positive" name="is_positive" <?php echo $feedback['is_positive'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_positive">
                                        <i class="bi bi-emoji-smile text-success"></i> Positive Feedback
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="evidence_file" class="form-label">Evidence (Optional)</label>
                                <input type="file" id="evidence_file" name="evidence_file" class="form-control">
                                <?php if ($feedback['evidence_file']): ?>
                                    <div class="mt-2">
                                        <small>Current file: <?php echo $feedback['evidence_file']; ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="update_feedback" class="btn btn-primary btn-lg me-2">
                                    <i class="bi bi-check-circle me-1"></i> Update Feedback
                                </button>
                                <a href="client_feedback.php" class="btn btn-outline-secondary btn-lg">
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
                <h5 class="mt-3">Feedback Updated Successfully!</h5>
                <p class="text-muted mb-0">The feedback has been updated.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <a href="client_feedback.php" class="btn btn-success px-4">View All Feedback</a>
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Continue Editing</button>
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

<script>
// Load engagements for selected client
function loadEngagements(selectedEngagementId = null) {
    const clientId = document.getElementById('client_id').value;
    const engagementSelect = document.getElementById('engagement_id');
    
    if (!clientId) {
        engagementSelect.innerHTML = '<option value="">Select Engagement (Optional)</option>';
        return;
    }
    
    fetch('includes/ajax/get_client_engagements.php?client_id=' + clientId)
        .then(response => response.json())
        .then(data => {
            let options = '<option value="">Select Engagement (Optional)</option>';
            if (data.success && data.engagements.length > 0) {
                data.engagements.forEach(eng => {
                    const selected = (selectedEngagementId && eng.engagement_id == selectedEngagementId) ? 'selected' : '';
                    options += `<option value="${eng.engagement_id}" ${selected}>${eng.title} (${eng.status})</option>`;
                });
            }
            engagementSelect.innerHTML = options;
        })
        .catch(error => {
            console.error('Error loading engagements:', error);
        });
}

// Load engagements on page load
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($feedback['engagement_id']): ?>
    loadEngagements(<?php echo $feedback['engagement_id']; ?>);
    <?php else: ?>
    loadEngagements();
    <?php endif; ?>
});
</script>