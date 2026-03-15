<?php
// Start output buffering
ob_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$client_id = '';
$engagement_id = '';
$employee_id = '';
$feedback_text = '';
$is_positive = 1;
$message = '';
$message_type = '';
$showSuccessModal = false;

// Get clients for dropdown
$clients_query = "SELECT client_id, company_name FROM clients ORDER BY company_name";
$clients_result = mysqli_query($connection, $clients_query);

// DEBUG: Check if clients are found
if (!$clients_result) {
    die("Error fetching clients: " . mysqli_error($connection));
}


// Get employees for dropdown - using employees table

// Get users for dropdown (all users, or filter by type_id if needed)
$users_query = "SELECT user_id, first_name, last_name FROM users ORDER BY first_name";
$users_result = mysqli_query($connection, $users_query);
if (!$users_result) {
    die("Error fetching users: " . mysqli_error($connection));
}
$user_count = mysqli_num_rows($users_result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    
    $client_id = (int)$_POST['client_id'];
    $engagement_id = !empty($_POST['engagement_id']) ? (int)$_POST['engagement_id'] : 'NULL';
    $employee_id = (int)$_POST['employee_id'];
    $feedback_text = mysqli_real_escape_string($connection, trim($_POST['feedback_text']));
    $is_positive = isset($_POST['is_positive']) ? 1 : 0;
    $created_by = $_SESSION['user_id'];
    
    // Handle file upload
    $evidence_file = '';
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
                $evidence_file = $new_filename;
            }
        }
    }
    
    // Validation
    if (empty($client_id) || empty($employee_id) || empty($feedback_text)) {
        $message = "Please fill in all required fields.";
        $message_type = "danger";
    } else {
        // Insert feedback
        $engagement_value = ($engagement_id !== 'NULL') ? $engagement_id : 'NULL';
        
        $insert_query = "INSERT INTO client_feedback 
                        (client_id, engagement_id, employee_id, feedback_text, is_positive, 
                         evidence_file, created_by, is_validated)
                        VALUES 
                        ($client_id, $engagement_value, $employee_id, '$feedback_text', $is_positive,
                         '$evidence_file', $created_by, 0)";
        
        if (mysqli_query($connection, $insert_query)) {
            $showSuccessModal = true;
            
            // Clear form
            $client_id = $engagement_id = $employee_id = '';
            $feedback_text = '';
            $is_positive = 1;
        } else {
            $message = "Error saving feedback: " . mysqli_error($connection);
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
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Log Client Feedback</h5>
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

                    <!-- Debug info - remove in production -->
                    <?php if ($user_count == 0): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        No employees found in the system. Please add employees first.
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="client_id" class="form-label">Client *</label>
                                <select id="client_id" name="client_id" class="form-control" required onchange="loadEngagements()">
                                    <option value="">Select Client</option>
                                    <?php 
                                    if ($clients_result && mysqli_num_rows($clients_result) > 0) {
                                        mysqli_data_seek($clients_result, 0);
                                        while($client = mysqli_fetch_assoc($clients_result)): 
                                    ?>
                                        <option value="<?php echo $client['client_id']; ?>" <?php echo ($client_id == $client['client_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($client['company_name']); ?>
                                        </option>
                                    <?php 
                                        endwhile;
                                    } else {
                                        echo '<option value="">No clients found</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="employee_id" class="form-label">Employee *</label>
                                <select id="employee_id" name="employee_id" class="form-control" required>
                                    </div>
                                    <!-- Select2 CSS -->
                                    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
                                    <!-- Select2 JS -->
                                    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
                                    <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        if (window.jQuery) {
                                            $('#employee_id').select2({
                                                placeholder: 'Select or search employee',
                                                allowClear: true,
                                                width: '100%'
                                            });
                                        }
                                    });
                                    </script>
                                    <option value="">Select Employee</option>
                                    <?php 
                                    if ($users_result && mysqli_num_rows($users_result) > 0) {
                                        mysqli_data_seek($users_result, 0);
                                        while($user = mysqli_fetch_assoc($users_result)):
                                    ?>
                                        <option value="<?php echo $user['user_id']; ?>" <?php echo ($employee_id == $user['user_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                        </option>
                                    <?php 
                                        endwhile;
                                    } else {
                                        echo '<option value="">No employees found</option>';
                                    }
                                    ?>
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
                            <textarea id="feedback_text" name="feedback_text" class="form-control" rows="4" 
                                      placeholder="Enter the client's feedback..." required><?php echo htmlspecialchars($feedback_text); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_positive" name="is_positive" <?php echo $is_positive ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_positive">
                                        <i class="bi bi-emoji-smile text-success"></i> Positive Feedback (Awards 50 points)
                                    </label>
                                </div>
                                <div class="form-text">Uncheck if this is neutral or negative feedback (no points)</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="evidence_file" class="form-label">Evidence (Optional)</label>
                                <input type="file" id="evidence_file" name="evidence_file" class="form-control">
                                <div class="form-text">Upload email, screenshot, or document as proof</div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="submit_feedback" class="btn btn-primary btn-lg me-2">
                                    <i class="bi bi-check-circle me-1"></i> Save Feedback
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
                <h5 class="mt-3">Feedback Logged Successfully!</h5>
                <p class="text-muted mb-0">The feedback has been saved and is pending validation.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <a href="client_feedback.php" class="btn btn-success px-4">View All Feedback</a>
                <a href="client_feedback.php?source=add_feedback" class="btn btn-outline-success px-4">Add Another</a>
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
function loadEngagements() {
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
                    options += `<option value="${eng.engagement_id}">${eng.title} (${eng.status})</option>`;
                });
            }
            engagementSelect.innerHTML = options;
        })
        .catch(error => {
            console.error('Error loading engagements:', error);
        });
}
</script>