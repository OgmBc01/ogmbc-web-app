<?php
ob_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header("Location: ../login.php");
    exit();
}

// Get engagement ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid engagement ID.";
    ob_end_clean();
    header("Location: engagements.php");
    exit();
}

$engagement_id = (int)$_GET['id'];
$message = '';
$message_type = '';
$showSuccessModal = false;

// Fetch engagement data
$query = "SELECT e.*, c.company_name, s.service_name 
          FROM engagements e
          JOIN clients c ON e.client_id = c.client_id
          JOIN service_types s ON e.service_id = s.service_id
          WHERE e.engagement_id = $engagement_id";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['error_message'] = "Engagement not found.";
    ob_end_clean();
    header("Location: engagements.php");
    exit();
}

$engagement = mysqli_fetch_assoc($result);

// Check if engagement can be edited (not closed)
if ($engagement['status'] == 'CLOSED') {
    $_SESSION['error_message'] = "Closed engagements cannot be edited.";
    ob_end_clean();
    header("Location: engagements.php");
    exit();
}

// Fetch clients for dropdown
$clients_query = "SELECT client_id, company_name FROM clients ORDER BY company_name";
$clients_result = mysqli_query($connection, $clients_query);

// Fetch services with active rules
$services_query = "SELECT s.service_id, s.service_name, 
                  MAX(r.rule_version) as latest_version
                  FROM service_types s
                  JOIN service_point_rules r ON s.service_id = r.service_id AND r.is_active = 1
                  WHERE s.is_active = 1
                  GROUP BY s.service_id
                  ORDER BY s.service_name";
$services_result = mysqli_query($connection, $services_query);

// Fetch departments for employee filtering
$depts_query = "SELECT id, dept_name FROM departments ORDER BY dept_name";
$depts_result = mysqli_query($connection, $depts_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_engagement'])) {
    
    $client_id = (int)$_POST['client_id'];
    $service_id = (int)$_POST['service_id'];
    $rule_version_id = (int)$_POST['rule_version_id'];
    $title = mysqli_real_escape_string($connection, trim($_POST['title']));
    $description = mysqli_real_escape_string($connection, trim($_POST['description'] ?? ''));
    $assigned_to = (int)$_POST['assigned_to'];
    $reviewer_id = !empty($_POST['reviewer_id']) ? (int)$_POST['reviewer_id'] : 'NULL';
    $start_date = mysqli_real_escape_string($connection, $_POST['start_date']);
    $original_deadline = mysqli_real_escape_string($connection, $_POST['original_deadline']);
    $status = mysqli_real_escape_string($connection, $_POST['status']);
    $evidence_required = isset($_POST['evidence_required']) ? 1 : 0;
    
    // Validation
    if (empty($client_id) || empty($service_id) || empty($rule_version_id) || empty($title) || empty($assigned_to) || empty($start_date) || empty($original_deadline)) {
        $message = "Please fill in all required fields.";
        $message_type = "danger";
    } elseif (strtotime($original_deadline) <= strtotime($start_date)) {
        $message = "Deadline must be after start date.";
        $message_type = "danger";
    } else {
        
        // Check if status changed
        $old_status = $engagement['status'];
        $status_changed = ($old_status != $status);
        
        // Update engagement
        $reviewer_value = ($reviewer_id !== 'NULL') ? $reviewer_id : 'NULL';
        
        $update_query = "UPDATE engagements SET 
                         client_id = $client_id,
                         service_id = $service_id,
                         rule_version_id = $rule_version_id,
                         title = '$title',
                         description = '$description',
                         assigned_to = $assigned_to,
                         reviewer_id = $reviewer_value,
                         start_date = '$start_date',
                         original_deadline = '$original_deadline',
                         status = '$status',
                         evidence_required = $evidence_required
                         WHERE engagement_id = $engagement_id";
        
        if (mysqli_query($connection, $update_query)) {
            
            // Add status history if status changed
            if ($status_changed) {
                $history_query = "INSERT INTO engagement_status_history 
                                (engagement_id, old_status, new_status, changed_by, notes) 
                                VALUES ($engagement_id, '$old_status', '$status', {$_SESSION['user_id']}, 'Status updated via edit')";
                mysqli_query($connection, $history_query);
            }
            
            $showSuccessModal = true;
            
            // Refresh engagement data
            $refresh_query = "SELECT e.*, c.company_name, s.service_name 
                             FROM engagements e
                             JOIN clients c ON e.client_id = c.client_id
                             JOIN service_types s ON e.service_id = s.service_id
                             WHERE e.engagement_id = $engagement_id";
            $refresh_result = mysqli_query($connection, $refresh_query);
            $engagement = mysqli_fetch_assoc($refresh_result);
            
        } else {
            $message = "Error updating engagement: " . mysqli_error($connection);
            $message_type = "danger";
        }
    }
}

