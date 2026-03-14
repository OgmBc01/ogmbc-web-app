<?php
ob_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$client_id = $service_id = $rule_version_id = '';
$title = '';
$description = '';
$assigned_to = $reviewer_id = '';
$start_date = date('Y-m-d');
$original_deadline = date('Y-m-d', strtotime('+30 days'));
$evidence_required = 1;
$message = '';
$message_type = '';
$showSuccessModal = false;
$new_engagement_id = null;

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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_engagement'])) {
    
    $client_id = (int)$_POST['client_id'];
    $service_id = (int)$_POST['service_id'];
    $rule_version_id = (int)$_POST['rule_version_id'];
    $description = mysqli_real_escape_string($connection, trim($_POST['description'] ?? ''));
    $assigned_to = (int)$_POST['assigned_to'];
    $reviewer_id = !empty($_POST['reviewer_id']) ? (int)$_POST['reviewer_id'] : 'NULL';
    $start_date = mysqli_real_escape_string($connection, $_POST['start_date']);
    $original_deadline = mysqli_real_escape_string($connection, $_POST['original_deadline']);
    $evidence_required = isset($_POST['evidence_required']) ? 1 : 0;
    $created_by = $_SESSION['user_id'];
    $title = mysqli_real_escape_string($connection, trim($_POST['title'] ?? ''));
    
    // Validation
    if (empty($client_id) || empty($service_id) || empty($rule_version_id) || empty($description) || empty($assigned_to) || empty($start_date) || empty($original_deadline)) {
        $message = "Please fill in all required fields.";
        $message_type = "danger";
    } elseif (strtotime($original_deadline) <= strtotime($start_date)) {
        $message = "Deadline must be after start date.";
        $message_type = "danger";
    } else {
        // Insert engagement
        $reviewer_value = ($reviewer_id !== 'NULL') ? $reviewer_id : 'NULL';
        $insert_query = "INSERT INTO engagements 
                        (client_id, service_id, rule_version_id, title, description, 
                         assigned_to, assigned_by, reviewer_id, start_date, original_deadline, 
                         evidence_required, status, created_by) 
                        VALUES 
                        ($client_id, $service_id, $rule_version_id, '$title', '$description',
                         $assigned_to, {$created_by}, $reviewer_value, '$start_date', '$original_deadline',
                         $evidence_required, 'ASSIGNED', $created_by)";
        
        if (mysqli_query($connection, $insert_query)) {
            $new_engagement_id = mysqli_insert_id($connection);
            
            // Add status history
            $history_query = "INSERT INTO engagement_status_history 
                            (engagement_id, old_status, new_status, changed_by, notes) 
                            VALUES ($new_engagement_id, NULL, 'ASSIGNED', $created_by, 'Engagement created')";
            mysqli_query($connection, $history_query);
            
            $showSuccessModal = true;
            
            // Clear form data
            $client_id = $service_id = $rule_version_id = '';
            $title = $description = '';
            $assigned_to = $reviewer_id = '';
            $start_date = date('Y-m-d');
            $original_deadline = date('Y-m-d', strtotime('+30 days'));
            $evidence_required = 1;
        } else {
            $message = "Error creating engagement: " . mysqli_error($connection);
            $message_type = "danger";
        }
    }
}

