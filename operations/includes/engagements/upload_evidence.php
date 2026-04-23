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

// Define engagement types that require checklist
$checklist_required_types = ['Monthly Bookkeeping', 'Backlog accounting', 'Monthly Internal Audit'];
$requires_checklist = in_array($engagement['service_name'], $checklist_required_types);

// Define checklist items with categories
$checklist_items = [
    'client_meeting' => [
        'label' => 'Last Visit / Meeting',
        'icon' => 'bi-calendar-check',
        'category' => 'client_interaction',
        'required' => true,
        'type' => 'text'
    ],
    'report_delivered' => [
        'label' => 'Report delivered till',
        'icon' => 'bi-file-text',
        'category' => 'reporting',
        'required' => false,
        'type' => 'text'
    ],
    'bank_reconciliation' => [
        'label' => 'Bank reconciliation',
        'icon' => 'bi-bank',
        'category' => 'accounting',
        'required' => true,
        'type' => 'radio_date'
    ],
    'account_receivables' => [
        'label' => 'Account receivables',
        'icon' => 'bi-credit-card',
        'category' => 'accounting',
        'required' => true,
        'type' => 'radio_date'
    ],
    'account_payables' => [
        'label' => 'Account payables',
        'icon' => 'bi-receipt',
        'category' => 'accounting',
        'required' => true,
        'type' => 'radio_date'
    ],
    'depreciation' => [
        'label' => 'Depreciation',
        'icon' => 'bi-graph-down',
        'category' => 'accounting',
        'required' => true,
        'type' => 'radio_date'
    ],
    'prepayments' => [
        'label' => 'Prepayments',
        'icon' => 'bi-clock-history',
        'category' => 'accounting',
        'required' => true,
        'type' => 'radio_date'
    ],
    'leave_salary' => [
        'label' => 'Leave salary',
        'icon' => 'bi-briefcase',
        'category' => 'payroll',
        'required' => true,
        'type' => 'radio_date'
    ],
    'gratuity' => [
        'label' => 'Gratuity',
        'icon' => 'bi-gift',
        'category' => 'payroll',
        'required' => true,
        'type' => 'radio_date'
    ],
    'salaries' => [
        'label' => 'Salaries',
        'icon' => 'bi-cash-stack',
        'category' => 'payroll',
        'required' => true,
        'type' => 'radio_date'
    ],
    'sales' => [
        'label' => 'Sales',
        'icon' => 'bi-graph-up',
        'category' => 'transactions',
        'required' => true,
        'type' => 'radio_date'
    ],
    'purchase' => [
        'label' => 'Purchase',
        'icon' => 'bi-cart',
        'category' => 'transactions',
        'required' => true,
        'type' => 'radio_date'
    ],
    'inventory' => [
        'label' => 'Inventory',
        'icon' => 'bi-box-seam',
        'category' => 'transactions',
        'required' => true,
        'type' => 'radio_date'
    ],
    'documentation_filing' => [
        'label' => 'Documentation and filing',
        'icon' => 'bi-folder2',
        'category' => 'documentation',
        'required' => true,
        'type' => 'text'
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

// Load existing checklist data if any
$checklist_data = [];
if ($requires_checklist) {
    $checklist_query = "SELECT * FROM engagement_checklist WHERE engagement_id = $engagement_id";
    $checklist_result = mysqli_query($connection, $checklist_query);
    if ($checklist_result && mysqli_num_rows($checklist_result) > 0) {
        $checklist_data = mysqli_fetch_assoc($checklist_result);
    }
}

// Fetch existing evidence
$evidence_query = "SELECT * FROM evidence WHERE engagement_id = $engagement_id ORDER BY uploaded_at DESC";
$evidence_result = mysqli_query($connection, $evidence_query);

$message = '';
$message_type = '';
$showSuccessModal = false;

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_evidence'])) {
    
    // First, check if checklist is required and validate it
    $checklist_valid = true;
    $missing_items = [];
    
    if ($requires_checklist) {
        foreach ($checklist_items as $key => $item) {
            if ($item['required']) {
                switch ($item['type']) {
                    case 'radio_date':
                        $yn = isset($_POST['checklist_' . $key . '_yn']) ? $_POST['checklist_' . $key . '_yn'] : '';
                        $date = isset($_POST['checklist_' . $key . '_date']) ? trim($_POST['checklist_' . $key . '_date']) : '';
                        
                        if ($yn === '' || $yn === null) {
                            $checklist_valid = false;
                            $missing_items[] = $item['label'] . ' (radio not selected)';
                        } elseif ($yn === 'Yes') {
                            if ($date === '' || $date === null) {
                                $checklist_valid = false;
                                $missing_items[] = $item['label'] . ' (date required when "Yes" is selected)';
                            } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                                $checklist_valid = false;
                                $missing_items[] = $item['label'] . ' (invalid date format, use YYYY-MM-DD)';
                            }
                        }
                        break;
                        
                    case 'date':
                        $value = isset($_POST['checklist_' . $key]) ? trim($_POST['checklist_' . $key]) : '';
                        if ($value === '' || $value === null) {
                            $checklist_valid = false;
                            $missing_items[] = $item['label'] . ' (date required)';
                        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                            $checklist_valid = false;
                            $missing_items[] = $item['label'] . ' (invalid date format, use YYYY-MM-DD)';
                        }
                        break;
                        
                    case 'textarea':
                    case 'text':
                        $value = isset($_POST['checklist_' . $key]) ? trim($_POST['checklist_' . $key]) : '';
                        if ($value === '' || $value === null) {
                            $checklist_valid = false;
                            $missing_items[] = $item['label'] . ' (text required)';
                        }
                        break;
                }
            }
        }
    }
    
    if (!$checklist_valid && $requires_checklist) {
        $message = "<strong>Checklist validation failed.</strong><br>Please complete all required checklist items before uploading evidence:<br><ul><li>"
            . implode("</li><li>", array_map('htmlspecialchars', $missing_items)) . "</li></ul>";
        $message_type = "danger";
    } elseif (!isset($_FILES['evidence_file']) || $_FILES['evidence_file']['error'] !== UPLOAD_ERR_OK) {
        $message = "Please select a file to upload.";
        $message_type = "danger";
    } else {
        $file = $_FILES['evidence_file'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if ($file_size > 50 * 1024 * 1024) { // 50MB max
            $message = "File size too large. Maximum size: 50MB";
            $message_type = "danger";
        } else {
            // Create upload directory
            $upload_dir = "../uploads/evidence/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename
            $new_filename = "evidence_" . $engagement_id . "_" . time() . "_" . rand(1000, 9999) . "." . $ext;
            $target_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file_tmp, $target_path)) {
                // Save to database
                $insert_query = "INSERT INTO evidence 
                                (engagement_id, file_name, file_path, uploaded_by)
                                VALUES 
                                ($engagement_id, '" . mysqli_real_escape_string($connection, $file_name) . "', 
                                 '$new_filename', $user_id)";
                
                if (mysqli_query($connection, $insert_query)) {
                    // Save checklist data if required
                    if ($requires_checklist) {
                        $checklist_data_save = [];
                        foreach ($checklist_items as $key => $item) {
                            switch ($item['type']) {
                                case 'radio_date':
                                    $yn = isset($_POST['checklist_' . $key . '_yn']) ? $_POST['checklist_' . $key . '_yn'] : '';
                                    $date = isset($_POST['checklist_' . $key . '_date']) ? trim($_POST['checklist_' . $key . '_date']) : '';
                                    if ($yn === 'Yes' && !empty($date)) {
                                        $value = mysqli_real_escape_string($connection, $date);
                                    } elseif ($yn === 'No') {
                                        $value = 'No';
                                    } else {
                                        $value = null;
                                    }
                                    break;
                                    
                                case 'date':
                                    $value = isset($_POST['checklist_' . $key]) ? mysqli_real_escape_string($connection, trim($_POST['checklist_' . $key])) : null;
                                    break;
                                    
                                case 'textarea':
                                case 'text':
                                    $value = isset($_POST['checklist_' . $key]) ? mysqli_real_escape_string($connection, trim($_POST['checklist_' . $key])) : null;
                                    break;
                                    
                                default:
                                    $value = isset($_POST['checklist_' . $key]) ? mysqli_real_escape_string($connection, trim($_POST['checklist_' . $key])) : null;
                                    break;
                            }
                            $checklist_data_save[$key] = $value;
                        }
                        
                        // Insert or update checklist
                        if (!empty($checklist_data)) {
                            $update_checklist = "UPDATE engagement_checklist SET ";
                            $updates = [];
                            foreach ($checklist_items as $key => $item) {
                                $value = $checklist_data_save[$key];
                                if ($value === null) {
                                    $updates[] = "$key = NULL";
                                } else {
                                    $updates[] = "$key = '$value'";
                                }
                            }
                            $update_checklist .= implode(", ", $updates);
                            $update_checklist .= " WHERE engagement_id = $engagement_id";
                            mysqli_query($connection, $update_checklist);
                        } else {
                            $fields = array_keys($checklist_items);
                            $field_values = [];
                            foreach ($fields as $field) {
                                $value = $checklist_data_save[$field];
                                if ($value === null) {
                                    $field_values[] = "NULL";
                                } else {
                                    $field_values[] = "'$value'";
                                }
                            }
                            $insert_checklist = "INSERT INTO engagement_checklist (engagement_id, " . implode(", ", $fields) . ") VALUES ($engagement_id, " . implode(", ", $field_values) . ")";
                            mysqli_query($connection, $insert_checklist);
                        }
                    }
                    
                    // Automatically set engagement status to AWAITING_REVIEW if not already in AWAITING_REVIEW, SUBMITTED, or CLOSED
                    $current_status = $engagement['status'];
                    if (!in_array($current_status, ['AWAITING_REVIEW', 'SUBMITTED', 'CLOSED'])) {
                        $update_status_query = "UPDATE engagements SET status = 'AWAITING_REVIEW' WHERE engagement_id = $engagement_id";
                        mysqli_query($connection, $update_status_query);
                        // Also add to status history
                        $history_query = "INSERT INTO engagement_status_history (engagement_id, old_status, new_status, changed_by, notes) VALUES ($engagement_id, '" . mysqli_real_escape_string($connection, $current_status) . "', 'AWAITING_REVIEW', $user_id, 'Status auto-updated to AWAITING_REVIEW after evidence upload.')";
                        mysqli_query($connection, $history_query);
                        // Update local variable for UI
                        $engagement['status'] = 'AWAITING_REVIEW';
                    }
                    $showSuccessModal = true;
                    // Add to activity log
                    $activity_query = "INSERT INTO user_activity_log 
                                      (user_id, activity_type, description, ip_address)
                                      VALUES ($user_id, 'evidence_upload', 'Uploaded evidence for engagement #$engagement_id', '{$_SERVER['REMOTE_ADDR']}')";
                    mysqli_query($connection, $activity_query);
                    // Refresh evidence list
                    $evidence_result = mysqli_query($connection, $evidence_query);
                } else {
                    $message = "Error saving evidence record: " . mysqli_error($connection);
                    $message_type = "danger";
                }
            } else {
                $message = "Error uploading file.";
                $message_type = "danger";
            }
        }
    }
}

