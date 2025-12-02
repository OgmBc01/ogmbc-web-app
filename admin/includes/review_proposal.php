<?php

// Debug: Check session
if(!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-danger'>Not logged in. User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set') . "</div>";
    echo "<div class='alert alert-info'>User Role: " . (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'not set') . "</div>";
    return;
}

// Debug: Check user role
$allowed_roles = ['manager', 'ceo', 'admin'];
if(!in_array($_SESSION['user_role'], $allowed_roles)) {
    echo "<div class='alert alert-danger'>Unauthorized access. Your role: " . $_SESSION['user_role'] . ". Allowed roles: " . implode(', ', $allowed_roles) . "</div>";
    return;
}

$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
$user_role = $_SESSION['user_role'];
$user_id = $_SESSION['user_id'];

// Debug: Check client_id
if($client_id <= 0) {
    echo "<div class='alert alert-danger'>Invalid client ID: $client_id</div>";
    return;
}

// Get client and latest proposal details - FIXED: Specify exact columns
$sql = "SELECT 
            c.client_id, c.company_name, c.contact_name, c.contact_email, c.contact_mobile, 
            c.contact_designation, c.address, c.country, c.client_status, c.service_total_fee, 
            c.payment_currency, c.payment_term, c.service_description,
            p.proposal_id, p.proposal_ref, p.version, p.file_path, p.prepared_at,
            cat.cat_title, 
            u.first_name, u.last_name
        FROM clients c 
        LEFT JOIN proposals p ON c.client_id = p.client_id 
        LEFT JOIN categories cat ON c.service_id = cat.cat_id 
        LEFT JOIN users u ON c.assigned_sales_id = u.user_id 
        WHERE c.client_id = ? 
        ORDER BY p.prepared_at DESC 
        LIMIT 1";

$stmt = $connection->prepare($sql);
if(!$stmt) {
    echo "<div class='alert alert-danger'>Database error: " . $connection->error . "</div>";
    return;
}

$stmt->bind_param("i", $client_id);
if(!$stmt->execute()) {
    echo "<div class='alert alert-danger'>Query execution failed: " . $stmt->error . "</div>";
    return;
}

$result = $stmt->get_result();

