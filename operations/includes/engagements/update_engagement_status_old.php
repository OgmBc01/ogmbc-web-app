<?php
ob_start();

// Check authentication
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    echo "<script>window.location.href = '../login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Get engagement ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>window.location.href = 'engagements.php';</script>";
    exit();
}

$engagement_id = (int)$_GET['id'];

// Fetch engagement details and verify ownership
$query = "SELECT e.*, c.company_name, s.service_name, s.service_category
          FROM engagements e
          JOIN clients c ON e.client_id = c.client_id
          JOIN service_types s ON e.service_id = s.service_id
          WHERE e.engagement_id = $engagement_id AND e.assigned_to = $user_id";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<script>window.location.href = 'engagements.php';</script>";
    exit();
}

$engagement = mysqli_fetch_assoc($result);

// Define allowed status transitions
$allowed_transitions = [
    'ASSIGNED' => ['IN_PROGRESS'],
    'IN_PROGRESS' => ['AWAITING_REVIEW', 'ASSIGNED'],
    'AWAITING_REVIEW' => ['SUBMITTED', 'IN_PROGRESS'],
    'SUBMITTED' => ['CLOSED', 'AWAITING_REVIEW'],
    'CLOSED' => []
];

// Define engagement types that require checklist
$checklist_required_types = ['Monthly Bookkeeping', 'Backlog accounting', 'Monthly Internal Audit'];

// Define checklist items with categories
$checklist_items = [
    'client_meeting' => [
        'label' => 'Last Visit / Meeting',
        'icon' => 'bi-calendar-check',
        'category' => 'client_interaction',
        'required' => true
    ],
    'report_delivered' => [
        'label' => 'Report delivered till',
        'icon' => 'bi-file-text',
        'category' => 'reporting',
        'required' => false
    ],
    'bank_reconciliation' => [
        'label' => 'Bank reconciliation',
        'icon' => 'bi-bank',
        'category' => 'accounting',
        'required' => true
    ],
    'account_receivables' => [
        'label' => 'Account receivables',
        'icon' => 'bi-credit-card',
        'category' => 'accounting',
        'required' => true
    ],
    'account_payables' => [
        'label' => 'Account payables',
        'icon' => 'bi-receipt',
        'category' => 'accounting',
        'required' => true
    ],
    'depreciation' => [
        'label' => 'Depreciation',
        'icon' => 'bi-graph-down',
        'category' => 'accounting',
        'required' => true
    ],
    'prepayments' => [
        'label' => 'Prepayments',
        'icon' => 'bi-clock-history',
        'category' => 'accounting',
        'required' => true
    ],
    'leave_salary' => [
        'label' => 'Leave salary',
        'icon' => 'bi-briefcase',
        'category' => 'payroll',
        'required' => true
    ],
    'gratuity' => [
        'label' => 'Gratuity',
        'icon' => 'bi-gift',
        'category' => 'payroll',
        'required' => true
    ],
    'salaries' => [
        'label' => 'Salaries',
        'icon' => 'bi-cash-stack',
        'category' => 'payroll',
        'required' => true
    ],
    'sales' => [
        'label' => 'Sales',
        'icon' => 'bi-graph-up',
        'category' => 'transactions',
        'required' => true
    ],
    'purchase' => [
        'label' => 'Purchase',
        'icon' => 'bi-cart',
        'category' => 'transactions',
        'required' => true
    ],
    'inventory' => [
        'label' => 'Inventory',
        'icon' => 'bi-box-seam',
        'category' => 'transactions',
        'required' => true
    ],
    'documentation_filing' => [
        'label' => 'Documentation and filing',
        'icon' => 'bi-folder2',
        'category' => 'documentation',
        'required' => true
    ],
    'expected_completion_date' => [
        'label' => 'Expected date to complete',
        'icon' => 'bi-calendar-date',
        'category' => 'planning',
        'required' => true,
        'type' => 'date'
    ],
    'other_matters' => [
        'label' => 'Any other matter',
        'icon' => 'bi-chat-text',
        'category' => 'notes',
        'required' => false,
        'type' => 'textarea'
    ],
    'tb_issues' => [
        'label' => 'TB issues',
        'icon' => 'bi-exclamation-triangle',
        'category' => 'issues',
        'required' => true,
        'type' => 'textarea'
    ]
];