ob_end_flush();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Engagement: <?php echo htmlspecialchars($engagement['title']); ?></h5>
                    <a href="engagements.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Engagements
                    </a>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($message) && !$showSuccessModal): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="engagementForm">
                        <input type="hidden" name="engagement_id" value="<?php echo $engagement_id; ?>">
                        <!-- Hidden client_id to ensure it is posted even if select is disabled -->
                        <input type="hidden" name="client_id" value="<?php echo htmlspecialchars($engagement['client_id']); ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="client_id" class="form-label">Client * <i class="bi bi-lock-fill text-secondary" title="Read-only"></i></label>
                                <select id="client_id" name="client_id" class="form-control" required disabled>
                                    <option value="<?php echo htmlspecialchars($engagement['client_id']); ?>" selected>
                                        <?php echo htmlspecialchars($engagement['company_name']); ?>
                                    </option>
                                </select>
                                <div class="form-text text-muted">This field is read-only and cannot be changed here.</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="title" class="form-label">Engagement Title * <i class="bi bi-lock-fill text-secondary" title="Read-only"></i></label>
                                <input type="text" id="title" name="title" class="form-control" 
                                       value="<?php echo htmlspecialchars($engagement['title']); ?>" required readonly>
                                <div class="form-text text-muted">This field is read-only to preserve the unique engagement ID.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="3"><?php echo htmlspecialchars($engagement['description']); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="service_id" class="form-label">Service Type *</label>
                                <select id="service_id" name="service_id" class="form-control" required onchange="loadRuleVersions(<?php echo $engagement['rule_version_id']; ?>)">
                                    <option value="">Select Service</option>
                                    <?php
                                    if ($services_result && mysqli_num_rows($services_result) > 0) {
                                        mysqli_data_seek($services_result, 0);
                                        while ($service = mysqli_fetch_assoc($services_result)) {
                                            $selected = ($engagement['service_id'] == $service['service_id']) ? 'selected' : '';
                                            echo "<option value='{$service['service_id']}' data-version='{$service['latest_version']}' $selected>" . htmlspecialchars($service['service_name']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="rule_version_id" class="form-label">Rule Version *</label>
                                <select id="rule_version_id" name="rule_version_id" class="form-control" required>
                                    <option value="">Select Service First</option>
                                </select>
                                <div class="form-text">Points calculation rule version</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="dept_filter" class="form-label">Department (Filter)</label>
                                <select id="dept_filter" class="form-control" onchange="loadEmployees(<?php echo $engagement['assigned_to']; ?>, <?php echo $engagement['reviewer_id'] ?: 'null'; ?>)">
                                    <option value="">All Departments</option>
                                    <?php
                                    if ($depts_result && mysqli_num_rows($depts_result) > 0) {
                                        mysqli_data_seek($depts_result, 0);
                                        while ($dept = mysqli_fetch_assoc($depts_result)) {
                                            echo "<option value='{$dept['id']}'>" . htmlspecialchars($dept['dept_name']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="assigned_to" class="form-label">Assign To *</label>
                                <select id="assigned_to" name="assigned_to" class="form-control" required>
                                    <option value="">Select Employee</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="reviewer_id" class="form-label">Reviewer (Optional)</label>
                                <select id="reviewer_id" name="reviewer_id" class="form-control">
                                    <option value="">No Reviewer</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="start_date" class="form-label">Start Date *</label>
                                <input type="date" id="start_date" name="start_date" class="form-control" 
                                       value="<?php echo $engagement['start_date']; ?>" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="original_deadline" class="form-label">Deadline *</label>
                                <input type="date" id="original_deadline" name="original_deadline" class="form-control" 
                                       value="<?php echo $engagement['original_deadline']; ?>" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="ASSIGNED" <?php echo ($engagement['status'] == 'ASSIGNED') ? 'selected' : ''; ?>>Assigned</option>
                                    <option value="IN_PROGRESS" <?php echo ($engagement['status'] == 'IN_PROGRESS') ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="AWAITING_REVIEW" <?php echo ($engagement['status'] == 'AWAITING_REVIEW') ? 'selected' : ''; ?>>Awaiting Review</option>
                                    <option value="SUBMITTED" <?php echo ($engagement['status'] == 'SUBMITTED') ? 'selected' : ''; ?>>Submitted</option>
                                    <option value="REJECTED" <?php echo ($engagement['status'] == 'REJECTED') ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="evidence_required" name="evidence_required" <?php echo $engagement['evidence_required'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="evidence_required">
                                    Evidence Required for Completion
                                </label>
                            </div>
                        </div>

                        <?php if ($engagement['approved_deadline']): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Note:</strong> This engagement has an approved deadline change. 
                            Approved deadline: <?php echo date('M d, Y', strtotime($engagement['approved_deadline'])); ?>
                        </div>
                        <?php endif; ?>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="update_engagement" class="btn btn-primary btn-lg me-2">
                                    <i class="bi bi-check-circle me-1"></i> Update Engagement
                                </button>
                                <a href="engagements.php" class="btn btn-outline-secondary btn-lg">
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
          <h5 class="mt-3">Engagement Updated Successfully!</h5>
          <p class="text-muted mb-0">The engagement "<?php echo htmlspecialchars($engagement['title']); ?>" has been updated.</p>
        </div>
      </div>
      <div class="modal-footer justify-content-center border-0 pt-0">
        <a href="engagements.php" class="btn btn-success px-4">
          <i class="bi bi-list-ul me-2"></i>View All Engagements
        </a>
        <a href="engagements.php?source=view_engagement&id=<?php echo $engagement_id; ?>" class="btn btn-outline-primary px-4">
          <i class="bi bi-eye"></i>View Details
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
// Load rule versions when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadRuleVersions(<?php echo $engagement['rule_version_id']; ?>);
    loadEmployees(<?php echo $engagement['assigned_to']; ?>, <?php echo $engagement['reviewer_id'] ?: 'null'; ?>);
});

// Load rule versions when service is selected
function loadRuleVersions(selectedRuleId = null) {
    const serviceId = document.getElementById('service_id').value;
    const ruleSelect = document.getElementById('rule_version_id');
    
    if (!serviceId) {
        ruleSelect.innerHTML = '<option value="">Select Service First</option>';
        return;
    }
    
    fetch('includes/ajax/get_rule_versions.php?service_id=' + serviceId)
        .then(response => response.json())
        .then(data => {
            let options = '<option value="">Select Rule Version</option>';
            if (data.success && data.rules.length > 0) {
                data.rules.forEach(rule => {
                    const effectiveDate = new Date(rule.effective_date).toLocaleDateString();
                    const selected = (selectedRuleId && rule.rule_id == selectedRuleId) ? 'selected' : '';
                    options += `<option value="${rule.rule_id}" ${selected}>v${rule.rule_version} - Base: ${rule.base_points} pts (Eff: ${effectiveDate})</option>`;
                });
            } else {
                options = '<option value="">No active rules for this service</option>';
            }
            ruleSelect.innerHTML = options;
        })
        .catch(error => {
            console.error('Error loading rules:', error);
        });
}

// Load employees when department is selected
function loadEmployees(selectedEmployeeId = null, selectedReviewerId = null) {
    const deptId = document.getElementById('dept_filter').value;
    const assignedSelect = document.getElementById('assigned_to');
    const reviewerSelect = document.getElementById('reviewer_id');
    
    let url = 'includes/ajax/get_employees.php';
    if (deptId) {
        url += '?dept_id=' + deptId;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            let options = '<option value="">Select Employee</option>';
            let reviewerOptions = '<option value="">No Reviewer</option>';
            
            if (data.success && data.employees.length > 0) {
                data.employees.forEach(emp => {
                    const name = emp.first_name + ' ' + emp.last_name;
                    const selected = (selectedEmployeeId && emp.user_id == selectedEmployeeId) ? 'selected' : '';
                    const selectedReviewer = (selectedReviewerId && emp.user_id == selectedReviewerId) ? 'selected' : '';
                    
                    options += `<option value="${emp.user_id}" ${selected}>${name}</option>`;
                    reviewerOptions += `<option value="${emp.user_id}" ${selectedReviewer}>${name}</option>`;
                });
            } else {
                options = '<option value="">No employees available</option>';
                reviewerOptions = '<option value="">No employees available</option>';
            }
            
            assignedSelect.innerHTML = options;
            reviewerSelect.innerHTML = reviewerOptions;
        })
        .catch(error => {
            console.error('Error loading employees:', error);
        });
}

// Validate deadline after start date
document.getElementById('start_date').addEventListener('change', function() {
    const deadlineField = document.getElementById('original_deadline');
    if (deadlineField.value && new Date(deadlineField.value) <= new Date(this.value)) {
        alert('Deadline must be after start date');
        deadlineField.value = '';
    }
});

document.getElementById('original_deadline').addEventListener('change', function() {
    const startDate = document.getElementById('start_date').value;
    if (startDate && new Date(this.value) <= new Date(startDate)) {
        alert('Deadline must be after start date');
        this.value = '';
    }
});
</script>