if (!$data = $result->fetch_assoc()) {
    echo "<div class='alert alert-danger'>Client or proposal not found for client ID: $client_id</div>";
    
    // Debug: Check if client exists
    $check_sql = "SELECT client_id, company_name, client_status FROM clients WHERE client_id = ?";
    $check_stmt = $connection->prepare($check_sql);
    $check_stmt->bind_param("i", $client_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if($check_client = $check_result->fetch_assoc()) {
        echo "<div class='alert alert-info'>Client exists: " . htmlspecialchars($check_client['company_name']) . " (Status: " . $check_client['client_status'] . ")</div>";
        
        // Check if proposal exists
        $prop_sql = "SELECT COUNT(*) as prop_count FROM proposals WHERE client_id = ?";
        $prop_stmt = $connection->prepare($prop_sql);
        $prop_stmt->bind_param("i", $client_id);
        $prop_stmt->execute();
        $prop_result = $prop_stmt->get_result();
        $prop_data = $prop_result->fetch_assoc();
        echo "<div class='alert alert-info'>Proposals found: " . $prop_data['prop_count'] . "</div>";
        $prop_stmt->close();
    } else {
        echo "<div class='alert alert-warning'>Client does not exist in database.</div>";
    }
    $check_stmt->close();
    return;
}

// Debug: Show retrieved data
echo "<!-- Debug: Client Status = " . htmlspecialchars($data['client_status']) . " -->";
echo "<!-- Debug: User Role = " . htmlspecialchars($user_role) . " -->";
echo "<!-- Debug: Proposal ID = " . ($data['proposal_id'] ?? 'not set') . " -->";

// Check if user can review based on current status
$can_review = false;
$current_status = $data['client_status'];

if ($user_role === 'manager' && $current_status === 'Proposal Drafted') {
    $can_review = true;
} elseif ($user_role === 'ceo' && $current_status === 'Manager Approved Proposal') {
    $can_review = true;
} elseif ($user_role === 'admin') {
    $can_review = true; // Admin can review any status
}

// Debug: Show review permission info
echo "<!-- Debug: Can Review = " . ($can_review ? 'YES' : 'NO') . " -->";

if (!$can_review) {
    echo "<div class='alert alert-warning'>You cannot review this proposal at this stage.<br>";
    echo "Your Role: " . htmlspecialchars($user_role) . "<br>";
    echo "Current Status: " . htmlspecialchars($current_status) . "<br>";
    echo "Required Status: ";
    if($user_role === 'manager') {
        echo "'Proposal Drafted'";
    } elseif($user_role === 'ceo') {
        echo "'Manager Approved Proposal'";
    }
    echo "</div>";
    return;
}

$stmt->close();
?>

<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <a href="clients.php" class="btn btn-outline-secondary me-3">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
                <h2 class="h4 mb-0">
                    <i class="bi bi-clipboard-check me-2"></i>
                    <?php echo ucfirst($user_role); ?> Review - Proposal
                </h2>
            </div>
            <div class="badge bg-info fs-6">
                Status: <?php echo htmlspecialchars($data['client_status']); ?>
            </div>
        </div>

        <!-- Client Summary -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Client Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <p><strong>Company:</strong> <?php echo htmlspecialchars($data['company_name']); ?></p>
                        <p><strong>Contact:</strong> <?php echo htmlspecialchars($data['contact_name']); ?></p>
                        <p><strong>Designation:</strong> <?php echo htmlspecialchars($data['contact_designation']); ?></p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($data['contact_email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($data['contact_mobile']); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($data['address']); ?></p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Country:</strong> <?php echo htmlspecialchars($data['country']); ?></p>
                        <p><strong>Service:</strong> <?php echo htmlspecialchars($data['cat_title']); ?></p>
                        <p><strong>Amount:</strong> <?php echo number_format($data['service_total_fee'], 2) . ' ' . $data['payment_currency']; ?></p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Payment Term:</strong> <span class="badge bg-info"><?php echo htmlspecialchars($data['payment_term']); ?></span></p>
                        <p><strong>Service Desc:</strong> <?php echo htmlspecialchars(substr($data['service_description'], 0, 100)) . (strlen($data['service_description']) > 100 ? '...' : ''); ?></p>
                        <p><strong>Sales Person:</strong> <?php echo htmlspecialchars($data['first_name'] . ' ' . $data['last_name']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Proposal Info -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Proposal Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <p><strong>Reference:</strong> <?php echo htmlspecialchars($data['proposal_ref'] ?? 'N/A'); ?></p>
                        <p><strong>Version:</strong> <?php echo htmlspecialchars($data['version'] ?? '1'); ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Prepared Date:</strong> <?php echo !empty($data['prepared_at']) ? date('M j, Y H:i', strtotime($data['prepared_at'])) : 'N/A'; ?></p>
                    </div>
                    <div class="col-md-4 text-end">
                        <?php if (!empty($data['file_path'])): ?>
                            <a href="<?php echo htmlspecialchars($data['file_path']); ?>" target="_blank" class="btn btn-outline-primary me-2">
                                <i class="bi bi-eye me-1"></i> View Full Proposal
                            </a>
                            <a href="<?php echo htmlspecialchars($data['file_path']); ?>" download class="btn btn-outline-success">
                                <i class="bi bi-download me-1"></i> Download
                            </a>
                        <?php else: ?>
                            <span class="text-danger">No proposal file available</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Proposal Preview -->
                <?php if (!empty($data['file_path'])): ?>
                <div class="mt-3 border rounded p-3">
                    <h6>Proposal Preview:</h6>
                    <iframe src="<?php echo htmlspecialchars($data['file_path']); ?>" width="100%" height="400" style="border: 1px solid #ddd;"></iframe>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Review Checklist -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="bi bi-checklist me-2"></i>Review Checklist</h5>
            </div>
            <!-- Progress Indicator -->
            <div class="mb-4">
                <div class="d-flex justify-content-between mb-2">
                    <span>Checklist Completion</span>
                    <span id="completionPercent">0%</span>
                </div>
                <div class="progress" style="height: 20px;">
                    <div id="completionBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                            role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small class="text-muted mt-2 d-block" id="completionMessage">
                    All checklist items must be checked before approval
                </small>
            </div>
            <div class="card-body">
                <form id="reviewChecklistForm">
                    <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
                    <input type="hidden" name="user_role" value="<?php echo $user_role; ?>">
                    <input type="hidden" name="proposal_id" value="<?php echo $data['proposal_id'] ?? 0; ?>">
                    
                    <!-- Company Details Section -->
                    <div class="checklist-section mb-4">
                        <h6 class="border-bottom pb-2 mb-3 text-primary">
                            <i class="bi bi-building me-1"></i>1. Company Details Verification
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input checklist-item" type="checkbox" name="checklist[]" value="company_name_correct" id="check1">
                                    <label class="form-check-label" for="check1">
                                        <strong>Company Name:</strong> Correct and properly spelled
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input checklist-item" type="checkbox" name="checklist[]" value="contact_details_correct" id="check2">
                                    <label class="form-check-label" for="check2">
                                        <strong>Contact Details:</strong> Person, email, phone are accurate
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input checklist-item" type="checkbox" name="checklist[]" value="address_complete" id="check3">
                                    <label class="form-check-label" for="check3">
                                        <strong>Address:</strong> Complete address is included
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input checklist-item" type="checkbox" name="checklist[]" value="country_specified" id="check4">
                                    <label class="form-check-label" for="check4">
                                        <strong>Country:</strong> Correctly specified
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Service Details Section -->
                    <div class="checklist-section mb-4">
                        <h6 class="border-bottom pb-2 mb-3 text-success">
                            <i class="bi bi-briefcase me-1"></i>2. Service Details Verification
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input checklist-item" type="checkbox" name="checklist[]" value="service_type_correct" id="check5">
                                    <label class="form-check-label" for="check5">
                                        <strong>Service Type:</strong> Matches client requirements
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input checklist-item" type="checkbox" name="checklist[]" value="description_clear" id="check6">
                                    <label class="form-check-label" for="check6">
                                        <strong>Description:</strong> Clear and complete
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input checklist-item" type="checkbox" name="checklist[]" value="scope_defined" id="check7">
                                    <label class="form-check-label" for="check7">
                                        <strong>Scope of Work:</strong> Well-defined
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input checklist-item" type="checkbox" name="checklist[]" value="start_date_realistic" id="check8">
                                    <label class="form-check-label" for="check8">
                                        <strong>Start Date:</strong> Realistic and appropriate
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Details Section -->
                    <div class="checklist-section mb-4">
                        <h6 class="border-bottom pb-2 mb-3 text-danger">
                            <i class="bi bi-cash-coin me-1"></i>3. Financial Details Verification
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input checklist-item" type="checkbox" name="checklist[]" value="amount_correct" id="check9">
                                    <label class="form-check-label" for="check9">
                                        <strong>Total Amount:</strong> Correctly calculated
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input checklist-item" type="checkbox" name="checklist[]" value="currency_correct" id="check10">
                                    <label class="form-check-label" for="check10">
                                        <strong>Currency:</strong> Correctly specified
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input checklist-item" type="checkbox" name="checklist[]" value="payment_term_appropriate" id="check11">
                                    <label class="form-check-label" for="check11">
                                        <strong>Payment Terms:</strong> Appropriate and clear
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input checklist-item" type="checkbox" name="checklist[]" value="breakdown_correct" id="check12">
                                    <label class="form-check-label" for="check12">
                                        <strong>Payment Breakdown:</strong> Accurate
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Document Quality Section -->
                    <div class="checklist-section mb-4">
                        <h6 class="border-bottom pb-2 mb-3 text-info">
                            <i class="bi bi-file-text me-1"></i>4. Document Quality Verification
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input checklist-item" type="checkbox" name="checklist[]" value="terms_complete" id="check13">
                                    <label class="form-check-label" for="check13">
                                        <strong>Terms & Conditions:</strong> Complete
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input checklist-item" type="checkbox" name="checklist[]" value="validity_period" id="check14">
                                    <label class="form-check-label" for="check14">
                                        <strong>Validity Period:</strong> Specified
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input checklist-item" type="checkbox" name="checklist[]" value="company_info_complete" id="check15">
                                    <label class="form-check-label" for="check15">
                                        <strong>OGM Info:</strong> Complete in document
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input checklist-item" type="checkbox" name="checklist[]" value="references_correct" id="check16">
                                    <label class="form-check-label" for="check16">
                                        <strong>References:</strong> All references are correct
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Notes -->
                    <div class="mb-4">
                        <label for="reviewNotes" class="form-label fw-bold">
                            <i class="bi bi-chat-text me-1"></i>Additional Review Notes
                        </label>
                        <textarea class="form-control" id="reviewNotes" name="review_notes" rows="3" 
                                  placeholder="Any additional comments, observations, or specific points to note..."></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between align-items-center border-top pt-4">
                        <div>
                            <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                                <i class="bi bi-arrow-left me-1"></i> Back to Clients
                            </button>
                        </div>
                        
                        <div class="btn-group">
                            <button type="button" class="btn btn-danger me-2" onclick="showRejectModal()">
                                <i class="bi bi-x-circle me-1"></i> Reject Proposal
                            </button>
                            <button type="button" class="btn btn-success" id="approveBtn" disabled onclick="showApproveModal()">
                                <i class="bi bi-check-circle me-1"></i> Approve Proposal
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i>Approve Proposal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to approve this proposal?</p>
                <p class="text-muted small">By approving, you confirm that all checklist items have been verified.</p>
                
                <?php if($user_role === 'manager'): ?>
                <div class="mb-3">
                    <label class="form-label">Manager's Signature</label>
                    <div class="signature-pad-container border rounded p-2">
                        <canvas id="managerSignatureCanvas" width="400" height="150" style="background: white;"></canvas>
                        <div class="mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearSignature()">
                                <i class="bi bi-eraser me-1"></i> Clear
                            </button>
                        </div>
                    </div>
                    <input type="hidden" id="managerSignatureData" name="manager_signature">
                </div>
                <?php elseif($user_role === 'ceo'): ?>
                <div class="mb-3">
                    <label class="form-label">CEO's Signature</label>
                    <div class="signature-pad-container border rounded p-2">
                        <canvas id="ceoSignatureCanvas" width="400" height="150" style="background: white;"></canvas>
                        <div class="mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearSignature()">
                                <i class="bi bi-eraser me-1"></i> Clear
                            </button>
                        </div>
                    </div>
                    <input type="hidden" id="ceoSignatureData" name="ceo_signature">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Company Stamp</label>
                    <select class="form-select" id="companyStampSelect" name="company_stamp">
                        <option value="">Select Company Stamp</option>
                        <?php
                        $stamps_query = "SELECT * FROM stamps WHERE is_active = 1 ORDER BY stamp_name";
                        $stamps_result = mysqli_query($connection, $stamps_query);
                        while($stamp = mysqli_fetch_assoc($stamps_result)) {
                            echo "<option value='{$stamp['stamp_id']}'>{$stamp['stamp_name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="submitApproval()">Confirm Approval</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Reject Proposal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Reason for Rejection *</label>
                    <textarea class="form-control" id="rejectReason" rows="4" placeholder="Please provide detailed reasons for rejection..." required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Action Required</label>
                    <select class="form-select" id="rejectAction">
                        <option value="revise">Needs Revision</option>
                        <option value="cancel">Cancel Proposal</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="submitRejection()">Confirm Rejection</button>
            </div>
        </div>
    </div>
</div>

<!-- Include Signature Pad library -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

<script>
let signaturePad;
let allCheckboxes = document.querySelectorAll('.checklist-item');
let approveBtn = document.getElementById('approveBtn');
let completionBar = document.getElementById('completionBar');
let completionPercent = document.getElementById('completionPercent');
let completionMessage = document.getElementById('completionMessage');

// Initialize Signature Pad
function initSignaturePad() {
    let canvas = null;
    <?php if($user_role === 'manager'): ?>
    canvas = document.getElementById('managerSignatureCanvas');
    <?php elseif($user_role === 'ceo'): ?>
    canvas = document.getElementById('ceoSignatureCanvas');
    <?php endif; ?>
    
    if(canvas) {
        signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'white',
            penColor: 'black',
            minWidth: 1,
            maxWidth: 3
        });
    }
}

// Calculate completion percentage and update UI
function updateCompletion() {
    let checkedCount = 0;
    allCheckboxes.forEach(checkbox => {
        if(checkbox.checked) checkedCount++;
    });
    
    let totalCount = allCheckboxes.length;
    let percentage = Math.round((checkedCount / totalCount) * 100);
    
    // Update progress bar
    if(completionBar) {
        completionBar.style.width = percentage + '%';
        completionBar.setAttribute('aria-valuenow', percentage);
    }
    
    if(completionPercent) {
        completionPercent.textContent = percentage + '%';
    }
    
    // Update button state
    if(approveBtn) {
        approveBtn.disabled = (percentage !== 100);
    }
    
    // Update message
    if(completionMessage) {
        if(percentage === 100) {
            completionMessage.innerHTML = '<span class="text-success"><i class="bi bi-check-circle me-1"></i>All checklist items verified! You can now approve.</span>';
            if(completionBar) {
                completionBar.classList.remove('bg-warning');
                completionBar.classList.add('bg-success');
            }
        } else {
            completionMessage.textContent = `${checkedCount} of ${totalCount} items checked. All items must be checked before approval.`;
            if(completionBar) {
                completionBar.classList.remove('bg-success');
                completionBar.classList.add('bg-warning');
            }
        }
    }
    
    return (percentage === 100);
}

// Show approve modal
function showApproveModal() {
    if(!updateCompletion()) {
        alert('Please check all checklist items before approving.');
        return;
    }
    
    // Initialize signature pad
    initSignaturePad();
    
    // Show modal
    const approveModalElement = document.getElementById('approveModal');
    if(approveModalElement) {
        const approveModal = new bootstrap.Modal(approveModalElement);
        approveModal.show();
    }
}

// Show reject modal
function showRejectModal() {
    const rejectModalElement = document.getElementById('rejectModal');
    if(rejectModalElement) {
        const rejectModal = new bootstrap.Modal(rejectModalElement);
        rejectModal.show();
    }
}

// Clear signature
function clearSignature() {
    if(signaturePad) {
        signaturePad.clear();
    }
}

// Submit approval - UPDATED VERSION
function submitApproval() {
    const clientId = <?php echo $client_id; ?>;
    const userRole = '<?php echo $user_role; ?>';
    const proposalId = <?php echo $data['proposal_id'] ?? 0; ?>;
    const reviewNotes = document.getElementById('reviewNotes') ? document.getElementById('reviewNotes').value : '';
    
    // Get checklist values
    const checklist = [];
    document.querySelectorAll('.checklist-item:checked').forEach(cb => {
        checklist.push(cb.value);
    });
    
    // Get signature data
    let signatureData = '';
    if(signaturePad && !signaturePad.isEmpty()) {
        signatureData = signaturePad.toDataURL();
    }
    
    // Get company stamp
    const companyStampSelect = document.getElementById('companyStampSelect');
    const companyStamp = companyStampSelect ? companyStampSelect.value : '';
    
    // Validate CEO signature and stamp
    if(userRole === 'ceo') {
        if(!signatureData) {
            alert('Please provide your signature before approving.');
            return;
        }
        if(!companyStamp) {
            alert('Please select a company stamp before approving.');
            return;
        }
    }
    
    // Validate Manager signature
    if(userRole === 'manager' && !signatureData) {
        alert('Please provide your signature before approving.');
        return;
    }
    
    // Submit via AJAX
    $.ajax({
        url: 'approve_proposal_ajax.php',
        type: 'POST',
        data: {
            client_id: clientId,
            proposal_id: proposalId,
            user_role: userRole,
            signature_data: signatureData,
            company_stamp: companyStamp,
            review_notes: reviewNotes,
            checklist: checklist
        },
        beforeSend: function() {
            // Show loading state
            const approveBtn = document.querySelector('#approveModal .btn-success');
            if(approveBtn) {
                approveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Approving...';
                approveBtn.disabled = true;
            }
        },
        success: function(response) {
            console.log('Approval Response:', response); // Debug log
            
            // Reset button state
            const approveBtn = document.querySelector('#approveModal .btn-success');
            if(approveBtn) {
                approveBtn.innerHTML = 'Confirm Approval';
                approveBtn.disabled = false;
            }
            
            try {
                const result = JSON.parse(response);
                if(result.success) {
                    // Close modal
                    const approveModal = bootstrap.Modal.getInstance(document.getElementById('approveModal'));
                    if(approveModal) {
                        approveModal.hide();
                    }
                    
                    // Show success message
                    alert('Proposal approved successfully! Status updated to: ' + result.new_status);
                    
                    // Redirect to clients page
                    setTimeout(() => {
                        window.location.href = 'clients.php';
                    }, 1500);
                    
                } else {
                    alert('Error: ' + result.message);
                }
            } catch(e) {
                console.error('Parse Error:', e, 'Response:', response);
                alert('Error parsing response. Check console for details.');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error, xhr.responseText);
            
            // Reset button state
            const approveBtn = document.querySelector('#approveModal .btn-success');
            if(approveBtn) {
                approveBtn.innerHTML = 'Confirm Approval';
                approveBtn.disabled = false;
            }
            
            alert('Request failed: ' + error + '\nCheck console for details.');
        }
    });
}

// Submit rejection - UPDATED VERSION
function submitRejection() {
    const clientId = <?php echo $client_id; ?>;
    const userRole = '<?php echo $user_role; ?>';
    const proposalId = <?php echo $data['proposal_id'] ?? 0; ?>;
    const rejectReasonElement = document.getElementById('rejectReason');
    const rejectActionElement = document.getElementById('rejectAction');
    const reviewNotesElement = document.getElementById('reviewNotes');
    
    const rejectReason = rejectReasonElement ? rejectReasonElement.value : '';
    const rejectAction = rejectActionElement ? rejectActionElement.value : '';
    const reviewNotes = reviewNotesElement ? reviewNotesElement.value : '';
    
    if(!rejectReason.trim()) {
        alert('Please provide a reason for rejection.');
        return;
    }
    
    // Get checklist values
    const checklist = [];
    document.querySelectorAll('.checklist-item:checked').forEach(cb => {
        checklist.push(cb.value);
    });
    
    // Confirm rejection
    if(!confirm('Are you sure you want to reject this proposal? This action cannot be undone.')) {
        return;
    }
    
    $.ajax({
        url: 'reject_proposal_ajax.php',
        type: 'POST',
        data: {
            client_id: clientId,
            proposal_id: proposalId,
            user_role: userRole,
            reject_reason: rejectReason,
            reject_action: rejectAction,
            review_notes: reviewNotes,
            checklist: checklist
        },
        beforeSend: function() {
            // Show loading state
            const rejectBtn = document.querySelector('#rejectModal .btn-danger');
            if(rejectBtn) {
                rejectBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Rejecting...';
                rejectBtn.disabled = true;
            }
        },
        success: function(response) {
            console.log('Rejection Response:', response); // Debug log
            
            // Reset button state
            const rejectBtn = document.querySelector('#rejectModal .btn-danger');
            if(rejectBtn) {
                rejectBtn.innerHTML = 'Confirm Rejection';
                rejectBtn.disabled = false;
            }
            
            try {
                const result = JSON.parse(response);
                if(result.success) {
                    // Close modal
                    const rejectModal = bootstrap.Modal.getInstance(document.getElementById('rejectModal'));
                    if(rejectModal) {
                        rejectModal.hide();
                    }
                    
                    // Show success message
                    alert('Proposal rejected successfully. Status updated to: ' + result.new_status);
                    
                    // Redirect to clients page
                    setTimeout(() => {
                        window.location.href = 'clients.php';
                    }, 1500);
                    
                } else {
                    alert('Error: ' + result.message);
                }
            } catch(e) {
                console.error('Parse Error:', e, 'Response:', response);
                alert('Error parsing response. Check console for details.');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error, xhr.responseText);
            
            // Reset button state
            const rejectBtn = document.querySelector('#rejectModal .btn-danger');
            if(rejectBtn) {
                rejectBtn.innerHTML = 'Confirm Rejection';
                rejectBtn.disabled = false;
            }
            
            alert('Request failed: ' + error + '\nCheck console for details.');
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Attach event listeners to checkboxes
    allCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateCompletion);
    });
    
    // Initial update
    updateCompletion();
    
    // Add event listener for Enter key in reject reason textarea
    const rejectReasonTextarea = document.getElementById('rejectReason');
    if(rejectReasonTextarea) {
        rejectReasonTextarea.addEventListener('keydown', function(e) {
            if(e.key === 'Enter' && e.ctrlKey) {
                e.preventDefault();
                submitRejection();
            }
        });
    }
});

// Handle modal hidden events
document.addEventListener('DOMContentLoaded', function() {
    const approveModal = document.getElementById('approveModal');
    const rejectModal = document.getElementById('rejectModal');
    
    if(approveModal) {
        approveModal.addEventListener('hidden.bs.modal', function() {
            // Clear signature pad when modal closes
            if(signaturePad) {
                signaturePad.clear();
            }
        });
    }
    
    if(rejectModal) {
        rejectModal.addEventListener('hidden.bs.modal', function() {
            // Clear reject reason when modal closes
            const rejectReason = document.getElementById('rejectReason');
            if(rejectReason) {
                rejectReason.value = '';
            }
        });
    }
});

// Add keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+Enter to submit approval (when in approve modal)
    if(e.ctrlKey && e.key === 'Enter') {
        const approveModal = document.getElementById('approveModal');
        if(approveModal && approveModal.classList.contains('show')) {
            e.preventDefault();
            submitApproval();
        }
    }
    
    // Escape to close modals
    if(e.key === 'Escape') {
        const activeModals = document.querySelectorAll('.modal.show');
        activeModals.forEach(modal => {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if(bsModal) {
                bsModal.hide();
            }
        });
    }
});
</script>