$message = '';
$message_type = '';
$showSuccessModal = false;
$checklist_data = [];
$checklist_completed = false;

// Check if engagement requires checklist
$requires_checklist = in_array($engagement['service_name'], $checklist_required_types);

// Load existing checklist data if any
if ($requires_checklist) {
    $checklist_query = "SELECT * FROM engagement_checklist WHERE engagement_id = $engagement_id";
    $checklist_result = mysqli_query($connection, $checklist_query);
    if ($checklist_result && mysqli_num_rows($checklist_result) > 0) {
        $checklist_data = mysqli_fetch_assoc($checklist_result);
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    
    $new_status = mysqli_real_escape_string($connection, $_POST['new_status']);
    $notes = mysqli_real_escape_string($connection, trim($_POST['notes'] ?? ''));
    
    // Validate transition
    if (!in_array($new_status, $allowed_transitions[$engagement['status']] ?? [])) {
        $message = "Invalid status transition from {$engagement['status']} to $new_status.";
        $message_type = "danger";
    } else {
        if ($new_status == 'CLOSED' && $requires_checklist) {
            $checklist_valid = true;
            $missing_items = [];
            foreach ($checklist_items as $key => $item) {
                if ($item['required']) {
                    // For accounting, payroll, transactions: check radio+date
                    if (in_array($item['category'], ['accounting', 'payroll', 'transactions'])) {
                        $yn = $_POST['checklist_' . $key . '_yn'] ?? '';
                        $date = $_POST['checklist_' . $key . '_date'] ?? '';
                        if ($yn !== 'Yes') {
                            $value = 'No';
                        } else {
                            $value = $date;
                        }
                        if ($yn !== 'Yes' && $yn !== 'No') {
                            $checklist_valid = false;
                            $missing_items[] = $item['label'];
                        } else if ($yn === 'Yes' && empty($date)) {
                            $checklist_valid = false;
                            $missing_items[] = $item['label'] . ' (date required)';
                        }
                    } else {
                        $post_key = 'checklist_' . $key;
                        $value = isset($_POST[$post_key]) ? trim($_POST[$post_key]) : '';
                        if ($value === '' || $value === null) {
                            $checklist_valid = false;
                            $missing_items[] = $item['label'];
                        }
                    }
                }
            }
            
            if (!$checklist_valid) {
                $message = "Cannot close this engagement. Please complete all required checklist items:<br><ul><li>" . implode("</li><li>", array_map('htmlspecialchars', $missing_items)) . "</li></ul>";
                $message_type = "danger";
            } else {
                // Save checklist data
                $checklist_data_save = [];
                foreach ($checklist_items as $key => $item) {
                    if (in_array($item['category'], ['accounting', 'payroll', 'transactions'])) {
                        $yn = $_POST['checklist_' . $key . '_yn'] ?? '';
                        $date = $_POST['checklist_' . $key . '_date'] ?? '';
                        if ($yn === 'Yes' && $date) {
                            $value = mysqli_real_escape_string($connection, $date);
                        } else {
                            $value = 'No';
                        }
                    } else {
                        $value = isset($_POST['checklist_' . $key]) ? mysqli_real_escape_string($connection, trim($_POST['checklist_' . $key])) : '';
                    }
                    $checklist_data_save[$key] = $value;
                }
                
                // Insert or update checklist
                if ($checklist_data) {
                    $update_checklist = "UPDATE engagement_checklist SET ";
                    $updates = [];
                    foreach ($checklist_items as $key => $item) {
                        $updates[] = "$key = '{$checklist_data_save[$key]}'";
                    }
                    $updates[] = "completed_at = NOW()";
                    $update_checklist .= implode(", ", $updates);
                    $update_checklist .= " WHERE engagement_id = $engagement_id";
                    mysqli_query($connection, $update_checklist);
                } else {
                    $insert_checklist = "INSERT INTO engagement_checklist (engagement_id, ";
                    $values = "";
                    $fields = [];
                    $field_values = [];
                    foreach ($checklist_items as $key => $item) {
                        $fields[] = $key;
                        $field_values[] = "'{$checklist_data_save[$key]}'";
                    }
                    $insert_checklist .= implode(", ", $fields) . ", completed_at) VALUES ($engagement_id, ";
                    $insert_checklist .= implode(", ", $field_values) . ", NOW())";
                    mysqli_query($connection, $insert_checklist);
                }
            }
        }
        
        // Check if evidence is required and uploaded
        if (empty($message) && $new_status == 'SUBMITTED' && $engagement['evidence_required']) {
            $evidence_check = "SELECT COUNT(*) as count FROM evidence WHERE engagement_id = $engagement_id";
            $evidence_result = mysqli_query($connection, $evidence_check);
            $evidence_count = mysqli_fetch_assoc($evidence_result)['count'];
            
            if ($evidence_count == 0) {
                $message = "Cannot submit engagement without uploading required evidence.";
                $message_type = "danger";
            }
        }
        
        if (empty($message)) {
            // Update status
            $update_query = "UPDATE engagements SET status = '$new_status' WHERE engagement_id = $engagement_id";
            
            if (mysqli_query($connection, $update_query)) {
                // Add to status history
                $history_query = "INSERT INTO engagement_status_history 
                                 (engagement_id, old_status, new_status, changed_by, notes)
                                 VALUES ($engagement_id, '{$engagement['status']}', '$new_status', $user_id, '$notes')";
                mysqli_query($connection, $history_query);
                
                // If status is CLOSED, calculate points
                if ($new_status == 'CLOSED') {
                    calculate_engagement_points($connection, $engagement_id);
                }
                
                $showSuccessModal = true;
            } else {
                $message = "Error updating status: " . mysqli_error($connection);
                $message_type = "danger";
            }
        }
    }
}

// Function to calculate points
function calculate_engagement_points($connection, $engagement_id) {
    $query = "SELECT e.*, r.points_within_deadline, r.points_tier_1, r.points_tier_2, r.points_tier_3
              FROM engagements e
              JOIN service_point_rules r ON e.rule_version_id = r.rule_id
              WHERE e.engagement_id = $engagement_id";
    $result = mysqli_query($connection, $query);
    $data = mysqli_fetch_assoc($result);
    
    if (!$data) return;
    
    $completion_date = new DateTime();
    $deadline = new DateTime($data['approved_deadline'] ?? $data['original_deadline']);
    $delay_days = $completion_date > $deadline ? $completion_date->diff($deadline)->days : 0;
    
    if ($delay_days == 0) {
        $points = $data['points_within_deadline'];
    } elseif ($delay_days >= 5 && $delay_days <= 15) {
        $points = $data['points_tier_1'];
    } elseif ($delay_days >= 16 && $delay_days <= 25) {
        $points = $data['points_tier_2'];
    } else {
        $points = $data['points_tier_3'];
    }
    
    $update = "UPDATE engagements SET 
               points_awarded = $points,
               delay_days = $delay_days,
               completion_date = CURDATE()
               WHERE engagement_id = $engagement_id";
    mysqli_query($connection, $update);
    
    $ledger = "INSERT INTO points_ledger 
               (employee_id, source_type, source_id, points, points_type, description, notes, created_by)
               VALUES (
                   {$data['assigned_to']}, 
                   'ENGAGEMENT', 
                   $engagement_id, 
                   $points, 
                   'EARNED', 
                   'Points awarded for completing engagement: {$data['title']}', 
                   '',
                   {$data['assigned_to']}
               )";
    mysqli_query($connection, $ledger);
}

if (ob_get_level() > 0) {
    ob_end_flush();
}
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header dark-header">
                    <h5 class="card-title">
                        <i class="bi bi-arrow-repeat me-2"></i>Update Engagement Status
                    </h5>
                    <a href="engagements.php" class="btn btn-sm btn-outline-light">
                        <i class="bi bi-arrow-left me-1"></i>Back
                    </a>
                </div>
                <div class="card-body">
                    
                    <!-- Engagement Summary -->
                    <div class="engagement-summary mb-4">
                        <h6><?php echo htmlspecialchars($engagement['title']); ?></h6>
                        <div class="d-flex justify-content-between mt-2">
                            <span class="text-muted">Client: <?php echo htmlspecialchars($engagement['company_name']); ?></span>
                            <span class="badge bg-<?php 
                                echo $engagement['status'] == 'IN_PROGRESS' ? 'primary' : 
                                    ($engagement['status'] == 'AWAITING_REVIEW' ? 'warning' : 
                                    ($engagement['status'] == 'SUBMITTED' ? 'info' : 'secondary')); 
                            ?>">Current: <?php echo $engagement['status']; ?></span>
                        </div>
                        <?php if ($requires_checklist): ?>
                            <div class="mt-2">
                                <span class="badge bg-info">
                                    <i class="bi bi-clipboard-check me-1"></i>
                                    Completion Checklist Required
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($message) && !$showSuccessModal): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="statusForm">
                        <div class="mb-3">
                            <label for="new_status" class="form-label">New Status</label>
                            <select class="form-select" id="new_status" name="new_status" required>
                                <option value="">Select Status</option>
                                <?php
                                foreach ($allowed_transitions[$engagement['status']] ?? [] as $transition):
                                    // For SUBMIT, check if the last evidence is approved
                                    if ($transition === 'SUBMITTED') {
                                        $last_evidence_query = "SELECT status FROM evidence WHERE engagement_id = $engagement_id ORDER BY evidence_id DESC LIMIT 1";
                                        $last_evidence_result = mysqli_query($connection, $last_evidence_query);
                                        $last_evidence_status = ($last_evidence_result && mysqli_num_rows($last_evidence_result) > 0)
                                            ? mysqli_fetch_assoc($last_evidence_result)['status']
                                            : '';
                                        if ($last_evidence_status === 'APPROVED') {
                                            echo '<option value="SUBMITTED">SUBMIT</option>';
                                        }
                                    } else {
                                        echo '<option value="' . $transition . '">' . str_replace('_', ' ', $transition) . '</option>';
                                    }
                                endforeach;
                                ?>
                            </select>
                            <small class="text-muted">
                                Current status: <strong><?php echo str_replace('_', ' ', $engagement['status']); ?></strong>
                            </small>
                        </div>

                        <!-- Checklist Section - Only show when closing and required -->
                        <?php if ($requires_checklist): ?>
                        <div id="checklistSection" style="display: none;">
                            <hr>
                            <div class="checklist-container">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="bi bi-clipboard-check fs-4 me-2 text-primary"></i>
                                    <h5 class="mb-0">Engagement Completion Checklist</h5>
                                    <span class="badge bg-danger ms-2">Required for Closing</span>
                                </div>
                                <p class="text-muted small mb-3">Please complete all required fields before closing this engagement.</p>
                                
                                <div class="row">
                                    <!-- Client Interaction Section -->
                                    <div class="col-md-6 mb-4">
                                        <div class="checklist-section">
                                            <h6 class="section-title">
                                                <i class="bi bi-chat-dots me-2"></i>Client Interaction
                                            </h6>
                                            <?php foreach ($checklist_items as $key => $item): ?>
                                                <?php if ($item['category'] == 'client_interaction'): ?>
                                                <div class="checklist-item">
                                                    <label class="form-label fw-semibold">
                                                        <i class="bi <?php echo $item['icon']; ?> me-2 text-muted"></i>
                                                        <?php echo $item['label']; ?>
                                                        <?php if ($item['required']): ?><span class="text-danger">*</span><?php endif; ?>
                                                    </label>
                                                    <?php if (isset($item['type']) && $item['type'] == 'date'): ?>
                                                        <input type="date" class="form-control" name="checklist_<?php echo $key; ?>" 
                                                               value="<?php echo htmlspecialchars($checklist_data[$key] ?? ''); ?>">
                                                    <?php elseif (isset($item['type']) && $item['type'] == 'textarea'): ?>
                                                        <textarea class="form-control" name="checklist_<?php echo $key; ?>" rows="2" 
                                                                  placeholder="Enter <?php echo strtolower($item['label']); ?>..."><?php echo htmlspecialchars($checklist_data[$key] ?? ''); ?></textarea>
                                                    <?php else: ?>
                                                        <input type="text" class="form-control" name="checklist_<?php echo $key; ?>" 
                                                               placeholder="Enter <?php echo strtolower($item['label']); ?>"
                                                               value="<?php echo htmlspecialchars($checklist_data[$key] ?? ''); ?>">
                                                    <?php endif; ?>
                                                </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Accounting Section -->
                                    <div class="col-md-6 mb-4">
                                        <div class="checklist-section">
                                            <h6 class="section-title">
                                                <i class="bi bi-calculator me-2"></i>Accounting Tasks
                                            </h6>
                                            <?php foreach ($checklist_items as $key => $item): ?>
                                                <?php if ($item['category'] == 'accounting'): ?>
                                                <div class="checklist-item">
                                                    <label class="form-label fw-semibold">
                                                        <i class="bi <?php echo $item['icon']; ?> me-2 text-muted"></i>
                                                        <?php echo $item['label']; ?>
                                                        <?php if ($item['required']): ?><span class="text-danger">*</span><?php endif; ?>
                                                    </label>
                                                    <?php 
                                                    $val = $checklist_data[$key] ?? '';
                                                    $is_yes = ($val && $val !== 'No') ? 'checked' : '';
                                                    $is_no = ($val === 'No' || $val === '') ? 'checked' : '';
                                                    $date_val = ($is_yes && $val && $val !== 'Yes') ? $val : '';
                                                    ?>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="checklist_<?php echo $key; ?>_yn" id="<?php echo $key; ?>_yes" value="Yes" <?php echo $is_yes; ?>>
                                                        <label class="form-check-label" for="<?php echo $key; ?>_yes">Yes, done</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="checklist_<?php echo $key; ?>_yn" id="<?php echo $key; ?>_no" value="No" <?php echo $is_no; ?>>
                                                        <label class="form-check-label" for="<?php echo $key; ?>_no">No</label>
                                                    </div>
                                                    <div class="mt-2" id="<?php echo $key; ?>_date_wrap" style="display:<?php echo $is_yes ? 'block' : 'none'; ?>;">
                                                        <input type="date" class="form-control" name="checklist_<?php echo $key; ?>_date" value="<?php echo htmlspecialchars($date_val); ?>" placeholder="Completion date">
                                                    </div>
                                                    <script>
                                                    document.addEventListener('DOMContentLoaded', function() {
                                                        var yesRadio = document.getElementById('<?php echo $key; ?>_yes');
                                                        var noRadio = document.getElementById('<?php echo $key; ?>_no');
                                                        var dateWrap = document.getElementById('<?php echo $key; ?>_date_wrap');
                                                        if (yesRadio && noRadio && dateWrap) {
                                                            yesRadio.addEventListener('change', function() {
                                                                if (this.checked) dateWrap.style.display = 'block';
                                                            });
                                                            noRadio.addEventListener('change', function() {
                                                                if (this.checked) dateWrap.style.display = 'none';
                                                            });
                                                        }
                                                    });
                                                    </script>
                                                </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Payroll Section -->
                                    <div class="col-md-6 mb-4">
                                        <div class="checklist-section">
                                            <h6 class="section-title">
                                                <i class="bi bi-cash-stack me-2"></i>Payroll & Benefits
                                            </h6>
                                            <?php foreach ($checklist_items as $key => $item): ?>
                                                <?php if ($item['category'] == 'payroll'): ?>
                                                <div class="checklist-item">
                                                    <label class="form-label fw-semibold">
                                                        <i class="bi <?php echo $item['icon']; ?> me-2 text-muted"></i>
                                                        <?php echo $item['label']; ?>
                                                        <?php if ($item['required']): ?><span class="text-danger">*</span><?php endif; ?>
                                                    </label>
                                                    <?php 
                                                    $val = $checklist_data[$key] ?? '';
                                                    $is_yes = ($val && $val !== 'No') ? 'checked' : '';
                                                    $is_no = ($val === 'No' || $val === '') ? 'checked' : '';
                                                    $date_val = ($is_yes && $val && $val !== 'Yes') ? $val : '';
                                                    ?>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="checklist_<?php echo $key; ?>_yn" id="<?php echo $key; ?>_yes" value="Yes" <?php echo $is_yes; ?>>
                                                        <label class="form-check-label" for="<?php echo $key; ?>_yes">Yes, done</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="checklist_<?php echo $key; ?>_yn" id="<?php echo $key; ?>_no" value="No" <?php echo $is_no; ?>>
                                                        <label class="form-check-label" for="<?php echo $key; ?>_no">No</label>
                                                    </div>
                                                    <div class="mt-2" id="<?php echo $key; ?>_date_wrap" style="display:<?php echo $is_yes ? 'block' : 'none'; ?>;">
                                                        <input type="date" class="form-control" name="checklist_<?php echo $key; ?>_date" value="<?php echo htmlspecialchars($date_val); ?>" placeholder="Completion date">
                                                    </div>
                                                    <script>
                                                    document.addEventListener('DOMContentLoaded', function() {
                                                        var yesRadio = document.getElementById('<?php echo $key; ?>_yes');
                                                        var noRadio = document.getElementById('<?php echo $key; ?>_no');
                                                        var dateWrap = document.getElementById('<?php echo $key; ?>_date_wrap');
                                                        if (yesRadio && noRadio && dateWrap) {
                                                            yesRadio.addEventListener('change', function() {
                                                                if (this.checked) dateWrap.style.display = 'block';
                                                            });
                                                            noRadio.addEventListener('change', function() {
                                                                if (this.checked) dateWrap.style.display = 'none';
                                                            });
                                                        }
                                                    });
                                                    </script>
                                                </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Transactions Section -->
                                    <div class="col-md-6 mb-4">
                                        <div class="checklist-section">
                                            <h6 class="section-title">
                                                <i class="bi bi-arrow-left-right me-2"></i>Transactions
                                            </h6>
                                            <?php foreach ($checklist_items as $key => $item): ?>
                                                <?php if ($item['category'] == 'transactions'): ?>
                                                <div class="checklist-item">
                                                    <label class="form-label fw-semibold">
                                                        <i class="bi <?php echo $item['icon']; ?> me-2 text-muted"></i>
                                                        <?php echo $item['label']; ?>
                                                        <?php if ($item['required']): ?><span class="text-danger">*</span><?php endif; ?>
                                                    </label>
                                                    <?php 
                                                    $val = $checklist_data[$key] ?? '';
                                                    $is_yes = ($val && $val !== 'No') ? 'checked' : '';
                                                    $is_no = ($val === 'No' || $val === '') ? 'checked' : '';
                                                    $date_val = ($is_yes && $val && $val !== 'Yes') ? $val : '';
                                                    ?>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="checklist_<?php echo $key; ?>_yn" id="<?php echo $key; ?>_yes" value="Yes" <?php echo $is_yes; ?>>
                                                        <label class="form-check-label" for="<?php echo $key; ?>_yes">Yes, done</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="checklist_<?php echo $key; ?>_yn" id="<?php echo $key; ?>_no" value="No" <?php echo $is_no; ?>>
                                                        <label class="form-check-label" for="<?php echo $key; ?>_no">No</label>
                                                    </div>
                                                    <div class="mt-2" id="<?php echo $key; ?>_date_wrap" style="display:<?php echo $is_yes ? 'block' : 'none'; ?>;">
                                                        <input type="date" class="form-control" name="checklist_<?php echo $key; ?>_date" value="<?php echo htmlspecialchars($date_val); ?>" placeholder="Completion date">
                                                    </div>
                                                    <script>
                                                    document.addEventListener('DOMContentLoaded', function() {
                                                        var yesRadio = document.getElementById('<?php echo $key; ?>_yes');
                                                        var noRadio = document.getElementById('<?php echo $key; ?>_no');
                                                        var dateWrap = document.getElementById('<?php echo $key; ?>_date_wrap');
                                                        if (yesRadio && noRadio && dateWrap) {
                                                            yesRadio.addEventListener('change', function() {
                                                                if (this.checked) dateWrap.style.display = 'block';
                                                            });
                                                            noRadio.addEventListener('change', function() {
                                                                if (this.checked) dateWrap.style.display = 'none';
                                                            });
                                                        }
                                                    });
                                                    </script>
                                                </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Documentation & Planning -->
                                    <div class="col-md-6 mb-4">
                                        <div class="checklist-section">
                                            <h6 class="section-title">
                                                <i class="bi bi-folder2 me-2"></i>Documentation & Planning
                                            </h6>
                                            <?php foreach ($checklist_items as $key => $item): ?>
                                                <?php if (in_array($item['category'], ['documentation', 'planning'])): ?>
                                                <div class="checklist-item">
                                                    <label class="form-label fw-semibold">
                                                        <i class="bi <?php echo $item['icon']; ?> me-2 text-muted"></i>
                                                        <?php echo $item['label']; ?>
                                                        <?php if ($item['required']): ?><span class="text-danger">*</span><?php endif; ?>
                                                    </label>
                                                    <?php if (isset($item['type']) && $item['type'] == 'date'): ?>
                                                        <input type="date" class="form-control" name="checklist_<?php echo $key; ?>" 
                                                               value="<?php echo htmlspecialchars($checklist_data[$key] ?? ''); ?>">
                                                    <?php else: ?>
                                                        <input type="text" class="form-control" name="checklist_<?php echo $key; ?>" 
                                                               placeholder="Enter <?php echo strtolower($item['label']); ?>"
                                                               value="<?php echo htmlspecialchars($checklist_data[$key] ?? ''); ?>">
                                                    <?php endif; ?>
                                                </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Notes & Issues -->
                                    <div class="col-12 mb-4">
                                        <div class="checklist-section">
                                            <h6 class="section-title">
                                                <i class="bi bi-chat-text me-2"></i>Additional Notes & Issues
                                            </h6>
                                            <?php foreach ($checklist_items as $key => $item): ?>
                                                <?php if (in_array($item['category'], ['notes', 'issues'])): ?>
                                                <div class="checklist-item">
                                                    <label class="form-label fw-semibold">
                                                        <i class="bi <?php echo $item['icon']; ?> me-2 text-muted"></i>
                                                        <?php echo $item['label']; ?>
                                                        <?php if ($item['required']): ?><span class="text-danger">*</span><?php endif; ?>
                                                    </label>
                                                    <textarea class="form-control" name="checklist_<?php echo $key; ?>" rows="3" 
                                                              placeholder="Enter <?php echo strtolower($item['label']); ?>..."><?php echo htmlspecialchars($checklist_data[$key] ?? ''); ?></textarea>
                                                </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                        </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Status Change Notes (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="Add any notes about this status change..."></textarea>
                        </div>

                        <!-- Status-specific tips -->
                        <div class="status-tips mb-3">
                            <?php if ($engagement['status'] == 'IN_PROGRESS'): ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Moving to "Awaiting Review"? Make sure all required evidence is uploaded.
                                </div>
                            <?php elseif ($engagement['status'] == 'AWAITING_REVIEW'): ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    Once submitted, the engagement will be reviewed by your supervisor.
                                </div>
                            <?php elseif ($engagement['status'] == 'SUBMITTED'): ?>
                                <div class="alert alert-success">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Closing this engagement will award points based on completion time.
                                    <?php if ($requires_checklist): ?>
                                        <strong>Please complete the checklist above before closing.</strong>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="text-center">
                            <button type="submit" name="update_status" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle me-2"></i>Update Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pro Tip Card -->
<div class="row mt-4">
    <div class="col-12">
        <div class="pro-tip-card">
            <div class="row align-items-center">
                <div class="col-md-9">
                    <h6 class="text-white mb-2">
                        <i class="bi bi-lightbulb me-2"></i>
                        Status Update Tips
                    </h6>
                    <p class="text-white-50 small mb-md-0">
                        ✅ <strong>ASSIGNED → IN PROGRESS:</strong> Start working on the task.<br>
                        ✅ <strong>IN PROGRESS → AWAITING REVIEW:</strong> Ready for review, ensure evidence is uploaded.<br>
                        ✅ <strong>AWAITING REVIEW → SUBMITTED:</strong> Final submission, cannot be changed after this.<br>
                        ✅ <strong>SUBMITTED → CLOSED:</strong> Engagement complete, points will be awarded.
                        <?php if ($requires_checklist): ?> For Monthly bookkeeping, Backlog Accounting, and Monthly Internal Audit engagements, a completion checklist must be filled.<?php endif; ?>
                    </p>
                </div>
                <div class="col-md-3 text-md-end">
                    <i class="bi bi-lightbulb display-4 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('new_status');
    const checklistSection = document.getElementById('checklistSection');
    
    statusSelect.addEventListener('change', function() {
        if (checklistSection) {
            if (this.value === 'CLOSED') {
                checklistSection.style.display = 'block';
            } else {
                checklistSection.style.display = 'none';
            }
        }
    });
});
</script>