// Get rule versions for selected service via AJAX
ob_end_flush();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Create New Engagement</h5>
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
                        <input type="hidden" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3 position-relative d-flex align-items-end">
                                <div class="flex-grow-1 me-2">
                                    <label for="client_id" class="form-label">Client *</label>
                                    <input type="text" id="client_search" class="form-control mb-2" placeholder="Type to search client..." autocomplete="off">
                                    <select id="client_id" name="client_id" class="form-control" required style="display:none;"></select>
                                    <div id="client_search_results" class="list-group position-absolute w-100" style="z-index: 10; margin-top:2.2rem;"></div>
                                </div>
                                <div class="d-flex flex-column align-items-start">
                                    <div id="no_client_found_msg" class="text-danger small" style="display:none;">No client found</div>
                                    <button type="button" id="create_new_client_btn" class="btn btn-outline-primary btn-sm mb-2" style="display:none;">Create New</button>
                                </div>
                            </div>
                            </script>
                            <script>
                            // --- Client Searchable Dropdown ---
                            const clients = [
                                <?php
                                if ($clients_result && mysqli_num_rows($clients_result) > 0) {
                                    mysqli_data_seek($clients_result, 0);
                                    $js = [];
                                    while ($client = mysqli_fetch_assoc($clients_result)) {
                                        $js[] = '{id: ' . (int)$client['client_id'] . ', name: "' . addslashes($client['company_name']) . '"}';
                                    }
                                    echo implode(",\n    ", $js);
                                }
                                ?>
                            ];

                            const clientInput = document.getElementById('client_search');
                            const clientSelect = document.getElementById('client_id');
                            const resultsDiv = document.getElementById('client_search_results');
                            const createBtn = document.getElementById('create_new_client_btn');

                            function showClientResults(filtered) {
                                resultsDiv.innerHTML = '';
                                const noClientMsg = document.getElementById('no_client_found_msg');
                                if (filtered.length === 0) {
                                    if (noClientMsg) noClientMsg.style.display = 'block';
                                    createBtn.style.display = 'block';
                                    clientSelect.value = '';
                                    clientSelect.style.display = 'none';
                                    return;
                                }
                                if (noClientMsg) noClientMsg.style.display = 'none';
                                createBtn.style.display = 'none';
                                filtered.forEach(client => {
                                    const item = document.createElement('button');
                                    item.type = 'button';
                                    item.className = 'list-group-item list-group-item-action';
                                    item.textContent = client.name;
                                    item.onclick = () => {
                                        clientInput.value = client.name;
                                        clientSelect.innerHTML = `<option value="${client.id}" selected>${client.name}</option>`;
                                        clientSelect.style.display = '';
                                        resultsDiv.innerHTML = '';
                                        createBtn.style.display = 'none';
                                        if (noClientMsg) noClientMsg.style.display = 'none';
                                    };
                                    resultsDiv.appendChild(item);
                                });
                                clientSelect.value = '';
                                clientSelect.style.display = 'none';
                            }

                            clientInput.addEventListener('input', function() {
                                const val = this.value.trim().toLowerCase();
                                if (!val) {
                                    resultsDiv.innerHTML = '';
                                    clientSelect.value = '';
                                    clientSelect.style.display = 'none';
                                    createBtn.style.display = 'none';
                                    return;
                                }
                                const filtered = clients.filter(c => c.name.toLowerCase().includes(val));
                                showClientResults(filtered);
                            });

                            clientInput.addEventListener('focus', function() {
                                if (this.value.trim()) {
                                    const filtered = clients.filter(c => c.name.toLowerCase().includes(this.value.trim().toLowerCase()));
                                    showClientResults(filtered);
                                }
                            });

                            document.addEventListener('click', function(e) {
                                if (!resultsDiv.contains(e.target) && e.target !== clientInput) {
                                    resultsDiv.innerHTML = '';
                                }
                            });

                            createBtn.addEventListener('click', function() {
                                window.location.href = 'clients.php?source=add_client';
                            });

                            // If editing, pre-select client
                            <?php if ($client_id): ?>
                                const selectedClient = clients.find(c => c.id == <?php echo (int)$client_id; ?>);
                                if (selectedClient) {
                                    clientInput.value = selectedClient.name;
                                    clientSelect.innerHTML = `<option value="${selectedClient.id}" selected>${selectedClient.name}</option>`;
                                    clientSelect.style.display = '';
                                }
                            <?php endif; ?>
                            </script>
                            
                            <div class="col-md-6 mb-3">
                                <label for="engagement_id" class="form-label">Engagement ID</label>
                                <input type="text" id="engagement_id" name="title" class="form-control" value="<?php echo htmlspecialchars($title); ?>" readonly required>
                                <input type="hidden" id="engagement_id_hidden" name="engagement_id" value="">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="3"><?php echo htmlspecialchars($description); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="service_id" class="form-label">Service Type *</label>
                                <select id="service_id" name="service_id" class="form-control" required onchange="loadRuleVersions()">
                                    <option value="">Select Service</option>
                                    <?php
                                    if ($services_result && mysqli_num_rows($services_result) > 0) {
                                        mysqli_data_seek($services_result, 0);
                                        while ($service = mysqli_fetch_assoc($services_result)) {
                                            $selected = ($service_id == $service['service_id']) ? 'selected' : '';
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
                                <select id="dept_filter" class="form-control" onchange="loadEmployees()">
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
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Start Date *</label>
                                <input type="date" id="start_date" name="start_date" class="form-control" 
                                       value="<?php echo $start_date; ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="original_deadline" class="form-label">Deadline *</label>
                                <input type="date" id="original_deadline" name="original_deadline" class="form-control" 
                                       value="<?php echo $original_deadline; ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="evidence_required" name="evidence_required" <?php echo $evidence_required ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="evidence_required">
                                    Evidence Required for Completion
                                </label>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="submit_engagement" class="btn btn-primary btn-lg me-2">
                                    <i class="bi bi-check-circle me-1"></i> Create Engagement
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

<?php if ($showSuccessModal && $new_engagement_id): ?>
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
          <h5 class="mt-3">Engagement Created Successfully!</h5>
          <p class="text-muted mb-0">The engagement "<?php echo htmlspecialchars($title); ?>" has been created and assigned.</p>
        </div>
      </div>
      <div class="modal-footer justify-content-center border-0 pt-0">
        <a href="engagements.php" class="btn btn-success px-4">
          <i class="bi bi-list-ul me-2"></i>View All Engagements
        </a>
        <a href="engagements.php?source=add_engagement" class="btn btn-outline-success px-4">
          <i class="bi bi-plus-circle me-2"></i>Create Another
        </a>
        <a href="engagements.php?source=upload_evidence&id=<?php echo $new_engagement_id; ?>" class="btn btn-outline-primary px-4">
          <i class="bi bi-upload"></i>Upload Evidence
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
// --- Engagement ID Generation ---
function generateEngagementID() {
    // Example: ENG-YYMMDD-XXXX
    const now = new Date();
    const pad = n => n.toString().padStart(2, '0');
    const year = now.getFullYear().toString().slice(-2);
    const datePart = year + pad(now.getMonth()+1) + pad(now.getDate());
    const randPart = Math.floor(1000 + Math.random() * 9000);
    return `ENG-${datePart}-${randPart}`;
}

document.addEventListener('DOMContentLoaded', function() {
    const engagementIdField = document.getElementById('engagement_id');
    if (engagementIdField && !engagementIdField.value) {
        engagementIdField.value = generateEngagementID();
    }
    // Regenerate on form submit if empty
    document.getElementById('engagementForm').addEventListener('submit', function() {
        if (engagementIdField && !engagementIdField.value) {
            engagementIdField.value = generateEngagementID();
        }
    });
});

// If form is reset, generate a new ID
const engagementForm = document.getElementById('engagementForm');
if (engagementForm) {
    engagementForm.addEventListener('reset', function() {
        setTimeout(setEngagementID, 50);
    });
}

// Load rule versions when service is selected
function loadRuleVersions() {
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
                    options += `<option value="${rule.rule_id}">v${rule.rule_version} - Base: ${rule.base_points} pts (Eff: ${effectiveDate})</option>`;
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
function loadEmployees() {
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
                    options += `<option value="${emp.user_id}">${name}</option>`;
                    reviewerOptions += `<option value="${emp.user_id}">${name}</option>`;
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