ob_end_flush();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <!-- Main Upload Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header dark-header">
                    <h5 class="card-title">
                        <i class="bi bi-cloud-upload me-2"></i>Upload Evidence
                    </h5>
                    <a href="engagements.php?source=view&id=<?php echo $engagement_id; ?>" class="btn btn-sm btn-outline-light">
                        <i class="bi bi-arrow-left me-1"></i>Back to Engagement
                    </a>
                </div>
                <div class="card-body">
                    
                    <!-- Engagement Summary -->
                    <div class="engagement-summary mb-4">
                        <div class="row">
                            <div class="col-md-8">
                                <h6><?php echo htmlspecialchars($engagement['title']); ?></h6>
                                <p class="text-muted mb-0">Client: <?php echo htmlspecialchars($engagement['company_name']); ?></p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <span class="badge bg-<?php 
                                    echo $engagement['status'] == 'CLOSED' ? 'dark' : 
                                        ($engagement['status'] == 'SUBMITTED' ? 'success' : 
                                        ($engagement['status'] == 'AWAITING_REVIEW' ? 'warning' : 'primary')); 
                                ?>"><?php echo $engagement['status']; ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($message) && !$showSuccessModal): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <!-- START OF UPLOAD FORM -->
                    <form method="POST" action="" enctype="multipart/form-data" id="uploadForm">
                    
                    <!-- Checklist Section (for required engagement types) -->
                    <?php if ($requires_checklist): ?>
                    <div class="checklist-container mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-clipboard-check fs-4 me-2 text-primary"></i>
                            <h5 class="mb-0">Engagement Completion Checklist</h5>
                            <span class="badge bg-danger ms-2">Required for Submission</span>
                        </div>
                        <p class="text-muted small mb-3">Please complete all required fields before uploading evidence for this engagement.</p>
                        
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
                                            <input type="text" class="form-control" name="checklist_<?php echo $key; ?>" 
                                                   placeholder="Enter <?php echo strtolower($item['label']); ?>"
                                                   value="<?php echo htmlspecialchars($checklist_data[$key] ?? ''); ?>">
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
                                            <div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input radio-yes-no" type="radio" name="checklist_<?php echo $key; ?>_yn" id="<?php echo $key; ?>_yes" value="Yes" data-key="<?php echo $key; ?>" <?php echo $is_yes; ?>>
                                                    <label class="form-check-label" for="<?php echo $key; ?>_yes">Yes, done</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input radio-yes-no" type="radio" name="checklist_<?php echo $key; ?>_yn" id="<?php echo $key; ?>_no" value="No" data-key="<?php echo $key; ?>" <?php echo $is_no; ?>>
                                                    <label class="form-check-label" for="<?php echo $key; ?>_no">No</label>
                                                </div>
                                            </div>
                                            <div class="mt-2" id="<?php echo $key; ?>_date_container" style="display:<?php echo $is_yes ? 'block' : 'none'; ?>;">
                                                <input type="date" class="form-control" name="checklist_<?php echo $key; ?>_date" id="<?php echo $key; ?>_date" value="<?php echo htmlspecialchars($date_val); ?>" placeholder="Completion date" <?php echo $is_yes ? '' : 'disabled'; ?>>
                                                <small class="text-muted">Completion date (if "Yes")</small>
                                            </div>
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
                                            <div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input radio-yes-no" type="radio" name="checklist_<?php echo $key; ?>_yn" id="<?php echo $key; ?>_yes" value="Yes" data-key="<?php echo $key; ?>" <?php echo $is_yes; ?>>
                                                    <label class="form-check-label" for="<?php echo $key; ?>_yes">Yes, done</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input radio-yes-no" type="radio" name="checklist_<?php echo $key; ?>_yn" id="<?php echo $key; ?>_no" value="No" data-key="<?php echo $key; ?>" <?php echo $is_no; ?>>
                                                    <label class="form-check-label" for="<?php echo $key; ?>_no">No</label>
                                                </div>
                                            </div>
                                            <div class="mt-2" id="<?php echo $key; ?>_date_container" style="display:<?php echo $is_yes ? 'block' : 'none'; ?>;">
                                                <input type="date" class="form-control" name="checklist_<?php echo $key; ?>_date" id="<?php echo $key; ?>_date" value="<?php echo htmlspecialchars($date_val); ?>" placeholder="Completion date" <?php echo $is_yes ? '' : 'disabled'; ?>>
                                                <small class="text-muted">Completion date (if "Yes")</small>
                                            </div>
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
                                            <div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input radio-yes-no" type="radio" name="checklist_<?php echo $key; ?>_yn" id="<?php echo $key; ?>_yes" value="Yes" data-key="<?php echo $key; ?>" <?php echo $is_yes; ?>>
                                                    <label class="form-check-label" for="<?php echo $key; ?>_yes">Yes, done</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input radio-yes-no" type="radio" name="checklist_<?php echo $key; ?>_yn" id="<?php echo $key; ?>_no" value="No" data-key="<?php echo $key; ?>" <?php echo $is_no; ?>>
                                                    <label class="form-check-label" for="<?php echo $key; ?>_no">No</label>
                                                </div>
                                            </div>
                                            <div class="mt-2" id="<?php echo $key; ?>_date_container" style="display:<?php echo $is_yes ? 'block' : 'none'; ?>;">
                                                <input type="date" class="form-control" name="checklist_<?php echo $key; ?>_date" id="<?php echo $key; ?>_date" value="<?php echo htmlspecialchars($date_val); ?>" placeholder="Completion date" <?php echo $is_yes ? '' : 'disabled'; ?>>
                                                <small class="text-muted">Completion date (if "Yes")</small>
                                            </div>
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
                                            <?php if ($item['type'] == 'date'): ?>
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
                    <?php endif; ?>

                    <!-- Upload Area -->
                    <div class="upload-area" id="uploadArea">
                        <?php if ($engagement['status'] === 'CLOSED'): ?>
                            <div class="alert alert-warning text-center my-4">
                                <i class="bi bi-lock-fill me-2"></i>
                                This engagement is <strong>closed</strong>. Uploading new evidence is no longer allowed.
                            </div>
                        <?php endif; ?>
                        
                        <div class="upload-box text-center p-5" id="dropZone" style="<?php echo $engagement['status'] === 'CLOSED' ? 'pointer-events: none; opacity: 0.6;' : ''; ?>">
                            <i class="bi bi-cloud-arrow-up display-1 text-muted"></i>
                            <h5 class="mt-3">Drag & Drop Files Here</h5>
                            <p class="text-muted">or</p>
                            <label for="evidence_file" class="btn btn-primary <?php echo $engagement['status'] === 'CLOSED' ? 'disabled' : ''; ?>">
                                <i class="bi bi-folder2-open me-2"></i>Browse Files
                            </label>
                            <input type="file" id="evidence_file" name="evidence_file" style="display: none;" <?php echo $engagement['status'] === 'CLOSED' ? 'disabled' : ''; ?>>
                            <p class="text-muted small mt-3">
                                <i class="bi bi-info-circle me-1"></i>
                                Max file size: 50MB | Any file type allowed
                            </p>
                            <div id="fileInfo" class="mt-3 text-start" style="display: none;"></div>
                        </div>
                        <div class="text-center mt-4">
                            <button type="submit" name="upload_evidence" class="btn btn-success btn-lg" id="uploadBtn" <?php echo $engagement['status'] === 'CLOSED' ? 'disabled' : ''; ?>>
                                <i class="bi bi-cloud-upload me-2"></i>Upload File
                            </button>
                        </div>
                    </div>
                    
                    </form>
                    <!-- END OF UPLOAD FORM -->
                    
                </div>
            </div>

            <!-- Uploaded Files List -->
            <?php if ($evidence_result && mysqli_num_rows($evidence_result) > 0): ?>
            <div class="card shadow-sm">
                <div class="card-header dark-header">
                    <h5 class="card-title">
                        <i class="bi bi-files me-2"></i>Uploaded Files (<?php echo mysqli_num_rows($evidence_result); ?>)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="files-list">
                        <?php while($file = mysqli_fetch_assoc($evidence_result)): ?>
                        <div class="file-item">
                            <div class="file-icon">
                                <?php
                                $ext = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
                                if ($ext == 'pdf') {
                                    echo '<i class="bi bi-file-earmark-pdf text-danger"></i>';
                                } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    echo '<i class="bi bi-file-earmark-image text-success"></i>';
                                } elseif (in_array($ext, ['doc', 'docx'])) {
                                    echo '<i class="bi bi-file-earmark-word text-primary"></i>';
                                } else {
                                    echo '<i class="bi bi-file-earmark-text text-secondary"></i>';
                                }
                                ?>
                            </div>
                            <div class="file-info">
                                <div class="file-name"><?php echo htmlspecialchars($file['file_name']); ?></div>
                                <div class="file-meta">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar me-1"></i><?php echo date('M d, Y H:i', strtotime($file['uploaded_at'])); ?>
                                    </small>
                                </div>
                            </div>
                            <div class="file-actions">
                                <a href="../uploads/evidence/<?php echo $file['file_path']; ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="../uploads/evidence/<?php echo $file['file_path']; ?>" class="btn btn-sm btn-outline-success" download title="Download">
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right Column - Tips -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header dark-header">
                    <h5 class="card-title">
                        <i class="bi bi-info-circle me-2"></i>Upload Guidelines
                    </h5>
                </div>
                <div class="card-body">
                    <div class="guidelines-list">
                        <div class="guideline-item">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span>Clear, legible documents only</span>
                        </div>
                        <div class="guideline-item">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span>PDF format preferred for reports</span>
                        </div>
                        <div class="guideline-item">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span>Images should be high resolution</span>
                        </div>
                        <div class="guideline-item">
                            <i class="bi bi-exclamation-circle-fill text-warning me-2"></i>
                            <span>Max file size: 10MB per file</span>
                        </div>
                        <div class="guideline-item">
                            <i class="bi bi-exclamation-circle-fill text-warning me-2"></i>
                            <span>Do not upload sensitive personal data</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pro Tip Card -->
            <div class="pro-tip-card">
                <h6 class="text-white mb-3">
                    <i class="bi bi-lightbulb me-2"></i>
                    Pro Tip
                </h6>
                <p class="text-white-50 small">
                    <?php if ($engagement['status'] == 'AWAITING_REVIEW'): ?>
                        ⚡ You're about to submit for review. Make sure all required documents are uploaded before proceeding.
                    <?php elseif ($engagement['status'] == 'IN_PROGRESS'): ?>
                        🚀 Upload evidence as you complete tasks, don't wait until the end!
                    <?php else: ?>
                        📁 Organize your files with clear names like "VAT_Return_Q1_2024.pdf" for easy reference.
                    <?php endif; ?>
                </p>
                <hr class="border-white-50">
                <p class="text-white-50 small mb-0">
                    <i class="bi bi-question-circle me-1"></i>
                    Need help? <a href="support.php?source=new&subject=Evidence Upload Help" class="text-white">Contact Support</a>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for checklist radio button toggles -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to toggle date field visibility
    function toggleDateField(radio) {
        const key = radio.getAttribute('data-key');
        if (!key) return;
        
        const dateContainer = document.getElementById(key + '_date_container');
        const dateField = document.getElementById(key + '_date');
        
        if (dateContainer) {
            if (radio.checked && radio.value === 'Yes') {
                dateContainer.style.display = 'block';
                if (dateField) {
                    dateField.disabled = false;
                }
            } else if (radio.checked && radio.value === 'No') {
                dateContainer.style.display = 'none';
                if (dateField) {
                    dateField.value = '';
                    dateField.disabled = true;
                }
            }
        }
    }
    
    // Get all radio buttons with class 'radio-yes-no'
    const radioButtons = document.querySelectorAll('.radio-yes-no');
    
    // Add event listeners to all radio buttons
    radioButtons.forEach(function(radio) {
        radio.addEventListener('change', function() {
            toggleDateField(this);
        });
        
        // Initialize on page load
        if (radio.checked) {
            toggleDateField(radio);
        }
    });
    
    // File upload handling
    const fileInput = document.getElementById('evidence_file');
    const fileInfo = document.getElementById('fileInfo');
    const dropZone = document.getElementById('dropZone');
    
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                fileInfo.innerHTML = `
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-file-earmark-check me-2"></i>
                        Selected: ${file.name} (${fileSizeMB} MB)
                    </div>
                `;
                fileInfo.style.display = 'block';
            } else {
                fileInfo.style.display = 'none';
            }
        });
    }
    
    // Drag and drop functionality
    if (dropZone) {
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('border-primary', 'bg-light');
        });
        
        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('border-primary', 'bg-light');
        });
        
        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('border-primary', 'bg-light');
            const files = e.dataTransfer.files;
            if (files.length > 0 && fileInput) {
                fileInput.files = files;
                // Trigger change event
                const event = new Event('change');
                fileInput.dispatchEvent(event);
            }
        });
    }
});
</script>