<?php if ($showSuccessModal): ?>
<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Status Updated!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h5 class="mt-3">Engagement Status Updated Successfully!</h5>
                <p class="text-muted">The engagement has been moved to <?php echo $new_status ?? 'new status'; ?>.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <a href="engagements.php?source=view&id=<?php echo $engagement_id; ?>" class="btn btn-success px-4">
                    <i class="bi bi-eye me-2"></i>View Engagement
                </a>
                <a href="engagements.php" class="btn btn-outline-success px-4">
                    <i class="bi bi-list-ul me-2"></i>All Engagements
                </a>
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

<style>
.engagement-summary {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
}

.status-tips {
    font-size: 0.9rem;
}

.pro-tip-card {
    background: linear-gradient(135deg, #2c3e50 0%, #1a2634 100%);
    border-radius: 16px;
    padding: 20px;
    color: white;
}

.checklist-container {
    background: #fef9e6;
    border-radius: 16px;
    padding: 20px;
    border-left: 4px solid #ffc107;
}

.checklist-section {
    background: white;
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.checklist-section:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.section-title {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 2px solid #ffc107;
    display: inline-block;
}

.checklist-item {
    margin-bottom: 15px;
}

.checklist-item:last-child {
    margin-bottom: 0;
}

.checklist-item .form-label {
    font-size: 0.85rem;
    margin-bottom: 5px;
}

.checklist-item .form-control {
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    transition: all 0.2s ease;
}

.checklist-item .form-control:focus {
    border-color: #ffc107;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
}

.bg-info {
    background-color: #17a2b8 !important;
}

@media (max-width: 768px) {
    .checklist-container {
        padding: 15px;
    }
    
    .checklist-section {
        margin-bottom: 20px;
    }
}
</style>