<?php if ($showSuccessModal): ?>
<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Upload Successful!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-cloud-check-fill text-success" style="font-size: 4rem;"></i>
                <h5 class="mt-3">File Uploaded Successfully!</h5>
                <p class="text-muted">Your evidence has been saved and attached to this engagement.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <a href="engagements.php?source=upload_evidence&id=<?php echo $engagement_id; ?>" class="btn btn-success px-4">
                    <i class="bi bi-cloud-upload me-2"></i>Upload More
                </a>
                <a href="engagements.php?source=view&id=<?php echo $engagement_id; ?>" class="btn btn-outline-success px-4">
                    <i class="bi bi-eye me-2"></i>View Engagement
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
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e9ecef;
}

.checklist-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.dark-header {
    background: #1e293b;
    color: white;
}

.upload-box {
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-box:hover {
    border-color: #0d6efd;
    background-color: #f8f9fa;
}

.file-item {
    display: flex;
    align-items: center;
    padding: 12px;
    border-bottom: 1px solid #e9ecef;
    transition: background-color 0.2s ease;
}

.file-item:hover {
    background-color: #f8f9fa;
}

.file-icon {
    font-size: 2rem;
    margin-right: 15px;
    min-width: 40px;
    text-align: center;
}

.file-info {
    flex: 1;
}

.file-name {
    font-weight: 500;
    margin-bottom: 4px;
}

.file-actions {
    margin-left: 15px;
}

.file-actions .btn {
    margin-left: 5px;
}

.pro-tip-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 20px;
    color: white;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.guidelines-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.guideline-item {
    display: flex;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #e9ecef;
}

.guideline-item:last-child {
    border-bottom: none;
}
</style>